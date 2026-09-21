<?php

namespace backend\models;

class LocalUdfemp  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'udfemp';
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
