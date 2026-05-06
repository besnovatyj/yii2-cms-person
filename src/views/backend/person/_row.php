<?php

/**
 * Partial view одной строки таблицы персон.
 *
 * Возвращается контроллером в ответ на htmx-запросы (заголовок HX-Request).
 * Структура столбцов должна совпадать с GridView в index.php.
 */

use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\helpers\PersonHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Person */
?>
<tr>
    <td style="width: 100px" data-label="Фото">
        <?= $model->mainPhoto ? Html::img($model->mainPhoto->getThumbUrl('file', 'admin')) : '' ?>
    </td>
    <td data-label="ФИО">
        <?= Html::a(Html::encode($model->name), Url::to(['view', 'id' => $model->id])) ?>
    </td>
    <td data-label="Дата рождения">
        <?= $model->birthday ? \Yii::$app->formatter->asDate($model->birthday) : '' ?>
    </td>
    <td data-label="Категория">
        <?= Html::encode($model->category->name ?? '') ?>
    </td>
    <td data-label="Статус">
        <?= PersonHelper::statusLabel($model) ?>
    </td>
    <td data-label="Видео">
        <?= count($model->videos) ?>
    </td>
    <td>
        <?= Html::a(
            '<i class="bi bi-eye"></i>',
            Url::to(['view', 'id' => $model->id]),
            ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Смотреть'],
        ) ?>
        <?= Html::a(
            '<i class="bi bi-pencil"></i>',
            Url::to(['update', 'id' => $model->id]),
            ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Изменить'],
        ) ?>
        <?php if ($model->isPendingDelete()): ?>
            <?= Html::tag('span', '<i class="bi bi-trash"></i>', [
                'class' => 'btn btn-sm btn-danger disabled',
                'title' => 'Уже помечен к удалению',
            ]) ?>
        <?php else: ?>
            <?= Html::a(
                '<i class="bi bi-trash"></i>',
                '#',
                [
                    'class' => 'btn btn-sm btn-outline-danger',
                    'title' => 'Пометить к удалению',
                    'hx-post' => Url::to(['mark-for-deletion', 'id' => $model->id]),
                    'hx-target' => 'closest tr',
                    'hx-swap' => 'outerHTML',
                    'hx-confirm' => 'Пометить «' . Html::encode($model->name) . '» к удалению?',
                ],
            ) ?>
        <?php endif; ?>
    </td>
</tr>
