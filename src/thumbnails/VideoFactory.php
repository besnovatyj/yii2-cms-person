<?php

declare(strict_types=1);

namespace Besnovatyj\Person\thumbnails;

use Exception;
use Besnovatyj\Person\thumbnails\providers\AbstractProvider;
use Besnovatyj\Person\thumbnails\providers\RutubeProvider;
use Besnovatyj\Person\thumbnails\providers\VimeoProvider;
use Besnovatyj\Person\thumbnails\providers\VkProvider;
use Besnovatyj\Person\thumbnails\providers\YoutubeProvider;
use Yii;
use yii\helpers\VarDumper;

/**
 * Фабрика видео-данных. // TODO Что кешировать и КОГДА обновлять кеш?
 * Принимает провайдеры через конструктор (OCP — расширяем без изменения класса).
 */
class VideoFactory
{
    /** @var AbstractProvider[] */
    protected array $providers;

    /**
     * @param AbstractProvider[]|null $providers Список провайдеров. Если null — используется стандартный набор.
     */
    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?? [
            new RutubeProvider(),
            new VimeoProvider(),
            new VkProvider(),
            new YoutubeProvider(),
        ];
    }

    /**
     * Принимает массив строк с URL/IFrame, возвращает массив VideoData.
     *
     * @param string[] $stringsWithUrl
     * @return VideoData[]
     */
    public function getVideoDataFromStrings(array $stringsWithUrl): array
    {
        $result = [];
        try {
            foreach ($stringsWithUrl as $stringWithUrl) {
                $videoData = $this->getVideoDataByString($stringWithUrl);
                if ($videoData !== null) {
                    $result[] = $videoData;
                }
            }
        } catch (Exception $e) {
            Yii::$app->errorHandler->logException($e);
            Yii::$app->session->setFlash('error', YII_DEBUG ? VarDumper::dumpAsString($e->getMessage()) : 'Ошибка');
        }
        return $result;
    }

    /**
     * Прогоняет строку по всем провайдерам, возвращает VideoData первого совпадения.
     */
    public function getVideoDataByString(string $stringWithUrl): ?VideoData
    {
        foreach ($this->providers as $provider) {
            $videoData = $provider->scanByRegexp($stringWithUrl);
            if ($videoData !== null) {
                return $videoData;
            }
        }
        return null;
    }
}
