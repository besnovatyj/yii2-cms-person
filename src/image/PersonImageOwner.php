<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\image;

use Besnovatyj\Images\base\BaseImage;
use Besnovatyj\Images\contracts\ImageOwnerInterface;
use Besnovatyj\Images\contracts\NullImageOwnerTrait;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\entities\person\Photo;
use Besnovatyj\Person\repositories\PersonRepository;
use yii\db\Exception;

/**
 * Адаптер Person к ImageOwnerInterface.
 *
 * Использует NullImageOwnerTrait — у Person нет необходимости в pessimistic lock,
 * так как фотографии загружаются по одной и отсутствует гонка за main_photo_id.
 */
class PersonImageOwner implements ImageOwnerInterface
{
    use NullImageOwnerTrait;

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
}
