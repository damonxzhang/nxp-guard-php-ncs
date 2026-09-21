<?php

namespace backend\models\search;

use backend\models\ExteriorRelation;
use yii\data\ActiveDataProvider;

class ExteriorRelationSearch extends ExteriorRelation
{
    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->setAttributes($params);
        $query        = ExteriorRelation::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $query->andFilterWhere([
            'user_pid'     => $this->user_pid,
            'work_id'     => $this->work_id,
            'user_wbi_id' => $this->user_wbi_id
        ]);
        $query->andFilterWhere(['like', 'user_name', $this->user_name]);
        $query->andFilterWhere(['like', 'leader_name', $this->leader_name]);
        $query->andFilterWhere(['in' , 'del',[NULL]]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
