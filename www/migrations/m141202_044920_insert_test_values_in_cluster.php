<?php

use my\yii2\Migration;
use Faker\Factory;

class m141202_044920_insert_test_values_in_cluster extends Migration
{

    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        $clusterTypeRows = [
            [
                'не определено',
                0,
                0,
                1,
            ],
            [
                'магазины',
                1,
                100,
                1,
            ],
            [
                'супермаркеты',
                2,
                200,
                1,
            ],
            [
                'гипермаркеты',
                2,
                200,
                1,
            ],
            [
                'торговые центры',
                2,
                200,
                1,
            ],
            [
                'рестораны',
                2,
                200,
                1,
            ],
        ];
        $this->batchInsert('{{%clusterType}}',
            [
                'description',
                'type',
                'accuracy',
                'enable',
            ], $clusterTypeRows);
//        $faker = Factory::create();
//        $clusterRows = [];
//        for ($i = 1; $i <= 10000000; ++$i) {
//            $clusterRows[] = [
//                mt_rand(1, 2),
//                $faker->address,
//                $faker->latitude,
//                $faker->longitude,
//                0,
//                1,
//            ];
//            if (($i % 10000) === 0) {
//                $this->batchInsert('{{%cluster}}',
//                    [
//                        'clusterTypeId',
//                        'description',
//                        'latitude',
//                        'longitude',
//                        'fakeDistributor',
//                        'enable',
//                    ], $clusterRows);
//                $clusterRows = [];
//            }
//        }
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');
        echo "insert test values in cluster: success up\n";
    }

    public function safeDown()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        $this->truncateTable('{{%cluster}}');
        $this->truncateTable('{{%clusterType}}');
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');
        echo "insert test values in cluster: success down\n";
    }

}