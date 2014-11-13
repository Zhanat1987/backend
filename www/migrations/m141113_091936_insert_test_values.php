<?php

use my\yii2\Migration;
use Faker\Factory;
use yii\helpers\StringHelper;

class m141113_091936_insert_test_values extends Migration
{

    public function safeUp()
    {
        $faker = Factory::create();
        $userRows = $statusRows = $manufacturerRows =
            $typeRows = $productRows = $itemRows = $scanRows = [];
        // insert into user table
        for ($i = 0; $i < 10; ++$i) {
            $userRows[] = [
                $faker->phoneNumber,
                $faker->uuid,
            ];
        }
        $this->batchInsert('{{%user}}', ['phoneNumber', 'phoneUUID'], $userRows);
        // insert into status table
        for ($i = 0; $i < 10; ++$i) {
            $statusRows[] = [
                StringHelper::truncate($faker->sentence(), 255),
                $faker->word,
                $faker->hexcolor,
            ];
        }
        $this->batchInsert('{{%status}}', ['description', 'icon', 'colour'], $statusRows);
        // insert into manufacturer table
        for ($i = 0; $i < 10; ++$i) {
            $manufacturerRows[] = [
                $faker->word,
                $faker->word,
            ];
        }
        $this->batchInsert('{{%manufacturer}}', ['name', 'brandLogo'], $manufacturerRows);
        // insert into type table
        for ($i = 0; $i < 10; ++$i) {
            $typeRows[] = [
                StringHelper::truncate($faker->sentence(), 255),
            ];
        }
        $this->batchInsert('{{%type}}', ['description'], $typeRows);
        // insert into product table
        for ($i = 0; $i < 10; ++$i) {
            $productRows[] = [
                $faker->word,
                $i + 1,
                $faker->word,
                StringHelper::truncate($faker->sentence(), 255),
                $i + 1,
            ];
        }
        $this->batchInsert('{{%product}}', ['name', 'typeId', 'image', 'description', 'manufacturerId'], $productRows);
        // insert into item table
        for ($i = 0; $i < 10; ++$i) {
            $itemRows[] = [
                $faker->randomNumber(),
                $faker->randomNumber(),
                $i + 1,
                $i + 1,
            ];
        }
        $this->batchInsert('{{%item}}', ['code', 'number', 'statusId', 'productId'], $itemRows);
        // insert into item table
        for ($i = 0; $i < 10; ++$i) {
            $scanRows[] = [
                $i + 1,
                $faker->latitude,
                $faker->longitude,
                $faker->unixTime,
                $i + 1,
                $faker->randomNumber(),
                $faker->address,
            ];
        }
        $this->batchInsert('{{%scan}}', ['itemId', 'latitude', 'longitude', 'time', 'userId', 'threshold', 'name'], $scanRows);
        echo "insert test values: success up\n";
    }

    public function safeDown()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        $this->truncateTable('{{%user}}');
        $this->truncateTable('{{%status}}');
        $this->truncateTable('{{%manufacturer}}');
        $this->truncateTable('{{%type}}');
        $this->truncateTable('{{%product}}');
        $this->truncateTable('{{%item}}');
        $this->truncateTable('{{%scan}}');
        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');
        echo "insert test values: success down\n";
    }

}