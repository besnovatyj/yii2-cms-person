<?php

use Besnovatyj\Person\forms\backend\CategoryForm;
use yii\bootstrap5\ActiveForm;

/**
 * Веб-форма, которая используется в двух виджетах:
 */
/** @var $model CategoryForm */

?>
<?php $form = ActiveForm::begin(); ?>
<?= $form->errorSummary($model) ?>
<?php
if ($model->parentId !== null) {
    echo $form->field($model, 'parentId')->hiddenInput()->label(false);
}
?>
<?= $form->field($model, 'nodeId')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
<?= $form->field($model, 'status')->dropDownList([true => 'Вкл.', false => 'Выкл.']) ?>

<div class="card">
    <div class="card-header d-md-flex justify-content-md-between">
        <div class="pt-1">Meta</div>
        <a class="btn btn-sm" data-bs-toggle="collapse" href="#person-taxonomy" role="button"
           aria-expanded="false" aria-controls="collapseExample"></a>
    </div>
    <div class="collapse" id="person-taxonomy">
        <div class="card-body">

            <?= $form->field($model->meta, 'title')->textInput() ?>
            <?= $form->field($model->meta, 'description')->textarea(['rows' => 2]) ?>
            <?= $form->field($model->meta, 'keywords')->textInput() ?>

        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>



