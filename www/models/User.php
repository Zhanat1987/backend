<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $phoneNumber
 * @property string $phoneUUID
 * @property integer $enable
 *
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
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScans()
    {
        return $this->hasMany(Scan::className(), ['userId' => 'id']);
    }
}
