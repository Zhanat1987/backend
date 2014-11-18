<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141113_072137_create_table_product extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%product}}', [
            'id' => Schema::TYPE_PK,
            'typeId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'manufacturerId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'name' => Schema::TYPE_STRING . '(255) NOT NULL',
            'description' => Schema::TYPE_STRING . '(255) NOT NULL',
            'devName' => Schema::TYPE_STRING . '(255)',
            'barcode' => Schema::TYPE_STRING . '(255)',
            'enable' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 1',
        ], $this->getTableOptions());
        $this->addForeignKey('typeIdProductFk', '{{%product}}', 'typeId', '{{%type}}', 'id');
        $this->addForeignKey('manufacturerIdProductFk', '{{%product}}', 'manufacturerId', '{{%manufacturer}}', 'id');
        echo "create table product: success up\n";
    }

    public function safeDown()
    {
        $this->dropForeignKey('typeIdProductFk', '{{%product}}');
        $this->dropForeignKey('manufacturerIdProductFk', '{{%product}}');
        $this->dropTable('{{%product}}');
        echo "create table product: success down\n";
    }

}