<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141113_051857_create_table_status extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%status}}', [
            'id' => Schema::TYPE_PK,
            'description' => Schema::TYPE_STRING . '(255) NOT NULL',
            'icon' => Schema::TYPE_STRING . '(255)',
            'colour' => Schema::TYPE_STRING . '(6)',
            'enable' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 1',
        ], $this->getTableOptions());
        $this->createIndex('descriptionUnique', '{{%status}}', 'description', true);
        echo "create table status: success up\n";
    }

    public function safeDown()
    {
        $this->dropTable('{{%status}}');
        echo "create table status: success down\n";
    }

}