<?php

namespace my\helpers;

use Yii;
use yii\base\Exception as YiiException;
use Exception as PhpException;
use yii\base\UserException;

/**
 * Class Exception
 * @package my\helpers
 *
 * @link http://www.yiiframework.com/doc-2.0/guide-runtime-handling-errors.html
 * @link http://www.yiiframework.com/doc-2.0/yii-log-filetarget.html
 */
class Exception
{

    /**
     * @param $e
     * @param string $action
     * @throws YiiException
     *
     * todo
     * уведомление по e-mail для администратора
     */
    public static function register(PhpException $e, $action = 'stop')
    {
        $eInfo = 'message - ' . $e->getMessage()
            . ', code - ' . $e->getCode()
            . ', file - ' . $e->getFile()
            . ', line - ' . $e->getLine()
            . ', dateTime - ' . date(Yii::$app->params['format']['dateTime']);
        Yii::info($eInfo, 'appErrors');
        if (YII_DEBUG) {
            throw new YiiException($eInfo, $e->getCode());
        }
        switch ($action) {
            case 'continue':
                return;
                break;
            case 'stop':
                throw new YiiException($e->getMessage(), $e->getCode());
                break;
            default:
                throw new YiiException(Yii::t('error', 'An error has occurred!!!'), 500);
                break;
        }
    }

    public static function suspiciousUser()
    {
        throw new UserException(Yii::$app->params['response']['suspiciousUser']['message'],
            Yii::$app->params['response']['suspiciousUser']['code']);
    }

}