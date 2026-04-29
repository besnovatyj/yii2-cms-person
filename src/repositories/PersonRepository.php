<?php

declare(strict_types=1);

namespace Besnovatyj\Person\repositories;

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\DomainEvents\dispatchers\EventDispatcher;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use RuntimeException;
use Throwable;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\StaleObjectException;

class PersonRepository
{
    private EventDispatcher $dispatcher;
    private TreeQueryScope $treeScope;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->treeScope = new TreeQueryScope(Category::class);
    }

    public function get(int $id): Person
    {
        if (!$person = Person::findOne($id)) {
            throw new NotFoundException('Никто не найден.');
        }
        return $person;
    }

    public function existsByMainCategory(int $id): bool
    {
        return Person::find()->andWhere(['category_id' => $id])->exists();
    }

    public function countByCategory(Category $category): int
    {
        return Person::find()->andWhere(['category_id' => $category->id])->count();
    }

    public function getAllByCategory(Category $category): DataProviderInterface
    {
        $query = Person::find()->alias('p')->active('p')->with('category');
        $ids = $this->treeScope->descendantIds($category, andSelf: true);
        $query->andWhere(['p.category_id' => $ids]);
        $query->groupBy('p.id');
        return new ActiveDataProvider(['query' => $query,]);
    }

    /**
     * Возвращает все персоны, помеченные к удалению.
     *
     * @return Person[]
     */
    public function getAllPendingDelete(): array
    {
        return Person::find()->andWhere(['status' => Person::STATUS_PENDING_DELETE])->all();
    }

    /**
     * @throws Exception
     */
    public function save(Person $person): void
    {
        if (!$person->save()) {
            throw new RuntimeException('Ошибка сохранения.');
        }
        $this->dispatcher->dispatchAll($person->releaseEvents());
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove(Person $person): void
    {
        if (!$person->delete()) {
            throw new RuntimeException('Ошибка удаления.');
        }
        $this->dispatcher->dispatchAll($person->releaseEvents());
    }
}
