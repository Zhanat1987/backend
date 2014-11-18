<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141113_075335_create_table_clusterType extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%clusterType}}', [
            'id' => Schema::TYPE_PK,
            'description' => Schema::TYPE_STRING . '(255) NOT NULL',
            'type' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'enable' => Schema::TYPE_SMALLINT . '(1)',
        ], $this->getTableOptions());
        echo "create table clusterType: success up\n";
    }

    public function safeDown()
    {
        $this->dropTable('{{%clusterType}}');
        echo "create table clusterType: success down\n";
    }

}