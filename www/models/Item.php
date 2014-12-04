<?php

namespace app\models;

use Yii;
use yii\db\Query;
use app\services\event\StaticInfo;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property string $code
 * @property integer $number
 * @property integer $statusId
 * @property integer $productId
 *
 * @property Product $product
 * @property Status $status
 * @property ItemCompletion[] $itemCompletions
 * @property ItemSpecialStatus[] $itemSpecialStatuses
 * @property Scan[] $scans
 */
class Item extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'statusId'], 'required'],
            [['number', 'statusId'], 'integer'],
            [['code'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['number'], 'unique'],
            [['productId'], 'default', 'value' => 1],
            [['statusId'], 'default', 'value' => Status::AUTHENTIC],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'number' => 'Number',
            'statusId' => 'Status ID',
            'productId' => 'Product ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'productId']);
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
    public function getItemCompletions()
    {
        return $this->hasMany(ItemCompletion::className(), ['itemId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemSpecialStatuses()
    {
        return $this->hasMany(ItemSpecialStatus::className(), ['itemId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScans()
    {
        return $this->hasMany(Scan::className(), ['itemId' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            StaticInfo::deleteCache($this->code, $this->number);
            return true;
        } else {
            return false;
        }
    }

    public static function getIdByCodeOrNumber($codeNumber, $type)
    {
        if ($type == 1) {
            $condition = 'code = :code';
            $params = [':code' => $codeNumber];
        } else if ($type == 2) {
            $condition = 'number = ":number"';
            $params = [':number' => $codeNumber];
        } else {
            return;
        }
        return (new Query)
            ->select('id')
            ->from(self::tableName())
            ->where($condition, $params)
            ->scalar();
    }

}