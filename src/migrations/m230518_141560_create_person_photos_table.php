<?php

namespace Besnovatyj\Person\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m230518_141560_create_person_photos_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%person_photos}}';

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
                ->comment('Идентификатор артиста'),
            'file' => $this->string(255)->notNull()
                ->comment('Путь к файлу фотографии'),
            'sort' => $this->integer(10)->notNull()
                ->comment('Сортировка фотографии'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Таблица фотографий артистов');

        $this->createIndexes(static::TABLE_NAME, 'person_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }

}
