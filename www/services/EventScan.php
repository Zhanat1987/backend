<?php

namespace app\services;

use app\models\Scan;
use app\models\User;
use app\models\Item;
use app\models\Status;
use Yii;
use yii\db\Exception as DbException;
use Exception;
use Yandex\Geo\Api;
use yii\helpers\ArrayHelper;

class EventScan
{

    private $_addressName;

    public static function execute($params)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $userId = User::getIdByNumberAndUUID($params['phoneNumber'], $params['uuid']);
            if (!$userId) {
                $user = new User;
                $user->phoneNumber = $params['phoneNumber'];
                $user->phoneUUID = $params['uuid'];
                if ($user->save()) {
                    $userId = $user->id;
                } else {
                    return ArrayHelper::merge(
                        Yii::$app->params['response']['error'],
                        [
                            'errors' => [
                                'user' => $user->getErrors(),
                            ],
                        ]
                    );
                }
            }
            $itemId = Item::getIdByCodeOrNumber($params['codeNumber'], $params['codeNumberType']);
            if (!$itemId) {
                $item = new Item;
                if ($params['codeNumberType'] == 1) {
                    $item->code = $params['codeNumber'];
                } else if ($params['codeNumberType'] == 2) {
                    $item->number = $params['codeNumber'];
                }
                $item->statusId = Status::FAKE;
                if ($item->save()) {
                    $itemId = $item->id;
                } else {
                    return ArrayHelper::merge(
                        Yii::$app->params['response']['error'],
                        [
                            'errors' => [
                                'item' => $item->getErrors(),
                            ],
                        ]
                    );
                }
            }
            $scan = new Scan;
            $scan->latitude = $params['latitude'];
            $scan->longitude = $params['longitude'];
            $scan->time = $params['time'];
            $scan->threshold = $params['threshold'];
            $scan->userId = $userId;
            $scan->itemId = $itemId;
            $static = new static();
            $scan->addressName = $static->getAddressName($params['latitude'], $params['longitude']);
            if (!$scan->save()) {
                return ArrayHelper::merge(
                    Yii::$app->params['response']['error'],
                    [
                        'errors' => [
                            'scan' => $scan->getErrors(),
                        ],
                    ]
                );
            }
            $transaction->commit();
        } catch (DbException $e) {
            $transaction->rollBack();
            return [
                'code' => $e->getCode(),
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
        return Yii::$app->params['response']['success'];
    }

    public function getAddressName($latitude, $longitude)
    {
        try {
            $this->_addressName = $this->getAddressNameFromYandex($latitude, $longitude);
        } catch (Exception $e) {

        }
        if ($this->_addressName) {
            return $this->_addressName;
        }
        try {
            $this->_addressName = $this->getAddressNameFromWikimapia($latitude, $longitude);
        } catch (Exception $e) {

        }
        if ($this->_addressName) {
            return $this->_addressName;
        }
        try {
            $this->_addressName = $this->getAddressNameFromGoogle($latitude, $longitude);
        } catch (Exception $e) {

        }
        if ($this->_addressName) {
            return $this->_addressName;
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