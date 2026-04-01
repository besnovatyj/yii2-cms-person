<?php

namespace Besnovatyj\Person\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m230518_141610_create_person_videos_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%person_videos}}';

    /**
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        parent::safeUp();

        if ($this->existTable(static::TABLE_NAME)) {
            return;
        }

        $this->createTable(static::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'person_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор актёра'),
            'source_url' => $this->string(500)->notNull()
                ->comment('Исходная строка URL/iframe от администратора'),
            'iframe_url' => $this->string(500)->notNull()
                ->comment('Предвычисленный URL для iframe встраивания'),
            'thumbnail_url' => $this->string(500)->notNull()
                ->comment('Предвычисленный URL превью видеоролика'),
            'provider_type' => $this->string(32)->notNull()
                ->comment('Тип провайдера: youtube, vimeo, vk, rutube'),
            'sort' => $this->integer(10)->notNull()->defaultValue(0)
                ->comment('Порядок сортировки'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(1)
                ->comment('Статус: 1 — активно, 0 — отключено'),
            'created_at' => $this->integer(10)->unsigned()->notNull()
                ->comment('Дата и время создания записи'),
            'updated_at' => $this->integer(10)->unsigned()->notNull()
                ->comment('Дата и время обновления записи'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Таблица видеороликов актёров');

        $this->createIndexes(static::TABLE_NAME, 'person_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
