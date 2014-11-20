<?php

namespace app\models;

use Yii;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use app\services\EventStaticInfo;

/**
 * This is the model class for table "type".
 *
 * @property integer $_id
 * @property integer $typeId
 * @property object $optionalParameters
 *
 * @property Type $type
 */
class OptionalParameters extends ActiveRecord
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
            'id' => 'ID',
            'typeId' => 'Type',
            'optionalParameters' => 'Parameters',
        ];
    }

    public function attributes()
    {
        return ['_id', 'typeId', 'optionalParameters'];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'typeId']);
    }

    public static function getOptionalParametersByTypeId($typeId)
    {
        return (new Query)->select('optionalParameters')
            ->from(self::collectionName())
            ->where('typeId = ' . $typeId);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            EventStaticInfo::deleteCacheThroughRelations($this->typeId, 'typeId');
            return true;
        } else {
            return false;
        }
    }

}