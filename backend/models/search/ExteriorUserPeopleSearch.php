<?php

namespace backend\models\search;

use backend\models\ExteriorUserPeople;
use yii\data\ActiveDataProvider;

class ExteriorUserPeopleSearch extends ExteriorUserPeople
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['depid'], 'safe'],
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
        $query = ExteriorUserPeople::find();
        $query->andFilterWhere(['empid' => $this->empid]);
        $query->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }

}