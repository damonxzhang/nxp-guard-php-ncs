<?php

namespace backend\models;

class LenelBadge extends Base
{
    const TYPE_FOEMAL = 1;//正式员工
    const TYPE_AUTH   = 9;//包商可接待人
    const TYPE_SNAP1  = 5;//临时员工
    const TYPE_SNAP2  = 6;//临时员工
    const TYPE_SNAP3  = 7;//临时员工
    const TYPE_CAR    = 8;//车卡
    const TYPE_GUARD  = 4;//长期包商
    const TYPE_TEMPORARY  = 3;//长期包商
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
    
        return 'BADGE';
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
     * 比对人员身份
     * @param       $empId
     * @param array $type
     * @return bool
     */
    public function getType($empId, $type = [])
    {
        if (empty($type)) return false;
        $query = self::find();
        $query->select([ 'EMPID' ]);
        $query->where([ 'EMPID' => $empId ]);
        $query->andWhere([ 'in', 'TYPE', $type ]);
        return $query->one() ? true : false;
    }

    /**
     * 获取当前卡状态
     * @param       $empId
     * @param array $type
     * @return bool
     */
    public function getStatus($empId)
    {
        if(empty($empId)) return false;
        $query = self::find();
        $query->select([ 'EMPID','STATUS' ]);
        $query->where([ 'EMPID' => $empId ]);
        // print_r($query->one()->STATUS);die;
        $status = '';
        if($query->one()){
            switch($query->one()->STATUS){
                case 1:
                    $status = 'ACTIVE';
                    break;
                case 3:
                    $status = 'RETURN';
                    break;
                default:
                    $status = '其他';
                    break;
            }
        }
        return $status;
    }
}

