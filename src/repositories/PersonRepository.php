<?php

declare(strict_types=1);

namespace Besnovatyj\Person\repositories;

use Besnovatyj\Person\entities\person\Person;
use common\components\dispatcher\dispatchers\EventDispatcher;
use RuntimeException;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class PersonRepository
{
    private EventDispatcher $dispatcher;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
