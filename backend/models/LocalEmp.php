<?php

namespace backend\models;

class LocalEmp  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'emp';
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
