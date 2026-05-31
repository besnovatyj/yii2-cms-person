<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\TreeManager\Manager\TreeDataSource;
use Besnovatyj\TreeManager\Manager\TreeWidget;
use Besnovatyj\Person\forms\backend\CategoryForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var string $title
 * @var TreeDataSource $treeDataSource
 */

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="category-tree-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="btn-group">
            <?= Html::a(
                '<i class="bi bi-list-ul"></i> Список',
                ['/Person/backend/category/list'],
                ['class' => 'btn btn-outline-secondary']
            ) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?= TreeWidget::widget([
                'dataSource' => $treeDataSource,
                'endpoints' => [
                    'loadChildren' => Url::to(['/Person/backend/category/load-children']),
                    'createNode' => Url::to(['/Person/backend/category/create']),
                    'updateNode' => Url::to(['/Person/backend/category/update']),
                    'deleteNode' => Url::to(['/Person/backend/category/delete']),
                    'moveNode' => Url::to(['/Person/backend/category/move']),
                    'toggleStatus' => Url::to(['/Person/backend/category/toggle-status']),
                    'checkIntegrity' => Url::to(['/Person/backend/category/check-integrity']),
                ],
//                'forms' => [
//                    'createFormClass' => CategoryForm::class,
//                    'updateFormClass' => CategoryForm::class,
//                ],
                'serverForms' => [
                    'enabled' => true,
                    'display' => 'modal',
                    'errorStrategy' => 'both',
                    'operations' => [
                        'create' => true,
                        'edit' => true,
                    ],
                    'getFormUrl' => Url::to(['/Person/backend/category/get-form']),
                ],
                'permissions' => [
                    'canCreate' => true, // Yii::$app->user->can('create'),
                    'canUpdate' => true, // Yii::$app->user->can('update'),
                    'canDelete' => true, // Yii::$app->user->can('delete'),
                    'canMove' => true, // Yii::$app->user->can('move'),
                ],
                'titleField' => 'title',
                'enablePersistence' => true,
                'storageKey' => 'Person-category-tree-state',
                'containerOptions' => [
                    'class' => 'Person-category-tree-widget',
                ],
            ]) ?>
        </div>
    </div>
</div>

<?php
// Дополнительные стили
$this->registerCss(<<<CSS
.Person-category-tree-widget {
    min-height: 400px;
}
CSS
);
?>
