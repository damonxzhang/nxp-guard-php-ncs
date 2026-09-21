<?php

namespace backend\models\search;

use app\models\UserRole;
use backend\models\UserInfo;
use yii\data\ActiveDataProvider;

class UserInfoSearch extends UserInfo
{
    public $name;
    public $pid;
    public $type;
    public $department_number;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'pid', 'type', 'department_number'], 'safe'],
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
        $query = UserInfo::find();
        $query->where(['>', 'id', 1]);
        $query->andFilterWhere(['department_number' => trim($this->department_number)]);
        $query->andFilterWhere(['=', 'type', $this->type]);
        $query->andFilterWhere(['like', 'pid', trim($this->pid)]);
        $query->andFilterWhere(['like', 'name', trim($this->name)]);
        $query->orderBy(['id' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $dataProvider;
    }

}