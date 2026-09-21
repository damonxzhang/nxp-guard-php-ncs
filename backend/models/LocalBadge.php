<?php

namespace backend\models;

class LocalBadge extends Base
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
    
        return 'badge';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return \Yii::$app->get('db3');
    }


}

