<?php

use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\forms\backend\person\PersonForm;
use yii\web\View;

/* @var $this View */
/* @var $person Person */
/* @var $model PersonForm */

$this->title = 'Редактировать: ' . $person->name;
$this->params['breadcrumbs'][] = ['label' => 'Все актёры', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $person->name, 'url' => ['view', 'id' => $person->id]];
$this->params['breadcrumbs'][] = 'Редактировать';
?>

<div class="person-edit">
    <?= $this->render('_form', [
        'model' => $model,
        'person' => $person,
    ]) ?>
</div>
