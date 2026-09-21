<?php

namespace backend\models;

class LenelAcclvlink  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ACCLVLINK';
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




