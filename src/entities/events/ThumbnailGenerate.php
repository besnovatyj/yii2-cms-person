<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\entities\events;

use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\DomainEvents\EntityEvent;

/**
 * Событие генерации превью для персоны.
 *
 * Хранит ссылку на объект Person, а не id, так как при recordEvent() в create()
 * запись ещё не сохранена и id может быть null.
 * К моменту диспатча (после save) id уже заполнен.
 *
 * Расширяет EntityEvent: при сериализации (push в Redis-очередь) сохраняется
 * только person_id. При десериализации (обработка воркером) Person лениво
 * загружается из БД через findEntity().
 */
class ThumbnailGenerate extends EntityEvent
{
    public function __construct(Person $person)
    {
        parent::__construct($person);
    }

    public function getPerson(): Person
    {
        /** @var Person $person */
        $person = $this->getEntity();
        return $person;
    }

    protected function findEntity(int $id): ?Person
    {
        return Person::findOne($id);
    }
}
