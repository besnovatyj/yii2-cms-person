<?php

declare(strict_types=1);

namespace Besnovatyj\Person\controllers\backend;

use Besnovatyj\Images\helpers\ImageActionsMap;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\entities\person\Photo;
use Besnovatyj\Person\forms\backend\person\PersonForm;
use Besnovatyj\Person\forms\backend\search\PersonSearch;
use Besnovatyj\Person\image\PersonImageOwner;
use Besnovatyj\Person\repositories\NotFoundException;
use Besnovatyj\Person\repositories\PersonRepository;
use Besnovatyj\Person\services\manage\PersonManageService;
use common\components\controller\ControllerTrait;
use common\components\urlmanager\UrlManagerHelperTrait;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PersonController extends Controller
{
    use ControllerTrait;
    use UrlManagerHelperTrait;

    private PersonManageService $service;
    private PersonRepository $persons;

    public function __construct(
        $id,
        $module,
        PersonManageService $service,
        PersonRepository $persons,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->persons = $persons;
    }

    /**
     * Регистрирует standalone image-actions через ImageActionsMap.
     *
     * Person не требует pessimistic lock (PersonImageOwner использует NullImageOwnerTrait),
     * поэтому подключение сводится к одной строке.
     *
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return ImageActionsMap::get(
            Photo::class,
            fn(int $id) => new PersonImageOwner($this->persons->get($id), $this->persons),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete'          => ['POST'],
                    'activate'        => ['POST'],
                    'draft'           => ['POST'],
                    'add-image'       => ['POST'],
                    'delete-image'    => ['POST'],
                    'get-images'      => ['POST'],
                    'set-main-image'  => ['POST'],
                    'set-new-sort'    => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel  = new PersonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return Response|string
     */
    public function actionCreate(): Response|string
    {
        $form = new PersonForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $person = $this->service->create($form);
                return $this->redirect(['view', 'id' => $person->id]);
            } catch (Throwable $e) {
                Yii::$app->errorHandler->logException($e);
                if (YII_DEBUG) {
                    Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка');
                }
            }
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * @throws NotFoundHttpException|InvalidConfigException
     */
    public function actionView(int $id): Response|string
    {
        $person              = $this->findModel($id);
        $absoluteFrontendUrl = $this->getAbsoluteFrontendRoute('/Person/person/person/', ['id' => $id]);

        return $this->render('view', [
            'person'             => $person,
            'absoluteFrontendUrl' => $absoluteFrontendUrl,
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): Response|string
    {
        $person = $this->findModel($id);
        $form   = new PersonForm($person);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($person->id, $form);
                return $this->redirect(['view', 'id' => $person->id]);
            } catch (Throwable $e) {
                Yii::$app->errorHandler->logException($e);
                if (YII_DEBUG) {
                    Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка');
                }
            }
        }
        return $this->render('update', [
            'model'  => $form,
            'person' => $person,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->service->remove($id);
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionActivate(int $id): Response
    {
        try {
            $this->service->activate($id);
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->goReferer();
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDraft(int $id): Response
    {
        try {
            $this->service->draft($id);
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
        }
        return $this->goReferer();
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): Person
    {
        try {
            return $this->persons->get($id);
        } catch (NotFoundException) {
            throw new NotFoundHttpException('Запрашиваемая страница не существует.');
        }
    }
}
