<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

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
        <div class="col-md-4 col-sm-6 video-card"
             data-video-id="<?= $video->id ?>"
             data-iframe-url="<?= Html::encode($video->iframe_url) ?>"
             data-iframe-allow="<?= Html::encode($video->iframe_allow) ?>"
             data-iframe-referrerpolicy="<?= Html::encode($video->iframe_referrerpolicy) ?>"
             data-source-url="<?= Html::encode($video->source_url) ?>">
            <div class="card h-100 <?= $video->isActive() ? '' : 'border-danger opacity-50' ?>">
                <div class="video-thumbnail-wrap video-play-btn" role="button" title="Смотреть видео">
                    <img src="<?= Html::encode($video->thumbnail_url) ?>"
                         class="card-img-top"
                         alt="<?= Html::encode($video->provider_type) ?>"
                         loading="lazy">
                    <div class="video-play-icon">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <span class="badge bg-secondary"><?= Html::encode($video->provider_type) ?></span>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary video-copy-btn p-0 px-1 border-0"
                                title="Скопировать исходную ссылку">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <small class="d-block text-muted text-truncate"
                           title="<?= Html::encode($video->source_url) ?>">
                        <?= Html::encode($video->source_url) ?>
                    </small>
                </div>
                <div class="card-footer p-2 d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary video-move-up-btn"
                            title="Переместить выше">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary video-move-down-btn"
                            title="Переместить ниже">
                        <i class="bi bi-arrow-down"></i>
                    </button>
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

<!-- Модальное окно просмотра видео -->
<div class="modal fade" id="video-preview-modal" tabindex="-1" aria-label="Просмотр видео" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Просмотр видео</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body p-0">
                <div class="video-preview-container">
                    <iframe id="video-preview-iframe"
                            src="about:blank"
                            frameborder="0"
                            allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast-уведомление для копирования -->
<div class="position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 1080;">
    <div id="video-copy-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-lg"></i> Ссылка скопирована
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрыть"></button>
        </div>
    </div>
</div>
