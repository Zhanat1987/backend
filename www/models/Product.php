<?php

namespace app\models;

use Yii;
use yii\db\Query;
use app\services\event\StaticInfo;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "product".
 *
 * @property integer $id
 * @property integer $typeId
 * @property integer $manufacturerId
 * @property string $name
 * @property string $name_ru
 * @property string $name_kz
 * @property string $description
 * @property string $description_ru
 * @property string $description_kz
 * @property string $devName
 * @property string $barcode
 * @property integer $enable
 *
 * @property Item[] $items
 * @property Manufacturer $manufacturer
 * @property Type $type
 * @property ProductImage[] $productImages
 */
class Product extends ActiveRecord
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
            [
                [
                    'name',
                    'name_ru',
                    'name_kz',
                    'description',
                    'description_ru',
                    'description_kz',
                    'devName',
                    'barcode'
                ],
                'string',
                'max' => 255
            ],
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
            'name_ru' => 'Name RU',
            'name_kz' => 'Name KZ',
            'description' => 'Description',
            'description_ru' => 'Description RU',
            'description_kz' => 'Description KZ',
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

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            self::deleteCacheFromEventStaticInfo($this->id);
            return true;
        } else {
            return false;
        }
    }

    public static function deleteCacheFromEventStaticInfo($ids)
    {
        $rows = (new Query)->select('code, number')
            ->from(Item::tableName())
            ->where('productId IN (' . $ids . ')')
            ->all();
        if ($rows) {
            foreach ($rows as $row) {
                StaticInfo::deleteCache($row['code'], $row['number']);
            }
        }
    }

}