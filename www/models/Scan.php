<?php

namespace app\models;

use Yii;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "scan".
 *
 * @property integer $id
 * @property integer $itemId
 * @property integer $userId
 * @property integer $clusterId
 * @property double $latitude
 * @property double $longitude
 * @property integer $time
 * @property integer $threshold
 *
 * @property Cluster $cluster
 * @property Item $item
 * @property User $user
 * @property AddressInfo $addressInfo
 */
class Scan extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['itemId', 'userId', 'latitude', 'longitude', 'time'], 'required'],
            [['itemId', 'userId', 'time', 'threshold', 'clusterId'], 'integer'],
            [['latitude', 'longitude'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'itemId' => 'Item ID',
            'userId' => 'User ID',
            'clusterId' => 'Cluster ID',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'time' => 'Time',
            'threshold' => 'Threshold',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCluster()
    {
        return $this->hasOne(Cluster::className(), ['id' => 'clusterId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'itemId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddressInfo()
    {
        return $this->hasOne(AddressInfo::className(), ['scanId' => 'id']);
    }

}