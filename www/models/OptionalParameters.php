<?php

namespace app\models;

use Yii;
use yii\mongodb\ActiveRecord;

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

}
