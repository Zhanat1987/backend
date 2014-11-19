<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $phoneNumber
 * @property string $phoneUUID
 * @property double $latitude
 * @property double $longitude
 * @property integer $enable
 *
 * @property ItemSpecialStatus[] $itemSpecialStatuses
 * @property Scan[] $scans
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phoneNumber', 'phoneUUID'], 'required'],
            [['latitude', 'longitude'], 'number'],
            [['enable'], 'integer'],
            [['phoneNumber', 'phoneUUID'], 'string', 'max' => 20],
            [['phoneNumber', 'phoneUUID'], 'unique', 'targetAttribute' => ['phoneNumber', 'phoneUUID'], 'message' => 'The combination of Phone Number and Phone Uuid has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phoneNumber' => 'Phone Number',
            'phoneUUID' => 'Phone Uuid',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemSpecialStatuses()
    {
        return $this->hasMany(ItemSpecialStatus::className(), ['userId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScans()
    {
        return $this->hasMany(Scan::className(), ['userId' => 'id']);
    }
}
