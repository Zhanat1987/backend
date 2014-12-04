<?php

namespace app\models;

use Yii;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "itemSpecialStatus".
 *
 * @property integer $id
 * @property integer $userId
 * @property integer $statusId
 * @property integer $itemId
 *
 * @property Item $item
 * @property Status $status
 * @property User $user
 */
class ItemSpecialStatus extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'itemSpecialStatus';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'statusId', 'itemId'], 'required'],
            [['userId', 'statusId', 'itemId'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userId' => 'User ID',
            'statusId' => 'Status ID',
            'itemId' => 'Item ID',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }

}