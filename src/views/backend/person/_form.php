<?php

use Besnovatyj\File\widgets\customeditor\src\CkeditorCustomWidget;
use Besnovatyj\Person\forms\backend\person\PersonForm;
use Besnovatyj\Person\entities\person\Person;
use common\widgets\datetime\src\DateTimeWidget;
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
    <div class="col-md-6">
        <div class="card">
            <?= $model->getAttributeLabel('name') ?>
            <?= $form->field($model, 'name')
                ->label(false)
                ->textInput(['maxlength' => true, 'class' => 'form-control rounded-0']) ?>
            <?= Html::submitButton('Сохранить', ['class' => 'btn  btn-block btn-success']) ?>
        </div><!-- Name -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <?= $model->getAttributeLabel('categories') ?>
                    <?= $form->field($model->categories, 'main')
                        ->label(false)
                        ->dropDownList($model->categories->categoriesList(), ['prompt' => 'Not selected', 'class' => 'custom-select rounded-0']) ?>
                    <?= Html::submitButton('Сохранить', ['class' => 'btn  btn-block btn-success']) ?>
                </div><!-- Categories -->
            </div>
            <div class="col-md-6">
                <div class="card">
                    <?= $model->getAttributeLabel('birthday') ?>
                    <?= DateTimeWidget::widget([
                        'model'       => $model,
                        'attribute'   => 'birthday',
                        'showTime'    => false,
                        'valueFormat' => 'Y-m-d',
                        'options'     => [
                            'data-locale'         => 'ru-RU',
                            'data-week-starts-on' => '1',
                        ],
                    ]) ?>
                </div><!-- Date of birth -->
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <?= $model->getAttributeLabel('description') ?>
            <?php
            if (!isset($person)) {
                echo '<div class="alert alert-danger" role="alert">Перед заполнением контента сохраните актёра.</div>';
            } else {
                // TODO создавать папку при создании актёра. При удалении удалять.
                $editorConfig = [];
                $editorConfig['language'] = 'ru';
                $editorConfig['fmDefaultPath'] = '/origin/Person/' . $person->id;
                echo $form->field($model, 'description')->widget(CkeditorCustomWidget::class, $editorConfig);
            }
            ?>
            <?= Html::submitButton('Сохранить', ['class' => 'btn  btn-block btn-success']) ?>
        </div><!-- General Information -->
    </div>
    <div class="col-md-6">
        <div class="card">
            <h3 class="card-title">SEO</h3>
            <div class="card-body">
                <?= $form->field($model->meta, 'title')->textInput(['class' => 'form-control rounded-0'])->label('Заголовок') ?>
                <?= $form->field($model->meta, 'description')->textarea(['rows' => 2, 'class' => 'form-control rounded-0'])->label('Описание') ?>
                <?= $form->field($model->meta, 'keywords')->textInput(['class' => 'form-control rounded-0'])->label('Ключевые слова') ?>
            </div>
            <div class="card-footer clearfix">
                <?= Html::submitButton('Сохранить', ['class' => 'btn  btn-block btn-success']) ?>
            </div>
        </div><!-- SEO -->
    </div>
</div>
<?php ActiveForm::end(); ?>
