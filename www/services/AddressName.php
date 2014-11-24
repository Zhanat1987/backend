<?php

namespace app\services;

use Yii;
use Exception;
use Yandex\Geo\Api;

class AddressName
{

    private $_addressName;

    public static function execute($latitude, $longitude)
    {
        try {
            $self = new static();
            $self->_addressName = $self->getAddressNameFromYandex($latitude, $longitude);
            if ($self->_addressName) {
                return $self->_addressName;
            }
            $self->_addressName = $self->getAddressNameFromWikimapia($latitude, $longitude);
            if ($self->_addressName) {
                return $self->_addressName;
            }
            $self->_addressName = $self->getAddressNameFromGoogle($latitude, $longitude);
            if ($self->_addressName) {
                return $self->_addressName;
            }
        } catch (Exception $e) {

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
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&sensor=false&language=ru";
        $result = file_get_contents($url);
        if ($result) {
            $data = json_decode($result, true);
            if ($data) {
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