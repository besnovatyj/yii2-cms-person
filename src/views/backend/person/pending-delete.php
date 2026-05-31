<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Person\entities\person\Person;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $persons Person[] */

$this->title = 'Удаление помеченных персон';
$this->params['breadcrumbs'][] = ['label' => 'Актёры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php if (empty($persons)): ?>
    <div class="alert alert-info">
        Нет персон, помеченных к удалению.
    </div>
    <p>
        <?= Html::a('← Назад к списку', ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>
<?php else: ?>
    <div class="alert alert-warning">
        <strong>Внимание!</strong> Следующие персоны будут удалены безвозвратно вместе со всеми фотографиями и видео.
        Это действие нельзя отменить.
    </div>

    <ul class="list-group mb-4">
        <?php foreach ($persons as $person): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= Html::encode($person->name) ?>
                <span class="text-muted small">ID: <?= $person->id ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <p class="d-flex gap-2">
        <?= Html::a('← Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
        <?= Html::a(
            '<i class="bi bi-trash"></i> Подтвердить удаление (' . count($persons) . ')',
            ['delete-pending'],
            [
                'class' => 'btn btn-danger',
                'data'  => [
                    'confirm' => 'Вы уверены? Удалить все помеченные персоны (' . count($persons) . ' шт.)?',
                    'method'  => 'post',
                ],
            ],
        ) ?>
    </p>
<?php endif; ?>
