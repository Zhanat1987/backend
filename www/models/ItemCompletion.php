<?php

namespace app\models;

use Yii;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "itemCompletion".
 *
 * @property integer $id
 * @property integer $statusId
 * @property integer $itemId
 * @property double $latitude
 * @property double $longitude
 *
 * @property Item $item
 * @property Status $status
 */
class ItemCompletion extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'itemCompletion';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['statusId', 'itemId', 'latitude', 'longitude'], 'required'],
            [['statusId', 'itemId'], 'integer'],
            [['latitude', 'longitude'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'statusId' => 'Status ID',
            'itemId' => 'Item ID',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
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
    public function getStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'statusId']);
    }

}