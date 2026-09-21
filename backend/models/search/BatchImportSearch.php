<?php

namespace backend\models\search;

use backend\models\ExteriorUser;
use backend\models\UserInfo;
use Mpdf\Tag\P;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExteriorUserRecord;

/**
 * ExteriorUserRecordSearch represents the model behind the search form of `backend\models\ExteriorUserRecord`.
 */
class BatchImportSearch extends ExteriorUserRecord
{
    public $start_time;
    public $end_time;
    //外部人员
    public $txterior_user;
    //接待人
    public $reception_user;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'txterior_user', 'reception_user'], 'string'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    
    public function searchHistory($params)
    {
        $query = ExteriorUserRecord::find()->alias('eur');
        
        // add conditions that should always apply here
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $query->andWhere(['eur.status' => self::STATUS_GO_OUT]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        
        if (!empty($this->start_time)) {
            
            $query->andWhere(['>', 'eur.entrance_time', strtotime($this->start_time),]);
        }
        
        if (!empty($this->end_time)) {
            
            $query->andWhere(['<', 'eur.entrance_time', strtotime($this->end_time),]);
        }
        
        if ($this->txterior_user) {
            $query->innerJoin(['eu' => ExteriorUser::tableName()], 'eu.id = eur.exterior_user')->andWhere(['like', 'eu.name', $this->txterior_user]);
        }
        
        if ($this->reception_user) {
            $query->innerJoin(['u' => UserInfo::tableName()], 'u.id = eur.create_user')->andWhere(['like', 'u.name', $this->reception_user]);
        }
        
        return $dataProvider;
    }
}

