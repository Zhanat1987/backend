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
        $statusRows[] = [
            [
                'authentic',
                'authentic icon',
                '5BD65D',
            ],
            [
                'uncertain',
                'uncertain icon',
                'D6945B',
            ],
            [
                'fake',
                'fake icon',
                'F00A43',
            ],
        ];
        for ($i = 0; $i < 10; ++$i) {
            $statusRows[] = [
                StringHelper::truncate($faker->sentence(), 255),
                $faker->word,
                $faker->hexcolor,
            ];
        }
        $this->batchInsert('{{%status}}', ['description', 'icon', 'colour'], $statusRows);
        // insert into manufacturer table
        $manufacturerRows[] = [
            [
                'Бахус',
                'logo image',
            ],
            [
                'Айдабульская',
                'logo image',
            ],
            [
                'Chivas',
                'logo image',
            ],
            [
                'Nemiroff',
                'logo image',
            ],
            [
                'Martini',
                'logo image',
            ],
        ];
        for ($i = 0; $i < 10; ++$i) {
            $manufacturerRows[] = [
                $faker->word,
                $faker->word,
            ];
        }
        $this->batchInsert('{{%manufacturer}}', ['name', 'brandLogo'], $manufacturerRows);
        // insert into type table
        $typeRows[] = [
            [
                'коньяқ',
            ],
            [
                'арақ',
            ],
            [
                'виски',
            ],
            [
                'вермут',
            ],
        ];
        for ($i = 0; $i < 10; ++$i) {
            $typeRows[] = [
                StringHelper::truncate($faker->sentence(), 255),
            ];
        }
        $this->batchInsert('{{%type}}', ['description'], $typeRows);
        // insert into product table
        $productRows[] = [
            [
                'ҚАЗАҚСТАН',
                1,
                'img-result-bachus.png',
                'Казахстанский коньяк приготовлен по классической технологии с утонченными тонами трехлетней выдержки на древесине дуба 50-летнего возраста',
                1,
            ],
            [
                'Айдабульская белая',
                2,
                'img-result-aidabulskaya-white.png',
                'Приготовлена с использованием умягченной воды, спирта этилового ректификованного марки «Люкс», с добавлением натурального меда и экстракта изюма, что придает особую мягкость вкусу.',
                2,
            ],
            [
                'Айдабульская черная',
                2,
                'img-result-aidabulskaya-black.png',
                'Приготовлена с использованием умягченной воды, спирта этилового ректификованного марки «Люкс», с добавлением натурального меда и экстракта изюма, что придает особую мягкость вкусу.',
                2,
            ],
            [
                'Chivas Regal 18',
                3,
                'img-result-chivas.png',
                'Совершенный вкус, богатый и утонченный аромат. В купаже участвуют виски, выдержанные не менее 18 лет. Каждая бутылка этого виски имеет собственный серийный номер, позволяющий отследить весь процесс производства данного виски.',
                3,
            ],
            [
                'Nemiroff Деликат',
                2,
                'img-result-nemiroff-delikat.png',
                'Кристально чистая артезианская вода и смягченный имбирем спирт наивысшего качества сорта «Люкс» создают особенный вкус напитка с прозрачным и едва уловимым послевкусием.',
                4,
            ],
            [
                'Вермут Martini Bianco',
                4,
                'img-result-martini-bianco.png',
                'Martini Bianco обладает мягким ароматом с лёгким оттенком ванили и пряностей. На вкус — менее горьковатый и более тонкий, чем Россо.',
                5,
            ],
        ];
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
        $itemRows[] = [
            [
                '017c93d1a143',
                494283243,
                3,
                1,
            ],
            [
                '01ca20fbf87e',
                663088007,
                2,
                1,
            ],
            [
                '010e6b68abc0',
                655806649,
                1,
                2,
            ],
            [
                '0100fcb73d9f',
                653539252,
                3,
                3,
            ],
            [
                '0192237b5974',
                159966317,
                2,
                4,
            ],
            [
                '01d57447544c',
                228739535,
                3,
                5,
            ],
            [
                '01b85aa968c7',
                188115835,
                1,
                6,
            ],
            [
                '01b8c5aa9368',
                188115835,
                1,
                6,
            ],
        ];
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
        $scanRows[] = [
            [
                1,
                51.132349,
                71.402818,
                1391310060,
                1,
                500,
                'Астана, Развлекательный Центр "Хан Шатыр"',
            ],
            [
                1,
                43.2083,
                76.9139,
                1391314560,
                2,
                500,
                'Алматы, проспект Аль-Фараби, 95',
            ],
            [
                1,
                51.145349,
                71.413839,
                1415592660,
                3,
                500,
                'Астана, Рамстор Астана Мега',
            ],
            [
                2,
                51.145349,
                71.413839,
                1409652187,
                4,
                500,
                'Астана, Рамстор Астана Мега',
            ],
            [
                3,
                43.2444,
                76.8357,
                1409987522,
                5,
                500,
                'Алматы, просп. Райымбека, 514а',
            ],
            [
                3,
                51.1281,
                71.4116,
                1414584660,
                6,
                500,
                'Астана, ТРЦ Asia Park',
            ],
            [
                4,
                51.1281,
                71.4116,
                1411906322,
                7,
                500,
                'Астана, ТРЦ Asia Park',
            ],
            [
                4,
                51.145349,
                71.413839,
                1413033122,
                8,
                500,
                'Алматы, проспект Аль-Фараби, 95',
            ],
        ];
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