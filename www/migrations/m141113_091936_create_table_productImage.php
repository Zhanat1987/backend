<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141113_091936_create_table_productImage extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%productImage}}', [
            'id' => Schema::TYPE_PK,
            'productId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'description' => Schema::TYPE_STRING . '(255) NOT NULL',
            'image' => Schema::TYPE_STRING . '(255) NOT NULL',
            'index' => Schema::TYPE_STRING . '(255) NOT NULL',
        ], $this->getTableOptions());
        $this->addForeignKey('productIdProductImageFk', '{{%productImage}}', 'productId', '{{%product}}', 'id');
        echo "create table productImage: success up\n";
    }

    public function safeDown()
    {
        $this->dropForeignKey('productIdProductImageFk', '{{%productImage}}');
        $this->dropTable('{{%productImage}}');
        echo "create table productImage: success down\n";
    }

}