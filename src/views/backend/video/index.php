<?php

use Besnovatyj\Person\assets\PersonVideoAsset;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\entities\person\PersonVideo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $person Person */

$this->title = 'Видеоролики: ' . $person->name;
$this->params['breadcrumbs'][] = ['label' => 'Все актёры', 'url' => ['/Person/backend/person/index']];
$this->params['breadcrumbs'][] = ['label' => $person->name, 'url' => ['/Person/backend/person/view', 'id' => $person->id]];
$this->params['breadcrumbs'][] = 'Видеоролики';

PersonVideoAsset::register($this);

$endpoints = [
    'add' => Url::to(['/Person/backend/video/add']),
    'delete' => Url::to(['/Person/backend/video/delete']),
    'reorder' => Url::to(['/Person/backend/video/reorder']),
    'verify' => Url::to(['/Person/backend/video/verify']),
    'refresh' => Url::to(['/Person/backend/video/refresh']),
];

?>

<p>
    <?= Html::a('&larr; Назад к актёру', ['/Person/backend/person/view', 'id' => $person->id], ['class' => 'btn btn-secondary']) ?>
</p>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Добавить видеоролик</h5>
    </div>
    <div class="card-body">
        <div class="input-group">
            <input type="text"
                   id="video-source-url"
                   class="form-control"
                   placeholder="Вставьте ссылку на видео (YouTube, Vimeo, VK, Rutube)..."
                   autocomplete="off">
            <button type="button" id="video-add-btn" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Добавить
            </button>
        </div>
        <div id="video-add-error" class="text-danger mt-2" style="display: none;"></div>
        <div id="video-add-loading" class="text-muted mt-2" style="display: none;">
            <span class="spinner-border spinner-border-sm"></span> Обработка...
        </div>
    </div>
</div>

<div id="video-grid"
     class="row g-3"
     data-person-id="<?= $person->id ?>"
     data-endpoints="<?= Html::encode(json_encode($endpoints)) ?>"
     data-csrf-param="<?= Yii::$app->request->csrfParam ?>"
     data-csrf-token="<?= Yii::$app->request->csrfToken ?>">

    <?php foreach ($person->videos as $video): ?>
        <div class="col-md-4 col-sm-6 video-card" data-video-id="<?= $video->id ?>">
            <div class="card h-100 <?= $video->isActive() ? '' : 'border-danger opacity-50' ?>">
                <div class="video-thumbnail-wrap">
                    <img src="<?= Html::encode($video->thumbnail_url) ?>"
                         class="card-img-top"
                         alt="<?= Html::encode($video->provider_type) ?>"
                         loading="lazy">
                </div>
                <div class="card-body p-2">
                    <span class="badge bg-secondary"><?= Html::encode($video->provider_type) ?></span>
                    <small class="d-block text-muted text-truncate mt-1"
                           title="<?= Html::encode($video->source_url) ?>">
                        <?= Html::encode($video->source_url) ?>
                    </small>
                </div>
                <div class="card-footer p-2 d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-info video-verify-btn"
                            title="Проверить работоспособность">
                        <i class="bi bi-check-circle"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary video-refresh-btn"
                            title="Обновить данные">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger video-delete-btn ms-auto"
                            title="Удалить">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="video-status-overlay" style="display: none;"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (count($person->videos) === 0): ?>
    <div id="video-empty-message" class="text-center text-muted py-5">
        <i class="bi bi-camera-video" style="font-size: 3rem;"></i>
        <p class="mt-2">Видеоролики ещё не добавлены</p>
    </div>
<?php else: ?>
    <div id="video-empty-message" class="text-center text-muted py-5" style="display: none;">
        <i class="bi bi-camera-video" style="font-size: 3rem;"></i>
        <p class="mt-2">Видеоролики ещё не добавлены</p>
    </div>
<?php endif; ?>
