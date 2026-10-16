<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Person\forms\backend\search\PersonSearch;
use Besnovatyj\Person\grid\PersonGrid;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel PersonSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Актёры';
$this->params['breadcrumbs'][] = $this->title;
$this->params['mobileFiltersForm'] = $this->render('_search', ['model' => $searchModel]);

// Стили для htmx спиннера на кнопку "Пометить к удалению"
// (и на кнопки смены статуса — PersonGrid::htmxButton)
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
        'class' => PersonGrid::TABLE_CLASS,
    ],
    // Колонки — в PersonGrid: из них же контроллёр собирает строку для htmx-ответов
    'columns' => PersonGrid::columns($searchModel),
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
