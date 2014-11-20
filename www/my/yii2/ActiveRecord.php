<?php

namespace my\yii2;

use yii\db\ActiveRecord as YiiActiveRecord;
use yii\base\Exception;

class ActiveRecord extends YiiActiveRecord
{

    public function delete()
    {
        throw new Exception('Method "' . __METHOD__ . '" not allowed!', 500);
    }

    public static function deleteAll($condition = '', $params = [])
    {
        throw new Exception('Method "' . __METHOD__ . '" not allowed!', 500);
    }

} 