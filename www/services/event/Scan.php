<?php

namespace app\services\event;

use yii\base\Model;
use app\models\Scan as ModelScan;
use app\models\User;
use app\models\Item;
use app\models\Status;
use Yii;
use yii\db\Exception as DbException;
use my\app\ConsoleRunner;

/**
 * Class DynamicInfo
 * @package app\services\event
 *
 * @property string $phoneNumber
 * @property string $phoneUUID
 * @property integer $latitude
 * @property integer $longitude
 * @property integer $time
 * @property integer $accuracy
 * @property string $codeNumber
 * @property integer $codeNumberType
 */
class Scan extends Model
{

    public $phoneNumber,
        $phoneUUID,
        $latitude,
        $longitude,
        $time,
        $accuracy,
        $codeNumber,
        $codeNumberType;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['latitude', 'longitude', 'time', 'accuracy', 'codeNumber', 'codeNumberType'], 'required'],
            [['latitude', 'longitude'], 'number'],
            [['time', 'accuracy', 'codeNumberType'], 'integer'],
            [['codeNumber'], 'string', 'length' => [9, 32]],
            [['codeNumberType'], 'in', 'range' => [1, 2]],
            [['phoneNumber', 'phoneUUID'], 'safe'],
        ];
    }

    public function beforeValidate()
    {
        $this->latitude = (float) $this->latitude;
        $this->longitude = (float) $this->longitude;
        $this->time = (int) $this->time;
        $this->accuracy = (int) $this->accuracy;
        $this->codeNumberType = (int) $this->codeNumberType;
        return parent::beforeValidate();
    }

    public function execute()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = User::getUserByUUID($this->phoneUUID);
            $itemId = Item::getIdByCodeOrNumber($this->codeNumber, $this->codeNumberType);
            if (!$itemId) {
                $item = new Item;
                if ($this->codeNumberType == 1) {
                    $item->code = $this->codeNumber;
                } else if ($this->codeNumberType == 2) {
                    $item->number = $this->codeNumber;
                }
                $item->statusId = Status::FAKE;
                if ($item->save()) {
                    $itemId = $item->id;
                } else {
                    return Yii::$app->current->getResponseWithErrors($item->getErrors(), 'item');
                }
            }
            $scan = new ModelScan;
            $scan->latitude = $this->latitude;
            $scan->longitude = $this->longitude;
            $scan->time = $this->time;
            $scan->accuracy = $this->accuracy;
            $scan->userId = $user->id;
            $scan->itemId = $itemId;
            if (!$scan->save()) {
                return Yii::$app->current->getResponseWithErrors($scan->getErrors(), 'scan');
            }
            $transaction->commit();
            ConsoleRunner::execute('address-info/index ' . $scan->id .
                ' ' . $this->latitude . ' ' . $this->longitude);
            ConsoleRunner::execute('scan-cluster/index ' . $scan->id .
                ' ' . $this->latitude . ' ' . $this->longitude . ' ' . $this->accuracy);
        } catch (DbException $e) {
            $transaction->rollBack();
            Yii::$app->exception->register($e, 'continue');
            return [
                'code' => $e->getCode(),
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
        return Yii::$app->params['response']['success'];
    }

} 