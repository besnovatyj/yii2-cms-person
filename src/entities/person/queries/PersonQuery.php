<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Person\entities\person\queries;

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\Person;
use yii\db\ActiveQuery;

class PersonQuery extends ActiveQuery
{
    /**
     * @param string|null $alias
     * @return $this
     */
    public function active(?string $alias = null): static
    {
        return $this->andWhere([
            ($alias ? $alias . '.' : '') . 'status' => Person::STATUS_ACTIVE,
        ]);
    }

    /**
     * Персона доступна анонимному посетителю: опубликована сама И лежит в видимом разделе.
     *
     * Ровно тот же критерий, что уже применяет страница персоны
     * ({@see \Besnovatyj\Person\readModels\PersonReadRepository::find()}), но проверка раздела идёт
     * целиком — вместе с предками (см. {@see \Besnovatyj\Person\entities\queries\CategoryQuery::visible()}),
     * иначе персона из ветки, скрытой на верхнем уровне, попадёт в поиск.
     * Персона без раздела (`category_id` NULL) видна: скрывать её не за что.
     *
     * @param string|null $alias алиас таблицы персон, если запрос строится с `alias()`
     */
    public function visible(?string $alias = null): static
    {
        $column = ($alias ? $alias . '.' : '') . 'category_id';

        return $this->active($alias)->andWhere([
            'or',
            [$column => null],
            [$column => Category::find()->visible()->select('id')],
        ]);
    }
}
