<?php

namespace app\services;

use Yii;
use Exception;
use Yandex\Geo\Api;

class AddressName
{

    public static function execute($latitude, $longitude)
    {
        try {
            $self = new static();
            $addressName = $self->getAddressNameFromYandex($latitude, $longitude);
            if ($addressName) {
                return $addressName;
            }
            $addressName = $self->getAddressNameFromWikimapia($latitude, $longitude);
            if ($addressName) {
                return $addressName;
            }
            $addressName = $self->getAddressNameFromGoogle($latitude, $longitude);
            if ($addressName) {
                return $addressName;
            }
        } catch (Exception $e) {
            return Yii::$app->exception->register($e, 'continue');
        }
        return 'не определено';
    }

    private function getAddressNameFromYandex($latitude, $longitude)
    {
        $api = new Api;
        $api->setPoint($longitude, $latitude);
        $api->setLimit(1)
            ->setLang(Api::LANG_RU)
            ->load();
        $response = $api->getResponse();
        $collection = $response->getList();
        if ($collection) {
            return $collection[0]->getAddress();
        }
        return;
    }

    private function getAddressNameFromWikimapia($latitude, $longitude)
    {
        $url = "http://api.wikimapia.org/?function=place.search&lat=$latitude&lon=$longitude&format=json&key=" .
            Yii::$app->params['keys']['wikimapia'];
        $result = file_get_contents($url);
        if ($result) {
            $data = json_decode($result, true);
            if (isset($data['places'][0])) {
                return $data['places'][0]['title'] . ', ' . $data['places'][0]['location']['city'] .
                    ', ' . $data['places'][0]['location']['country'];
            }
        }
        return;
    }

    private function getAddressNameFromGoogle($latitude, $longitude)
    {
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&sensor=false&language=ru";
        $result = file_get_contents($url);
        if ($result) {
            $data = json_decode($result, true);
            if ($data) {
                return (isset($data['results'][0]['formatted_address']) &&
                    $data['results'][0]['formatted_address'] != 'undefined') ?
                    $data['results'][0]['formatted_address'] : null;
            }
        }
        return;
    }

} 