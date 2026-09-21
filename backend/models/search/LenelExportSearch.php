<?php

namespace backend\models\search;

use backend\models\ExteriorUser;
use backend\models\UserInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExteriorUserRecord;
use yii\helpers\ArrayHelper;
use backend\models\LenelEmp;

class LenelExportSearch extends \backend\models\Base
{
    public $start_time;
    public $end_time;
    //外部人员
    public $txterior_user;
    //接待人
    public $reception_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [ [ 'start_time', 'end_time', 'txterior_user', 'reception_user' ], 'string' ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * 查询数据
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ExteriorUserRecord::find();
        $query->alias('eur');
        $dataProvider = new ActiveDataProvider([ 'query' => $query ]);
        $this->load($params);
        $query->andWhere([ 'eur.status' => self::$showStatus ]);
        if (!$this->validate()) return $dataProvider;
        $query->innerJoin([ 'eu' => ExteriorUser::tableName() ], 'eu.id = eur.exterior_user');
        $startTime = $this->start_time ? strtotime($this->start_time) : '';
        $endTime   = $this->start_time ? strtotime($this->end_time) : '';
        $query->andFilterWhere([ '>', 'eur.real_entrance_time', $startTime]);
        $query->andFilterWhere([ '<=', 'eur.real_entrance_time', $endTime]);
        $query->andFilterWhere([ 'like', 'eu.name', $this->txterior_user ]);
        $query->orderBy('eur.id desc');
     
        return $dataProvider;
    }

}

