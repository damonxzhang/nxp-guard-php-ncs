<?php
namespace backend\models;

class    ShelfActionLog extends Base{

    const SOLD_OUT = 1;
    const HIT_SHELF = 2;
    const GET = 3;
    const APPLY = 4;
    const CONFIRM = 5;
    const REVOCATION = 6;
    const GET_LIST = 7;
    const HOLD = 8;
    const TRANSFER = 9;
    const SOLD_OUT_CONFIRM = 10;
    static $actionList = [
        self::SOLD_OUT => '下架物料',
        self::HIT_SHELF => '上架物料',
        self::GET => '领取物料',
        self::APPLY => '申请物料',
        self::CONFIRM => '二次确认领取物料',
        self::REVOCATION => '取消物料申请',
        self::GET_LIST => '领取物料列表',
        self::HOLD => 'hold',
        self::TRANSFER => '物料转移',
        self::SOLD_OUT_CONFIRM => '物料下架二次确认',
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_shelf_action_log';
    }

    public function getUser(){
        return $this->hasOne(UserInfo::tableName(),['id' => 'user_id']);
    }

    public static function saveLog($user_id,$action,$value){
        $model = new self();
        $model->user_id = $user_id;
        $model->action = $action;
        $model->value = json_encode($value);
        return $model->save();
    }


}