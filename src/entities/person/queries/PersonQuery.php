<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Person\entities\person\queries;

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
}
