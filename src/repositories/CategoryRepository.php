<?php

namespace Besnovatyj\Person\repositories;

use Besnovatyj\Person\entities\Category;
use RuntimeException;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class CategoryRepository
{
    public function get($id): Category
    {
        if (!$category = Category::findOne($id)) {
            throw new NotFoundException('Категория не найдена.');
        }
        return $category;
    }

    /**
     * @throws Exception
     */
    public function save(Category $category): void
    {
        if (!$category->save()) {
            throw new RuntimeException('Ошибка сохранения.');
        }
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove(Category $category): void
    {
        if (!$category->delete()) {
            throw new RuntimeException('Ошибка удаления.');
        }
    }
}
