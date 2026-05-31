<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

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
                    'group' => 'Person',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Дерево категорий
    [
        'label' => 'Дерево категорий',
        'iconClass' => 'bi bi-diagram-3 me-1',
        'url' => ['/Person/backend/category/index'],
        'active' => static function () {
            return strpos(\Yii::$app->request->url, 'Person/backend/category/index');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Person',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Список категорий
    [
        'label' => 'Список категорий',
        'iconClass' => 'bi bi-list-ul me-1',
        'url' => ['/Person/backend/category/list'],
        'active' => static function () {
            return (bool)preg_match('#Person/backend/category/(list|view)#', Yii::$app->request->url);
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Person',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 100,
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
                    'group' => 'Person',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 100,
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
                    'group' => 'Person',
                    'groupIcon' => 'bi bi-person-badge',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

]; // PERSON
