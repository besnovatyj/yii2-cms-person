<?php

declare(strict_types=1);

namespace Besnovatyj\Person\thumbnails\providers;

use JsonException;

class RutubeProvider extends AbstractProvider
{
    protected const string HOST = 'rutube';
    protected array $regexes = [
        // https://rutube.ru/video/319bf9087d346da9cf10c830c9cbf99c/
        // https://rutube.ru/play/embed/319bf9087d346da9cf10c830c9cbf99c
        '/(?:https?:\/\/)?(?:www\.)?rutube\.ru\/(?:video|play\/embed)?\/([A-Za-z0-9]+)/i',
    ];

    /**
     * @throws JsonException
     */
    protected function resolveThumbnailUrl(string $match): string
    {
        // https://github.com/suth/video-thumbnails/blob/master/php/providers/class-rutube-thumbnails.php
        $url = strlen($match) < 32
            ? 'https://rutube.ru/api/oembed/?url=http%3A//rutube.ru/tracks/' . $match . '.html&format=json'
            : 'https://rutube.ru/api/video/' . $match . '/?format=json';

        $response = $this->curlGet(html_entity_decode($url));

        return json_decode($response, false, 512, JSON_THROW_ON_ERROR)->thumbnail_url;
    }

    protected function resolveIframeUrl(string $markup, string $match): string
    {
        // <iframe src="https://rutube.ru/play/embed/319bf9087d346da9cf10c830c9cbf99c" ></iframe>
        return 'https://rutube.ru/play/embed/' . $match;
    }
}
