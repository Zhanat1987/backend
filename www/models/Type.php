<?php

namespace app\models;

use Yii;
use app\services\EventStaticInfo;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "type".
 *
 * @property integer $id
 * @property string $description
 * @property integer $enable
 *
 * @property Product[] $products
 * @property OptionalParameters $optionalParameters
 */
class Type extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'required'],
            [['enable'], 'integer'],
            [['description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['typeId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptionalParameters()
    {
        return $this->hasOne(OptionalParameters::className(), ['typeId' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            EventStaticInfo::deleteCacheThroughRelations($this->id, 'typeId');
            return true;
        } else {
            return false;
        }
    }

}