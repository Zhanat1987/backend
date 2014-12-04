<?php

namespace app\models;

use Yii;
use app\services\event\StaticInfo;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "manufacturer".
 *
 * @property integer $id
 * @property string $name
 * @property string $name_ru
 * @property string $name_kz
 * @property string $devName
 * @property string $brandLogo
 * @property integer $enable
 *
 * @property Product[] $products
 */
class Manufacturer extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'manufacturer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['enable'], 'integer'],
            [['name', 'name_ru', 'name_kz', 'devName', 'brandLogo'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'name_ru' => 'Name RU',
            'name_kz' => 'Name KZ',
            'devName' => 'Dev Name',
            'brandLogo' => 'Brand Logo',
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['manufacturerId' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            StaticInfo::deleteCacheThroughRelations($this->id, 'manufacturerId');
            return true;
        } else {
            return false;
        }
    }

}