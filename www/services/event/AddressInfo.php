<?php

namespace app\services\event;

use Yii;
use Exception;
use app\models\AddressInfo as Model;
use Geocoder\Geocoder;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\ChainProvider;
use Geocoder\Provider\GoogleMapsProvider;
use Geocoder\Provider\YandexProvider;
use Geocoder\Provider\OpenStreetMapProvider;
use Geocoder\Provider\BingMapsProvider;

class AddressInfo
{

    public static function execute($scanId, $latitude, $longitude)
    {
        try {
            $locale = 'ru_RU';
            $adapter = new CurlHttpAdapter;
            $chain = new ChainProvider([
                new GoogleMapsProvider($adapter, $locale, 'Russia', true),
                new YandexProvider($adapter, $locale),
                new OpenStreetMapProvider($adapter, $locale),
                new BingMapsProvider($adapter, Yii::$app->params['keys']['bingMaps'], 'ru_RU'),
            ]);
            $geocoder = new Geocoder;
            $geocoder->registerProvider($chain);
            $geocode = $geocoder->reverse($latitude, $longitude);
            $model = new Model;
            $model->scanId = $scanId;
            $model->country = $geocode->getCountry();
            $model->city = $geocode->getCity();
            $model->street = $geocode->getStreetName();
            $model->house = $geocode->getStreetNumber();
            $model->save();
            return true;
        } catch (Exception $e) {
            return Yii::$app->exception->register($e, 'continue');
        }
    }

} 