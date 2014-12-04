<?php

namespace my\helpers;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Current
 * @package my\helpers
 * текущий помощник конкретного проекта
 */
class Current
{

    public static function immortal()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }

    public static function getResponseWithErrors($errors, $key = null)
    {
        return ArrayHelper::merge(
            Yii::$app->params['response']['notValid'],
            ['errors' => $key ? [$key => $errors] : $errors]
        );
    }

    public static function getLanguagePostfix($upperCase = true)
    {
        switch (Yii::$app->language) {
            case 'en' :
                return;
                break;
            case 'ru' :
                return $upperCase ? 'RU' : '_ru';
                break;
            case 'kz' :
                return $upperCase ? 'KZ' : '_kz';
                break;
        }
    }

}