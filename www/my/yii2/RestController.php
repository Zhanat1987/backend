<?php

namespace my\yii2;

use app\services\CheckUser;
use app\services\Request;
use Yii;
use app\models\User;
use yii\helpers\Json;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;

class RestController extends Controller
{

    private $_params, $_newToken;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get', 'post', 'put', 'delete'],
//                'create' => ['post'],
//                'update' => ['put', 'post'],
//                'delete' => ['post', 'delete'],
                'scan' => ['post'],
                'static-info' => ['get'],
                'dynamic-info' => ['get'],
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
            $this->secure(Yii::$app->request->headers);
            $this->userAuthenticate($this->getParam('phoneUUID'));
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
        if ($this->_newToken) {
            $result = $this->addNewToken($result);
        }
        return $result;
//        return [
//            'test' => 'false00',
//        ];
    }

    /**
     * @return bool|void
     */
    public function init()
    {
        parent::init();
        $this->setParams();
        $this->setUUID();
        $this->setLanguage();
    }

    private function setParams()
    {
        if (in_array(Yii::$app->request->method, ['POST', 'PUT', 'DELETE'])) {
            if (Yii::$app->request->headers['content-type'] == 'application/json') {
                $inputJSON = file_get_contents('php://input');
                if ($inputJSON) {
                    if ($inputJSON[0] == '{' && substr($inputJSON, -1) == '}') {
                        $this->_params = Json::decode($inputJSON);
                    } else {
                        parse_str($inputJSON, $this->_params);
                    }
                }
            } else {
                $this->_params = Yii::$app->request->getBodyParams();
            }
        } else {
            $this->_params = Yii::$app->request->getQueryParams();
        }
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getParam($key, $defaultValue = null)
    {
        return ArrayHelper::getValue($this->_params, $key, $defaultValue);
    }

    private function setLanguage()
    {
        Yii::$app->language = $this->getParam('language') ? : 'en';
    }

    private function userAuthenticate($phoneUUID)
    {
        if (!$phoneUUID || (($user = User::getUserByUUID($phoneUUID)) && !$user->enable)) {
            Yii::$app->exception->suspiciousUser();
        }
        Request::execute($user->id);
        CheckUser::single($user->id);
        return true;
    }

    private function secure($headers)
    {
        if (isset($headers['token']) && isset($headers['key'])) {
            if (Yii::$app->cache->get($headers['token'])) {
                return true;
            }
            if (base64_decode($headers['key']) == Yii::$app->params['restKey']) {
                $this->_newToken = true;
                return true;
            }
            throw new BadRequestHttpException(Yii::t('error', 'Invalid key value'), 400);
        }
        throw new BadRequestHttpException(Yii::t('error', 'Not isset token and key parameters in headers'), 400);
    }

    private function addNewToken($result)
    {
        $accessToken = Yii::$app->security->generateRandomString();
        Yii::$app->cache->set($accessToken, true, Yii::$app->params['duration']['day']);
        $result['token'] = $accessToken;
        return $result;
    }

    private function setUUID()
    {
        $headers = Yii::$app->request->headers;
        if (isset($headers['phoneUUID'])) {
            $this->_params['phoneUUID'] = $headers['phoneUUID'];
        } else {
            throw new BadRequestHttpException(Yii::t('error', 'Not isset phoneUUID parameter in headers'), 400);
        }
    }

}