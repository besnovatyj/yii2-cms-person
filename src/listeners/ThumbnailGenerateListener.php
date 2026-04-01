<?php

declare(strict_types=1);

namespace Besnovatyj\Person\listeners;

use Besnovatyj\Person\entities\events\ThumbnailGenerate;

/**
 * Слушатель события генерации превью.
 * Person загружается лениво из БД через EntityEvent::getEntity() —
 * репозиторий не нужен. Это корректно работает как синхронно, так и
 * при асинхронной обработке через Redis-очередь.
 */
class ThumbnailGenerateListener
{
    public function handle(ThumbnailGenerate $event): void
    {
        $person = $event->getPerson();

        foreach ($person->photos as $photo) {
            $photo->createThumbs();
        }
    }
}
