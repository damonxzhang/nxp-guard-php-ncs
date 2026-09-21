<?php

namespace backend\models;

use yii\db\Query;

class LenelEvents extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'EVENTS';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return \Yii::$app->get('db2');
    }

    /**
     * 获取RMPID
     * @param $device
     * @return false|string|null
     */
    public function getEmpid($device)
    {
        try {
            $query = self::find();
            $query->select([ 'EMPID' ]);
            $query->where([ 'MACHINE' => $device['machine'] ]);
            $query->andWhere([ 'DEVID' => $device['devid'] ]);
            $query->andWhere([ '>', 'EMPID', 0 ]);
            $query->andWhere([ '>', 'EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime('-482minute')) ]);
            $query->andWhere([ '<=', 'EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime('-480minute')) ]);
            return $query->scalar();
        }catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 获取正式员工刷卡RMPID
     * @param $startTime
     * @param $device
     * @return false|string|null
     */
    public function getEmpIdFoemal($startTime,$device)
    {
        try {
            $startTime = date('Y-m-d H:i:s', $startTime);
            $query = self::find();
            $query->alias('evn');
            $query->distinct();
            $query->select([ 'evn.EMPID' ]);
            $query->leftJoin(LenelBadge::tableName() . ' lb', 'evn.EMPID = lb.EMPID');
            $query->where([ 'evn.MACHINE' => $device['machine'] ]);
            $query->andWhere([ 'evn.DEVID' => $device['devid'] ]);
            $query->andWhere([ 'in', 'lb.TYPE', [LenelBadge::TYPE_FOEMAL, LenelBadge::TYPE_AUTH]]);
            $query->andWhere([ '>', 'evn.EMPID', 0 ]);
            $query->andWhere([ '>', 'evn.EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime("$startTime -28802second"))]);
            return $query->scalar();
        }catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 获取刷卡列表
     * @param $startTime
     * @param $device
     * @return array
     */
    public function getEmpIdData($startTime,$device)
    {
        // $startTime = date('Y-m-d H:i:s', $startTime);
        // $query = self::find();
        // $query->select([ 'EMPID' ]);
        // $query->where([ 'MACHINE' => $device['machine'] ]);
        // $query->andWhere([ 'DEVID' => $device['devid'] ]);
        // $query->andWhere([ '>', 'EMPID', 0 ]);
        // $query->andWhere([ '>', 'EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime("$startTime -28810second"))]);
        // return $query->column();
        try {
            $startTime = date('Y-m-d H:i:s', $startTime);
            $query = self::find();
            $query->select([ 'EMPID' ]);
            $query->where([ 'MACHINE' => $device['machine'] ]);
            $query->andWhere([ 'DEVID' => $device['devid'] ]);
            $query->andWhere([ '>', 'EMPID', 0 ]);
            $query->andWhere([ '>', 'EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime("$startTime -28802second"))]);
            return $query->column();
        }catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取临时员工刷卡RMPID
     * @param $startTime
     * @param $device
     * @return false|string|null
     */
    public function getEmpIdSnap($startTime,$device)
    {
        try {
            $startTime = date('Y-m-d H:i:s', $startTime);
            $query = self::find();
            $query->alias('evn');
            $query->select([ 'evn.EMPID' ]);
            $query->leftJoin(LenelBadge::tableName() . ' lb', 'evn.EMPID = lb.EMPID');
            $query->where([ 'evn.MACHINE' => $device['machine'] ]);
            $query->andWhere([ 'evn.DEVID' => $device['devid'] ]);
            $query->andWhere(['in', 'lb.TYPE', [LenelBadge::TYPE_SNAP1,LenelBadge::TYPE_SNAP2,LenelBadge::TYPE_SNAP3 ]]);
            $query->andWhere([ '>', 'evn.EMPID', 0 ]);
            $query->andWhere([ '>', 'evn.EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime("$startTime -28802second"))]);//减20秒,北门警卫电脑比卡机快四十秒
            return $query->scalar();
        }catch (\Exception $e) {
            return '';
        }
    }

}

