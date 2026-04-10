<?php

declare(strict_types=1);

namespace Besnovatyj\Person\services\manage;

use Besnovatyj\Person\entities\person\PersonVideo;
use Besnovatyj\Person\repositories\PersonRepository;
use Besnovatyj\Person\thumbnails\VideoData;
use Besnovatyj\Person\thumbnails\VideoFactory;
use DomainException;
use RuntimeException;
use Yii;
use yii\db\Expression;

/**
 * Сервис управления видеороликами актёров.
 *
 * Отвечает за добавление, удаление, переупорядочивание,
 * проверку и обновление видеороликов.
 */
class PersonVideoService
{
    public function __construct(
        private readonly VideoFactory $videoFactory,
        private readonly PersonRepository $persons,
    ) {}

    /**
     * Добавляет видеоролик к актёру.
     *
     * @throws DomainException если URL не распознан
     */
    public function addVideo(int $personId, string $sourceUrl): PersonVideo
    {
        $person = $this->persons->get($personId);

        $videoData = $this->videoFactory->getVideoDataByString($sourceUrl);
        if ($videoData === null) {
            throw new DomainException('Не удалось распознать URL видео.');
        }

        $providerType = $this->detectProviderType($sourceUrl);

        $maxSort = (int)PersonVideo::find()
            ->where(['person_id' => $person->id])
            ->max('sort');

        $video = PersonVideo::create(
            $person->id,
            $sourceUrl,
            $videoData->iframeUrl,
            $videoData->thumbnailUrl,
            $providerType,
            $maxSort + 1,
            $videoData->iframeAllow,
            $videoData->iframeReferrerPolicy,
        );

        if (!$video->save()) {
            throw new RuntimeException('Ошибка сохранения видеоролика.');
        }

        return $video;
    }

    /**
     * Удаляет видеоролик.
     */
    public function removeVideo(int $videoId): void
    {
        $video = $this->getVideo($videoId);
        if (!$video->delete()) {
            throw new RuntimeException('Ошибка удаления видеоролика.');
        }
    }

    /**
     * Переупорядочивает видеоролики актёра.
     *
     * @param int $personId
     * @param int[] $orderedIds Массив ID видеороликов в нужном порядке
     */
    public function reorderVideos(int $personId, array $orderedIds): void
    {
        $this->persons->get($personId);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($orderedIds as $sort => $videoId) {
                PersonVideo::updateAll(
                    ['sort' => $sort, 'updated_at' => time()],
                    ['id' => (int)$videoId, 'person_id' => $personId],
                );
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Проверяет актуальность данных видеоролика.
     *
     * @return array{current_iframe: string, new_iframe: string|null, current_thumbnail: string, new_thumbnail: string|null, match: bool}
     */
    public function verifyVideo(int $videoId): array
    {
        $video = $this->getVideo($videoId);
        $videoData = $this->videoFactory->getVideoDataByString($video->source_url);

        return [
            'current_iframe' => $video->iframe_url,
            'new_iframe' => $videoData?->iframeUrl,
            'current_thumbnail' => $video->thumbnail_url,
            'new_thumbnail' => $videoData?->thumbnailUrl,
            'match' => $videoData !== null
                && $videoData->iframeUrl === $video->iframe_url
                && $videoData->thumbnailUrl === $video->thumbnail_url,
        ];
    }

    /**
     * Обновляет предвычисленные данные видеоролика (перепарсивает source_url).
     *
     * @throws DomainException если URL больше не распознаётся
     */
    public function refreshVideo(int $videoId): PersonVideo
    {
        $video = $this->getVideo($videoId);
        $videoData = $this->videoFactory->getVideoDataByString($video->source_url);

        if ($videoData === null) {
            throw new DomainException('Не удалось распознать URL видео при обновлении.');
        }

        $video->refreshData(
            $videoData->iframeUrl,
            $videoData->thumbnailUrl,
            $videoData->iframeAllow,
            $videoData->iframeReferrerPolicy,
        );

        if (!$video->save()) {
            throw new RuntimeException('Ошибка сохранения видеоролика.');
        }

        return $video;
    }

    /**
     * Тестирует парсинг URL без сохранения в БД.
     */
    public function testParseUrl(string $url): ?VideoData
    {
        return $this->videoFactory->getVideoDataByString($url);
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

    /**
     * Находит видеоролик по ID или выбрасывает исключение.
     */
    private function getVideo(int $videoId): PersonVideo
    {
        $video = PersonVideo::findOne($videoId);
        if ($video === null) {
            throw new DomainException('Видеоролик не найден.');
        }
        return $video;
    }
}
