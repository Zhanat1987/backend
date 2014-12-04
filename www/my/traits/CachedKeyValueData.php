<?php

namespace my\traits;

use Yii;
use yii\db\Query;

trait CachedKeyValueData
{

    public static function getCachedKeyValueData(
        $table,
        $fields,
        $where,
        $key,
        $defaultValue = []
    )
    {
        if (($data = unserialize(Yii::$app->cache->get($table . $key))) === false) {
            $data = $defaultValue;
            $rows = (new Query)
                ->select($fields)
                ->from($table)
                ->where($where)
                ->all();
            if ($rows) {
                foreach ($rows as $row) {
                    $data[$row[$fields[0]]] = $row[isset($fields[1]) ?
                        $fields[1] : $fields[0]];
                }
            }
            Yii::$app->cache->set($table . $key, serialize($data), 86400);
        }
        return $data;
    }

}