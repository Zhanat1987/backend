<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cluster".
 *
 * @property integer $id
 * @property integer $clusterTypeId
 * @property string $description
 * @property double $latitude
 * @property double $longitude
 * @property integer $radius
 * @property integer $fakeDistributor
 * @property integer $enable
 *
 * @property ClusterType $clusterType
 * @property Scan[] $scans
 */
class Cluster extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cluster';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clusterTypeId', 'description', 'latitude', 'longitude'], 'required'],
            [['clusterTypeId', 'fakeDistributor', 'enable', 'radius'], 'integer'],
            [['latitude', 'longitude'], 'number'],
            [['description'], 'string', 'max' => 255],
            [['fakeDistributor'], 'default', 'value' => 0],
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
            'clusterTypeId' => 'Cluster Type ID',
            'description' => 'Description',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'radius' => 'Radius',
            'fakeDistributor' => 'Fake Distributor',
            'enable' => 'Enable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClusterType()
    {
        return $this->hasOne(ClusterType::className(), ['id' => 'clusterTypeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScans()
    {
        return $this->hasMany(Scan::className(), ['clusterId' => 'id']);
    }

}