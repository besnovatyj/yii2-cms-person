<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Person\forms\backend\search\PersonSearch;
use Besnovatyj\DateTime\DateTimeRangeWidget;
use yii\bootstrap5\ActiveForm;
use yii\web\View;

/* @var $this View */
/* @var $model PersonSearch */

?>

<?php $form = ActiveForm::begin([
    'id'     => 'mobile-filter-form',
    'action' => ['index'],
    'method' => 'get',
]); ?>

<?= $form->field($model, 'name')->textInput() ?>

<?= $form->field($model, 'birthday')->widget(DateTimeRangeWidget::class, [
    'attributeFrom' => 'date_from',
    'attributeTo'   => 'date_to',
    'valueFormat' => 'Y-m-d',
]) ?>

<?= $form->field($model, 'category_id')->dropDownList(
    $model->categoriesList(),
    ['prompt' => 'Все']
) ?>

<?= $form->field($model, 'status')->dropDownList(
    $model->statusList(),
    ['prompt' => 'Любой']
) ?>

<?php ActiveForm::end(); ?>
