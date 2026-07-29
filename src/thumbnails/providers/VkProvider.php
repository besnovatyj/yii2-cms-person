<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\thumbnails\providers;

use Besnovatyj\Person\thumbnails\VideoData;

class VkProvider extends AbstractProvider
{
    protected const string HOST = 'vk';

    protected array $regexes = [
        // //vk.ru/video_ext.php?oid=-26052787&id=456239576&hash=12661f74200a6f7a - должно получиться это (запрашиваем страницу и парсим thumbnail)
        '#(//(?:www\.)?vk\.ru/video_ext\.php\?oid=\-?[0-9]+(?:&|&\#038;|&amp;)id=\-?[0-9]+(?:&|&\#038;|&amp;)hash=[0-9a-zA-Z]+)#i',
        // https://vk.ru/video-26052787_456239576
        '#(//(?:www\.)?vk\.ru/video\-?[0-9_]+)#i',
        // https://vk.ru/clip-211352495_456239182
        '#(//(?:www\.)?vk\.ru/clip\-?[0-9_]+)#i',
    ];

    /**
     * Переопределяем buildVideoData, чтобы передать iframeUrlWithHash в оба метода,
     * избегая хранения состояния в свойствах объекта.
     */
    protected function buildVideoData(string $markup, string $match): VideoData
    {
        $iframeUrlWithHash = null;

        if (!str_contains($match, 'hash')) {
            // Если полученная ссылка является просто ссылкой на видео
            // Ищем: <link itemprop="embedUrl" href="...">
            $response = $this->curlGet(html_entity_decode("https:$match"), [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:122.0) Gecko/20100101 Firefox/122.0',
            ]);
            if ($response) {
                $document = new \DOMDocument('1.0', 'UTF-8');
                $internalErrors = libxml_use_internal_errors(true);
                $document->loadHTML($response);
                libxml_use_internal_errors($internalErrors);
                $metas = $document->getElementsByTagName('link');
                for ($i = 0; $i < $metas->length; $i++) {
                    $meta = $metas->item($i);
                    if ($meta->getAttribute('itemprop') === 'embedUrl') {
                        // https://vk.com/video_ext.php?oid=-26052787&amp;id=456239576&amp;hash=12661f74200a6f7a
                        $iframeUrlWithHash = $meta->getAttribute('href');
                        break;
                    }
                }
            }
        }

        return new VideoData(
            iframeUrl: $this->resolveIframeUrl($markup, $match, $iframeUrlWithHash),
            thumbnailUrl: $this->resolveThumbnailUrl($match, $iframeUrlWithHash),
            iframeAllow: $this->getIframeAllow(),
            iframeReferrerPolicy: $this->getIframeReferrerPolicy(),
        );
    }

    /**
     * @param string|null $iframeUrlWithHash URL с hash, если был спарсен со страницы VK
     */
    protected function resolveThumbnailUrl(string $match, ?string $iframeUrlWithHash = null): string
    {
        // Если ссылку спарсили из IFrame - $match = //vk.com/video_ext.php?oid=-26052787&id=456239576&hash=...
        $url = html_entity_decode($iframeUrlWithHash ?: "https:$match");
        $response = $this->curlGet($url);

        $dom = new \DOMDocument('1.0', 'windows-1251');
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($response);
        libxml_use_internal_errors($internalErrors);
        $finder = new \DomXPath($dom);
        $nodes = $finder->query("//*[contains(@class, 'video_box_msg_background')]");
        $previewDiv = $nodes->item(0);
        // background-image:url(https://i.mycdn.me/getVideoPreview?...)
        preg_match('#(https://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i', $previewDiv->getAttribute('style'), $matches);
        return $matches[1] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getIframeAllow(): string
    {
        return 'autoplay; fullscreen; encrypted-media';
    }

    protected function resolveIframeUrl(string $markup, string $match, ?string $iframeUrlWithHash = null): string
    {
        // <iframe src="https://vk.com/video_ext.php?oid=-26052787&id=456239576&hash=12661f74200a6f7a" ></iframe>
        if (str_contains($match, 'hash')) {
            // Ссылку спарсили из IFrame
            return 'https:' . $match;
        }
        // Полученная ссылка является просто ссылкой на видео
        return $iframeUrlWithHash ?? '';
    }
}
