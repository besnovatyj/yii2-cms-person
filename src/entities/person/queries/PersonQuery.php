<?php

namespace Besnovatyj\Person\entities\person\queries;

use Besnovatyj\Person\entities\person\Person;
use yii\db\ActiveQuery;

class PersonQuery extends ActiveQuery
{
    /**
     * @param null $alias
     * @return $this
     */
    public function active($alias = null): static
    {
        return $this->andWhere([
            ($alias ? $alias . '.' : '') . 'status' => Person::STATUS_ACTIVE,
        ]);
    }
}
