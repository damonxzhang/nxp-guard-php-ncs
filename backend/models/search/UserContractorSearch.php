<?php

namespace backend\models\search;

use backend\models\UserContractor;
use yii\data\ActiveDataProvider;

class UserContractorSearch extends UserContractor
{

    public $name;
    public $workid;
    public $depid;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'workid', 'depid'], 'safe'],
        ];
    }

    /**
     * 数据查询
     * @param $param
     * @return ActiveDataProvider
     */
    public function search($param)
    {
        $this->setAttributes($param);
        $query = UserContractor::find();
        $query->andFilterWhere(['like', 'name', trim($this->name)]);
        $query->andFilterWhere(['like', 'workid', trim($this->workid)]);
        $query->andFilterWhere(['like', 'depid', trim($this->depid)]);
        $query->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }

}