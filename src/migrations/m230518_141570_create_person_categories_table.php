<?php

namespace Besnovatyj\Person\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m230518_141570_create_person_categories_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%person_categories}}';

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
            'tree' => $this->integer()->null()
                ->comment('Идентификатор дерева'), // TODO Кажется, при переносе веток между деревьями обнуляется, проверить
            'lft' => $this->integer(10)->notNull()
                ->comment('Левый ключ NestedSets'),
            'rgt' => $this->integer(10)->notNull()
                ->comment('Правый ключ NestedSets'),
            'depth' => $this->integer(10)->notNull()
                ->comment('Глубина NestedSets'),
            'name' => $this->string(255)->notNull()
                ->comment('Название категории'),
            'slug' => $this->string(255)->notNull()
                ->comment('Slug категории'),
            'status' => $this->integer(10)->notNull()->defaultValue('0')
                ->comment('Статус активности категории'),
            'description' => $this->text()
                ->comment('Описание категории'),
            'meta_json' => $this->text()
                ->comment('JSON of meta-obj'),
            'sort_order' => $this->integer(10)->notNull()->defaultValue(0)
                ->comment('Сортировка корней'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Категория артиста');

        $this->createIndexes(static::TABLE_NAME, 'slug');

    }

    public function safeDown(): void
    {
        parent::safeDown();
    }

}
