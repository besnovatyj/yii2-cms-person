<?php

use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\forms\backend\search\PersonSearch;
use Besnovatyj\Person\helpers\PersonHelper;
use yii\bootstrap5\LinkPager;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $searchModel PersonSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Актёры';
$this->params['breadcrumbs'][] = $this->title;
$this->params['mobileFiltersForm'] = $this->render('_search', ['model' => $searchModel]);

// Стили для htmx спиннера на кнопку "Пометить к удалению"
$this->registerCss('
/* По умолчанию скрываем спиннер */
.htmx-indicator {
    display: none;
}

/* Когда кнопка в состоянии htmx-request, показываем спиннер */
.htmx-request .htmx-indicator {
    display: inline-block;
}

/* Опционально: скрываем иконку мусорки, пока крутится спиннер,
   чтобы кнопка не растягивалась */
.htmx-request i {
    display: none;
}
');

$data = GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
//    'layout' => "{summary}\n{items}",
    'layout' => "
        <div class='d-flex justify-content-between align-items-center mb-3'>
            {summary}
            <button class='btn btn-primary d-md-none' type='button' data-bs-toggle='offcanvas' data-bs-target='#offcanvasFilters'>
                <i class='bi bi-funnel'></i> Фильтры
            </button>
        </div>
        <div class='table-responsive'>{items}</div>
    ",
//    'options' => ['class' => 'grid-view table-responsive'],
    'tableOptions' => [
        'class' => 'table table-hover no-margin table-mobile-cards'
    ],
    'columns' => [
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
                return Html::a(Html::encode($model->name), ['view', 'id' => $model->id]);
            },
            'format' => 'raw',
            'contentOptions' => ['data-label' => 'ФИО'],
        ],
        [
            'attribute' => 'birthday',
            'value' => function (Person $model) {
                return $model->birthday;
            },
            'filter' => \Besnovatyj\DateTime\DateTimeRangeWidget::widget([
                'model' => $searchModel,
                'attributeFrom' => 'date_from',
                'attributeTo' => 'date_to',
                'valueFormat' => 'Y-m-d',
            ]),
            'format' => 'date',
            'contentOptions' => ['data-label' => 'Дата рождения'],
        ],
        [
            'attribute' => 'category_id',
            'filter' => $searchModel->categoriesList(),
            'value' => 'category.name',
            'contentOptions' => ['data-label' => 'Категория, раздел'],
        ],
        [
            'attribute' => 'status',
            'filter' => $searchModel->statusList(),
            'value' => function (Person $model) {
                return PersonHelper::statusLabel($model);
            },
            'format' => 'raw',
            'contentOptions' => ['data-label' => 'Статус'],
        ],
        [
            'attribute' => 'videosCount',
            'label' => 'Видео',
            'value' => function (Person $model) {
                return count($model->videos);
            },
            'format' => 'raw',
            'contentOptions' => ['data-label' => 'Видео'],
        ],
        [
            'class' => '\backend\widgets\grid\ActionColumn',
            'template' => '{view} {update} {mark-for-deletion}',
            'buttons' => [
                'view' => static function (string $url, Person $model): string {
                    return Html::a(
                        '<i class="bi bi-eye"></i>',
                        ['view', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Смотреть'],
                    );
                },
                'update' => static function (string $url, Person $model): string {
                    return Html::a(
                        '<i class="bi bi-pencil"></i>',
                        ['update', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Изменить'],
                    );
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
                            'hx-post' => Url::to(['mark-for-deletion', 'id' => $model->id]),
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
    ],
]);

$pager = LinkPager::widget([
    'pagination' => $dataProvider->getPagination(),
    'maxButtonCount' => 6,
    'options' => ['class' => 'mt-3'],
]);

?>

<p class="d-flex gap-2">
    <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
    <?= Html::a(
        '<i class="bi bi-trash"></i> Удалить подготовленные',
        ['pending-delete'],
        ['class' => 'btn btn-outline-danger'],
    ) ?>
</p>

<div class="card">
    <div class="card-header">
        <nav aria-label="" class="nav-pagination">
            <?= $pager ?>
        </nav>
    </div>
    <div class="card-body">
        <?= $data ?>
    </div>
    <div class="card-footer">
        <nav aria-label="" class="nav-pagination">
            <?= $pager ?>
        </nav>
    </div>
</div>
