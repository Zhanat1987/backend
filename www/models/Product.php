<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product".
 *
 * @property integer $id
 * @property integer $typeId
 * @property integer $manufacturerId
 * @property string $name
 * @property string $description
 * @property string $devName
 * @property string $barcode
 * @property integer $enable
 *
 * @property Item[] $items
 * @property Manufacturer $manufacturer
 * @property Type $type
 * @property ProductImage[] $productImages
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['typeId', 'manufacturerId', 'name', 'description'], 'required'],
            [['typeId', 'manufacturerId', 'enable'], 'integer'],
            [['name', 'description', 'devName', 'barcode'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'typeId' => 'Type ID',
            'manufacturerId' => 'Manufacturer ID',
            'name' => 'Name',
            'description' => 'Description',
            'devName' => 'Dev Name',
            'barcode' => 'Barcode',
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['productId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManufacturer()
    {
        return $this->hasOne(Manufacturer::className(), ['id' => 'manufacturerId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'typeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages()
    {
        return $this->hasMany(ProductImage::className(), ['productId' => 'id']);
    }
}
