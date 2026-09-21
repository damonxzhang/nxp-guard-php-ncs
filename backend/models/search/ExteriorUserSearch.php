<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExteriorUser;

class ExteriorUserSearch extends ExteriorUser
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','phone','idcard','company'], 'safe'],
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
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->setAttributes($params);
        $query = ExteriorUser::find();
        $query->andFilterWhere(['like', 'name', trim($this->name)]);
        $query->orderBy(['update_time' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }

    public function searchList($params)
    {
        $this->setAttributes($params);
        $query = ExteriorUser::find();
        $query->andFilterWhere(['like', 'name', trim($this->name)]);
        $query->andFilterWhere(['like', 'phone', trim($this->phone)]);
        $query->andFilterWhere(['idcard'=> trim($this->idcard)]);
        $query->andFilterWhere(['like', 'company', trim($this->company)]);
        $query->orderBy(['update_time' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }
}
