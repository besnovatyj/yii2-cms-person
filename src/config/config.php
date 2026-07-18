<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

return [
    'id' => 'Person',
    'params' => [
        'iconClass' => 'bi bi-person-badge',

        /**
         * Способ отображения превью в виджете управления изображениями (вкладка «Фотографии»).
         * Значение переопределяется через модуль настроек yii2-cms-config (опция объявлена
         * в config/options.php) — дефолт здесь нужен, чтобы опции было «за что зацепиться».
         * Читается в views/backend/person/view.php при вызове виджета.
         * Одно из Besnovatyj\Images\widgets\upload\Widget::PREVIEW_FIT_* ('cover' | 'contain').
         */
        'previewFit' => 'cover',

        'directories' => true, // Если для работы модуля необходимы директории для статики
    ],
];
