<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141112_203447_create_table_user extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => Schema::TYPE_PK,
            'phoneNumber' => Schema::TYPE_STRING . '(20) NOT NULL',
            'phoneUUID' => Schema::TYPE_STRING . '(20) NOT NULL',
            'enable' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 1',
        ], $this->getTableOptions());
        $this->createIndex('phoneNumberAndUUIDUnique', '{{%user}}', ['phoneNumber', 'phoneUUID'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }

}
