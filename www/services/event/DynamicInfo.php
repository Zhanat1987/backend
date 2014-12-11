<?php

namespace app\services\event;

use yii\base\Model;
use Yii;
use app\models\Status;
use app\models\Scan;
use app\models\Item;
use app\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class DynamicInfo
 * @package app\services\event
 *
 * @property string $phoneNumber
 * @property string $phoneUUID
 * @property string $codeNumber
 * @property integer $codeNumberType
 */
class DynamicInfo extends Model
{

    public $phoneNumber,
        $phoneUUID,
        $codeNumber,
        $codeNumberType;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codeNumber', 'codeNumberType'], 'required'],
            [['codeNumberType'], 'integer'],
            [['codeNumber'], 'string', 'length' => [9, 32]],
            [['codeNumberType'], 'in', 'range' => [1, 2]],
            [['phoneNumber', 'phoneUUID'], 'safe'],
        ];
    }

    public function beforeValidate()
    {
        $this->codeNumberType = (int) $this->codeNumberType;
        return parent::beforeValidate();
    }

    public function execute()
    {
        $itemTable = Item::tableName();
        $statusTable = Status::tableName();
        $scanTable = Scan::tableName();
        $userTable = User::tableName();
        $condition = $itemTable . ($this->codeNumberType == 1 ?
                '.code = :codeNumber' : '.number = :codeNumber');
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
            $scanTable . '.itemId',
            $scanTable . '.userId',
            $userTable . '.phoneNumber',
            $userTable . '.phoneUUID',
            $userTable . '.id',
        ];
        /*
         * собрать через with (relations) - будет больше запросов,
         * поэтому делаем все одним запросом с join'ами
         */
        $rows = Item::find()
            ->select($fields)
            ->from($itemTable)
            ->asArray()
            ->leftJoin([$statusTable], $statusTable . '.id = ' . $itemTable . '.statusId')
            ->leftJoin([$scanTable], $scanTable . '.itemId = ' . $itemTable . '.id')
            ->leftJoin([$userTable], $userTable . '.id = ' . $scanTable . '.userId')
            ->where($condition, [':codeNumber' => $this->codeNumber])
            ->all();
        if (!$rows) {
            return Yii::$app->params['response']['empty'];
        }
        $scans = [];
        foreach ($rows as $row) {
            if (ArrayHelper::getValue($row, 'latitude') &&
                ArrayHelper::getValue($row, 'longitude') &&
                ArrayHelper::getValue($row, 'time') &&
                ArrayHelper::getValue($row, 'threshold') &&
                ArrayHelper::getValue($row, 'phoneNumber') &&
                ArrayHelper::getValue($row, 'phoneUUID')) {
                $scans[] = [
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'time' => $row['time'],
                    'threshold' => $row['threshold'],
                    'ownDevice' => $this->isOwnDevice($row),
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

    private function isOwnDevice($data)
    {
        return $this->phoneUUID == $data['phoneUUID'];
    }

} 