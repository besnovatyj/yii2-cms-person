<?php

namespace Besnovatyj\Person\repositories;

use Besnovatyj\Person\entities\Category;

class CategoryRepository
{
    public function find($id): ?Category
    {
        return Category::findOne($id);
    }
}
