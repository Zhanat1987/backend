<?php

namespace app\models;

use Yii;
use my\yii2\ActiveRecord;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\base\UserException;

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
 * @property Request[] $requests
 */
class User extends ActiveRecord
{

    const TAG_NUMBER_UUID = 'userByNumberAndUUID';

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
            [['enable'], 'default', 'value' => 1],
            [['phoneNumber', 'phoneUUID'], 'string', 'max' => 20],
            [
                ['phoneNumber', 'phoneUUID'],
                'unique',
                'targetAttribute' => ['phoneNumber', 'phoneUUID'],
                'message' => 'The combination of Phone Number and Phone Uuid has already been taken.'
            ]
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRequests()
    {
        return $this->hasMany(Request::className(), ['userId' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!$this->isNewRecord) {
                Yii::$app->cache->delete($this->phoneNumber . $this->phoneUUID);
            }
            return true;
        } else {
            return false;
        }
    }

    public static function getUserByNumberAndUUID($number, $uuid)
    {
        $key = $number . $uuid;
        if (($data = unserialize(Yii::$app->cache->get($key))) === false) {
            $data = self::find()
                ->where('phoneNumber = :phoneNumber AND phoneUUID = :phoneUUID',
                    [':phoneNumber' => $number, ':phoneUUID' => $uuid])
                ->one();
            if (!$data) {
                $data = new self;
                $data->phoneNumber = $number;
                $data->phoneUUID = $uuid;
                if ($data->validate()) {
                    $data->save(false);
                } else {
                    throw new UserException(Yii::t('common', 'Error in user create: ') .
                        implode(', ', $data->getErrors()), 400);
                }
            }
            $tagDependency = new TagDependency;
            $tagDependency->tags = self::TAG_NUMBER_UUID;
            Yii::$app->cache->set($key, serialize($data), Yii::$app->params['duration']['day'], $tagDependency);
        }
        return $data;
    }

}