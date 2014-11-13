<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "scan".
 *
 * @property integer $id
 * @property integer $itemId
 * @property double $latitude
 * @property double $longitude
 * @property integer $time
 * @property integer $userId
 * @property integer $threshold
 * @property string $name
 *
 * @property Item $item
 * @property User $user
 */
class Scan extends \yii\db\ActiveRecord
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
            [['itemId', 'latitude', 'longitude', 'time', 'userId', 'name'], 'required'],
            [['itemId', 'time', 'userId', 'threshold'], 'integer'],
            [['latitude', 'longitude'], 'number'],
            [['name'], 'string', 'max' => 255]
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
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'time' => 'Time',
            'userId' => 'User ID',
            'threshold' => 'Threshold',
            'name' => 'Name',
        ];
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
}
