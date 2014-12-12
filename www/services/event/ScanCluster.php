<?php

namespace app\services\event;

use Yii;
use Exception;
use app\models\Scan;
use app\models\Cluster;

class ScanCluster
{

    public static function execute($scanId, $latitude, $longitude, $accuracy)
    {
        try {
            $sql = "CALL getNearestCluster($latitude, $longitude, $accuracy);";
            $cluster = Yii::$app->db->createCommand($sql)->queryOne();
            if (!$cluster) {
                $cluster = new Cluster;
                $cluster->latitude = $latitude;
                $cluster->longitude = $longitude;
                $cluster->save();
            }
            Scan::updateAll(['clusterId' => $cluster ? $cluster['id'] : $cluster->id], 'id = :id', [':id' => $scanId]);
            return true;
        } catch (Exception $e) {
            return Yii::$app->exception->register($e, 'continue');
        }
    }

} 