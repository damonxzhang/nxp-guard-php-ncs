<?php

namespace backend\models;

use app\models\UserRole;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class UserSpecial extends Base
{
   
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_user_special';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','empid'], 'int'],
            [[
                'name',
                'code'
                ], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'empid'         => 'EmpId',
            'name'          => 'Name',
            'code'          => 'Code',
        ];
    }

}
