<?php

declare(strict_types=1);

namespace Besnovatyj\Person\services\import;

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\PersonVideo;
use Besnovatyj\Person\thumbnails\VideoFactory;
use common\NestedSetsCore\claude\NestedSetsColumnMap;
use common\NestedSetsCore\claude\NestedSetsRepository;
use common\NestedSetsCore\claude\NestedSetsService;
use Besnovatyj\Meta\Meta;
use RuntimeException;
use Throwable;
use Yii;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\Query;

/**
 * Сервис импорта данных из старого модуля OldPerson в новый Person.
 *
 * Этапы:
 * 1. Категории (через NestedSetsService для корректного дерева)
 * 2. Персоны (raw SQL insert)
 * 3. Фотографии (raw SQL insert + копирование файлов)
 */
class ImportService
{
    private DescriptionBuilder $descriptionBuilder;
    private VideoFactory $videoFactory;
    private Connection $db;

    /** @var array<int, int> old_category_id => new_category_id */
    private array $categoryMap = [];

    /** @var array<int, int> old_person_id => new_person_id */
    private array $personMap = [];

    /** @var array<int, int> old_photo_id => new_photo_id */
    private array $photoMap = [];

    public function __construct()
    {
        $this->descriptionBuilder = new DescriptionBuilder();
        $this->videoFactory = new VideoFactory();
        $this->db = Yii::$app->db;
    }

    /**
     * Запуск полного импорта.
     *
     * @return array Статистика: categories, persons, photos, photosCopied, errors
     */
    public function run(): array
    {
        $stats = [
            'categories'   => 0,
            'persons'      => 0,
            'photos'       => 0,
            'photosCopied' => 0,
            'videos'       => 0,
            'videoErrors'  => 0,
            'errors'       => [],
        ];

        $transaction = $this->db->beginTransaction();
        try {
            $stats['categories'] = $this->importCategories();
            $stats['persons'] = $this->importPersons();
            [$photos, $copied, $photoErrors] = $this->importPhotos();
            $stats['photos'] = $photos;
            $stats['photosCopied'] = $copied;
            $stats['errors'] = $photoErrors;

            $this->updateMainPhotoIds();

            [$videosImported, $videoErrors] = $this->importVideos();
            $stats['videos'] = $videosImported;
            $stats['videoErrors'] = $videoErrors;

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw new RuntimeException('Импорт не удался: ' . $e->getMessage(), 0, $e);
        }

        return $stats;
    }

    /**
     * Проверяет, пусты ли таблицы нового модуля.
     *
     * @return bool true если таблицы пусты и импорт безопасен
     */
    public function isNewModuleEmpty(): bool
    {
        $personsCount = (new Query())
            ->from('{{%person_persons}}')
            ->count('*', $this->db);

        $categoriesCount = (new Query())
            ->from('{{%person_categories}}')
            ->count('*', $this->db);

        $videosCount = (new Query())
            ->from('{{%person_videos}}')
            ->count('*', $this->db);

        return (int)$personsCount === 0 && (int)$categoriesCount === 0 && (int)$videosCount === 0;
    }

    // ==================== Категории ====================

    /**
     * Импорт категорий из old_person_categories в person_categories.
     *
     * @return int Количество импортированных категорий
     */
    private function importCategories(): int
    {
        $oldCategories = (new Query())
            ->from('{{%old_person_categories}}')
            ->orderBy(['lft' => SORT_ASC])
            ->all($this->db);

        $columns = new NestedSetsColumnMap('lft', 'rgt', 'depth', 'tree', 'sort_order');
        $nsRepo = new NestedSetsRepository(Category::class, $columns, $this->db);
        $nsService = new NestedSetsService($nsRepo);

        $count = 0;

        // Стек для определения родителя: [depth => new_category_id]
        $parentStack = [];

        foreach ($oldCategories as $oldCat) {
            $oldDepth = (int)$oldCat['depth'];

            // Пропускаем виртуальный ROOT (depth=0)
            if ($oldDepth === 0) {
                continue;
            }

            $category = $this->createCategoryEntity($oldCat);

            if ($oldDepth === 1) {
                // Корневой узел в новом multi-tree
                $newId = $nsService->createRoot($category);
                $parentStack[1] = $newId;
            } else {
                // Дочерний узел — родитель на уровне depth-1
                $parentNewId = $parentStack[$oldDepth - 1] ?? null;
                if ($parentNewId === null) {
                    throw new RuntimeException(
                        "Не найден родитель для категории '{$oldCat['name']}' (old_id={$oldCat['id']}, depth={$oldDepth})"
                    );
                }
                $parentNode = $nsRepo->findOne($parentNewId);
                $newId = $nsService->appendTo($parentNode, $category);
                $parentStack[$oldDepth] = $newId;
            }

            $this->categoryMap[(int)$oldCat['id']] = $newId;
            $count++;
        }

        return $count;
    }

    /**
     * Создаёт сущность Category из данных старой записи (без сохранения).
     *
     * @param array $oldCat Строка из old_person_categories
     * @return Category
     */
    private function createCategoryEntity(array $oldCat): Category
    {
        $metaData = json_decode((string)($oldCat['meta_json'] ?? '{}'), true) ?: [];

        return Category::create(
            name: (string)$oldCat['name'],
            slug: (string)$oldCat['slug'],
            description: (string)($oldCat['description'] ?? ''),
            meta: new Meta(
                (string)($metaData['title'] ?? ''),
                (string)($metaData['description'] ?? ''),
                (string)($metaData['keywords'] ?? ''),
            ),
        );
    }

    // ==================== Персоны ====================

    /**
     * Импорт персон из old_person_persons в person_persons.
     *
     * @return int Количество импортированных персон
     */
    private function importPersons(): int
    {
        // Предварительно загружаем все характеристики
        $characteristicsMap = $this->loadCharacteristicsMap();

        $oldPersons = new Query()
            ->from('{{%old_person_persons}}')
            ->orderBy(['id' => SORT_ASC])
            ->all($this->db);

        $count = 0;

        foreach ($oldPersons as $oldPerson) {
            $oldId = (int)$oldPerson['id'];

            // Маппинг category_id
            $oldCatId = (int)$oldPerson['category_id'];
            $newCatId = $this->categoryMap[$oldCatId] ?? null;
            if ($newCatId === null) {
                throw new RuntimeException(
                    "Категория old_id={$oldCatId} не найдена в маппинге для персоны '{$oldPerson['name']}' (old_id={$oldId})"
                );
            }

            // Характеристики персоны
            $characteristics = $characteristicsMap[$oldId] ?? [];

            // Собираем description
            $description = $this->descriptionBuilder->build($oldPerson, $characteristics);

            $this->db->createCommand()->insert('{{%person_persons}}', [
                'category_id' => $newCatId,
                'name'        => $oldPerson['name'],
                'birthday'    => $oldPerson['dateOfBirth'] ?: null,
                'description' => $description ?: null,
                'meta_json'   => $oldPerson['meta_json'] ?: null,
                'videos_json' => $oldPerson['videos_json'] ?: null,
                'main_photo_id' => null, // обновим позже
                'status'      => (int)$oldPerson['status'],
                'created_at'  => (int)$oldPerson['created_at'],
            ])->execute();

            $newId = (int)$this->db->getLastInsertID();
            $this->personMap[$oldId] = $newId;
            $count++;
        }

        return $count;
    }

    /**
     * Загружает все характеристики и значения, сгруппированные по person_id.
     *
     * @return array<int, array<array{name: string, value: string}>>
     */
    private function loadCharacteristicsMap(): array
    {
        $rows = (new Query())
            ->select(['v.person_id', 'c.name', 'v.value'])
            ->from(['v' => '{{%old_person_values}}'])
            ->innerJoin(['c' => '{{%old_person_characteristics}}'], 'v.characteristic_id = c.id')
            ->orderBy(['c.sort' => SORT_ASC])
            ->all($this->db);

        $map = [];
        foreach ($rows as $row) {
            $personId = (int)$row['person_id'];
            $map[$personId][] = [
                'name'  => $row['name'],
                'value' => $row['value'],
            ];
        }

        return $map;
    }

    // ==================== Фотографии ====================

    /**
     * Импорт фотографий: записи в БД + копирование файлов.
     *
     * @return array [int $dbCount, int $copiedCount, array $errors]
     */
    private function importPhotos(): array
    {
        $oldPhotos = (new Query())
            ->from('{{%old_person_photos}}')
            ->orderBy(['person_id' => SORT_ASC, 'sort' => SORT_ASC])
            ->all($this->db);

        $staticPath = Yii::getAlias('@static');
        $dbCount = 0;
        $copiedCount = 0;
        $errors = [];

        foreach ($oldPhotos as $oldPhoto) {
            $oldPersonId = (int)$oldPhoto['person_id'];
            $oldPhotoId = (int)$oldPhoto['id'];
            $file = (string)$oldPhoto['file'];

            $newPersonId = $this->personMap[$oldPersonId] ?? null;
            if ($newPersonId === null) {
                $errors[] = "Персона old_id={$oldPersonId} не найдена для фото old_id={$oldPhotoId}";
                continue;
            }

            // Вставка записи в БД
            $this->db->createCommand()->insert('{{%person_photos}}', [
                'person_id' => $newPersonId,
                'file'      => $file,
                'sort'      => (int)$oldPhoto['sort'],
            ])->execute();

            $newPhotoId = (int)$this->db->getLastInsertID();
            $this->photoMap[$oldPhotoId] = $newPhotoId;
            $dbCount++;

            // Копирование файла
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $oldFilePath = $staticPath . '/origin/OldPerson/' . $oldPersonId . '/' . $oldPhotoId . '.' . $extension;
            $newDir = $staticPath . '/origin/Person/' . $newPersonId;
            $newFilePath = $newDir . '/' . $newPhotoId . '.' . $extension;

            if (is_file($oldFilePath)) {
                if (!is_dir($newDir)) {
                    mkdir($newDir, 0775, true);
                }
                if (copy($oldFilePath, $newFilePath)) {
                    $copiedCount++;
                } else {
                    $errors[] = "Не удалось скопировать: {$oldFilePath} → {$newFilePath}";
                }
            } else {
                $errors[] = "Файл не найден: {$oldFilePath}";
            }
        }

        return [$dbCount, $copiedCount, $errors];
    }

    // ==================== Видеоролики ====================

    /**
     * Импорт видеороликов: парсит videos_json из импортированных персон
     * и заполняет таблицу person_videos пред вычисленными данными.
     *
     * @return array [int $imported, int $errors]
     * @throws Exception
     */
    private function importVideos(): array
    {
        $imported = 0;
        $errors = 0;

        $rows = new Query()
            ->select(['id', 'videos_json'])
            ->from('{{%person_persons}}')
            ->where(['not', ['videos_json' => null]])
            ->andWhere(['not', ['videos_json' => '[]']])
            ->andWhere(['not', ['videos_json' => '']])
            ->all($this->db);

        foreach ($rows as $row) {
            $personId = (int)$row['id'];
            $videos = json_decode($row['videos_json'], true);

            if (!is_array($videos)) {
                continue;
            }

            $sort = 0;
            foreach ($videos as $videoItem) {
                $srcString = $videoItem['srcString'] ?? '';
                if (empty($srcString)) {
                    continue;
                }

                $iframeUrl = '';
                $thumbnailUrl = '';
                $providerType = 'unknown';
                $status = PersonVideo::STATUS_ACTIVE;

                try {
                    $videoData = $this->videoFactory->getVideoDataByString($srcString);
                    if ($videoData !== null) {
                        $iframeUrl = $videoData->iframeUrl;
                        $thumbnailUrl = $videoData->thumbnailUrl;
                        $providerType = $this->detectProviderType($srcString);
                    } else {
                        $status = PersonVideo::STATUS_DISABLED;
                        $errors++;
                        Yii::warning(
                            "Импорт видео: не удалось распарсить для person_id=$personId: $srcString",
                            'import',
                        );
                    }
                } catch (Throwable $e) {
                    $status = PersonVideo::STATUS_DISABLED;
                    $errors++;
                    Yii::warning(
                        "Импорт видео: ошибка для person_id=$personId: {$e->getMessage()}",
                        'import',
                    );
                }

                $now = time();
                $this->db->createCommand()->insert('{{%person_videos}}', [
                    'person_id' => $personId,
                    'source_url' => $srcString,
                    'iframe_url' => $iframeUrl,
                    'thumbnail_url' => $thumbnailUrl,
                    'provider_type' => $providerType,
                    'sort' => $sort,
                    'status' => $status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->execute();

                $imported++;
                $sort++;
            }
        }

        return [$imported, $errors];
    }

    /**
     * Определяет тип провайдера по URL.
     */
    private function detectProviderType(string $url): string
    {
        if (str_contains($url, 'youtu')) {
            return PersonVideo::TYPE_YOUTUBE;
        }
        if (str_contains($url, 'vimeo')) {
            return PersonVideo::TYPE_VIMEO;
        }
        if (str_contains($url, 'rutube')) {
            return PersonVideo::TYPE_RUTUBE;
        }
        if (str_contains($url, 'vk')) {
            return PersonVideo::TYPE_VK;
        }
        return 'unknown';
    }

    // ==================== Прочее ====================

    /**
     * Обновляет main_photo_id у персон после импорта фотографий.
     */
    private function updateMainPhotoIds(): void
    {
        $oldPersons = new Query()
            ->select(['id', 'main_photo_id'])
            ->from('{{%old_person_persons}}')
            ->where(['not', ['main_photo_id' => null]])
            ->all($this->db);

        foreach ($oldPersons as $oldPerson) {
            $oldPersonId = (int)$oldPerson['id'];
            $oldMainPhotoId = (int)$oldPerson['main_photo_id'];

            $newPersonId = $this->personMap[$oldPersonId] ?? null;
            $newPhotoId = $this->photoMap[$oldMainPhotoId] ?? null;

            if ($newPersonId !== null && $newPhotoId !== null) {
                $this->db->createCommand()->update(
                    '{{%person_persons}}',
                    ['main_photo_id' => $newPhotoId],
                    ['id' => $newPersonId],
                )->execute();
            }
        }
    }
}
