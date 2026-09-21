<?php

namespace backend\models\search;

use backend\models\ExteriorUserRecord;
use yii\data\ActiveDataProvider;
use backend\models\CardRecord;
use yii\helpers\ArrayHelper;
use backend\models\LenelEmp;
use backend\models\LenelBadge;
use backend\models\ExteriorUser;

class CardRecordSearch extends CardRecord
{

    public $start_time;
    public $end_time;
    public $user_card_name;
    public $badge_type;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time','user_card_name','badge_type'], 'safe'],
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->setAttributes($params);
        $query        = ExteriorUserRecord::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $query->andWhere(['in','status' , [ExteriorUserRecord::STATUS_ENTER, ExteriorUserRecord::STATUS_GO_OUT, ExteriorUserRecord::STATUS_REMARK]]);
        $startTime = ArrayHelper::getValue($this, 'start_time', '');
        $endTime   = ArrayHelper::getValue($this, 'end_time', '');
        $startTime = $startTime ? strtotime($startTime) : '';
        $endTime   = $endTime ? strtotime($endTime) : '';
        $query->andFilterWhere(['>', 'real_entrance_time', $startTime]);
        $query->andFilterWhere(['<=', 'real_entrance_time', $endTime]);
        if ($this->badge_type) {
            (new ExteriorUserRecord())->changeBadgeType();
            if($this->badge_type == 1){
                $type = [LenelBadge::TYPE_SNAP1,LenelBadge::TYPE_SNAP2,LenelBadge::TYPE_SNAP3]; 
                $query->andWhere([ 'in', 'badge_type',$type]); 
            }elseif ($this->badge_type == 2) {
                $query->andFilterWhere(['>', 'car_card',0]);  
            }elseif ($this->badge_type == 3){
                $query->andWhere([ 'badge_type'=>LenelBadge::TYPE_TEMPORARY]);  
            }
        }
        if(!empty($this->user_card_name)){
            $model = LenelEmp::findOne(['LASTNAME'=>trim($this->user_card_name)]);
            if($model){
                $query->andFilterWhere([ 'user_card'=> $model->ID]);
            }
        }
        
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
        $query        = ExteriorUserRecord::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $query->andWhere(['status' => ExteriorUserRecord::STATUS_ENTER]);
        $query->andWhere(['factory' => $factory]);
        $query->andWhere(['in','delay_result' ,[null,1]]);
        $query->andFilterWhere(['<=', 'real_entrance_time', strtotime("-24hour")]);
        $query->orderBy('id desc');
        return $dataProvider;
    }
}
