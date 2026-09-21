<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExteriorUserCar;
use yii\helpers\ArrayHelper;
use backend\models\ExteriorUser;

class ExteriorUserCarSearch extends ExteriorUserCar
{
    public $start_time;
    public $end_time;
    public $username;
    public $vehicle;
     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [ [ 'start_time', 'end_time','username','vehicle'], 'string' ],
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
     * 查询数据
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->setAttributes($params);
        $query        = ExteriorUserCar::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $query->andWhere(['in','status' , [ExteriorUserCar::STATUS_WAITE, ExteriorUserCar::STATUS_ENTER]]);
//        $startTime = ArrayHelper::getValue($this, 'start_time', '');
//        $endTime   = ArrayHelper::getValue($this, 'end_time', '');
//        $startTime = $startTime ? strtotime($startTime) : '';
//        $endTime   = $endTime ? strtotime($endTime) : '';
//        $query->andFilterWhere(['>', 'real_entrance_time', $startTime]);
//        $query->andFilterWhere(['<=', 'real_entrance_time', $endTime]);
        $query->andWhere(['factory'=>(new ExteriorUser())->getFactory()]);
        $query->orderBy('id desc');
        return $dataProvider;
    }

    public function history($params)
    {
        $this->setAttributes($params);
        $query        = ExteriorUserCar::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
      //  $query->andWhere(['in','status' , [ExteriorUserCar::STATUS_WAITE, ExteriorUserCar::STATUS_ENTER]]);
        $startTime = ArrayHelper::getValue($this, 'start_time', '');
        $endTime   = ArrayHelper::getValue($this, 'end_time', '');
        $startTime = $startTime ? strtotime($startTime) : '';
        $endTime   = $endTime ? strtotime($endTime) : '';
        $query->andFilterWhere(['>', 'entrance_time', $startTime]);
        $query->andFilterWhere(['<=', 'entrance_time', $endTime]);
        $query->andFilterWhere([ 'like', 'vehicle', $this->vehicle ]);
        $query->andWhere(['factory'=>(new ExteriorUser())->getFactory()]);
        $query->orderBy('id desc');
        return $dataProvider;
    }

    
    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchDelay()
    {
        // 通过账号获取厂区信息
        $uid      = \Yii::$app->user->id;
        $factory = 1;
        if(in_array($uid, ExteriorUser::$attjUserInfoArr)){
            $factory = 1;
        }
        if(in_array($uid, ExteriorUser::$tcUserInfoArr)){
            $factory = 2;
        }

        $this->setAttributes([]);
        $query        = ExteriorUserCar::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $query->andWhere(['status' => 2]);
        $query->andWhere(['factory' => $factory]);
        // $query->andWhere(['in','delay_result' ,[null,1]]);
        $query->andFilterWhere(['<=', 'entrance_time', strtotime("-24hour")]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
