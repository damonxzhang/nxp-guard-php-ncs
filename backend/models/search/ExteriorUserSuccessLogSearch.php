<?php
namespace backend\models\search;

use backend\models\ExteriorUserSuccessLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ExteriorUserSuccessLogSearch extends ExteriorUserSuccessLog
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'safe'],
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
        $query = ExteriorUserSuccessLog::find();
        $query->andFilterWhere(['like', 'name', trim($this->name)]);
        $query->orderBy(['create_time' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }
}
