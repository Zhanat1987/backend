<?php

namespace app\services;

use Yii;
use app\models\User;
use yii\db\Exception;
use yii\caching\TagDependency;
use app\models\Request;
use yii\redis\Connection as RedisConnection;
use yii\mongodb\Collection;

class CheckUser
{

    const MONTH = 2000;
    const WEEK = 600;
    const DAY = 200;
    const HOUR = 20;

    public static function single($id)
    {
        try {
            /** @var RedisConnection $redis */
            $redis = Yii::$app->redis;
            $key = 'suspiciousUser' . $id;
            if ($redis->exists($key)) {
                $redis->incr($key);
                if ($redis->get($key) >= self::HOUR) {
                    $redis->del($key);
                    self::blockUsers($id, time());
                    Yii::$app->exception->suspiciousUser();
                }
            } else {
                $redis->set($key, 1);
                $redis->expire($key, Yii::$app->params['duration']['hour']);
            }
            return true;
        } catch (Exception $e) {
            Yii::$app->exception->register($e);
        }
    }

    public function all()
    {
        $time = time();
        try {
            $idsByMonth = $this->getBadUsers(self::MONTH, $time - Yii::$app->params['duration']['month']);
            $ids = $idsByMonth ? : '';
            $idsByWeek = $this->getBadUsers(self::WEEK, $time - Yii::$app->params['duration']['week']);
            $ids .= $idsByWeek ? ($ids ? ', ' : '') . $idsByWeek : '';
            $idsByDay = $this->getBadUsers(self::DAY, $time - Yii::$app->params['duration']['day']);
            $ids .= $idsByDay ? ($ids ? ', ' : '') . $idsByDay : '';
            if ($ids) {
                $this->blockUsers($ids, $time);
            }
            return true;
        } catch (Exception $e) {
            return Yii::$app->exception->register($e, 'continue');
        }
        return false;
    }

    private function getBadUsers($count, $fromTimestamp)
    {
        /** @var Collection $collection */
        $collection = Yii::$app->mongodb->getCollection(Request::collectionName());
        $rows = $collection->aggregate(
            [
                '$match' => [
                    '$and' => [
                        ['enable' => 1],
                        ['time' => ['$gt' => $fromTimestamp]],
                    ]
                ]
            ],
            [
                '$group' => [
                    '_id' => ['userId' => '$userId', 'method' => '$method'],
                    'count' => [
                        '$sum' => 1,
                    ],
                ],
            ],
            [
                '$match' => ['count' => ['$gt' => $count]]
            ]
        );
        $ids = [];
        if ($rows) {
            foreach ($rows as $row) {
                if (!in_array($row['_id']['userId'], $ids)) {
                    $ids[] = $row['_id']['userId'];
                }
            }
        }
        return $ids ? implode(',', $ids) : null;
    }

    private function blockUsers($ids, $time)
    {
        User::updateAll(['enable' => 0], 'id IN (:ids)', [':ids' => $ids]);
        Request::updateAll(['enable' => 0], ['userId' => ['$in' => [explode(',', $ids)]]]);
        TagDependency::invalidate(Yii::$app->cache, User::TAG_UUID);
        $message = 'в ' . date(Yii::$app->params['format']['dateTime'], $time) .
            ' были заблокированы пользователи с id: ' . $ids;
        Yii::info($message, 'disableUser');
        return true;
    }

}