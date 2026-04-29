<?php

namespace Besnovatyj\Person\controllers\backend;

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\forms\backend\CategoryForm;
use Besnovatyj\Person\forms\backend\search\CategorySearch;
use Besnovatyj\Person\repositories\CategoryRepository;
use Besnovatyj\Person\repositories\PersonRepository;
use Besnovatyj\TreeManager\Manager\controllers\TreeController;
use Besnovatyj\TreeManager\Manager\TreeDataSource;
use Besnovatyj\TreeManager\Manager\TreeManager;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CategoryController extends TreeController
{
    private CategoryRepository $categoryRepo;
    private PersonRepository $personRepo;

    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function __construct($id, $module, CategoryRepository $categoryRepo, PersonRepository $personRepo, $config = [])
    {
        // Обычный просмотр
        $this->categoryRepo = $categoryRepo;
        $this->personRepo = $personRepo;

        //Управление деревом Drag-and-drop
        /** @var TreeManager $treeManager */
        $treeManager = Yii::$container->get('person.tree.manager');
        $this->treeManager = $treeManager;
        $this->dataSource = new TreeDataSource(
            Category::class,
            function (Category $model) {
                return [
                    'id' => $model->id,
                    'title' => $model->name,
                    'slug' => $model->slug,
                ];
            },
            'sort_order'
        );
        $this->createFormClass = CategoryForm::class;
        $this->updateFormClass = CategoryForm::class;
        $this->formView = '_form';
        $this->indexTitle = 'Управление категориями';

        parent::__construct($id, $module, $config);
    }

    /**
     * Список категорий
     */
    public function actionList(): string
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр категории
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        if (!$category = $this->categoryRepo->find($id)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = $this->personRepo->getAllByCategory($category);

        return $this->render('view', [
            'category' => $category,
            'dataProvider' => $dataProvider,
        ]);
    }

}
