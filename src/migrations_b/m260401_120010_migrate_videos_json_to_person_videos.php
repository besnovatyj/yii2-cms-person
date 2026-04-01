<?php

declare(strict_types=1);

namespace Besnovatyj\Person\migrations;

use Besnovatyj\Person\entities\person\PersonVideo;
use Besnovatyj\Person\thumbnails\VideoFactory;
use common\components\migration\BaseMigration;
use Yii;

/**
 * Оставить только нужные миграции в папке и запустить
 * `docker compose exec php-fpm /app/yii migrate m260401_120000_create_person_videos_table --migrationNamespaces="Besnovatyj\Person\migrations"`
 */

/**
 *
 * Миграция данных из videos_json в таблицу person_videos.
 *
 * Не удаляет колонку videos_json — это делается отдельной миграцией
 * после проверки корректности перенесённых данных.
 */
class m260401_120010_migrate_videos_json_to_person_videos extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $factory = new VideoFactory();

        $rows = (new \yii\db\Query())
            ->select(['id', 'videos_json'])
            ->from('{{%person_persons}}')
            ->where(['not', ['videos_json' => null]])
            ->andWhere(['not', ['videos_json' => '[]']])
            ->andWhere(['not', ['videos_json' => '']])
            ->all();

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
                    $videoData = $factory->getVideoDataByString($srcString);
                    if ($videoData !== null) {
                        $iframeUrl = $videoData->iframeUrl;
                        $thumbnailUrl = $videoData->thumbnailUrl;
                        $providerType = $this->detectProviderType($srcString);
                    } else {
                        $status = PersonVideo::STATUS_DISABLED;
                        Yii::warning(
                            "Не удалось распарсить видео для person_id=$personId: $srcString",
                            'migration',
                        );
                    }
                } catch (\Throwable $e) {
                    $status = PersonVideo::STATUS_DISABLED;
                    Yii::warning(
                        "Ошибка парсинга видео для person_id=$personId: {$e->getMessage()}",
                        'migration',
                    );
                }

                $now = time();
                $this->insert('{{%person_videos}}', [
                    'person_id' => $personId,
                    'source_url' => $srcString,
                    'iframe_url' => $iframeUrl,
                    'thumbnail_url' => $thumbnailUrl,
                    'provider_type' => $providerType,
                    'sort' => $sort,
                    'status' => $status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $sort++;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->delete('{{%person_videos}}');
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
}
