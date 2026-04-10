<?php

declare(strict_types=1);

namespace Besnovatyj\Person\thumbnails;

/**
 * Иммутабельный объект-значение, содержащий данные одного видео:
 * ссылку для IFrame, URL превью и атрибуты iframe.
 */
readonly class VideoData
{
    /**
     * @param string $iframeUrl URL для iframe встраивания
     * @param string $thumbnailUrl URL превью видеоролика
     * @param string $iframeAllow Значение атрибута allow для iframe (Permissions Policy)
     * @param string $iframeReferrerPolicy Значение атрибута referrerpolicy для iframe
     */
    public function __construct(
        public string $iframeUrl,
        public string $thumbnailUrl,
        public string $iframeAllow = '',
        public string $iframeReferrerPolicy = '',
    ) {
    }
}
