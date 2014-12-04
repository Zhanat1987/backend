<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141202_044920_create_table_request extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%request}}', [
            'id' => Schema::TYPE_PK,
            'userId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'time' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'url' => Schema::TYPE_STRING . '(255) NOT NULL',
            'method' => Schema::TYPE_STRING . '(255) NOT NULL',
            'enable' => Schema::TYPE_SMALLINT . '(1)',
        ], $this->getTableOptions());
        $this->addForeignKey('userIdRequestFk', '{{%request}}', 'userId', '{{%user}}', 'id');
        echo "create table request: success up\n";
    }

    public function safeDown()
    {
        $this->dropForeignKey('userIdRequestFk', '{{%request}}');
        $this->dropTable('{{%request}}');
        echo "create table request: success down\n";
    }

}