<?php

namespace backend\models;

class LocalBadgelink  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'badgelink';
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


