<?php

namespace backend\models;

class LenelBuilding  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'BUILDING';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return \Yii::$app->get('db2');
    }

}



