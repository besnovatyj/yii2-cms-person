<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Person\repositories;

use Besnovatyj\Person\entities\Category;

class CategoryRepository
{
    public function find($id): ?Category
    {
        return Category::findOne($id);
    }
}
