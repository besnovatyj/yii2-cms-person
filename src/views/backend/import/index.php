<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $isEmpty bool */

$this->title = 'Импорт из OldPerson';
$this->params['breadcrumbs'][] = ['label' => 'Актёры', 'url' => ['/Person/backend/person/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><?= Html::encode($this->title) ?></h5>
    </div>
    <div class="card-body">
        <p>Импорт перенесёт данные из старого модуля <strong>OldPerson</strong> в новый модуль <strong>Person</strong>:</p>
        <ul>
            <li>Категории (с сохранением иерархии)</li>
            <li>Персоны (текстовые поля объединяются в описание)</li>
            <li>Характеристики (добавляются в описание)</li>
            <li>Фотографии (записи в БД + копирование файлов)</li>
        </ul>

        <?php if (!$isEmpty): ?>
            <div class="alert alert-warning">
                <strong>Внимание!</strong> Таблицы нового модуля уже содержат данные.
                Перед импортом необходимо очистить таблицы <code>person_persons</code>,
                <code>person_photos</code> и <code>person_categories</code>.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Таблицы нового модуля пусты. Импорт готов к запуску.
            </div>
            <?= Html::beginForm(['run'], 'POST') ?>
                <?= Html::submitButton('Запустить импорт', [
                    'class' => 'btn btn-primary',
                    'data-confirm' => 'Начать импорт данных из OldPerson? Операция может занять продолжительное время.',
                ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
</div>
