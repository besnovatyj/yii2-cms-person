<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\migrations;

use common\components\migration\BaseMigration;

/**
 * Удаляет колонку videos_json из таблицы person_persons.
 *
 * ВАЖНО: Запускать только после проверки корректности данных в person_videos.
 */
class m260401_120020_drop_videos_json_column extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->dropColumn('{{%person_persons}}', 'videos_json');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->addColumn(
            '{{%person_persons}}',
            'videos_json',
            "MEDIUMTEXT NULL DEFAULT NULL COMMENT 'Сериализованные данные о видеороликах'",
        );
    }
}
