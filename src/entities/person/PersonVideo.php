<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\entities\person;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Видеоролик актёра.
 *
 * @property int $id
 * @property int $person_id
 * @property string $source_url
 * @property string $iframe_url
 * @property string $thumbnail_url
 * @property string $provider_type
 * @property string $iframe_allow
 * @property string $iframe_referrerpolicy
 * @property int $sort
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Person $person
 */
class PersonVideo extends ActiveRecord
{
    public const string TYPE_YOUTUBE = 'youtube';
    public const string TYPE_VIMEO = 'vimeo';
    public const string TYPE_VK = 'vk';
    public const string TYPE_RUTUBE = 'rutube';

    public const int STATUS_ACTIVE = 1;
    public const int STATUS_DISABLED = 0;

    /**
     * Создаёт новый экземпляр видеоролика.
     */
    public static function create(
        int $personId,
        string $sourceUrl,
        string $iframeUrl,
        string $thumbnailUrl,
        string $providerType,
        int $sort,
        string $iframeAllow = '',
        string $iframeReferrerPolicy = '',
    ): self {
        $video = new static();
        $video->person_id = $personId;
        $video->source_url = $sourceUrl;
        $video->iframe_url = $iframeUrl;
        $video->thumbnail_url = $thumbnailUrl;
        $video->provider_type = $providerType;
        $video->iframe_allow = $iframeAllow;
        $video->iframe_referrerpolicy = $iframeReferrerPolicy;
        $video->sort = $sort;
        $video->status = self::STATUS_ACTIVE;
        $video->created_at = time();
        $video->updated_at = time();
        return $video;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Отключает видеоролик.
     */
    public function disable(): void
    {
        $this->status = self::STATUS_DISABLED;
        $this->updated_at = time();
    }

    /**
     * Включает видеоролик.
     */
    public function enable(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->updated_at = time();
    }

    /**
     * Обновляет предвычисленные данные iframe и превью.
     */
    public function refreshData(
        string $iframeUrl,
        string $thumbnailUrl,
        string $iframeAllow = '',
        string $iframeReferrerPolicy = '',
    ): void {
        $this->iframe_url = $iframeUrl;
        $this->thumbnail_url = $thumbnailUrl;
        $this->iframe_allow = $iframeAllow;
        $this->iframe_referrerpolicy = $iframeReferrerPolicy;
        $this->updated_at = time();
    }

    /**
     * @return ActiveQuery
     */
    public function getPerson(): ActiveQuery
    {
        return $this->hasOne(Person::class, ['id' => 'person_id']);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%person_videos}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'person_id' => 'Актёр',
            'source_url' => 'Исходный URL',
            'iframe_url' => 'URL iframe',
            'thumbnail_url' => 'URL превью',
            'provider_type' => 'Провайдер',
            'iframe_allow' => 'Iframe allow',
            'iframe_referrerpolicy' => 'Iframe referrerpolicy',
            'sort' => 'Сортировка',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }
}
