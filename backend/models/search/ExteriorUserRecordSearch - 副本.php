<?php

namespace backend\models\search;

use backend\models\ExteriorUser;
use backend\models\UserInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExteriorUserRecord;
use yii\helpers\ArrayHelper;
use backend\models\LenelEmp;

class ExteriorUserRecordSearch extends ExteriorUserRecord
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

    /**
     * 查询来访人员信息
     * @param $params
     * @param $id
     * @return ActiveDataProvider
     */
    public function getRegisterUserList($params, $id)
    {
        $query        = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->where([ 'create_user' => $id, 'status' => self::STATUS_AWAIT ]);
        $query->andWhere(['>','get_out_time' ,time()]);
        return $dataProvider;
    }

    /**
     * 查询来访人员信息
     * @param $id
     * @return ActiveDataProvider
     */
    public function getExteriorUserData($id)
    {
        $query        = ExteriorUser::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->where([ 'apply_id' => $id, 'status' => ExteriorUser::STATUS_YES ]);
        $query->andWhere(['>','get_out_time' ,date('Y-m-d', time())]);
        return $dataProvider;
    }

  /**
     * 已完成
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchHistory($params)
    {
        $query        = ExteriorUserRecord::find()->alias('eur');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->andWhere([ 'in', 'eur.status', [self::STATUS_GO_OUT, self::STATUS_REMARK]]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if (!empty($this->start_time)) {
            $query->andWhere([ '>', 'eur.real_entrance_time', strtotime($this->start_time), ]);
        }
        if (!empty($this->end_time)) {
            $query->andWhere([ '<', 'eur.real_entrance_time', strtotime($this->end_time), ]);
        }
        if ($this->txterior_user) {
            $query->innerJoin([ 'eu' => ExteriorUser::tableName() ], 'eu.id = eur.exterior_user')->andWhere([ 'like', 'eu.name', trim($this->txterior_user) ]);
        }
        if ($this->reception_user) {
            $query->innerJoin([ 'u' => LenelEmp::tableName() ], 'u.ID = eur.create_user')->andWhere([ 'like', 'u.MIDNAME', trim($this->reception_user) ]);
        }
        $query->orderBy('eur.id desc');
        return $dataProvider;
    }
}
