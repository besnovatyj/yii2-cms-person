<?php

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\forms\backend\search\CategorySearch;
use backend\widgets\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
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

<div class="card rounded-0">
    <div class="card-header">
        <h3 class="card-title"><?= $this->title ?></h3>
    </div>
    <!-- /.card-header -->
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
                    'template' => \modules\user\components\Helper::filterActionColumn(['view',]),
                ],
            ],
        ]) ?>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <nav aria-label="" class="nav-pagination">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->getPagination(),
            ]) ?>
        </nav>
    </div>
</div>
