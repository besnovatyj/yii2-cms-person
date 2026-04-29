<?php

declare(strict_types=1);

namespace Besnovatyj\Person\controllers\backend;

use Besnovatyj\Person\repositories\NotFoundException;
use Besnovatyj\Person\repositories\PersonRepository;
use Besnovatyj\Person\services\manage\PersonVideoService;
use Throwable;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Контроллер управления видеороликами актёров.
 */
class VideoController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly PersonVideoService $service,
        private readonly PersonRepository $persons,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST'],
                    'delete' => ['POST'],
                    'reorder' => ['POST'],
                    'verify' => ['POST'],
                    'refresh' => ['POST'],
                    'test-parse' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Страница управления видеороликами актёра.
     *
     * @throws NotFoundHttpException
     */
    public function actionIndex(int $personId): string
    {
        $person = $this->findPerson($personId);

        return $this->render('index', [
            'person' => $person,
        ]);
    }

    /**
     * Автономная страница тестирования парсинга URL.
     */
    public function actionTestPage(): string
    {
        return $this->render('test-parse');
    }

    /**
     * Добавляет видеоролик к актёру (AJAX).
     */
    public function actionAdd(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $personId = (int)Yii::$app->request->post('person_id');
        $sourceUrl = trim((string)Yii::$app->request->post('source_url'));

        try {
            $video = $this->service->addVideo($personId, $sourceUrl);

            return [
                'status' => 'success',
                'data' => [
                    'id' => $video->id,
                    'source_url' => $video->source_url,
                    'iframe_url' => $video->iframe_url,
                    'thumbnail_url' => $video->thumbnail_url,
                    'provider_type' => $video->provider_type,
                    'iframe_allow' => $video->iframe_allow,
                    'iframe_referrerpolicy' => $video->iframe_referrerpolicy,
                ],
            ];
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            return [
                'status' => 'error',
                'message' => YII_DEBUG ? $e->getMessage() : 'Ошибка добавления видеоролика.',
            ];
        }
    }

    /**
     * Удаляет видеоролик (AJAX).
     */
    public function actionDelete(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $videoId = (int)Yii::$app->request->post('video_id');

        try {
            $this->service->removeVideo($videoId);
            return ['status' => 'success'];
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            return [
                'status' => 'error',
                'message' => YII_DEBUG ? $e->getMessage() : 'Ошибка удаления видеоролика.',
            ];
        }
    }

    /**
     * Переупорядочивает видеоролики (AJAX).
     */
    public function actionReorder(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $personId = (int)Yii::$app->request->post('person_id');
        $orderedIds = Yii::$app->request->post('ordered_ids', []);

        try {
            $this->service->reorderVideos($personId, $orderedIds);
            return ['status' => 'success'];
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            return [
                'status' => 'error',
                'message' => YII_DEBUG ? $e->getMessage() : 'Ошибка сортировки.',
            ];
        }
    }

    /**
     * Проверяет актуальность данных видеоролика (AJAX).
     */
    public function actionVerify(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $videoId = (int)Yii::$app->request->post('video_id');

        try {
            $result = $this->service->verifyVideo($videoId);
            return [
                'status' => 'success',
                'data' => $result,
            ];
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            return [
                'status' => 'error',
                'message' => YII_DEBUG ? $e->getMessage() : 'Ошибка проверки.',
            ];
        }
    }

    /**
     * Обновляет предвычисленные данные видеоролика (AJAX).
     */
    public function actiongoReferer(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $videoId = (int)Yii::$app->request->post('video_id');

        try {
            $video = $this->service->refreshVideo($videoId);
            return [
                'status' => 'success',
                'data' => [
                    'id' => $video->id,
                    'iframe_url' => $video->iframe_url,
                    'thumbnail_url' => $video->thumbnail_url,
                    'iframe_allow' => $video->iframe_allow,
                    'iframe_referrerpolicy' => $video->iframe_referrerpolicy,
                ],
            ];
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            return [
                'status' => 'error',
                'message' => YII_DEBUG ? $e->getMessage() : 'Ошибка обновления.',
            ];
        }
    }

    /**
     * Тестирует парсинг URL без сохранения в БД (AJAX).
     */
    public function actionTestParse(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $url = trim((string)Yii::$app->request->post('url'));

        try {
            $videoData = $this->service->testParseUrl($url);

            if ($videoData === null) {
                return [
                    'status' => 'error',
                    'message' => 'Не удалось распознать URL видео.',
                ];
            }

            return [
                'status' => 'success',
                'data' => [
                    'iframe_url' => $videoData->iframeUrl,
                    'thumbnail_url' => $videoData->thumbnailUrl,
                ],
            ];
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            return [
                'status' => 'error',
                'message' => YII_DEBUG ? $e->getMessage() : 'Ошибка парсинга URL.',
            ];
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    private function findPerson(int $id): \Besnovatyj\Person\entities\person\Person
    {
        try {
            return $this->persons->get($id);
        } catch (NotFoundException) {
            throw new NotFoundHttpException('Запрашиваемая страница не существует.');
        }
    }
}
