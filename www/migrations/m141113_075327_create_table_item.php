<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141113_075327_create_table_item extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%item}}', [
            'id' => Schema::TYPE_PK,
            'code' => Schema::TYPE_STRING . '(255) NOT NULL',
            'number' => Schema::TYPE_INTEGER . '(9)',
            'statusId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'productId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
        ], $this->getTableOptions());
        $this->createIndex('codeUnique', '{{%item}}', 'code', true);
        $this->createIndex('numberUnique', '{{%item}}', 'number', true);
        $this->addForeignKey('statusIdItemFk', '{{%item}}', 'statusId', '{{%status}}', 'id');
        $this->addForeignKey('productIdItemFk', '{{%item}}', 'productId', '{{%product}}', 'id');
        echo "create table item: success up\n";
    }

    public function safeDown()
    {
        $this->dropForeignKey('statusIdItemFk', '{{%item}}');
        $this->dropForeignKey('productIdItemFk', '{{%item}}');
        $this->dropTable('{{%item}}');
        echo "create table item: success down\n";
    }

}