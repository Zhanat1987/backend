<?php

use yii\db\Schema;
use my\yii2\Migration;

class m141118_094547_create_table_scan extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%scan}}', [
            'id' => Schema::TYPE_PK,
            'itemId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'userId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'clusterId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'latitude' => Schema::TYPE_FLOAT . '(3,6) NOT NULL',
            'longitude' => Schema::TYPE_FLOAT . '(3,6) NOT NULL',
            'time' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'threshold' => Schema::TYPE_SMALLINT . '(5)',
        ], $this->getTableOptions());
        $this->addForeignKey('itemIdScanFk', '{{%scan}}', 'itemId', '{{%item}}', 'id');
        $this->addForeignKey('userIdScanFk', '{{%scan}}', 'userId', '{{%user}}', 'id');
        $this->addForeignKey('clusterIdScanFk', '{{%scan}}', 'clusterId', '{{%cluster}}', 'id');
        echo "create table scan: success up\n";
    }

    public function safeDown()
    {
        $this->dropForeignKey('itemIdScanFk', '{{%scan}}');
        $this->dropForeignKey('userIdScanFk', '{{%scan}}');
        $this->dropForeignKey('clusterIdScanFk', '{{%scan}}');
        $this->dropTable('{{%scan}}');
        echo "create table scan: success down\n";
    }

}