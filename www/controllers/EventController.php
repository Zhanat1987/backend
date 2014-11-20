<?php

namespace app\controllers;

use Yii;
use my\yii2\RestController;
use yii\base\InvalidParamException;
use app\services\EventScan;
use app\services\EventDynamicInfo;

class EventController extends RestController
{

    public function actionScan()
    {
        if ($this->getParams('latitude') && $this->getParams('longitude') &&
            $this->getParams('time') && $this->getParams('threshold') &&
            $this->getParams('uuid') && $this->getParams('phoneNumber') &&
            $this->getParams('codeNumber') && $this->getParams('codeNumberType')) {
            return EventScan::execute($this->getParams());
        }
        throw new InvalidParamException('Переданы не все обязательные параметры', 400);
    }

    public function actionStaticInfo()
    {
        /**
        1-й get запрос - кэшируется, get by number or code
        product.name - name
        type.id - type
        type.description - typeDescription
        mongodb optionalParameters.optionalParameters - optionalParameters
        manufacturer.name - brand
        item.number - number
        productImage.image - image
        manufacturer.brandLogo - brandLogo
        product.description - description

        kesh na mesyac
        vezde kesh sbrasivat vo vseh tablicah svyazannih

        keshirovat po parametru (number or code) na sutki, no togda nado vezde sbrasivat kesh pri izmenenii v svyazannih tablicah

        "item.code - budet v zaprose ili poluchit iz item table po item.number": {
        "product.name - esli item.productId not null, to iz product table poluchit product.name": "Вермут Martini Bianco",
        "type - esli item.productId not null, to iz product table poluchit product.typeId, zatem iz type table poluchit type.id": "03", //alcohol products, vermuts
        "typeDescription - esli item.productId not null, to iz product table poluchit product.typeId, zatem iz type table poluchit type.description": "вермут",
        "optionalParameters - esli item.productId not null, to iz product table poluchit product.typeId, zatem iz optionalParameters collection poluchit optionalParameters.optionalParameters": {
        "volume" : "0,5л"
        },
        "brand - esli item.productId not null, to iz product table poluchit product.manufacturerId, zatem iz manufacturer table poluchit manufacturer.name": "Martini",
        "number - budet v zaprose ili poluchit iz item table po item.code": "188115835",
        "image - esli item.productId not null, to iz productImage table poluchit productImage.image": "res/img/products/img-result-martini-bianco.png", //binary
        //"brandLogo": binary
        "description - esli item.productId not null, to iz product table poluchit product.description": "Martini Bianco обладает мягким ароматом с лёгким оттенком ванили и пряностей. На вкус — менее горьковатый и более тонкий, чем Россо.",
        }

        (po url number or code) and userId (uuid and phoneNumber)

        item.code - "01b8c5aa9368": {
            product.name - "name": "Вермут Martini Bianco",
            type.id - "type": "03", //alcohol products, vermuts
            type.description - "typeDescription": "вермут",
            mongodb optionalParameters.optionalParameters - "optionalParameters": {
            "volume" : "0,5л"
            },
            manufacturer.name - "brand": "Martini",
            item.number - "number": "188115835",
            productImage.image - "image": "res/img/products/img-result-martini-bianco.png", //binary
            //"brandLogo": binary
            product.description - "description": "Martini Bianco обладает мягким ароматом с лёгким оттенком ванили и пряностей. На вкус — менее горьковатый и более тонкий, чем Россо.",
            status.description - "status": "authentic", //["authentic", "uncertain", "fake"]
            scan - "scans": [
            {
            "latitude" : "43.2444",
            "longitude" : "76.8357",
            "time" : "1411204322", //Sat, 20 Sep 2014 15:12:02 +0600
            "device" : "325B47898A8B", //MAC or Id
            "threshold" : "500",
            "name" : "Алматы, ТРЦ Прайм Плаза" //optional
            },
            {
            "latitude" : "51.1797",
            "longitude" : "71.4367",
            "time" : "1414667522", //Thu, 30 Oct 2014 17:12:02 +0600
            "device" : "CA56551BB098", //MAC or Id
            "threshold" : "500",
            "name" : "Астана, Торговый центр Джафар" //optional
            }
            ]
        }
         */
        return [
            'test' => $this->getParams('test'),
            'params' => $this->getParams(),
        ];
    }

    public function actionDynamicInfo()
    {
        return EventDynamicInfo::execute($this->getParams('codeNumber'), $this->getParams('codeNumberType'));
    }

} 