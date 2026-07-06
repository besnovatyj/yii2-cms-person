<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\forms\backend\search\CategorySearch;
use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CategorySearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Categories';
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?= Html::a('Create Category', ['create'], ['class' => 'btn  btn-success']) ?>
</p>

<div class="card">
    <div class="card-header">
        <?= $this->title ?>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{summary}\n{items}",
            'columns' => [
                [
                    'attribute' => 'name',
                    'value' => static function (Category $model) {
                        $indent = ($model->depth > 1 ? str_repeat('&bull;', $model->depth - 1) . ' ' : '');
                        return $indent . Html::a(Html::encode($model->name), ['view', 'id' => $model->id]);
                    },
                    'format' => 'raw',
                ],
                'slug',
                'status',
                'sort_order',
                ['class' => ActionColumn::class,
                    'template' => \Besnovatyj\User\components\Helper::filterActionColumn(['view',]),
                ],
            ],
        ]) ?>
    </div>
    <div class="card-footer">
        <div class="d-grid gap-2">
            <nav aria-label="" class="nav-pagination">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->getPagination(),
                ]) ?>
            </nav>
        </div>
    </div>
</div>
