<?php

namespace backend\models;

use app\models\UserRole;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class UserContractor extends Base
{
    //设备列表
    public static $indexCodeArr = [
        '794059621e314822a89e2e776b6e20fe',//前厅
        'a9870e299cff41df868721871bcff4ec',//东南门
        'e80e362285b144418306f1c314bc7f56'//北门
    ];

    const IS_DEL_NO   = 1;
    const IS_DEL_YES  = 2;

    //删除状态
    public static $isDelArr = [
        self::IS_DEL_YES  => '已删除',
        self::IS_DEL_NO   => '正常',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_user_contractor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','create_time','update_time','is_del'], 'int'],
            [[
                'ebarcode',
                'name',
                'workid',
                'depid',
                'compid',
                'ebdate',
                'empphoto',
                'contactid',
                'contactname',
                'contactdepart',
                'idcard',
                'inputdate',
                'depname'
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
            'ebarcode'      => 'EBarcode',
            'name'          => 'Name',
            'workid'        => 'WorkID',
            'depid'         => 'DepID',
            'compid'        => 'CompID',
            'is_del'        => 'IS_DEL',
            'ebdate'        => 'EBDate',
            'empphoto'      => 'EmpPhoto',
            'contactid'     => 'ContactID',
            'contactname'   => 'ContactName',
            'contactdepart' => 'ContactDepart',
            'idcard'        => 'IDCard',
            'inputdate'     => 'InputDate',
            'depname'       => 'DepName',
            'create_time'   => 'create_time',
            'update_time'   => 'update_time',
        ];
    }

}
