<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// Опция модуля настроек yii2-cms-config: способ вписывания превью в виджете управления
// изображениями (вкладка «Фотографии» карточки персоны). Значение применяется в
// Yii::$app->getModule('Person')->params['previewFit'] (см. ConfigApplier), откуда его
// читает вызов виджета в views/backend/person/view.php.
// range/items держать синхронно с константами Widget::PREVIEW_FIT_* пакета yii2-cms-images.
return [
    'person_preview_fit' => [
        'path'        => 'modules.Person.params.previewFit',
        'label'       => '[Персоны] Отображение превью изображений',
        'description' => "Yii::\$app->getModule('Person')->params['previewFit']: "
            . 'cover — заполнить ячейку с обрезкой краёв, contain — вписать изображение целиком',
        'category'    => 'Person',
        'rules'       => [
            ['required'],
            ['in', 'range' => ['cover', 'contain']],
        ],
        'inputOptions' => [
            'type'  => 'dropdown',
            'items' => [
                'cover'   => 'Заполнять ячейку (обрезка по краям)',
                'contain' => 'Вписывать целиком (поля по краям)',
            ],
        ],
    ],
];
