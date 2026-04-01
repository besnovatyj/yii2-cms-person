<?php

use Besnovatyj\Person\migrations\m230518_141550_create_person_persons_table;
use Besnovatyj\Person\migrations\m230518_141560_create_person_photos_table;
use Besnovatyj\Person\migrations\m230518_141610_create_person_videos_table;
use common\components\migration\BaseMigration;

class m230518_141620_create_person_foreign_key_constraints extends BaseMigration
{
    /**
     * @throws Exception
     */
    public function safeUp(): void
    {
        parent::safeUp();

        Yii::$app->getDb()->createCommand("SET foreign_key_checks = 0")->execute();

        // Фотографии
        $this->createFKs(
            m230518_141560_create_person_photos_table::TABLE_NAME,
            'person_id',
            m230518_141550_create_person_persons_table::TABLE_NAME,
            'id',
            'CASCADE',
        );

        // Видео
        $this->createFKs(
            m230518_141610_create_person_videos_table::TABLE_NAME,
            'person_id',
            m230518_141550_create_person_persons_table::TABLE_NAME,
            'id',
            'CASCADE',
            'CASCADE',
        );


        Yii::$app->db->createCommand('SET foreign_key_checks = 1')->execute();

    }

    public function safeDown(): void
    {
        // Отменяем действия по умолчанию,
        // так как \common\components\migration\BaseMigration::safeDown() вызывает static::TABLE_NAME,
        // которого в данной миграции не существует.
        // Так же, \common\components\migration\BaseMigration::safeDown() при удалении таблиц сам удалит у них все индексы и внешние ключи.

        // parent::safeDown();
    }

}
