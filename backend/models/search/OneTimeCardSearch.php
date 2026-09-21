<?php

namespace backend\models\search;

use yii\data\ActiveDataProvider;
use backend\models\OneTimeCard;

class OneTimeCardSearch extends OneTimeCard
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['number'], 'safe'],
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->setAttributes($params);
        $query = OneTimeCard::find();
        $query->andFilterWhere(['like', 'number', $this->number]);
        $query->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }
}
