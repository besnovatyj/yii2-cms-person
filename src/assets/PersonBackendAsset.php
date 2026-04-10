<?php

declare(strict_types=1);

namespace Besnovatyj\Person\assets;

use yii\web\AssetBundle;

/**
 * Ассеты для бэкенда модуля Person.
 *
 * Подключает htmx (CDN) и инициализационный скрипт с CSRF-хуком.
 * Чтобы перейти на локальный htmx — скачайте htmx.min.js в assets/media/
 * и замените CDN-строку на 'htmx.min.js'.
 */
class PersonBackendAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/media';

    public $js = [
//        'https://unpkg.com/htmx.org@2.0.4/dist/htmx.min.js',
        'htmx.min.js',
        'person-backend.js',
    ];
}
