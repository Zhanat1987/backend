<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141118_095100_create_table_itemCompletion extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%itemCompletion}}', [
            'id' => Schema::TYPE_PK,
            'statusId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'itemId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'latitude' => Schema::TYPE_FLOAT . '(3,6) NOT NULL',
            'longitude' => Schema::TYPE_FLOAT . '(3,6) NOT NULL',
        ], $this->getTableOptions());
        $this->addForeignKey('statusIdItemCompletionFk', '{{%itemCompletion}}', 'statusId', '{{%status}}', 'id');
        $this->addForeignKey('itemIdItemCompletionFk', '{{%itemCompletion}}', 'itemId', '{{%item}}', 'id');
        echo "create table itemCompletion: success up\n";
    }

    public function safeDown()
    {
        $this->dropForeignKey('statusIdItemCompletionFk', '{{%itemCompletion}}');
        $this->dropForeignKey('itemIdItemCompletionFk', '{{%itemCompletion}}');
        $this->dropTable('{{%itemCompletion}}');
        echo "create table itemCompletion: success down\n";
    }

}