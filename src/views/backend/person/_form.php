<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\DateTime\DateTimeWidget;
use Besnovatyj\File\widgets\CkeditorCustomWidget;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\forms\backend\person\PersonForm;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model PersonForm */
/* @var $person Person */

?>
<?php $form = ActiveForm::begin(); ?>
<p>
    <?= Html::submitButton('Сохранить', ['class' => 'btn  btn-block btn-success']) ?>
</p>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                Имя, категория, возраст
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'name')
                            ->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model->categories, 'main')->label('Категория')
                            ->dropDownList($model->categories->categoriesList(), ['prompt' => 'Not selected', 'class' => 'custom-select']) ?>
                    </div>
                    <div class="col-6">
                        <?= $model->getAttributeLabel('birthday') ?>
                        <?= DateTimeWidget::widget([
                            'model' => $model,
                            'attribute' => 'birthday',
                            'showTime' => false,
                            'valueFormat' => 'Y-m-d',
                            'options' => [
                                'data-locale' => 'ru-RU',
                                'data-week-starts-on' => '1',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn  btn-block btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <?= $model->getAttributeLabel('description') ?>
            </div>
            <div class="card-body">
                <?php
                if (!isset($person)) {
                    echo '<div class="alert alert-danger" role="alert">Перед заполнением контента сохраните актёра.</div>';
                } else {
                    // TODO создавать папку при создании актёра. При удалении удалять.
                    $editorConfig = [];
                    $editorConfig['language'] = 'ru';
                    $editorConfig['fmDefaultPath'] = '/static/origin/Person/' . $person->id;
                    echo $form->field($model, 'description')->widget(CkeditorCustomWidget::class, $editorConfig)->label(false);
                }
                ?>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn  btn-block btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">SEO</div>
            <div class="card-body">
                <?= $form->field($model->meta, 'title')->textInput(['class' => 'form-control'])->label('Заголовок') ?>
                <?= $form->field($model->meta, 'description')->textarea(['rows' => 2, 'class' => 'form-control'])->label('Описание') ?>
                <?= $form->field($model->meta, 'keywords')->textInput(['class' => 'form-control'])->label('Ключевые слова') ?>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn  btn-block btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
