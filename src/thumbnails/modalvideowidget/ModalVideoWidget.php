<?php

namespace Besnovatyj\Person\thumbnails\modalvideowidget;

use Besnovatyj\Person\thumbnails\modalvideowidget\assets\ModalVideoAsset;
use Besnovatyj\Person\thumbnails\VideoFactory;
use Yii;

class ModalVideoWidget extends \yii\base\Widget
{
    // Любые строки из которых можно извлечь ссылки на видео и превью
    public array $stringsWithUrl = [];
    // Идентификатор текущего актёра, нужен для кеширования превью
    public int $personId;
    // Массив объектов, которые могут вернуть айфрейм и превьюху
    private $videoObjects;

    public function __construct(VideoFactory $factory, $config = [])
    {
        parent::__construct($config);
        if (empty($this->stringsWithUrl)) {
            Yii::$app->session->setFlash('error', 'Недостаточно данных для отображения виджета.');
        }
        $this->videoObjects = $factory->getVideoDataFromStrings($this->stringsWithUrl);
    }

    public function run(): string
    {
        // https://codepen.io/JacobLett/pen/ExmqNLb
        ModalVideoAsset::register($this->getView());
        return $this->render('index', [
            'videoObjects' => $this->videoObjects,
        ]);
    }

}
