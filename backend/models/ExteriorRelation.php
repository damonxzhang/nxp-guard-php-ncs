<?php

namespace backend\models;

/**
 * This is the model class for table "nxp_exterior_relation".
 *
 * @property int $id
 * @property int|null $create_time 创建时间
 * @property int|null $update_time 更新时间
 */
class ExteriorRelation extends \backend\models\Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_exterior_relation';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'user_pid', 'user_wbi_id', 'work_id','user_name', 'leader_pid', 'leader_name','depid','badge'], 'string'],
            [[ 'update_time', 'create_time'], 'integer'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'user_pid'       => '员工PID',
            'user_wbi_id'   => '员工WbiId',
            'user_name'      => '员工姓名',
            'leader_pid'     => '经理PID',
            'depid'          => 'DEPID',
            'work_id'        => 'Work Id',
            'badge'          => 'Badge',
            'leader_name'    => '经理姓名',
            'create_time'    => '创建时间',
            'update_time'    => 'Update Time',
        ];
    }
   
}
