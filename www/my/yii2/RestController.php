<?php

namespace my\yii2;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;

class RestController extends Controller
{

    private $_params;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get', 'post', 'put', 'delete'],
//                'sign-up' => ['post'],
//                'login' => ['post'],
//                'index' => ['get'],
//                'view' => ['get'],
//                'create' => ['post'],
//                'update' => ['put', 'post'],
//                'delete' => ['post', 'delete'],
            ],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        return $result;
    }

    /**
     * @return bool|void
     */
    public function init()
    {
        parent::init();
        $this->setParams();
    }

    protected function setParams()
    {
        if (in_array(Yii::$app->request->method, ['POST', 'PUT', 'DELETE'])) {
            if (Yii::$app->request->headers['content-type'] == 'application/json') {
                $inputJSON = file_get_contents('php://input');
                if ($inputJSON) {
                    parse_str($inputJSON, $this->_params);
                }
            } else {
                $this->_params = Yii::$app->request->getBodyParams();
            }
        } else {
            $this->_params = Yii::$app->request->getQueryParams();
        }
    }

    protected function getParams($key = null, $defaultValue = null)
    {
        if (!$key) {
            return $this->_params;
        }
        return ArrayHelper::getValue($this->_params, $key, $defaultValue);
    }

}