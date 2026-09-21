<?php

namespace backend\models;
use yii\helpers\ArrayHelper;
class LocalBadgeTyp  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'badgetyp';
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


