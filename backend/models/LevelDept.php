<?php

namespace backend\models;

class LevelDept  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'DEPT';
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
     * 根据empid获取部门
     * @param $empid
     * @return false|mixed|null
     */
    public function departmentByEmpId($empid)
    {
        $udfemp = LenelUdfemp::findOne(['ID' => $empid]);
        if (!$udfemp) {
            return false;
        }
        $dept = LevelDept::findOne(['ID' => $udfemp->DEPT]);
        if (!$dept) {
            return false;
        }
        return $dept->NAME;
    }

    /**
     * 根据pid获取部门
     * @param $pid
     * @return false|mixed|null
     */
    public function departmentByPid($pid)
    {
        $emp = LenelEmp::findOne(['SSNO' => $pid]);
        if (!$emp) {
            return false;
        }
        $udfemp = LenelUdfemp::findOne(['ID' => $emp->id]);
        if (!$udfemp) {
            return false;
        }
        $dept = LevelDept::findOne(['ID' => $udfemp->DEPT]);
        if (!$dept) {
            return false;
        }
        return $dept->NAME;
    }

    /**
     * 根据workid 获取部门
     * @param $workid
     * @return false|mixed|null
     */
    public function departmentByWorkId($workid)
    {
        $udfemp = LenelUdfemp::findOne(['ADDR1' => $workid]);
        if (!$udfemp) {
            return false;
        }
        $dept = LevelDept::findOne(['ID' => $udfemp->DEPT]);
        if (!$dept) {
            return false;
        }
        return $dept->NAME;
    }
}
