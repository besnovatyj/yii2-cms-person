<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\controllers\backend;

use Besnovatyj\Person\services\import\ImportService;
use Besnovatyj\Kernel\controller\ControllerTrait;
use Throwable;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;

/**
 * Контроллер импорта данных из старого модуля OldPerson.
 */
class ImportController extends Controller
{
    use ControllerTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'run' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Страница импорта с описанием и кнопкой запуска.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $importService = new ImportService();
        $isEmpty = $importService->isNewModuleEmpty();

        return $this->render('index', [
            'isEmpty' => $isEmpty,
        ]);
    }

    /**
     * Запуск импорта.
     *
     * @return Response|string
     */
    public function actionRun(): Response|string
    {
        set_time_limit(0);

        $importService = new ImportService();

        if (!$importService->isNewModuleEmpty()) {
            Yii::$app->session->setFlash('warning', 'Таблицы нового модуля не пусты. Очистите их перед импортом.');
            return $this->redirect(['index']);
        }

        try {
            $stats = $importService->run();

            Yii::$app->session->setFlash('success',
                "Импорт завершён. Категорий: {$stats['categories']}, "
                . "персон: {$stats['persons']}, "
                . "фото (БД): {$stats['photos']}, "
                . "фото (файлы скопированы): {$stats['photosCopied']}, "
                . "видео: {$stats['videos']}"
                . ($stats['videoErrors'] > 0 ? " (ошибок парсинга: {$stats['videoErrors']})" : '') . '.'
            );

            if (!empty($stats['errors'])) {
                Yii::$app->session->setFlash('warning',
                    'Ошибки при копировании файлов (' . count($stats['errors']) . "):\n"
                    . implode("\n", array_slice($stats['errors'], 0, 50))
                );
            }
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                Yii::$app->session->setFlash('error', VarDumper::dumpAsString($e->getMessage()));
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка импорта. Подробности в логах.');
            }
        }

        return $this->redirect(['index']);
    }
}
