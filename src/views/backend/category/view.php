<?php


use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\helpers\PersonHelper;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;

/* @var $this View */
/* @var $category Category */
/* @var $dataProvider ActiveDataProvider */

$this->title = $category->name;
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                Common
            </div>
            <div class="card-body">
                <?= DetailView::widget([
                    'model' => $category,
                    'attributes' => [
                        'id',
                        'name',
                        'slug',
                        'status',
                        'sort_order',
                    ],
                ]) ?>
            </div>
            <div class="card-footer clearfix"></div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                SEO
            </div>
            <div class="card-body">
                <?= DetailView::widget([
                    'model' => $category,
                    'attributes' => [
                        'meta.title',
                        'meta.description',
                        'meta.keywords',
                    ],
                ]) ?>
            </div>
            <div class="card-footer clearfix"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Description
    </div>
    <div class="card-body">
        <?= Yii::$app->formatter->asHtml($category->description, [
            'Attr.AllowedRel' => array('nofollow'),
            'HTML.SafeObject' => true,
            'Output.FlashCompat' => true,
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
        ]) ?>
    </div>
    <div class="card-footer clearfix"></div>
</div>

<div class="card">
    <div class="card-header">
        Актёры в категории
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{summary}\n{items}",
            'columns' => [
                'id',
                [
                    'label' => 'Фото',
                    'value' => static function (Person $model) {
                        return $model->mainPhoto ? Html::img($model->mainPhoto->getThumbUrl('file', 'admin')) : null;
                    },
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'width: 100px'],
                ],
                [
                    'attribute' => 'name',
                    'value' => function (Person $model) {
                        return Html::a(Html::encode($model->name), ['/Person/backend/person/view', 'id' => $model->id]);
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'status',
                    'value' => function (Person $model) {
                        return PersonHelper::statusLabel($model);
                    },
                    'format' => 'raw',
                    'contentOptions' => ['data-label' => 'Статус'],
                ],
            ],
        ]); ?>
    </div>
    <div class="card-footer clearfix">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
            'maxButtonCount' => 6,
            'options' => ['class' => 'mt-3'],
        ]); ?>
    </div>
</div>


