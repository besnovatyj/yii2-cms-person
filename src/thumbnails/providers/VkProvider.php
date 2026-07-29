<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\thumbnails\providers;

use Besnovatyj\Person\thumbnails\VideoData;
use Yii;

class VkProvider extends AbstractProvider
{
    protected const string HOST = 'vk';

    /** Категория лога для диагностики парсинга VK. */
    protected const string LOG_CATEGORY = 'Besnovatyj\Person\thumbnails\vk';

    /**
     * User-Agent краулера: VK отдаёт Open Graph-мета (og:image/og:video) только
     * распознанным ботам, обычному браузеру возвращается SPA-оболочка без мета-тегов.
     */
    protected const string CRAWLER_USER_AGENT = 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)';

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
        // Клипы VK (vk.ru/clip-XXX_YYY) не содержат ни <link itemprop="embedUrl">,
        // ни блока .video_box_msg_background — данные берём из Open Graph страницы клипа.
        if (str_contains($match, 'clip')) {
            return $this->buildClipVideoData($match);
        }

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

            if ($iframeUrlWithHash === null) {
                Yii::warning(
                    "VK: не найден <link itemprop=\"embedUrl\"> на странице \"$match\" "
                    . '(длина ответа: ' . strlen((string)$response) . ')',
                    self::LOG_CATEGORY,
                );
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
        if ($previewDiv === null) {
            Yii::warning(
                "VK: не найден блок .video_box_msg_background по URL \"$url\" "
                . '(из embedUrl: ' . ($iframeUrlWithHash !== null ? 'да' : 'нет') . ', '
                . 'длина ответа: ' . strlen((string)$response) . ')',
                self::LOG_CATEGORY,
            );
            throw new \RuntimeException("VK: не удалось получить превью видео (блок превью не найден) по URL $url");
        }
        // background-image:url(https://i.mycdn.me/getVideoPreview?...)
        preg_match('#(https://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i', $previewDiv->getAttribute('style'), $matches);
        if (!isset($matches[1])) {
            Yii::warning(
                "VK: не удалось извлечь URL превью из style блока по URL \"$url\": "
                . '"' . $previewDiv->getAttribute('style') . '"',
                self::LOG_CATEGORY,
            );
            throw new \RuntimeException("VK: не удалось извлечь URL превью из разметки VK по URL $url");
        }
        return $matches[1];
    }

    /**
     * Строит VideoData для клипа VK по данным Open Graph страницы клипа.
     * iframe берётся из og:video (там уже embed-ссылка video_ext.php с hash),
     * при её отсутствии собирается из oid/id ссылки клипа; превью — из og:image.
     *
     * @param string $match //vk.ru/clip-211352495_456239182
     */
    protected function buildClipVideoData(string $match): VideoData
    {
        $response = $this->curlGet(html_entity_decode("https:$match"), [
            'User-Agent' => self::CRAWLER_USER_AGENT,
        ]);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($response);
        libxml_use_internal_errors($internalErrors);
        $xpath = new \DOMXPath($dom);

        // og:video обычно содержит //vk.ru/video_ext.php?oid=...&id=...&hash=...
        $iframeUrl = $this->ogContent($xpath, 'og:video:iframe')
            ?? $this->ogContent($xpath, 'og:video:url')
            ?? $this->ogContent($xpath, 'og:video')
            ?? $this->buildClipEmbedUrl($match);
        if ($iframeUrl !== null && str_starts_with($iframeUrl, '//')) {
            $iframeUrl = 'https:' . $iframeUrl;
        }

        $thumbnailUrl = $this->ogContent($xpath, 'og:image')
            ?? $this->ogContent($xpath, 'og:image:url')
            ?? $this->ogContent($xpath, 'twitter:image');
        if ($thumbnailUrl === null) {
            Yii::warning(
                "VK: не найден og:image на странице клипа \"$match\" (длина ответа: "
                . strlen($response) . '). Доступные meta: ' . $this->collectMetaNames($xpath),
                self::LOG_CATEGORY,
            );
            throw new \RuntimeException("VK: не удалось получить превью клипа (og:image) со страницы https:$match");
        }

        if ($iframeUrl === null || $iframeUrl === '') {
            throw new \RuntimeException("VK: не удалось определить embed-ссылку клипа со страницы https:$match");
        }

        return new VideoData(
            iframeUrl: $iframeUrl,
            thumbnailUrl: $thumbnailUrl,
            iframeAllow: $this->getIframeAllow(),
            iframeReferrerPolicy: $this->getIframeReferrerPolicy(),
        );
    }

    /**
     * Собирает embed-ссылку клипа из его oid/id, когда og:video отсутствует.
     * clip-211352495_456239182 → https://vk.ru/video_ext.php?oid=-211352495&id=456239182
     */
    protected function buildClipEmbedUrl(string $match): ?string
    {
        if (!preg_match('#clip(\-?[0-9]+)_([0-9]+)#', $match, $m)) {
            return null;
        }
        return "https://vk.ru/video_ext.php?oid=$m[1]&id=$m[2]";
    }

    /**
     * Возвращает content мета-тега по property или name
     * (<meta property="og:image" content="..."> / <meta name="twitter:image" content="...">) или null.
     */
    protected function ogContent(\DOMXPath $xpath, string $property): ?string
    {
        $node = $xpath->query("//meta[@property='$property' or @name='$property']/@content")->item(0);
        if ($node === null || $node->nodeValue === '') {
            return null;
        }
        return html_entity_decode($node->nodeValue);
    }

    /**
     * Собирает список property/name всех мета-тегов страницы — для диагностики,
     * когда ожидаемый og-тег не найден. Возвращает не более 40 имён.
     */
    protected function collectMetaNames(\DOMXPath $xpath): string
    {
        $names = [];
        foreach ($xpath->query('//meta[@property or @name]') as $meta) {
            /** @var \DOMElement $meta */
            $names[] = $meta->getAttribute('property') ?: $meta->getAttribute('name');
        }
        return $names === [] ? '(мета-тегов нет)' : implode(', ', array_slice($names, 0, 40));
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
