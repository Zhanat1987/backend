<?php

namespace app\services;

use Yii;
use app\models\Item;
use app\models\Product;
use app\models\ProductImage;
use app\models\Manufacturer;
use app\models\Type;
use app\models\OptionalParameters;
use yii\helpers\ArrayHelper;
use yii\db\Query;

class EventStaticInfo
{

    public static function execute($codeNumber, $codeNumberType)
    {
        if (($data = unserialize(Yii::$app->cache->get($codeNumber))) === false) {
            $itemTable = Item::tableName();
            $productTable = Product::tableName();
            $productImageTable = ProductImage::tableName();
            $manufacturerTable = Manufacturer::tableName();
            $typeTable = Type::tableName();
            if ($codeNumberType == 1) {
                $condition = $itemTable . '.code = :codeNumber';
            } else if ($codeNumberType == 2) {
                $condition = $itemTable . '.number = :codeNumber';
            }
            $fields = [
                $itemTable . '.code',
                $itemTable . '.number',
                $itemTable . '.id',
                $productTable . '.name AS productName',
                $productTable . '.description AS productDescription',
                $productTable . '.id',
                $productImageTable . '.image',
                $productImageTable . '.id',
                $manufacturerTable . '.name AS manufacturerName',
                $manufacturerTable . '.brandLogo',
                $manufacturerTable . '.id',
                $typeTable . '.description AS typeDescription',
                $typeTable . '.id AS typeId',
            ];
            $result = Item::find()
                ->select($fields)
                ->from($itemTable)
                ->asArray()
                ->leftJoin([$productTable], $productTable . '.id = ' . $itemTable . '.productId')
                ->leftJoin([$productImageTable], $productImageTable . '.productId = ' . $productTable . '.id')
                ->leftJoin([$manufacturerTable], $manufacturerTable . '.id = ' . $productTable . '.manufacturerId')
                ->leftJoin([$typeTable], $typeTable . '.id = ' . $productTable . '.typeId')
                ->where($condition, [':codeNumber' => $codeNumber])
                ->one();
            if (!$result) {
                return Yii::$app->params['response']['empty'];
            }
            $data = [
                $result['code'] => [
                    'name' => ArrayHelper::getValue($result, 'productName'),
                    'type' => ArrayHelper::getValue($result, 'typeId'),
                    'typeDescription' => ArrayHelper::getValue($result, 'typeDescription'),
                    'optionalParameters' => ArrayHelper::getValue($result, 'typeId') ?
                        OptionalParameters::getOptionalParametersByTypeId($result['typeId']) : null,
                    'brand' => ArrayHelper::getValue($result, 'manufacturerName'),
                    'number' => ArrayHelper::getValue($result, 'number'),
                    'image' => ArrayHelper::getValue($result, 'image'),
                    'brandLogo' => ArrayHelper::getValue($result, 'brandLogo'),
                    'description' => ArrayHelper::getValue($result, 'productDescription'),
                ],
            ];
            Yii::$app->cache->set($codeNumber, serialize($data), Yii::$app->params['cacheDuration']['month']);
        }
        return $data;
    }

    public static function deleteCache($code, $number)
    {
        Yii::$app->cache->delete($code);
        Yii::$app->cache->delete($number);
    }

    public static function deleteCacheThroughRelations($id, $column)
    {
        $rows = (new Query)->select('id')
            ->from(Product::tableName())
            ->where($column . ' = ' . $id)
            ->all();
        if ($rows) {
            Product::deleteCacheFromEventStaticInfo(implode(', ', $rows));
        }
    }

} 