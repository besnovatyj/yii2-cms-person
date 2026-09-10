<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Person\entities\queries;

use Besnovatyj\Person\entities\Category;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

/* @see \Besnovatyj\Person\entities\Category */
class CategoryQuery extends ActiveQuery
{
    /**
     * Раздел опубликован сам по себе (без учёта дерева).
     *
     * Для фронтенда этого недостаточно: раздел внутри скрытого родителя тоже не должен быть
     * доступен — см. {@see visible()}.
     */
    public function active($alias = null): CategoryQuery
    {
        return $this->andWhere([
            ($alias ? $alias . '.' : '') . 'status' => Category::STATUS_ACTIVE,
        ]);
    }

    /**
     * Раздел доступен анонимному посетителю: опубликован сам и не спрятан ни одним из предков.
     *
     * Скрытие родителя обязано скрывать всю ветку — иначе дочерний раздел остаётся открыт по
     * прямой ссылке и попадает в сквозной поиск, хотя из навигации он исчез. Проверка идёт по
     * ключам Nested Sets одним подзапросом: у видимого узла не должно существовать предка со
     * снятой публикацией.
     *
     * Виртуальный корень дерева (`depth = 0`) из проверки исключён: он служебный, его статус
     * не редактируется и скрывать по нему весь раздел персон нельзя.
     *
     * Реализация повторяет {@see \Besnovatyj\Blog\entities\queries\TaxonomyQuery::visible()} —
     * единая политика видимости деревьев во всех контентных модулях.
     */
    public function visible(?string $alias = null): static
    {
        $table = Category::tableName();
        $self = $alias ?? $table;

        $hiddenAncestor = (new Query())
            ->select(new Expression('1'))
            ->from(['anc' => $table])
            ->where(new Expression(
                "anc.[[tree]] = {$self}.[[tree]] AND anc.[[lft]] < {$self}.[[lft]] AND anc.[[rgt]] > {$self}.[[rgt]]",
            ))
            ->andWhere(['>', 'anc.depth', 0])
            ->andWhere(['<>', 'anc.status', Category::STATUS_ACTIVE]);

        return $this->active($alias)->andWhere(['not exists', $hiddenAncestor]);
    }
}
