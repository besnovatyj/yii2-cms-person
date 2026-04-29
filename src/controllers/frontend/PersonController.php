<?php

namespace Besnovatyj\Person\controllers\frontend;

use Besnovatyj\Person\forms\frontend\search\SearchForm;
use Besnovatyj\Person\readModels\CategoryReadRepository;
use Besnovatyj\Person\readModels\PersonReadRepository;
use Exception;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PersonController extends Controller
{
    private PersonReadRepository $persons;
    private CategoryReadRepository $categories;

    public function __construct(
        $id,
        $module,
        PersonReadRepository $persons,
        CategoryReadRepository $categories,
        $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->persons = $persons;
        $this->categories = $categories;
    }

    public function actionIndex(): string
    { // TODO - Что за метод? Теперь много корней деревьев
        $dataProvider = $this->persons->getAll();
        $category = $this->categories->getRoot();

        return $this->render('index', [
            'category' => $category,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionCategory(string $slug): string
    {
        if (!$category = $this->categories->findActive($slug)) {
            throw new NotFoundHttpException('Запрашиваемая страница не существует.');
        }

        $dataProvider = $this->persons->getAllByCategory($category);

        return $this->render('category', [
            'category' => $category,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPerson(int $id): string
    {
        if (!$person = $this->persons->find($id)) {
            throw new NotFoundHttpException('Запрашиваемая страница не существует.');
        }

        return $this->render('person', [
            'person' => $person,
        ]);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function actionSearch(): string
    {
        $form = new SearchForm();
        $form->load(Yii::$app->request->queryParams);
        $form->validate();

        $dataProvider = $this->persons->search($form);

        return $this->render('search', [
            'dataProvider' => $dataProvider,
            'searchForm' => $form,
        ]);
    }

}
