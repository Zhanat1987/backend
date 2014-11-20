<?php

namespace app\services;

use app\models\Status;
use app\models\Scan;
use app\models\Item;
use app\models\User;
use yii\helpers\ArrayHelper;

class EventDynamicInfo
{

    public static function execute($codeNumber, $codeNumberType)
    {
        $itemTable = Item::tableName();
        $statusTable = Status::tableName();
        $scanTable = Scan::tableName();
        $userTable = User::tableName();
        if ($codeNumberType == 1) {
            $condition = $itemTable . '.code = :codeNumber';
        } else if ($codeNumberType == 2) {
            $condition = $itemTable . '.number = :codeNumber';
        }
        $fields = [
            $itemTable . '.code',
            $itemTable . '.statusId',
            $itemTable . '.id',
            $statusTable . '.description',
            $statusTable . '.id',
            $scanTable . '.latitude',
            $scanTable . '.longitude',
            $scanTable . '.time',
            $scanTable . '.threshold',
            $scanTable . '.addressName',
            $scanTable . '.itemId',
            $scanTable . '.userId',
            $userTable . '.phoneNumber',
            $userTable . '.phoneUUID',
            $userTable . '.id',
        ];
        $rows = Item::find()
            ->select($fields)
            ->from($itemTable)
            ->asArray()
            ->leftJoin([$statusTable], $statusTable . '.id = ' . $itemTable . '.statusId')
            ->leftJoin([$scanTable], $scanTable . '.itemId = ' . $itemTable . '.id')
            ->leftJoin([$userTable], $userTable . '.id = ' . $scanTable . '.userId')
            ->where($condition, [':codeNumber' => $codeNumber])
            ->all();
        if (!$rows) {
            return [
                'code' => 200,
                'status' => 'empty',
            ];
        }
        $scans = [];
        foreach ($rows as $row) {
            if (ArrayHelper::getValue($row, 'latitude') &&
                ArrayHelper::getValue($row, 'longitude') &&
                ArrayHelper::getValue($row, 'time') &&
                ArrayHelper::getValue($row, 'threshold') &&
                ArrayHelper::getValue($row, 'addressName') &&
                ArrayHelper::getValue($row, 'phoneNumber') &&
                ArrayHelper::getValue($row, 'phoneUUID')) {
                $scans[] = [
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'time' => $row['time'],
                    'threshold' => $row['threshold'],
                    'addressName' => $row['addressName'],
                    'phoneNumber' => $row['phoneNumber'],
                    'phoneUUID' => $row['phoneUUID'],
                ];
            }
        }
        $response = [
            $rows[0]['code'] => [
                'status' => ArrayHelper::getValue($rows[0], 'description'),
                'scans' => $scans,
            ],
        ];
        return $response;
    }

} 