<?php

namespace Besnovatyj\Person\migrations;

use common\components\migration\BaseMigration;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m230518_141550_create_person_persons_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%person_persons}}';

    public function safeUp(): void
    {
        parent::safeUp();

        if ($this->existTable(static::TABLE_NAME)) {
            return;
        }

        $this->createTable(static::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer(10)->notNull()
                ->comment('Категория'),
            'name' => $this->string(255)->notNull()
                ->comment('Имя и фамилия актёра'),
            'birthday' => $this->date()->null()
                ->comment('Дата рождения'),
            'description' => $this->text()->null()
                ->comment('Информация об актёре'),
            'meta_json' => $this->text()->null()
                ->comment('JSON of meta-obj'),
            'videos_json' => "MEDIUMTEXT NULL DEFAULT NULL COMMENT 'Сериализованные данные о видеороликах'",
            'main_photo_id' => $this->integer(10)->null()
                ->comment('Идентификатор главной фотографии'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус активности'),
            'created_at' => $this->integer(10)->unsigned()->notNull()
                ->comment('Дата и время создания записи'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Основная таблица с артистами');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }

}
