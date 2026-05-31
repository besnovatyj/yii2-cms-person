<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Person\helpers;

use Besnovatyj\Person\entities\Category;
use Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class  CategoryHelper
{
    public static function statusList(): array
    {
        return [
            Category::STATUS_DRAFT => 'OFF',
            Category::STATUS_ACTIVE => 'ON',
        ];
    }

    /**
     * @throws Exception
     */
    public static function statusName($status): string
    {
        return ArrayHelper::getValue(self::statusList(), $status);
    }

    public static function statusLabel($model): string
    {
        switch ($model->status) {
            case Category::STATUS_DRAFT:
                $class = 'badge bg-secondary float-right';
                $action = 'activate';
                break;
            case Category::STATUS_ACTIVE:
                $class = 'badge bg-success float-right';
                $action = 'draft';
                break;

            default:
                $class = 'badge bg-default float-right';
                $action = 'activate';
        }

        $text = Html::tag('span', ArrayHelper::getValue(self::statusList(), $model->status), [
            'class' => $class,
        ]);
        $url = Url::to(['/Person/backend/category/' . $action, 'id' => $model->id]);
        return Html::a($text, $url, [
            'data' => [
                'confirm' => "Сменить статус?",
                'method' => 'post',
            ],
        ]);

    }

}
