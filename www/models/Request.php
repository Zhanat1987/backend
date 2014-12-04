<?php

namespace app\models;

use Yii;
use my\yii2\MongoActiveRecord;

/**
 * This is the model class for collection "request".
 *
 * @property integer $_id
 * @property integer $userId
 * @property integer $time
 * @property string $url
 * @property string $method
 * @property integer $enable
 *
 * @property User $user
 */
class Request extends MongoActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'request';
    }

    public function attributes()
    {
        return ['_id', 'userId', 'time', 'url', 'method', 'enable'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'time', 'url', 'method'], 'required'],
            [['userId', 'time'], 'integer'],
            [['url'], 'string', 'max' => 255],
            [['method'], 'in', 'range' => ['GET', 'POST', 'PUT', 'DELETE']],
            [['enable'], 'integer'],
            [['enable'], 'default', 'value' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'userId' => 'User ID',
            'time' => 'Time',
            'url' => 'Url',
            'method' => 'Method',
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }

}