<?php
namespace  backend\models\search;

use backend\models\User;
use yii\data\ActiveDataProvider;

class UserSearch extends User {

    public $name;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'safe'],
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
        $query = User::find();
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }

}