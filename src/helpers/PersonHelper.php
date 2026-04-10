<?php

namespace Besnovatyj\Person\helpers;

use Besnovatyj\Person\entities\person\Person;
use Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class PersonHelper
{
    public static function statusList(): array
    {
        return [
            Person::STATUS_DRAFT          => 'НЕ активно',
            Person::STATUS_ACTIVE         => 'Активно',
            Person::STATUS_PENDING_DELETE => 'К удалению',
        ];
    }

    /**
     * @throws Exception
     */
    public static function statusName($status): string
    {
        return ArrayHelper::getValue(self::statusList(), $status);
    }

    /**
     * @throws Exception
     */
    public static function statusLabel($model): string
    {
        switch ($model->status) {
            case Person::STATUS_DRAFT:
                $class = 'badge bg-secondary';
                $action = 'activate';
                break;
            case Person::STATUS_ACTIVE:
                $class = 'badge bg-success';
                $action = 'draft';
                break;
            case Person::STATUS_PENDING_DELETE:
                $class = 'badge bg-danger';
                $action = 'draft';
                break;
            default:
                $class = 'badge bg-secondary';
                $action = 'activate';
        }

        $text = Html::tag('span', ArrayHelper::getValue(self::statusList(), $model->status), [
            'class' => $class,
        ]);
        $url = Url::to([$action, 'id' => $model->id]);
        return Html::a($text, $url, [
            'data' => [
                'confirm' => "Сменить статус?",
                'method' => 'post',
            ],
        ]);

    }

    public static function languageLabel($status)
    {
        switch ($status) {
            case 0:
                $class = 'text-muted';
                $text = '<i class="fa fa-times-circle"></i>';
                break;
            case 1:
                $class = 'text-success';
                $text = '<i class="fa fa-check-circle" aria-hidden="true"></i>';
                break;
            default:
                $class = 'text-success';
                $text = $status;
        }

        return Html::tag('span', $text, [
            'class' => $class,
        ]);
    }

}
