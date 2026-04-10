<?php

declare(strict_types=1);

namespace Besnovatyj\Person\thumbnails\providers;

class VimeoProvider extends AbstractProvider
{
    protected const string HOST = 'vimeo';
    // https://stackoverflow.com/questions/10488943/easy-way-to-get-vimeo-id-from-a-vimeo-url
    protected array $regexes = [
        // https://vimeo.com/672693509
        // https://player.vimeo.com/video/672693509?h=fbb0496092

        '~(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?]?.*~i', //до добавления `?:` было = 5
//        '~(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*~i', //6
    ];

    protected function resolveThumbnailUrl(string $match): string
    {
        // https://github.com/vimeo/vimeo-oembed-examples
        // https://developer.vimeo.com/api/oembed/videos
        $url = 'https://vimeo.com/api/oembed.xml?url=https://vimeo.com/' . $match . '&width=640&height=480';
        $response = $this->curlGet(html_entity_decode($url));

        return (string)simplexml_load_string($response)->thumbnail_url;
    }

    protected function resolveIframeUrl(string $markup, string $match): string
    {
        // <iframe src="https://player.vimeo.com/video/672693509" ></iframe>
        if (str_contains($markup, 'player.vimeo.com/video')) {
            // for https://player.vimeo.com/video/672693509?h=fbb0496092
            return $markup;
        }
        // for https://vimeo.com/672693509
        return 'https://player.vimeo.com/video/' . $match;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIframeAllow(): string
    {
        return 'autoplay; fullscreen; picture-in-picture';
    }
}
