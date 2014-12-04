<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141113_064745_create_table_type extends Migration
{

    public function up()
    {
        $this->createTable('{{%type}}', [
            'id' => Schema::TYPE_PK,
            'description' => Schema::TYPE_STRING . '(255) NOT NULL',
            'description_ru' => Schema::TYPE_STRING . '(255)',
            'description_kz' => Schema::TYPE_STRING . '(255)',
            'enable' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 1',
        ], $this->getTableOptions());
        echo "create table type: success up\n";
    }

    public function down()
    {
        $this->dropTable('{{%type}}');
        echo "create table type: success down\n";
    }

}