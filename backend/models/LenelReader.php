<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

class LenelReader  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'READER';
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
     * @return array|\yii\db\ActiveRecord[]
     * @date 2021/7/16 14:38
     */
    public static function getReader()
    {
        $query = self::find();
        $query->select(['READERDESC']);
        $query->asArray();
        $array = $query->all();
        return ArrayHelper::map($array, 'READERDESC', 'READERDESC');
    }
}


