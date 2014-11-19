<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "status".
 *
 * @property integer $id
 * @property string $description
 * @property string $icon
 * @property string $colour
 * @property integer $enable
 *
 * @property Item[] $items
 * @property ItemCompletion[] $itemCompletions
 * @property ItemSpecialStatus[] $itemSpecialStatuses
 */
class Status extends \yii\db\ActiveRecord
{

    const AUTHENTIC = 1;
    const UNCERTAIN = 2;
    const FAKE = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'required'],
            [['enable'], 'integer'],
            [['description', 'icon'], 'string', 'max' => 255],
            [['colour'], 'string', 'max' => 6],
            [['description'], 'unique']
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
            'icon' => 'Icon',
            'colour' => 'Colour',
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['statusId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemCompletions()
    {
        return $this->hasMany(ItemCompletion::className(), ['statusId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemSpecialStatuses()
    {
        return $this->hasMany(ItemSpecialStatus::className(), ['statusId' => 'id']);
    }

}