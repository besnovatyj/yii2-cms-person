<?php

use Besnovatyj\Person\forms\backend\search\PersonSearch;
use common\widgets\datetime\src\DateTimeRangeWidget;
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
    ['prompt' => '']
) ?>

<?= $form->field($model, 'status')->dropDownList(
    $model->statusList(),
    ['prompt' => '']
) ?>

<?php ActiveForm::end(); ?>
