<?php

namespace Besnovatyj\Person\entities\queries;

use Besnovatyj\Person\entities\Category;
use yii\db\ActiveQuery;

/* @see \Besnovatyj\Person\entities\Category */
class CategoryQuery extends ActiveQuery
{
    public function active($alias = null): CategoryQuery
    {
        return $this->andWhere([
            ($alias ? $alias . '.' : '') . 'status' => Category::STATUS_ACTIVE,
        ]);
    }
}
