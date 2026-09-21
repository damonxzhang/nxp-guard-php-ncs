<?php

namespace backend\models\search;

use backend\models\UserCard;
use yii\data\ActiveDataProvider;
use backend\models\LenelEmp;
use backend\models\LenelBadge;

class UserCardSearch extends UserCard
{
  public $user_card_name;

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['depid','user_card_name'], 'safe'],
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
    $query = UserCard::find();
    // $query->andFilterWhere(['id' => $this->empid]);
    $query->orderBy(['id' => SORT_DESC]);
    $dataProvider = new ActiveDataProvider(['query' => $query]);
    if(!empty($this->user_card_name)){
      $model = LenelEmp::findOne(['LASTNAME'=>trim($this->user_card_name)]);
      if($model){
          $query->andFilterWhere([ 'empid'=> $model->ID]);
          // print_r($model->ID);die;
      }
    }
    return $dataProvider;
  }

}