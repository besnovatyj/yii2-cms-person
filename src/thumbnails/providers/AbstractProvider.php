<?php

declare(strict_types=1);

namespace Besnovatyj\Person\thumbnails\providers;

use Besnovatyj\Person\thumbnails\VideoData;

/**
 * Базовый класс провайдера видео.
 * Провайдер stateless — не хранит состояние между вызовами,
 * результат возвращается через VideoData.
 */
abstract class AbstractProvider
{
    protected string $cachePath = '@static/cache/Person/{id}/videoThumbnails';

    /**
     * Разбирает строку с URL/IFrame и возвращает VideoData, если провайдер подходит.
     */
    public function scanByRegexp(string $markup): ?VideoData
    {
        if (!$this->detectProvider($markup)) {
            return null;
        }
        foreach ($this->regexes as $regex) {
            if (preg_match($regex, $markup, $matches)) {
                return $this->buildVideoData($markup, $matches[1]);
            }
        }
        return null;
    }

    protected function detectProvider(string $markup): bool
    {
        return str_contains($markup, static::HOST);
    }

    /**
     * Строит VideoData по совпавшему match.
     */
    protected function buildVideoData(string $markup, string $match): VideoData
    {
        return new VideoData(
            iframeUrl: $this->resolveIframeUrl($markup, $match),
            thumbnailUrl: $this->resolveThumbnailUrl($match),
        );
    }

    abstract protected function resolveThumbnailUrl(string $match): string;

    abstract protected function resolveIframeUrl(string $markup, string $match): string;

    /**
     * Выполняет GET-запрос через нативный cURL.
     *
     * @param string $url
     * @param array<string, string> $headers Дополнительные заголовки вида ['Header-Name' => 'value']
     * @return string Тело ответа
     * @throws \RuntimeException при ошибке cURL или не-200 HTTP-статусе
     */
    protected function curlGet(string $url, array $headers = []): string
    {
        $ch = curl_init();

        $curlHeaders = [];
        foreach ($headers as $name => $value) {
            $curlHeaders[] = "$name: $value";
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => $curlHeaders,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($errno !== 0) {
            throw new \RuntimeException("cURL error $errno: $error");
        }
        if ($httpCode !== 200) {
            throw new \RuntimeException("HTTP $httpCode for $url");
        }

        return (string)$response;
    }
}
