<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "nxp_exterior_user".
 *
 * @property int         $id
 * @property string|null $name        中文名
 * @property string|null $phone       联系电话
 * @property string|null $ename       英文名
 * @property string|null $idcard      身份证
 * @property string|null $company     公司名称
 * @property int|null    $create_time 创建时间
 * @property int|null    $update_time 更新时间
 */
class ExteriorUser extends Base
{
    public static $guardArr        = [7030,7035,7039,7042,7041,7049];//警卫
    public static $iwhUserInfoArr  = [1,7048,7049];//大田
    public static $attjUserInfoArr = [1,7031,7030,7035,7034];//旧厂
    public static $tcUserInfoArr   = [1,7038,7039,7040,7041,7042,7043,7046,7044,7045];//新厂
    public static $specialArr = [9065,7593,7595,7591,7602,2369,9792,3525,9784,9790,9796,9059,4442,4453,4451,4322,4478,4402,4457,4325,4332,4471,4410,4444,4338]; //特殊警卫卡，可以随意接人

    //厂区
    const FACTORY_ATTJ = 1;
    const FACTORY_TC   = 2;
    const FACTORY_IWH   = 3;

    public static $factoryList = [
        self::FACTORY_ATTJ => 'ATTJ',
        self::FACTORY_TC   => 'TC',
        self::FACTORY_IWH   => 'IWH'
    ];

    const STATUS_YES = 1;
    const STATUS_NO  = 2;

    public static $statusList = [
        self::STATUS_YES => '启用',
        self::STATUS_NO  => '禁用',

    ];
    const TYPE_FORMAL     = 1;
    const TYPE_CONTRACTOR = 2;

    /**
     * @var string[]
     */
    public static $typeList = [
        self::TYPE_FORMAL     => '正式员工',
        self::TYPE_CONTRACTOR => '包商',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_exterior_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone'], 'required'],
            [['factory', 'create_time', 'type', 'status', 'update_time', 'add_user', 'apply_id'], 'integer'],
            [['vehicle', 'purpose', 'name', 'phone', 'idcard', 'company', 'pid', 'workid', 'get_out_time', 'get_enter_time','personnel_pid'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'name'           => '中文名',
            'phone'          => '联系电话',
            'idcard'         => '身份证',
            'company'        => '公司名称',
            'vehicle'        => '车牌号',
            'type'           => '划分',
            'status'         => '状态',
            'add_user'       => '添加人员',
            'purpose'        => '来访原因',
            'pid'            => '正式员工pid',
            'apply_id'       => '申请人id',
            'workid'         => '包商work_id',
            'factory'        => '厂区',
            'get_enter_time' => '预计进厂时间',
            'get_out_time'   => '预计出厂时间',
            'create_time'    => '创建时间',
            'update_time'    => 'Update Time',
            'personnel_pid'  => '员工登记PID',
        ];
    }

    public function behaviors()
    {
        return [$this->getTimestampBehaviorArray()];
    }

    public static function getName($id)
    {
        $model = self::findOne($id);
        return $model ? $model->name : '';
    }

    public function getUserName($id)
    {
        $model = self::findOne(['id' => $id]);
        return $model ? $model->name : '';
    }

    /**
     * 查询单个字段的值
     * @param $key
     * @param $where
     * @return false|null|string
     */
    public function getScalarByWhere($key, $where)
    {
        return ExteriorUser::find()->where($where)->select($key)->scalar();
    }

    public static function validatePhone($phone)
    {
        if(preg_match("/^1[3456789]{1}\d{9}$/", $phone)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据手机号检查区分厂区
     * @param $phone
     * @return false|int
     * @date 2021/7/8 10:22
     */
    public function getFactorByPhone($phone)
    {
        $userInfoId = \Yii::$app->user->id;
        //登陆账号在旧厂数组里面,并且导入数据中有进入旧厂权限
        if(in_array($userInfoId, self::$attjUserInfoArr)) {
            $checkUser = self::findOne(['phone' => $phone, 'status' => ExteriorUser::STATUS_YES, 'factory' => self::FACTORY_ATTJ]);
            if($checkUser) return self::FACTORY_ATTJ;
        }
        //登陆账号在新厂数组里面,并且导入数据中有进入新厂权限
        if(in_array($userInfoId, self::$tcUserInfoArr)) {
            $checkUser = self::findOne(['phone' => $phone, 'status' => ExteriorUser::STATUS_YES, 'factory' => self::FACTORY_TC]);
            if($checkUser) return self::FACTORY_TC;
        }
        //登陆账号在大田数组里面,并且导入数据中有进入新厂权限
        if(in_array($userInfoId, self::$iwhUserInfoArr)) {
            $checkUser = self::findOne(['phone' => $phone, 'status' => ExteriorUser::STATUS_YES, 'factory' => self::FACTORY_IWH]);
            if($checkUser) return self::FACTORY_IWH;
        }
        return false;
    }

    /**
     * 获取当前登陆账号厂区
     * @return int|string
     * @date 2021/6/30 9:56
     */
    public function getFactory()
    {
        $userInfoId   = \Yii::$app->user->id;
        if(in_array($userInfoId, self::$attjUserInfoArr)) {
            return self::FACTORY_ATTJ;
        }
        if(in_array($userInfoId, self::$tcUserInfoArr)) {
            return self::FACTORY_TC;
        }
    }
}
