<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

class LenelLocation  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'LOCATION';
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
    public static function getStatus()
    {
        $query = self::find();
        $query->select(['ID', 'NAME']);
        $query->andWhere(['>', 'ID', 0]);
        $query->asArray();
        $array = $query->all();
        return ArrayHelper::map($array, 'ID', 'NAME');
    }

}



