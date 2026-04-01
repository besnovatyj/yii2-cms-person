<?php

namespace Besnovatyj\Person\readModels;

use Besnovatyj\Person\entities\Category;
use common\treeModule\TreeQueryScope;

class CategoryReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Category::class);
    }

    public function getRoot(): ?Category
    {
        return Category::find()->andWhere(['depth' => 0])->one();
    }

    /**
     * @return Category[]
     */
    public function getRoots(): array
    {
        return $this->treeScope->rootsQuery()->all();
    }

    /**
     * @return Category[]
     */
    public function getAll(): array
    {
        return Category::find()->orderBy('lft')->all();
    }

    public function find($id): ?Category
    {
        return Category::find()->andWhere(['id' => $id])->one();
    }

    public function findActive($slug): ?Category
    {
        return Category::find()->active()->andWhere(['slug' => $slug])->one();
    }

    public function findBySlug($slug): ?Category
    {
        return Category::find()->andWhere(['slug' => $slug])->one();
    }

    public function getTreeWithSubsOf(?Category $category = null): array
    {
        $query = Category::find()->orderBy('lft');
        if ($category) {
            $parents = $this->treeScope->parentsQuery($category)->all();
            $criteria = ['or', ['depth' => 1]];
            foreach (array_merge([$category], $parents) as $item) {
                $criteria[] = ['and', ['>', 'lft', $item->lft], ['<', 'rgt', $item->rgt], ['depth' => $item->depth + 1]];
            }
            $query->andWhere($criteria);
        } else {
            $query->andWhere(['depth' => 1]);
        }

        return $query->all();
    }
}
