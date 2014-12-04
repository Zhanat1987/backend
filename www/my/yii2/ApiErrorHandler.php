<?php

namespace my\yii2;

use Yii;
use yii\web\ErrorHandler;
use yii\web\Response;

/**
 * Class ApiErrorHandler
 * @package my\yii2
 *
 * @link http://aabramoff.ru/yii2-sozdaem-svojj-obrabotchik-oshibok/
 */
class ApiErrorHandler extends ErrorHandler
{

    /**
     * @param \Exception $exception
     */
    protected function renderException($exception)
    {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
        } else {
            $response = new Response();
        }
        $response->data = $this->convertExceptionToArray($exception);
        $response->setStatusCode($exception->statusCode);
        $response->format = Response::FORMAT_JSON;
        $response->send();
    }

    /**
     * @inheritdoc
     */
    protected function convertExceptionToArray($exception)
    {
        return [
            'meta' =>
                [
                    'status' => 'error',
                    'errors' => [
                        [
                            'exception name' => $exception->getName(),
                            'statusCode' => $exception->statusCode,
                            'error' => $exception->getMessage(),
                            'code' => $exception->getCode(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
//                            'traceAsString' => $exception->getTraceAsString(),
                        ]
                    ]
                ]
        ];
    }

}