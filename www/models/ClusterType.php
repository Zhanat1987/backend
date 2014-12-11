<?php

namespace app\models;

use Yii;
use my\yii2\ActiveRecord;

/**
 * This is the model class for table "clusterType".
 *
 * @property integer $id
 * @property string $description
 * @property integer $type
 * @property integer $threshold
 * @property integer $enable
 *
 * @property Cluster[] $clusters
 */
class ClusterType extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clusterType';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'threshold'], 'required'],
            [['type', 'threshold'], 'integer'],
            [['description'], 'string', 'max' => 255],
            [['enable'], 'default', 'value' => 1],
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
            'type' => 'Type',
            'threshold' => 'Threshold',
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClusters()
    {
        return $this->hasMany(Cluster::className(), ['clusterTypeId' => 'id']);
    }

}