<?php

namespace Besnovatyj\Person\controllers\backend;

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\forms\backend\CategoryForm;
use common\treeModule\controllers\TreeController;
use common\treeModule\TreeDataSource;
use Yii;

class CategoryController extends TreeController
{
    public function __construct($id, $module, $config = [])
    {
        $this->treeManager = Yii::$container->get('person.tree.manager');
        $this->dataSource = new TreeDataSource(
            Category::class,
            function (Category $model) {
                return [
                    'id' => $model->id,
                    'title' => $model->name,
                    'slug' => $model->slug,
                ];
            },
            ['id', 'name', 'slug'], // SELECT только нужные поля
            'sort_order'
        );
        $this->createFormClass = CategoryForm::class;
        $this->updateFormClass = CategoryForm::class;
        $this->formView = '_form';
        $this->indexTitle = 'Управление категориями';
        parent::__construct($id, $module, $config);
    }
}
