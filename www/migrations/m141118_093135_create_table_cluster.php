<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141118_093135_create_table_cluster extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%cluster}}', [
            'id' => Schema::TYPE_PK,
            'clusterTypeId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'description' => Schema::TYPE_STRING . '(255) NOT NULL',
            'latitude' => Schema::TYPE_FLOAT . '(3,6) NOT NULL',
            'longitude' => Schema::TYPE_FLOAT . '(3,6) NOT NULL',
            'fakeDistributor' => Schema::TYPE_SMALLINT . '(1)',
            'enable' => Schema::TYPE_SMALLINT . '(1)',
        ], $this->getTableOptions());
        $this->addForeignKey('clusterTypeIdClusterFk', '{{%cluster}}', 'clusterTypeId', '{{%clusterType}}', 'id');
        echo "create table cluster: success up\n";
    }

    public function safeDown()
    {
        $this->dropForeignKey('clusterTypeIdClusterFk', '{{%cluster}}');
        $this->dropTable('{{%cluster}}');
        echo "create table cluster: success down\n";
    }

}