<?php

namespace backend\models;

class LocalAcclvlink  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'acclvlink';
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




