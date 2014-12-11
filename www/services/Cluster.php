<?php

namespace app\services;

use Yii;
use Exception;
use app\models\Cluster as Model;

class Cluster
{

    private static $clusters = [];

    private $cities = [
        'Almaty',
        'Astana',
        'Shymkent',
        'Karaganda',
        'Aktobe',
        'Taraz',
        'Pavlodar',
        'Semey',
        'Ust-Kamenogorsk',
        'Kyzylorda',
        'Uralsk',
        'Kostanay',
        'Atyrau',
        'Petropavlovsk',
        'Aktau',
        'Temirtau',
        'Kokshetau',
        'Turkestan',
        'Ekibastus',
        'Taldykorgan',
        'Rudnii',
        'Zhanaozen',
    ];

    private $cities2 = [
        'Алматы',
        'Астана',
        'Шымкент',
        'Караганда',
        'Актобе',
        'Тараз',
        'Павлодар',
        'Семей',
        'Усть-Каменогорск',
        'Кызылорда',
        'Уральск',
        'Костанай',
        'Атырау',
        'Петропавловск',
        'Актау',
        'Темиртау',
        'Кокшетау',
        'Туркестан',
        'Экибастуз',
        'Талдыкорган',
        'Рудный',
        'Жанаозен',
    ];

    private $columns = [
        'clusterTypeId',
        'description',
        'latitude',
        'longitude',
        'fakeDistributor',
        'enable',
    ];

    public static function google()
    {
        try {
            $self = new static;
            foreach ($self->cities as $city) {
                $self->markets('market+in+' . $city . '&types=food', 2);
            }
            foreach ($self->cities as $city) {
                $self->markets('supermarket+in+' . $city, 3);
            }
            foreach ($self->cities as $city) {
                $self->markets('hypermarket+in+' . $city, 4);
            }
            foreach ($self->cities as $city) {
                $self->markets('shopping+centers+in+' . $city, 5);
            }
            foreach ($self->cities as $city) {
                $self->markets('mall+in+' . $city, 5);
            }
            foreach ($self->cities as $city) {
                $self->markets('restaurants+in+' . $city, 6);
            }
            foreach ($self->cities2 as $city) {
                $self->markets('магазины+в+' . $city . '&types=food', 2);
            }
            foreach ($self->cities2 as $city) {
                $self->markets('супермаркеты+в+' . $city, 3);
            }
            foreach ($self->cities2 as $city) {
                $self->markets('гипермаркеты+в+' . $city, 4);
            }
            foreach ($self->cities2 as $city) {
                $self->markets('торговые+центры+в+' . $city, 5);
            }
            foreach ($self->cities2 as $city) {
                $self->markets('рестораны+в+' . $city, 6);
            }
            if (self::$clusters) {
                Yii::$app->db
                    ->createCommand()
                    ->batchInsert(Model::tableName(), $self->columns, self::$clusters)
                    ->execute();
            }
            return true;
        } catch (Exception $e) {
            return Yii::$app->exception->register($e, 'continue');
        }
    }

    private function markets($condition, $clusterType, $nextPageToken = false)
    {
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$condition}&key=" .
            Yii::$app->params['keys']['google'] . ($nextPageToken ? '&next_page_token=' . $nextPageToken : '');
        $result = json_decode(file_get_contents($url), true);
        if ($result['results']) {
            foreach ($result['results'] as $row) {
                self::$clusters[$row['geometry']['location']['lat'] . $row['geometry']['location']['lng']] = [
                    $clusterType,
                    $row['name'] . ', ' . $row['formatted_address'],
                    $row['geometry']['location']['lat'],
                    $row['geometry']['location']['lng'],
                    0,
                    1,
                ];
            }
            if (isset($result['next_page_token'])) {
                $this->markets($condition, $clusterType, $result['next_page_token']);
            }
        }
    }

} 