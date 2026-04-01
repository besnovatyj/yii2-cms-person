<?php

namespace Besnovatyj\Person\thumbnails\modalvideowidget\assets;

use yii\web\AssetBundle;

class ModalVideoAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/media';

    public $css = [
        'modalvideo.css',
    ];

    public $js = [
        'modalvideo.js',
    ];

    public $depends = [
//        \core\adminlte\assets\JqueryAsset::class,
//        \core\adminlte\assets\BootstrapPluginAsset::class,
    ];

}
