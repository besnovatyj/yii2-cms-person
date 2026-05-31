<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\entities\person;

use Besnovatyj\Images\base\BaseImage;

/**
 * Фотография персоны.
 *
 * @property int $id
 * @property int $person_id
 * @property string $file
 * @property int $sort
 */
class Photo extends BaseImage
{
    /**
     * {@inheritdoc}
     */
    protected static function getParentAttribute(): string
    {
        return 'person_id';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getStorageName(): string
    {
        return 'Person';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getThumbProfiles(): array
    {
        return [
            'admin'     => ['width' => 70,  'height' => 100, 'thumbsType' => 'thumbnail'], // person/index
            'thumb'     => ['width' => 640, 'height' => 480, 'thumbsType' => 'resize'],    // person/view
            'frontGrid' => ['width' => 320, 'height' => 450, 'thumbsType' => 'thumbnail'], // frontend/person grid
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%person_photos}}';
    }

}
