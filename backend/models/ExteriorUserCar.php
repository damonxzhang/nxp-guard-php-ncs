<?php

namespace backend\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "nxp_exterior_user_car".
 * @property int         $id
 * @property string|null $user_card             临时卡
 * @property int|null    $exterior_user         外部人员
 * @property int|null    $create_user           申请人
 * @property int|null    $reception_user        接待人
 * @property int|null    $status                状态
 * @property int|null    $type                  状态
 * @property int|null    $items                 是否有物品
 * @property int|null    $add_user              批量导入人
 * @property string|null $comment               备注
 * @property string|null $entrance_guard        入厂警卫
 * @property int|null    $car_card              车辆临时卡
 * @property int|null    $entrance              是否进厂
 * @property int|null    $entrance_time         进厂时间
 * @property int|null    $real_entrance_time    实际进厂时间
 * @property string|null $get_out_guard         出厂警卫
 * @property int|null    $get_out_time          出厂时间
 * @property int|null    $real_get_out_time     实际出厂时间
 * @property int|null    $create_time           创建时间
 * @property int|null    $update_time           更新时间
 * @property string|null $remark
 */
class ExteriorUserCar extends \backend\models\Base
{
    const STATUS_WAITE       = 1;
    const STATUS_ENTER       = 2;
    const STATUS_GREEN_LIGHT = 3;
    const STATUS_GO_OUT      = 4;
    const STATUS_REMARK      = 5;

    /**
     * @var int[]
     */
    public static $showStatus = [
        self::STATUS_ENTER,
        self::STATUS_GREEN_LIGHT
    ];

    public static $statusList = [
        self::STATUS_WAITE       => '待警卫确认',
        self::STATUS_ENTER       => '已进厂',
        self::STATUS_GREEN_LIGHT => '警卫确认',
        self::STATUS_GO_OUT      => '已出厂',
        self::STATUS_REMARK      => '卡片丢失',
    ];

    const SELECT_YES = 1;
    const SELECT_NO  = 0;
    /**
     * @var string[]
     */
    public static $itemsList = [
        self::SELECT_NO  => '无',
        self::SELECT_YES => '有',

    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_exterior_user_car';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['create_user', 'status', 'entrance_time', 'get_out_time', 'create_time', 'factory'], 'integer'],
            [['pid','phone','vehicle', 'entrance_guard', 'get_out_guard', 'car_card','remark'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'pid'            => 'PID',
            'status'         => '状态',
            'create_user'    => '申请人',
            'phone'    => '联系方式',
            'entrance_guard' => '入厂警卫',
            'car_card'       => '车辆临时卡',
            'entrance_time'  => '实际进厂时间',
            'get_out_guard'  => '出厂警卫',
            'get_out_time'   => '出厂时间',
            'create_time'    => 'Create Time',
            'vehicle'        => '车牌号',
            'factory'        => '厂区',
            'remark'        => '备注'
        ];
    }

    public function behaviors()
    {
        return [$this->getTimestampBehaviorArray()];
    }


    /**
     * @param $id
     * @return false|int
     */
    public function add($id)
    {
        $exteriorUser         = ExteriorUser::findOne($id);
        $model                = new ExteriorUser();
        $model->type          = $exteriorUser->type;
        $model->purpose       = $exteriorUser->purpose;
        $model->pid           = $exteriorUser->pid;
        $model->workid        = $exteriorUser->workid;
        $model->exterior_user = $exteriorUser->id;
        $model->create_user   = $exteriorUser->apply_id;
        $model->factory       = (new ExteriorUser())->getFactory();
        $model->status        = ExteriorUserRecord::STATUS_AWAIT;
        $model->get_out_time  = strtotime($exteriorUser->get_out_time);//计划出厂日期
        if(!$model->save(false)) {
            return false;
        }
        return $model->id;
    }
}

