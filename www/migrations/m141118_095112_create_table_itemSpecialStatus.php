<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141118_095112_create_table_itemSpecialStatus extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%itemSpecialStatus}}', [
            'id' => Schema::TYPE_PK,
            'userId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'statusId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'itemId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
        ], $this->getTableOptions());
        $this->addForeignKey('userIdItemSpecialStatusFk', '{{%itemSpecialStatus}}', 'userId', '{{%user}}', 'id');
        $this->addForeignKey('statusIdItemSpecialStatusFk', '{{%itemSpecialStatus}}', 'statusId', '{{%status}}', 'id');
        $this->addForeignKey('itemIdItemSpecialStatusFk', '{{%itemSpecialStatus}}', 'itemId', '{{%item}}', 'id');
        echo "create table itemSpecialStatus: success up\n";
    }

    public function safeDown()
    {
        $this->dropForeignKey('userIdItemSpecialStatusFk', '{{%itemSpecialStatus}}');
        $this->dropForeignKey('statusIdItemSpecialStatusFk', '{{%itemSpecialStatus}}');
        $this->dropForeignKey('itemIdItemSpecialStatusFk', '{{%itemSpecialStatus}}');
        $this->dropTable('{{%itemSpecialStatus}}');
        echo "create table itemSpecialStatus: success down\n";
    }

}