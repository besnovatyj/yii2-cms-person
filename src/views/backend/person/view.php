<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Images\widgets\upload\Widget;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\helpers\PersonHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $person Person */
/* @var $absoluteFrontendUrl string */

$this->title = $person->name;
$this->params['breadcrumbs'][] = ['label' => 'Все актёры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
    <?php if ($person->isActive()): ?>
        <?= Html::a('Деактивировать', ['draft', 'id' => $person->id], ['class' => 'btn btn-warning mb-1', 'data-method' => 'post']) ?>
    <?php else: ?>
        <?= Html::a('Активировать', ['activate', 'id' => $person->id], ['class' => 'btn btn-success mb-1', 'data-method' => 'post']) ?>
    <?php endif; ?>
    <?= Html::a('Редактировать', ['update', 'id' => $person->id], ['class' => 'btn btn-primary mb-1']) ?>
    <?= Html::a(
        '<i class="bi bi-camera-video"></i> Управление видео (' . count($person->videos) . ')',
        ['/Person/backend/video/index', 'personId' => $person->id],
        ['class' => 'btn btn-primary mb-1']
    ) ?>
    <?= Html::a('Удалить', ['delete', 'id' => $person->id], [
        'class' => 'btn  btn-danger mb-1',
        'data' => [
            'confirm' => 'Вы точно желаете удалить?',
            'method' => 'post',
        ],
    ]) ?>
    <a class="btn btn-info mb-1" target="_blank" href="<?= $absoluteFrontendUrl ?>">
        <i class="bi bi-eye"></i>
    </a>
</p>

<div class="row">

    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">Общая информация</div>
            <div class="card-body">
                <?= DetailView::widget([
                    'model' => $person,
                    'attributes' => [
                        'id',
                        'name',
                        'birthday',
                        'age',
                        [
                            'attribute' => 'status',
                            'value' => PersonHelper::statusLabel($person),
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'category_id',
                            'value' => ArrayHelper::getValue($person, 'category.name'),
                        ],
                        'created_at:datetime',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Описание</div>
            <div class="card-body">
                <?= Yii::$app->formatter->asHtml($person->description, [
                    'Attr.AllowedRel' => array('nofollow'),
                    'HTML.SafeObject' => true,
                    'Output.FlashCompat' => true,
                    'HTML.SafeIframe' => true,
                    'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">Главное фото</div>
            <div class="card-body">
                <?php if ($person->mainPhoto): ?>
                    <?= Html::a(
                        Html::img($person->mainPhoto->getThumbUrl('file', 'thumb'), ['class' => 'img-fluid']),
                        $person->mainPhoto->getUploadUrl('file'),
                        ['target' => '_blank']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Видео</div>
            <div class="card-body">
                <?= Html::a(
                    '<i class="bi bi-camera-video"></i> Управление видео (' . count($person->videos) . ')',
                    ['/Person/backend/video/index', 'personId' => $person->id],
                    ['class' => 'btn btn-outline-primary btn-sm mb-3']
                ) ?>
                <?php if (count($person->videos) > 0): ?>
                    <div class="row g-2">
                        <?php foreach ($person->videos as $video): ?>
                            <div class="col-4">
                                <img class="img-fluid rounded"
                                     src="<?= Html::encode($video->thumbnail_url) ?>"
                                     alt="<?= Html::encode($video->provider_type) ?>"
                                     loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-md-flex justify-content-md-between">
                <div class="pt-1">SEO</div>
                <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-SEO" role="button"
                   aria-expanded="false" aria-controls="collapseSEO">
                    <i class="bi bi-plus-lg"></i>
                    <i class="bi bi-dash-lg"></i>
                </a>
            </div>
            <div class="collapse" id="collapse-SEO">
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $person,
                        'attributes' => [
                            [
                                'attribute' => 'meta.title',
                                'label' => 'Заголовок',
                                'value' => $person->meta->title,
                            ],
                            [
                                'attribute' => 'meta.description',
                                'label' => 'Описание',
                                'value' => $person->meta->description,
                            ],
                            [
                                'attribute' => 'meta.keywords',
                                'label' => 'Ключевые слова',
                                'value' => $person->meta->keywords,
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">Фотографии</div>
            <div class="card-body">
                <?= Widget::widget([
                    'ownerId' => $person->id,
                    'endpoints' => [
                        'getImages' => Url::to(['/Person/backend/person/get-images'], true),
                        'setNewSort' => Url::to(['/Person/backend/person/set-new-sort'], true),
                        'upload' => Url::to(['/Person/backend/person/add-image'], true),
                        'deleteImage' => Url::to(['/Person/backend/person/delete-image'], true),
                        'setMainImage' => Url::to(['/Person/backend/person/set-main-image'], true),
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>












