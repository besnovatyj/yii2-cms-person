<?php

declare(strict_types=1);

namespace Besnovatyj\Person\behaviors;

use Besnovatyj\Person\entities\person\Video;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Model;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class VideosBehavior extends Behavior
{
    public string $attribute = 'videos';
    public string $jsonAttribute = 'videos_json';

    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_FIND => 'onAfterFind',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'onBeforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'onBeforeSave',
        ];
    }

    public function onAfterFind(Event $event): void
    {
        $model = $event->sender;
        $model->{$this->attribute} = [];
        $videos = Json::decode($model->getAttribute($this->jsonAttribute)) ?? [];
        foreach ($videos as $video) {
            $model->{$this->attribute}[] = new Video(
                ArrayHelper::getValue($video, 'srcString')
            );
        }
    }

    public function onBeforeSave(Event $event): void
    {
        /** @var $model Model */
        $model = $event->sender;
        $model->setAttribute($this->jsonAttribute, Json::encode($model->{$this->attribute}));
    }
}
















