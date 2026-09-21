<?php
namespace  backend\models\search;

use backend\models\Role;
use yii\data\ActiveDataProvider;

class RoleSearch extends Role {

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
        $query = Role::find();
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->orderBy('id desc');
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }

}