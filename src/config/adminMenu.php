<?php

declare(strict_types=1);

return [

    // Актёры
    [
        'label' => 'Актёры',
        'iconClass' => 'bi bi-person-badge me-1',
        'url' => ['/Person/backend/person/index'],
        'active' => static function () {
            return strpos(\Yii::$app->request->url, 'Person/backend/person');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Актёры',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 50,
                ],
            ],
        ],
    ],

    // Категории
    [
        'label' => 'Категории',
        'iconClass' => 'bi bi-diagram-3 me-1',
        'url' => ['/Person/backend/category/index'],
        'active' => static function () {
            return strpos(\Yii::$app->request->url, 'Person/backend/category');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Актёры',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 50,
                ],
            ],
        ],
    ],

    // Проверка видео
    [
        'label' => 'Проверка видео',
        'iconClass' => 'bi bi-camera-video me-1',
        'url' => ['/Person/backend/video/test-page'],
        'active' => static function () {
            return strpos(\Yii::$app->request->url, 'Person/backend/video');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Актёры',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 50,
                ],
            ],
        ],
    ],

    // Импорт из OldPerson
    [
        'label' => 'Импорт из OldPerson',
        'iconClass' => 'bi bi-download me-1',
        'url' => ['/Person/backend/import/index'],
        'active' => static function () {
            return strpos(\Yii::$app->request->url, 'Person/backend/import');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Актёры',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 50,
                ],
            ],
        ],
    ],

]; // PERSON
