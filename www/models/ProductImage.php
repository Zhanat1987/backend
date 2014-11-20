<?php

namespace app\models;

use Yii;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "productImage".
 *
 * @property integer $id
 * @property integer $productId
 * @property string $description
 * @property string $image
 * @property string $index
 *
 * @property Product $product
 */
class ProductImage extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'productImage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['productId', 'description', 'image', 'index'], 'required'],
            [['productId'], 'integer'],
            [['description', 'image', 'index'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'productId' => 'Product ID',
            'description' => 'Description',
            'image' => 'Image',
            'index' => 'Index',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'productId']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            (new Product())->deleteCacheFromEventStaticInfo($this->productId);
            return true;
        } else {
            return false;
        }
    }

}