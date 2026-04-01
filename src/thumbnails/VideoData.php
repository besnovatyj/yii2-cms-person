<?php

declare(strict_types=1);

namespace Besnovatyj\Person\thumbnails;

/**
 * Иммутабельный объект-значение, содержащий данные одного видео:
 * ссылку для IFrame и URL превью.
 */
readonly class VideoData
{
    public function __construct(
        public string $iframeUrl,
        public string $thumbnailUrl,
    ) {
    }
}
