<?php

namespace app\services\event;

use yii\base\Model;
use Yii;
use app\models\Item;
use app\models\Product;
use app\models\ProductImage;
use app\models\Manufacturer;
use app\models\Type;
use app\models\OptionalParameters;
use yii\helpers\ArrayHelper;
use yii\db\Query;

/**
 * Class DynamicInfo
 * @package app\services\event
 *
 * @property string $codeNumber
 * @property integer $codeNumberType
 */
class StaticInfo extends Model
{

    public $codeNumber, $codeNumberType;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codeNumber', 'codeNumberType'], 'required'],
            [['codeNumberType'], 'integer'],
            [['codeNumber'], 'string', 'length' => [9, 32]],
            [['codeNumberType'], 'in', 'range' => [1, 2]],
        ];
    }

    public function beforeValidate()
    {
        $this->codeNumberType = (int) $this->codeNumberType;
        return parent::beforeValidate();
    }

    public function execute()
    {
        if (($data = unserialize(Yii::$app->cache->get($this->codeNumber))) === false) {
            $itemTable = Item::tableName();
            $productTable = Product::tableName();
            $productImageTable = ProductImage::tableName();
            $manufacturerTable = Manufacturer::tableName();
            $typeTable = Type::tableName();
            $condition = $itemTable . ($this->codeNumberType == 1 ? '.code = :codeNumber' : '.number = :codeNumber');
            $fields = [
                $itemTable . '.code',
                $itemTable . '.number',
                $itemTable . '.id',
                $productTable . '.name AS productName',
                $productTable . '.name_ru AS productNameRU',
                $productTable . '.name_kz AS productNameKZ',
                $productTable . '.description AS productDescription',
                $productTable . '.description_ru AS productDescriptionRU',
                $productTable . '.description_kz AS productDescriptionKZ',
                $productTable . '.id',
                $productImageTable . '.image',
                $productImageTable . '.id',
                $manufacturerTable . '.name AS manufacturerName',
                $manufacturerTable . '.name_ru AS manufacturerNameRU',
                $manufacturerTable . '.name_kz AS manufacturerNameKZ',
                $manufacturerTable . '.brandLogo',
                $manufacturerTable . '.id',
                $typeTable . '.description AS typeDescription',
                $typeTable . '.description_ru AS typeDescriptionRU',
                $typeTable . '.description_kz AS typeDescriptionKZ',
                $typeTable . '.id AS typeId',
            ];
            /*
             * собрать через with (relations) - будет больше запросов,
             * поэтому делаем все одним запросом с join'ами
             */
            $rows = Item::find()
                ->select($fields)
                ->from($itemTable)
                ->asArray()
                ->leftJoin([$productTable], $productTable . '.id = ' . $itemTable . '.productId')
                ->leftJoin([$productImageTable], $productImageTable . '.productId = ' . $productTable . '.id')
                ->leftJoin([$manufacturerTable], $manufacturerTable . '.id = ' . $productTable . '.manufacturerId')
                ->leftJoin([$typeTable], $typeTable . '.id = ' . $productTable . '.typeId')
                ->where($condition, [':codeNumber' => $this->codeNumber])
                ->all();
            if (!$rows) {
                return Yii::$app->params['response']['empty'];
            }
            $data = self::getData($rows);
            Yii::$app->cache->set($this->codeNumber, serialize($data), Yii::$app->params['duration']['month']);
        }
        return $data;
    }

    private static function getData($rows)
    {
        $productImages = [];
        foreach ($rows as $row) {
            $productImages[] = $row['image'];
        }
        $row = $rows[0];
        $data = [
            $row['code'] => [
                'name' => ArrayHelper::getValue($row, 'productName' .
                    Yii::$app->current->getLanguagePostfix()),
                'type' => ArrayHelper::getValue($row, 'typeId'),
                'typeDescription' => ArrayHelper::getValue($row, 'typeDescription' .
                    Yii::$app->current->getLanguagePostfix()),
                'optionalParameters' => ArrayHelper::getValue($row, 'typeId') ?
                    OptionalParameters::getOptionalParametersByTypeId($row['typeId']) : null,
                'brand' => ArrayHelper::getValue($row, 'manufacturerName' .
                    Yii::$app->current->getLanguagePostfix()),
                'number' => ArrayHelper::getValue($row, 'number'),
                'images' => $productImages,
                'brandLogo' => ArrayHelper::getValue($row, 'brandLogo'),
                'description' => ArrayHelper::getValue($row, 'productDescription' .
                    Yii::$app->current->getLanguagePostfix()),
            ],
        ];
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