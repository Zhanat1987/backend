<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141113_063236_create_table_manufacturer extends Migration
{

    public function up()
    {
        $this->createTable('{{%manufacturer}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . '(255) NOT NULL',
            'brandLogo' => Schema::TYPE_STRING . '(255)',
            'enable' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 1',
        ], $this->getTableOptions());
        echo "create table manufacturer: success up\n";
    }

    public function down()
    {
        $this->dropTable('{{%manufacturer}}');
        echo "create table manufacturer: success down\n";
    }

}