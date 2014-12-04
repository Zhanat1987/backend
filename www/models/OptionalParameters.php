<?php

namespace app\models;

use Yii;
use my\yii2\MongoActiveRecord;
use yii\mongodb\Query;
use app\services\event\StaticInfo;

/**
 * This is the model class for table "type".
 *
 * @property integer $_id
 * @property integer $typeId
 * @property object $optionalParameters
 *
 * @property Type $type
 */
class OptionalParameters extends MongoActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'optionalParameters';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['typeId'], 'required'],
            [['typeId'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'typeId' => 'Type',
            /*
             * 'optionalParameters' => [
             *      'language (en, ru, kz)' => [
             *          'someData'
             *      ]
             * ]
             */
            'optionalParameters' => 'Parameters',
        ];
    }

    public function attributes()
    {
        return ['_id', 'typeId', 'optionalParameters'];
    }

    /**
     * @return \yii\mongodb\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'typeId']);
    }

    public static function getOptionalParametersByTypeId($typeId)
    {
        return (new Query)->select('optionalParameters')
            ->from(self::collectionName())
            ->where('typeId = ' . $typeId)->one();
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            StaticInfo::deleteCacheThroughRelations($this->typeId, 'typeId');
            return true;
        } else {
            return false;
        }
    }

}