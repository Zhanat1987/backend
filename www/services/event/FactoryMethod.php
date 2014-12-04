<?php

namespace app\services\event;

use Yii;

class FactoryMethod
{

    public static function execute($class, $params)
    {
        $object = new $class;
        $object->setAttributes($params);
        if ($object->validate()) {
            return $object->execute();
        }
        return Yii::$app->current->getResponseWithErrors($object->getErrors());
    }

} 