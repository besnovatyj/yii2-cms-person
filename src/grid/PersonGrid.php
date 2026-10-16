<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\grid;

use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use Besnovatyj\DateTime\DateTimeRangeWidget;
use Besnovatyj\Kernel\security\AccessHelper;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\forms\backend\search\PersonSearch;
use Besnovatyj\Person\helpers\PersonHelper;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Колонки GridView списка персон в админке.
 *
 * Единственное место описания колонок: из него строится таблица в `views/backend/person/index.php`
 * и отдельная строка для htmx-ответов контроллёра ({@see renderRow()}), поэтому строка после
 * смены статуса/пометки к удалению по разметке совпадает с исходной.
 *
 * `data-label` задаётся на сервере: JS админки (`grid-view.ts`) проставляет подписи только при
 * загрузке страницы, а строки, подменённые htmx, без них потеряли бы подписи в мобильных карточках.
 */
final class PersonGrid
{
    /** Абсолютный маршрут контроллёра: строка рендерится и во вьюхе, и в htmx-ответе */
    private const ROUTE = '/Person/backend/person/';

    /** Классы таблицы: `table-mobile-cards` превращает строки в карточки на мобилке (grid-view.scss) */
    public const TABLE_CLASS = 'table table-hover no-margin table-mobile-cards';

    /**
     * Описание колонок.
     *
     * @param PersonSearch|null $searchModel модель фильтров; null — строка для htmx-ответа,
     *                                       фильтры не строятся (не нужны и стоят запросов в БД)
     */
    public static function columns(?PersonSearch $searchModel = null): array
    {
        $canActivate = AccessHelper::checkRoute(self::ROUTE . 'activate');
        $canDraft = AccessHelper::checkRoute(self::ROUTE . 'draft');

        return [
            [
                'label' => 'Фото',
                'value' => static function (Person $model) {
                    return $model->mainPhoto ? Html::img($model->mainPhoto->getThumbUrl('file', 'admin')) : null;
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'width: 100px', 'data-label' => 'Фото'],
            ],
            [
                'attribute' => 'name',
                'value' => function (Person $model) {
                    return Html::a(Html::encode($model->name), [self::ROUTE . 'view', 'id' => $model->id]);
                },
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'ФИО'],
            ],
            [
                'attribute' => 'birthday',
                'value' => function (Person $model) {
                    return $model->birthday;
                },
                // Режим без модели: скрытые поля без id — иначе они совпали бы по id с полями
                // того же виджета в мобильной форме фильтров (_search.php)
                'filter' => $searchModel ? DateTimeRangeWidget::widget([
                    'nameFrom' => Html::getInputName($searchModel, 'date_from'),
                    'nameTo' => Html::getInputName($searchModel, 'date_to'),
                    'valueFrom' => (string)$searchModel->date_from,
                    'valueTo' => (string)$searchModel->date_to,
                    'valueFormat' => 'Y-m-d',
                ]) : false,
                'format' => 'date',
                'contentOptions' => ['data-label' => 'Дата рождения'],
            ],
            [
                'attribute' => 'category_id',
                'filter' => $searchModel?->categoriesList() ?? false,
                'value' => 'category.name',
                'contentOptions' => ['data-label' => 'Категория, раздел'],
            ],
            [
                'attribute' => 'status',
                'filter' => $searchModel?->statusList() ?? false,
                'value' => static function (Person $model) use ($canActivate, $canDraft): string {
                    $badge = Html::tag('span', PersonHelper::statusName($model->status), [
                        'class' => match ($model->status) {
                            Person::STATUS_ACTIVE => 'badge bg-success',
                            Person::STATUS_PENDING_DELETE => 'badge bg-danger',
                            default => 'badge bg-secondary',
                        },
                    ]);
                    // Активная → в черновик; черновик → активна; помеченная к удалению → в черновик
                    $action = $model->isDraft() ? 'activate' : 'draft';
                    if (!($action === 'activate' ? $canActivate : $canDraft)) {
                        return $badge;
                    }
                    return self::htmxButton($action, $model, $badge, [
                        'class' => 'btn btn-link p-0 d-inline-flex align-items-center gap-1',
                        'title' => 'Сменить статус',
                    ], 'Сменить статус?');
                },
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'Статус'],
            ],
            [
                'attribute' => 'videosCount',
                'label' => 'Видео',
                'value' => function (Person $model) {
                    // videos_count считает подзапрос PersonSearch; в htmx-строке его нет — один COUNT
                    return $model->videos_count ?? (int)$model->getVideos()->count();
                },
                'format' => 'raw',
                'contentOptions' => ['data-label' => 'Видео'],
            ],
            [
                'class' => ActionColumn::class,
                // Имена кнопок совпадают с действиями контроллёра: AccessHelper проверяет права по ним
                'template' => AccessHelper::filterActionColumn(['view', 'update', 'activate', 'draft', 'mark-for-deletion']),
                'contentOptions' => ['data-label' => ''],
                'buttons' => [
                    'view' => static function (string $url, Person $model): string {
                        return Html::a(
                            '<i class="bi bi-eye"></i>',
                            [self::ROUTE . 'view', 'id' => $model->id],
                            ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Смотреть'],
                        );
                    },
                    'update' => static function (string $url, Person $model): string {
                        return Html::a(
                            '<i class="bi bi-pencil"></i>',
                            [self::ROUTE . 'update', 'id' => $model->id],
                            ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Изменить'],
                        );
                    },
                    'activate' => static function (string $url, Person $model): string {
                        if (!$model->isDraft()) return '';
                        return self::htmxButton('activate', $model, '<i class="bi bi-check-circle"></i>', [
                            'class' => 'btn btn-sm btn-outline-success',
                            'title' => 'Активировать',
                        ], 'Активировать «' . $model->name . '»?');
                    },
                    'draft' => static function (string $url, Person $model): string {
                        if ($model->isDraft()) return '';
                        return self::htmxButton('draft', $model, '<i class="bi bi-file-earmark"></i>', [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'В черновик',
                        ], 'Перевести «' . $model->name . '» в черновик?');
                    },
                    'mark-for-deletion' => static function (string $url, Person $model): string {
                        if ($model->isPendingDelete()) {
                            return Html::tag('span', '<i class="bi bi-trash"></i>', [
                                'class' => 'btn btn-sm btn-danger disabled',
                                'title' => 'Уже помечен к удалению',
                            ]);
                        }
                        return Html::a(
                            '<i class="bi bi-trash"></i>'.
                            '<span class="spinner-border spinner-border-sm htmx-indicator" role="status" aria-hidden="true"></span>',
                            '#',
                            [
                                'class' => 'btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1',
                                'title' => 'Пометить к удалению',
                                'hx-post' => Url::to([self::ROUTE . 'mark-for-deletion', 'id' => $model->id]),
                                'hx-target' => 'closest tr',
                                'hx-swap' => 'outerHTML swap:0.5s',
                                // 'hx-confirm' => 'Пометить «' . Html::encode($model->name) . '» к удалению?',
                                // Указываем htmx, что индикатор находится внутри ЭТОГО элемента
                                'hx-indicator' => 'this',
                            ],
                        );
                    },
                ],
            ]
        ];
    }

    /**
     * Строка таблицы для htmx-ответа: тот же GridView по одной записи, без фильтров.
     *
     * @param Person $person
     */
    public static function renderRow(Person $person): string
    {
        $grid = new GridView([
            'dataProvider' => new ArrayDataProvider(['allModels' => [$person], 'pagination' => false]),
            'columns' => self::columns(),
        ]);

        // Ключ строки как у ActiveDataProvider в index — первичный ключ (атрибут data-key)
        return $grid->renderTableRow($person, $person->id, 0);
    }

    /**
     * Кнопка htmx-действия над персоной: POST, ответ — обновлённая строка таблицы вместо текущей.
     * Ошибку ответа (4xx/5xx) показывает глобальный обработчик админки (`htmx-errors.ts`).
     *
     * @param string      $action  действие контроллёра
     * @param Person      $model
     * @param string      $content HTML содержимого кнопки
     * @param array       $options атрибуты кнопки
     * @param string|null $confirm текст подтверждения; null — без подтверждения
     */
    private static function htmxButton(string $action, Person $model, string $content, array $options, ?string $confirm = null): string
    {
        return Html::button(
            $content . '<span class="spinner-border spinner-border-sm htmx-indicator" role="status" aria-hidden="true"></span>',
            array_merge([
                'hx-post' => Url::to([self::ROUTE . $action, 'id' => $model->id]),
                'hx-target' => 'closest tr',
                'hx-swap' => 'outerHTML',
                // Индикатор — сама кнопка: спиннер внутри неё (стили во views/backend/person/index.php)
                'hx-indicator' => 'this',
                'hx-confirm' => $confirm,
            ], $options),
        );
    }
}
