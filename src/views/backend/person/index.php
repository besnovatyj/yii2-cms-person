<?php

use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\forms\backend\search\PersonSearch;
use Besnovatyj\Person\helpers\PersonHelper;
use yii\bootstrap5\LinkPager;
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
                return $model->mainPhoto ? Html::img($model->mainPhoto->getThumbFileUrl('file', 'admin')) : null;
            },
            'format' => 'raw',
            'contentOptions' => ['style' => 'width: 100px'],
        ],
        [
            'attribute' => 'name',
            'value' => function (Person $model) {
                return Html::a(Html::encode($model->name), ['view', 'id' => $model->id]);
            },
            'format' => 'raw',
            'contentOptions' => ['data-label' => 'ФИО'],
        ],
//        'birthday',
        [
            'attribute' => 'birthday',
            'value' => function (Person $model) {
                return $model->birthday;
            },
            'filter' => \common\widgets\datetime\src\DateTimeRangeWidget::widget([
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
            'contentOptions' => ['data-label' => 'Категория'],
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
        'id',
    ],
]);

$pager = LinkPager::widget([
    'pagination' => $dataProvider->getPagination(),
    'maxButtonCount' => 6,
    'options' => ['class' => 'mt-3'],
]);

?>

<div class="text-danger border border-warning m-1 p-2">TODO - Сделать флаг удаления и страницу удаления помеченных</div>

<p>
    <?= Html::a('Добавить', ['create'], ['class' => 'btn  btn-success']) ?>
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
