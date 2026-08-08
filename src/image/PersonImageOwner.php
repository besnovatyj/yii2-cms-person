<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\image;

use Besnovatyj\Images\base\BaseImage;
use Besnovatyj\Images\contracts\ImageOwnerInterface;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\entities\person\Photo;
use Besnovatyj\Person\repositories\PersonRepository;
use yii\db\Exception;

/**
 * Адаптер Person к ImageOwnerInterface.
 *
 * Реализует pessimistic lock через PessimisticLockBehavior Person,
 * чтобы исключить race condition при параллельной загрузке фотографий:
 * виджет грузит файлы пачками (по несколько параллельных запросов), и без
 * блокировки несколько запросов одновременно видят main_photo_id = null и
 * пытаются его установить, что приводит к взаимной блокировке (deadlock) и
 * падению части файлов из пачки.
 */
class PersonImageOwner implements ImageOwnerInterface
{
    public function __construct(
        private readonly Person           $person,
        private readonly PersonRepository $repository,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getOwnerId(): int
    {
        return $this->person->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return Photo[]
     */
    public function getOwnedImages(): array
    {
        return $this->person->photos;
    }

    /**
     * {@inheritdoc}
     */
    public function getMainImageId(): ?int
    {
        return $this->person->main_photo_id ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function setMainImageId(?int $imageId): void
    {
        $this->person->setMainPhoto($imageId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function saveOwner(): void
    {
        $this->repository->save($this->person);
    }

    /**
     * Блокирует строку персоны (SELECT FOR UPDATE) до конца транзакции.
     *
     * Исключает race condition при параллельной загрузке нескольких файлов.
     *
     * @throws Exception
     */
    public function lockOwner(): void
    {
        $this->person->lock();
    }

    /**
     * Обновляет данные персоны из БД после применения блокировки.
     */
    public function refreshOwner(): void
    {
        $this->person->refresh();
    }
}
