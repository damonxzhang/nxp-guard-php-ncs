<?php

namespace backend\models\search;

use backend\models\ExteriorUser;
use backend\models\UserInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExteriorUserRecord;
use yii\helpers\ArrayHelper;
use backend\models\LenelEmp;
use backend\models\LenelBadge;

class ExteriorUserRecordSearch extends ExteriorUserRecord
{
    public $start_time;
    public $end_time;
    public $user_card_name;
    //外部人员
    public $txterior_user;
    //接待人
    public $reception_user;
    public $badge_type;
    public $factory;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [ [ 'factory','start_time', 'end_time', 'badge_type','user_card_name','txterior_user', 'reception_user' ], 'string' ],
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
        // 通过账号获取厂区信息
        $uid      = \Yii::$app->user->id;
        $factory = 1;
        if(in_array($uid, ExteriorUser::$attjUserInfoArr)){
            $factory = 1;
        }
        if(in_array($uid, ExteriorUser::$tcUserInfoArr)){
            $factory = 2;
        }
        if(in_array($uid, ExteriorUser::$iwhUserInfoArr)){
            $factory = 3;
        }
    
        $query = ExteriorUserRecord::find();
        $query->alias('eur');
        $dataProvider = new ActiveDataProvider([ 'query' => $query ]);
        $this->load($params);
        $query->andWhere([ 'eur.status' => self::$showStatus ]);
        if (!$this->validate()) return $dataProvider;
        $query->innerJoin([ 'eu' => ExteriorUser::tableName() ], 'eu.id = eur.exterior_user');
        $startTime = $this->start_time ? strtotime($this->start_time) : '';
        $endTime   = $this->start_time ? strtotime($this->end_time) : '';
        $query->andFilterWhere([ '>', 'eur.real_entrance_time', $startTime]);
        $query->andFilterWhere([ '<=', 'eur.real_entrance_time', $endTime]);
        $query->andFilterWhere([ 'like', 'eu.name', $this->txterior_user ]);
        if(!empty($this->user_card_name)){
            $model = LenelEmp::findOne(['LASTNAME'=>$this->user_card_name]);
            if($model){
                $query->andFilterWhere([ 'eur.user_card'=> $model->ID]);
            }
        }
        
        $query->andFilterWhere([ 'eur.factory'=> $factory ]);
        $query->orderBy('eur.id desc');
        return $dataProvider;
    }

    /**
     * 查询来访人员信息
     * @param $params
     * @param $id
     * @return ActiveDataProvider
     */
    public function getRegisterUserList($params, $id)
    {
        $query        = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->where([ 'create_user' => $id, 'status' => self::STATUS_AWAIT ]);
        $query->andWhere(['>','get_out_time' ,time()]);
        return $dataProvider;
    }

    /**
     * 查询来访人员信息
     * @param $id
     * @return ActiveDataProvider
     */
    public function getExteriorUserData($id)
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
        if(in_array($uid, ExteriorUser::$iwhUserInfoArr)){
            $factory = 3;
        }
    
        $query        = ExteriorUser::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->andWhere([ 'apply_id' => $id]);
        $query->andWhere([ 'status' => ExteriorUser::STATUS_YES ]);
        $query->andWhere(['>=','get_out_time' ,date('Y-m-d H:i:s', time())]);
        $query->andWhere(['<=','get_enter_time' ,date('Y-m-d H:i:s', time())]);
        $query->andWhere(['factory' =>$factory]);
       
        
        return $dataProvider;
    }

  /**
     * 已完成
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchHistory($params)
    {
        $this->setAttributes($params);
        $query        = ExteriorUserRecord::find()->alias('eur');
        $query->select(['eur.*']);
        // $query->select(['eur.*','eu.name as eu_name','eu.company as eu_company','eu.phone as eu_phone']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->andWhere([ 'in', 'eur.status', [self::STATUS_GO_OUT, self::STATUS_REMARK]]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if (!empty($this->start_time)) {
            $query->andWhere([ '>', 'eur.real_entrance_time', strtotime($this->start_time)]);
        }
        if (!empty($this->end_time)) {
            $query->andWhere([ '<', 'eur.real_entrance_time', strtotime($this->end_time)]);
        }
        if ($this->txterior_user) {
            $query->innerJoin([ 'eu' => ExteriorUser::tableName() ], 'eu.id = eur.exterior_user')->andWhere([ 'like', 'eu.name', trim($this->txterior_user)]);
        }
        if ($this->badge_type) {
            (new ExteriorUserRecord())->changeBadgeType();
            if($this->badge_type == 1){
                $type = [LenelBadge::TYPE_SNAP1,LenelBadge::TYPE_SNAP2,LenelBadge::TYPE_SNAP3]; 
                $query->andWhere([ 'in', 'eur.badge_type',$type]); 
            }elseif ($this->badge_type == 2) {
                $query->andFilterWhere(['>', 'eur.car_card',0]);  
            }elseif ($this->badge_type == 3){
                $query->andWhere([ 'eur.badge_type'=>LenelBadge::TYPE_TEMPORARY]);  
            }
        }
        if(!empty($this->user_card_name)){
            $model = LenelEmp::findOne(['LASTNAME'=>$this->user_card_name]);
            if($model){
                $query->andFilterWhere([ 'eur.user_card'=> $model->ID]);
            }
        }
        if(!empty($this->factory)){
            $query->andFilterWhere([ 'eur.factory'=> $this->factory]);
        }
        $query->orderBy('eur.real_entrance_time DESC');
        return $dataProvider;
    }

   
}
