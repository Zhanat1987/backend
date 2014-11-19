<?php

namespace app\models;

use Yii;

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
class Item extends \yii\db\ActiveRecord
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
            [['code', 'statusId', 'productId'], 'required'],
            [['number', 'statusId', 'productId'], 'integer'],
            [['code'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['number'], 'unique']
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
}
