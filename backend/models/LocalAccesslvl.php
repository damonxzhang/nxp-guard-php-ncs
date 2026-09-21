<?php

namespace backend\models;

use yii\helpers\ArrayHelper;


class LocalAccesslvl  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accesslvl';
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



