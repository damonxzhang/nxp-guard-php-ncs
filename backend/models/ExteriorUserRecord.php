<?php

namespace backend\models;

use Yii;
use yii\db\Expression;
use backend\models\LenelBadge;
/**
 * This is the model class for table "nxp_exterior_user_record".
 * @property int $id
 * @property string|null $qr_code            二维码参数
 * @property string|null $user_card          临时卡
 * @property int|null $exterior_user         外部人员
 * @property int|null $create_user           申请人
 * @property int|null $reception_user        接待人
 * @property int|null $status                状态
 * @property int|null $type                  状态
 * @property int|null $items                 是否有物品
 * @property int|null $add_user              批量导入人
 * @property string|null $comment            备注
 * @property string|null $entrance_guard     入厂警卫
 * @property int|null $car_card              车辆临时卡
 * @property int|null $entrance              是否进厂
 * @property int|null $entrance_time         进厂时间
 * @property int|null $real_entrance_time    实际进厂时间
 * @property string|null $get_out_guard      出厂警卫
 * @property int|null $get_out_time          出厂时间
 * @property int|null $real_get_out_time     实际出厂时间
 * @property int|null $create_time           创建时间
 * @property int|null $update_time           更新时间
 * @property int|null $delay_result           延迟未归还处理
 * @property string|null $remark
 */
class ExteriorUserRecord extends \backend\models\Base
{
    const RECEPTION_TYPE = 1;//提前申请
    const SCENE_TYPE     = 2;//现场申请

    const STATUS_AWAIT       = 1;
    const STATUS_GREEN_LIGHT = 2;
    const STATUS_ENTER       = 3;
    const STATUS_GO_OUT      = 4;
    const STATUS_REMARK      = 5;

    public static $statusListData = [
        self::STATUS_GREEN_LIGHT => '警卫确认',
        self::STATUS_ENTER       => '已进厂',
        self::STATUS_GO_OUT      => '已出厂',
        self::STATUS_REMARK      => '卡片丢失',
    ];

    /**
     * @var int[]
     */
    public static $showStatus = [
        self::STATUS_AWAIT,
        self::STATUS_GREEN_LIGHT,
        self::STATUS_ENTER
    ];

    /**
     * @var string[]
     */
    public static $statusList = [
        self::STATUS_GREEN_LIGHT => '警卫确认',
        self::STATUS_ENTER       => '已进厂',
        self::STATUS_GO_OUT      => '已出厂',
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
        return 'nxp_exterior_user_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'exterior_user', 'create_user', 'reception_user', 'status', 'entrance_time', 'get_out_time', 'create_time', 'update_time','factory','delay_result'], 'integer'],
            [['vehicle', 'purpose', 'pid', 'workid', 'entrance_guard', 'get_out_guard', 'user_card', 'car_card','remark', 'get_enter_time'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'status'             => '状态',
            'exterior_user'      => '外部人员',
            'create_user'        => '申请人',
            'reception_user'     => '接待人',
            'entrance_guard'     => '入厂警卫',
            'car_card'           => '车辆临时卡',
            'real_entrance_time' => '实际进厂时间',
            'get_out_guard'      => '出厂警卫',
            'user_card'          => '人员临时卡',
            'get_out_time'       => '出厂时间',
            'get_enter_time'     => '计划入厂时间',
            'real_get_out_time'  => '实际出厂时间',
            'create_time'        => 'Create Time',
            'update_time'        => 'Update Time',
            'vehicle'            => '车牌号',
            'type'               => '划分',
            'purpose'            => '来访原因',
            'pid'                => '正式员工pid',
            'workid'             => '包商work_id',
            'remark'             => '备注',
            'factory'            => '厂区',
            'delay_result'            => '延迟未归还处理',
        ];
    }

    public function behaviors()
    {
        return [$this->getTimestampBehaviorArray()];
    }

    public function getRecordUserId($id)
    {
        return self::find()->select('exterior_user')->where(['id' => $id])->one();
    }

    /**
     * 查询外部人员状态
     * @param $id
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getStatusById($id)
    {
        $query = self::find();
        $query->where(['exterior_user' => $id]);
        $query->andWhere(['!=', 'status', self::STATUS_GO_OUT]);
        $query->andWhere(['>', 'get_out_time', time()]);
        return $query->one();
    }

    /**
     * 是否有照片
     */
    public function ifThereIsImg()
    {
        return (new ExteriorPhoto())->ifThereIsImgByRecordId($this->id);
    }

    public function getExteriorUser()
    {
        return $this->hasOne(ExteriorUser::className(), ['id' => 'exterior_user']);
    }

    /**
     * 降读取到的登记id存储到session
     * @param $id
     */
    public static function cacheId($id)
    {
        $key     = 'exteriorId';
        $session = Yii::$app->session;
        $session->remove($key);
        $session->set($key, $id);
    }

    /**
     * 拿到登记id
     * @return mixed
     */
    public static function getCacheId()
    {
        $key     = 'exteriorId';
        $session = Yii::$app->session;
        $id      = $session->get($key);
        return $id;
    }

    /**
     * 拿到登记id
     * @return mixed
     */
    public static function removeCacheId()
    {
        $key     = 'exteriorId';
        $session = Yii::$app->session;
        $session->remove($key);
    }

    /**
     * 查询登记信息
     * @param $phone
     * @return string
     */
    public function checkExteriorUser($phone)
    {
        $query = self::find();
        $query->alias('eur');
        $query->innerJoin(['eu' => ExteriorUser::tableName()], 'eu.id = eur.exterior_user');
        $query->where(new Expression(' eu.phone  =  ' . $phone));
        $query->andWhere(['!=', 'eur.status', self::STATUS_GO_OUT]);
        $query->andWhere(['>', 'eur.get_out_time', time()]);
        $query->limit(1);
        $query->orderBy('id desc');
        return $query->one();
    }

    /**
     * 检查是否有未完成的流程
     * @param $id
     * @return bool
     */
    public function exteriorUserCheck($id)
    {
        $query = self::find();
        $query->where(['exterior_user' => $id]);
        $query->andWhere(['in', 'status', self::$showStatus]);
        $result = $query->one();
        // return $result;
        return $result ? true : false;
    }

    /**
     * @param $id
     * @return false|int
     */
    public function add($id)
    {
        $exteriorUser          = ExteriorUser::findOne($id);
        $model                 = new ExteriorUserRecord();
        $model->type           = $exteriorUser->type;
        $model->purpose        = $exteriorUser->purpose;
        $model->pid            = $exteriorUser->pid;
        $model->workid         = $exteriorUser->workid;
        $model->exterior_user  = $exteriorUser->id;
        $model->create_user    = $exteriorUser->apply_id;
        $model->factory        = $exteriorUser->factory;
        $model->status         = ExteriorUserRecord::STATUS_AWAIT;
        $model->get_enter_time = strtotime($exteriorUser->get_enter_time);//计划入厂日期
        $model->get_out_time   = strtotime($exteriorUser->get_out_time);  //计划出厂日期
        if(!$model->save(false)) {
            return false;
        }
        return $model->id;
    }

     /**
     * 修改临时卡类型
     */
    public function changeBadgeType()
    {
        $userEmpId = ExteriorUserRecord::find()->select(['user_card','id'])->where(['badge_type' => 0])->asArray()->all();
       if (!empty($userEmpId)) {
            foreach ($userEmpId as $key => $value) {
                $badge = LenelBadge::findOne(['EMPID' => $value['user_card']]);
                if ($badge) {
                $model = ExteriorUserRecord::findOne($value['id']);
                $model-> badge_type = $badge->TYPE;
                $model->save(false);
                }
            }
        }
        return true;
    }

}

