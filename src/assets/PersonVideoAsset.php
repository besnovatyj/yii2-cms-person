<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\assets;

use yii\web\AssetBundle;

/**
 * Ассеты для управления видеороликами актёров.
 */
class PersonVideoAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/media';

    public $js = [
        'person-video.js',
    ];

    public $css = [
        'person-video.css',
    ];
}
