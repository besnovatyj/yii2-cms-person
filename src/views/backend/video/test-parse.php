<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Person\assets\PersonVideoAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */

$this->title = 'Проверка парсинга видео';
$this->params['breadcrumbs'][] = $this->title;

PersonVideoAsset::register($this);

$testParseEndpoint = Url::to(['/Person/backend/video/test-parse']);

?>

<div class="text-danger border border-warning m-1 p-2">TODO - Сделать массовую проверку всех видео и выводить ссылки на
    актёров у которых видео не грузится
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Проверка парсинга URL видеоролика</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Введите ссылку на видеоролик для проверки работоспособности парсинга.
            Данные не сохраняются в базу.
        </p>
        <div class="input-group">
            <input type="text"
                   id="test-parse-url"
                   class="form-control"
                   placeholder="Вставьте ссылку на видео..."
                   autocomplete="off">
            <button type="button"
                    id="test-parse-btn"
                    class="btn btn-primary"
                    data-endpoint="<?= Html::encode($testParseEndpoint) ?>"
                    data-csrf-param="<?= Yii::$app->request->csrfParam ?>"
                    data-csrf-token="<?= Yii::$app->request->csrfToken ?>">
                <i class="bi bi-search"></i> Проверить
            </button>
        </div>
        <div id="test-parse-loading" class="text-muted mt-2" style="display: none;">
            <span class="spinner-border spinner-border-sm"></span> Обработка...
        </div>
        <div id="test-parse-error" class="alert alert-danger mt-3" style="display: none;"></div>
    </div>
</div>

<div id="test-parse-result" class="card" style="display: none;">
    <div class="card-header">
        <h5 class="card-title mb-0">Результат</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Превью</h6>
                <img id="test-result-thumbnail" src="" class="img-fluid rounded" alt="Превью">
                <div class="mt-2">
                    <small class="text-muted">URL:</small>
                    <code id="test-result-thumbnail-url" class="d-block text-break"></code>
                </div>
            </div>
            <div class="col-md-6">
                <h6>Iframe</h6>
                <div class="ratio ratio-16x9">
                    <iframe id="test-result-iframe"
                            src=""
                            frameborder="0"
                            allowfullscreen
                            allow="autoplay; encrypted-media"></iframe>
                </div>
                <div class="mt-2">
                    <small class="text-muted">URL:</small>
                    <code id="test-result-iframe-url" class="d-block text-break"></code>
                </div>
            </div>
        </div>
    </div>
</div>
