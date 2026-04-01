<?php

use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\forms\backend\search\PersonSearch;
use Besnovatyj\Person\helpers\PersonHelper;
use yii\bootstrap5\LinkPager;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;

/* @var $this View */
/* @var $searchModel PersonSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Актёры';
$this->params['breadcrumbs'][] = $this->title;

$data = GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => "{summary}\n{items}",
    'columns' => [
        [
            'label' => 'Фото',
            'value' => static function (Person $model) {
                return $model->mainPhoto ? Html::img($model->mainPhoto->getThumbFileUrl('file', 'admin')) : null;
            },
            'format' => 'raw',
            'contentOptions' => ['style' => 'width: 100px'],
        ],
        'id',
        'birthday',
//                'description',
        [
            'attribute' => 'name',
            'value' => function (Person $model) {
                return Html::a(Html::encode($model->name), ['view', 'id' => $model->id]);
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'category_id',
            'filter' => $searchModel->categoriesList(),
            'value' => 'category.name',
        ],
        [
            'attribute' => 'status',
            'filter' => $searchModel->statusList(),
            'value' => function (Person $model) {
                return PersonHelper::statusLabel($model);
            },
            'format' => 'raw',
        ],
    ],
]);

$pager = LinkPager::widget([
    'pagination' => $dataProvider->getPagination(),
]);

?>

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
