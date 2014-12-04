<?php

namespace app\models;

use Yii;
use my\yii2\MongoActiveRecord;

/**
 * This is the model class for table "type".
 *
 * @property integer $_id
 * @property integer $scanId
 * @property string $country
 * @property string $city
 * @property string $street
 * @property string $house
 *
 * @property Scan $scan
 */
class AddressInfo extends MongoActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'addressInfo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scanId'], 'required'],
            [['country', 'city', 'street', 'house'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'scanId' => 'Scan ID',
            'country' => 'Country',
            'city' => 'City',
            'street' => 'Street',
            'house' => 'House',
        ];
    }

    public function attributes()
    {
        return ['_id', 'scanId', 'country', 'city', 'street', 'house'];
    }

    /**
     * @return \yii\mongodb\ActiveQuery
     */
    public function getScan()
    {
        return $this->hasOne(Scan::className(), ['id' => 'scanId']);
    }

}