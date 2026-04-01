<?php

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
        <?= Html::a('Деактивировать', ['draft', 'id' => $person->id], ['class' => 'btn  btn-warning', 'data-method' => 'post']) ?>
    <?php else: ?>
        <?= Html::a('Активировать', ['activate', 'id' => $person->id], ['class' => 'btn  btn-success', 'data-method' => 'post']) ?>
    <?php endif; ?>
    <?= Html::a('Редактировать', ['update', 'id' => $person->id], ['class' => 'btn  btn-primary']) ?>
    <?= Html::a(
        '<i class="bi bi-camera-video"></i> Управление видео (' . count($person->videos) . ')',
        ['/Person/backend/video/index', 'personId' => $person->id],
        ['class' => 'btn btn-primary']
    ) ?>
    <?= Html::a('Удалить', ['delete', 'id' => $person->id], [
        'class' => 'btn  btn-danger',
        'data'  => [
            'confirm' => 'Вы точно желаете удалить?',
            'method'  => 'post',
        ],
    ]) ?>
    <a class="btn btn-info" target="_blank" href="<?= $absoluteFrontendUrl ?>">
        <i class="bi bi-eye"></i>
    </a>
</p>

<div class="row">
    <div class="col-md-6">
        <h3 class="card-title">Общая информация</h3>
        <?= DetailView::widget([
            'model'      => $person,
            'attributes' => [
                'id',
                'name',
                'created_at:datetime',
                'birthday',
                'age',
                [
                    'attribute' => 'status',
                    'value'     => PersonHelper::statusLabel($person),
                    'format'    => 'raw',
                ],
                [
                    'attribute' => 'category_id',
                    'value'     => ArrayHelper::getValue($person, 'category.name'),
                ],
            ],
        ]) ?>

        <h3 class="card-title">Описание</h3>
        <?= Yii::$app->formatter->asHtml($person->description, [
            'Attr.AllowedRel'      => array('nofollow'),
            'HTML.SafeObject'      => true,
            'Output.FlashCompat'   => true,
            'HTML.SafeIframe'      => true,
            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
        ]) ?>

        <h3 class="card-title">SEO</h3>
        <?= DetailView::widget([
            'model'      => $person,
            'attributes' => [
                [
                    'attribute' => 'meta.title',
                    'label'     => 'Заголовок',
                    'value'     => $person->meta->title,
                ],
                [
                    'attribute' => 'meta.description',
                    'label'     => 'Описание',
                    'value'     => $person->meta->description,
                ],
                [
                    'attribute' => 'meta.keywords',
                    'label'     => 'Ключевые слова',
                    'value'     => $person->meta->keywords,
                ],
            ],
        ]) ?>
    </div>
    <div class="col-md-6">
        <h3 class="card-title">Главное фото</h3>
        <?php if ($person->mainPhoto): ?>
            <?= Html::a(
                Html::img($person->mainPhoto->getThumbFileUrl('file', 'thumb'), ['class' => 'img-fluid']),
                $person->mainPhoto->getUploadedFileUrl('file'),
                ['target' => '_blank']
            ) ?>
        <?php endif; ?>

        <h3 class="card-title">Видео</h3>
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
<div class="row" id="photos">
    <div class="col-md-12">
        <h3 class="card-title">Фотографии</h3>
        <?= Widget::widget([
            'ownerId'   => $person->id,
            'endpoints' => [
                'getImages'    => Url::to(['/Person/backend/person/get-images'], true),
                'setNewSort'   => Url::to(['/Person/backend/person/set-new-sort'], true),
                'upload'       => Url::to(['/Person/backend/person/add-image'], true),
                'deleteImage'  => Url::to(['/Person/backend/person/delete-image'], true),
                'setMainImage' => Url::to(['/Person/backend/person/set-main-image'], true),
            ],
        ]) ?>
    </div>
</div>
