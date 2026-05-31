<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Person\forms\backend\person\PersonForm;
use yii\web\View;

/* @var $this View */
/* @var $model PersonForm */

$this->title = 'Создать';
$this->params['breadcrumbs'][] = ['label' => 'Все актёры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="person-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
