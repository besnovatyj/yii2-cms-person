<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\widgets\dashboard;

use Besnovatyj\Person\entities\person\Person;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Плитка дашборда: число актёров с разбивкой активные/неактивные и ссылкой к списку.
 *
 * Рендерит только тело карточки — «каркас» рисует модуль дашборда.
 */
class PersonsCountTile extends Widget
{
    public function run(): string
    {
        $active = (int)Person::find()->where(['status' => Person::STATUS_ACTIVE])->count();
        $inactive = (int)Person::find()->where(['!=', 'status', Person::STATUS_ACTIVE])->count();

        $counters = Html::tag('div',
            Html::tag('span', (string)$active, ['class' => 'display-6 fw-bold lh-1 text-success'])
            . Html::tag('span', 'активных', ['class' => 'text-muted ms-2']),
            ['class' => 'd-flex align-items-baseline'])
            . Html::tag('div', 'Неактивных: ' . $inactive, ['class' => 'text-muted small mt-1']);

        $link = Html::a(
            '<i class="bi bi-person-badge me-1"></i>К актёрам',
            Url::to(['/Person/backend/person/index']),
            ['class' => 'btn btn-sm btn-outline-primary mt-3']
        );

        return $counters . $link;
    }
}
