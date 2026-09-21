<?php

namespace backend\models\serves;

use app\models\UserRole;
use backend\models\CardRecord;
use backend\models\ExteriorPhoto;
use backend\models\ExteriorUser;
use backend\models\ExteriorUserRecord;
use backend\models\LevelDept;
use backend\models\OneTimeCard;
use backend\models\UserInfo;
use Faker\Provider\Uuid;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use backend\models\LenelEmp;
use backend\models\LenelUdfemp;
use backend\models\ExteriorRelation;

class ExteriorUserRecordServes extends ExteriorUserRecord
{

    const CREATE            = 'create';
    const ENTRANCE_REGISTER = 'entrance_register';
    const GUARD_REGISTER    = 'guard_registration';
    const SCENE_REGISTER    = 'scene_registration';


    //手机号
    public $phone;
    //姓名
    public $name;
    //英文名
    public $ename;
    //公司
    public $company;
    //车辆信息
    public $vehicle;

    //验证后的参数
    public $safeParams;

    /**
     * 接待人卡号
     * @var
     */
    public $receptionUserCode;

    public $entranceGuardCode;

    public $recodeData = [];

    public function scenarios()
    {
        return [
            self::CREATE            => [ 'phone', 'name', 'ename', 'items', 'vehicle', 'type', 'company', 'comment', 'entrance_time', 'get_out_time', ],
            self::ENTRANCE_REGISTER => [ 'reception_user', 'status', 'real_entrance_time' ],
            self::GUARD_REGISTER    => [ 'reception_user', 'status', 'receptionUserCode', 'entranceGuardCode', 'user_card', 'car_card', 'vehicle' ],
            self::SCENE_REGISTER    => [ 'factory','phone', 'name', 'ename', 'items', 'vehicle', 'type', 'company', 'comment', 'get_out_time', 'reception_user' ],
        ];
    }

    public function beforeSave($insert)
    {
        $this->trimAttrSpan();
        return parent::beforeSave($insert);
    }

    public function rules()
    {
        return [
            [ [ 'phone', 'entrance_time', 'get_out_time', 'items', 'real_get_out_time' ], 'integer' ],
            [ [ 'vehicle','phone', 'name', 'ename', 'company', 'receptionUser', 'comment', 'entrance_guard', 'get_out_guard' ], 'string' ],
            [ 'phone', 'match', 'pattern' => "/^1[345789]{1}\d{9}$/" ],
        ];
    }

    /**
     * 保存临时卡借用记录
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveCardRecord()
    {
        if (!empty($this->recodeData)) {
            $params = $this->setCardAndRecord();
            if ($params) {
                $res = $this->batchInsert(CardRecord::tableName(), [
                    'exterior_id',
                    'exterior_user',
                    'car_card',
                    'create_time',
                    'update_time'
                ], $params);

                if (empty($res)) {
                    $this->addError('car_card', '记录保存失败！请联系管理员');
                    return false;
                }

                return true;
            } else {
                return false;
            }
        }
        $this->addError('car_card', '临时卡不存在！');
        return false;
    }

    public function setCardAndRecord()
    {
        $params = [];
        foreach ($this->recodeData as $v) {
            $params[] = [
                'exterior_id'   => $this->id,
                'exterior_user' => $this->exterior_user,
                'car_card'       => $v->id,
                'create_time'   => time(),
                'update_time'   => time(),
            ];

            $v->status = OneTimeCard::STATUS_LOAN;
            if (!$v->save()) {
                $this->addError('car_card', "临时卡更新失败！请联系管理员");
                return false;
            }
        }
        return $params;
    }

    /**
     * 剔除部分属性的前后方空格
     */
    public function trimAttrSpan()
    {


        $trimsKey = [ 'phone', 'user_card', 'receptionUser', 'entrance_guard' ];
        foreach ($this->attributes as $k => $v) {
            if (in_array($k, $trimsKey) && !empty($v)) {
                $this->$k = trim($v);
            }
        }
    }

    /**
     * 查询申请人和接待人是否是同一个部门
     * @return bool
     */
    public function validateReceptionUserSection()
    {
        //提前申请 提前申请的接待人必须和创建人是一个分部
        $createUser    = UserInfo::findOne($this->create_user);//申请人
        $cd            = $createUser ? $createUser->department_number : '';
        $receptionUser = UserInfo::findOne($this->reception_user);//接待人
        $rd            = $receptionUser ? $receptionUser->department_number : '';
        if ($cd != $rd) {
            $this->addError('id', '接待人必须与创建人是相同分部！');
            return false;
        }
        return true;
    }

    /**
     * 查询申请人和接待人是否是同一个部门
     * @return bool
     */
    public function checkDepartment()
    {
        //提前申请 提前申请的接待人必须和创建人是一个分部
        $createDepartment = (new LevelDept())->departmentByEmpId($this->create_user);//申请人
        $receptionDepartment = (new LevelDept())->departmentByEmpId($this->reception_user);//接待人
        if ($createDepartment != $receptionDepartment) {
            $this->addError('id', '接待人必须与创建人是相同分部！');
            return false;
        }
        return true;
    }


     /**
     * 新的查询申请人和接待人是否是同一个部门
     * @return bool
     */
    public function newCheckDepartment()
    {
        $createDepartment    =  $this->getRelationLeaderName($this->create_user);//申请人
        $receptionDepartment =  $this->getRelationLeaderName($this->reception_user);//接待人
        if($createDepartment == '' || $receptionDepartment == ''){
            $this->addError('id', '接待人与申请人必须是同分部！');
            return false;
        }
        if ($createDepartment != $receptionDepartment) {
            $this->addError('id', '接待人必须与申请人是相同分部！');
            return false;
        }
        return true;
    }

    /**
     * 获取部门经理名称
     */
    public function getRelationLeaderName($id)
    {
        $emp    = LenelEmp::findOne(['ID' => $id]);
        if ($emp) {
            $relation = ExteriorRelation::findOne(['user_pid' => $emp->SSNO]);
            return $relation? trim($relation->leader_name) : '';
        }else{
            $udfemp = LenelUdfemp::findOne(['ID' => $id]);
            if ($udfemp) {
                $relation = ExteriorRelation::findOne(['work_id' => $udfemp->ADDR1]);
                return $relation? trim($relation->leader_name) : '';
            }
        }
        return '';
    }

    /**
     * 警卫确认验证接待人和申请人是否是同一个部门
     * @return bool
     */
    public function validateGuardDepartment()
    {
        //提前申请 提前申请的接待人必须和申请人是一个分部
        $createUser    = UserInfo::findOne($this->create_user);//申请人
        $cd            = $createUser ? $createUser->department_number : '';
        $receptionUser = UserInfo::findOne(['emp_id'=>$this->receptionUserCode]);//接待人
        $rd            = $receptionUser ? $receptionUser->department_number : '';
        if ($cd != $rd) {
            $this->addError('id', '接待人必须和申请人是同一个部门！');
            return false;
        }
        return true;
    }

    /**
     * 验证请求的数据
     * @param $data
     * @return bool
     */
    public function validateParams()
    {

        foreach ($this->safeParams as $k => $val) {
            $model = new ExteriorUserRecordServes();
            $model->setScenario($model::CREATE);

            $model->setAttributes($val);
            if (!$model->validate()) {
                $this->addError($k, $model->errorToString());
                return false;
            }
        }
        if (empty($this->safeParams)) {
            $this->addError($k, '参数错误！');
            return false;
        }
        return true;
    }

    /**
     * 更新临时卡状态为正常
     * @throws Exception
     */
    public function updateCardStatus()
    {
        $userCard = OneTimeCard::getCardId($this->user_card, OneTimeCard::STATUS_LOAN);

        if (empty($userCard) || $userCard->id != $this->oldAttributes['user_card']) {
            throw new Exception('人员临时卡卡号错误！');
        }
        $userCard->status = OneTimeCard::SELECT_YES;
        if (!$userCard->save()) {
            throw new Exception('修改失败！请联系管理员！');
        }

        if (!empty($this->oldAttributes['car_card'])) {

            if (!empty($this->car_card)) {
                $Card = OneTimeCard::getCardId($this->user_card, OneTimeCard::STATUS_LOAN);
                if (empty($Card) || $Card->id != $this->oldAttributes['car_card']) {
                    throw new Exception('车辆临时卡卡号错误！');
                }
                $Card->status = OneTimeCard::SELECT_YES;

                if (!$Card->save()) {
                    throw new Exception('修改失败！请联系管理员！');
                }
            } else {
                throw new Exception('请输入车辆临时卡！');
            }
        }
        return true;
    }

    /**
     * 入场
     * @return bool
     */
    public function entrance()
    {
        $this->setAttributes([ 'status' => self::STATUS_ENTER, 'real_entrance_time' => time()]);
        return $this->save();
    }

    /**
     * 验证接待人和警卫
     * @return bool
     */
    public function validateConfirmUser()
    {
        if ($this->validateReceptionUser() && $this->validateEntranceGuardRole(self::STATUS_GREEN_LIGHT)) {
            return true;
        }
        return false;
    }

    /**
     * 验证用户卡号
     * @return bool
     */
    public function validateGetOutUserCode()
    {
        $old = $this->oldAttributes;
        if ($this->user_card != $old['user_card']) {
            $this->addError('user_card', '人员临时卡错误！');
            return false;
        }
        if (!empty($old['car_card']) && $old['car_card'] != $this->car_card) {
            $this->addError('car_card', '车辆临时卡错误！');
            return false;
        }

        if ($this->validateEntranceGuardRole(self::STATUS_GO_OUT)) {

            return true;
        }
        return false;

    }

    /**
     * 验证接待人
     * @return bool
     */
    public function validateReceptionUser()
    {
        $badge_code = UserInfo::find()->where([ 'id' => $this->reception_user ])->select('badge_code')->scalar();
        if (!empty($badge_code) && $badge_code == $this->receptionUserCode) {
            return true;
        }
        $this->addError('receptionUserCode', '接待人卡号错误！');
        return false;
    }

    /**
     * 验证警卫角色
     * @return bool
     */
    public function validateEntranceGuardRole($status)
    {
        $userInfo = new UserInfo();
        $user     = $userInfo->findUserByBadgeCode($this->entranceGuardCode);
        if (!empty($user) && UserRole::find()->where([ 'user_id' => $user->id, 'role_id' => UserRole::GUARD_ROLE ])->exists()) {
            if ($status == self::STATUS_GREEN_LIGHT) {
                $this->entrance_guard = $user->id;
            } else {
                $this->get_out_guard = $user->id;
            }
            return true;
        }
        $this->addError('entranceGuardCode', '只有警卫可以确认！');
        return false;
    }

    /**
     * 记录出厂
     * @return bool
     */
    public function leaveFactory()
    {
        $this->status            = self::STATUS_GO_OUT;
        $this->real_get_out_time = time();
        return $this->save();
    }

    /**
     * 设置参数
     * @return bool
     */
    public function saveRecord($type = 0)
    {

        $params      = [];
        $time        = time();
        $insertTimes = [ 'entrance_time', 'get_out_time', 'items', 'comment', 'exterior_user', 'create_user', 'create_time', 'update_time', 'status', 'type' ];
        foreach ($this->safeParams as $key => $val) {
            $user = $this->getOrCreateExteriorUser($val);
            if (!$user) {
                return false;
            }
            //剔除不是此表的数据
            foreach ($val as $k => $v) {
                if (!in_array($k, $insertTimes)) {

                    unset($val[$k]);
                }
            }
            $params[$key] = $val;
            $params[$key] = array_merge($params[$key], [
                'exterior_user' => $user->id,
                'create_user'   => \Yii::$app->user->id,
                'create_time'   => $time,
                'update_time'   => $time,
                'status'        => self::STATUS_AWAIT,
                'type'          => $type
            ]);
        }
        //保存记录
        $res = \Yii::$app->db->createCommand()->batchInsert(ExteriorUserRecord::tableName(), $insertTimes, $params)->execute();
        if (empty($res)) {
            $this->addError('id', '数据保存失败！请联系管理员');
            return false;
        }
        return true;
    }

    /**
     * 保存现场登记
     * @return bool
     * @throws Exception
     */
    public function saveScene($type,$pid,$apply_id,$vehicle = '',$factory,$name)
    {
        // print_r($factory);die;
        // $this->entrance_time && $this->entrance_time = strtotime($this->entrance_time);
        if($type == 1){
            $query = ExteriorUser::findOne(['phone' => $this->phone,'pid'=>$pid,'factory'=>$factory]);
            $emp    = LenelEmp::findOne(['SSNO' => $pid]);
        }
        else{
            $query = ExteriorUser::findOne(['phone' => $this->phone,'workid'=>$pid,'factory'=>$factory]);
            $emp    = LenelEmp::findOne(['SSNO' => $pid]);
        }
            
        if(!$query)
            return false;

            
        $query->get_enter_time = date("Y-m-d H:i:s", time());
        $query->get_out_time = date("Y-m-d H:i:s", strtotime('+1day'));
        $query->company      = $this->company;
        $query->purpose      = $this->comment;
        $query->name      = $name;
        $query->type         = $type;
        // print_r($emp->ID);die;
        if($emp){
            $query->apply_id         = $emp->ID;
        }
        if($type == 1){
            $query->pid          = $pid;
            $query->workid          = NULL;

        }
        else{
            $query->workid          = $pid;
            $query->pid          = NULL;

        }

        $query->vehicle      = $vehicle;
        $query->status       = ExteriorUser::STATUS_YES;
        // $query->apply_id     = $apply_id;
        $query->factory     = $factory;
        if ($query->save(false)){
            // 更新所有手机号的姓名
            $queryUpdate = ExteriorUser::find();
            $queryUpdate->andWhere(['phone' => $this->phone]);
            $queryUpdatedata = $queryUpdate->asArray()->all();
            foreach ($queryUpdatedata as $key => $value) {
                $queryCheck = ExteriorUser::findOne(['id'=>$value['id']]);
                $queryCheck->name      = $name;
                $queryCheck->save(false);
            }
            return true;

        }
        return false;

        
        $this->get_out_time = strtotime("+1day");
        $this->create_user = $this->reception_user;
        $this->status      = self::STATUS_GREEN_LIGHT;//待登记
        $this->type        = self::SCENE_TYPE;
        if ($this->save(false)) {
            // if (( new ExteriorPhoto() )->savePhoto($this)) {
                return true;
            // } else {
            //     $this->addError('id', '请拍照！');
            // }
        }
        return false;
    }

    /**
     * 设置外部用户setOrSaveUserNew
     * @param $data
     * @return bool
     */
    public function setOrSaveUser($data)
    {
        $exteriorUserId = ( new ExteriorUser() )->getScalarByWhere('id', [ 'phone' => $data['phone'] ]);
        // print_r($exteriorUserId);die;
        if (empty($exteriorUserId)) {
            $model = new ExteriorUser();
            $model->setAttributes($data);
            if (!$model->save()) {
                $this->addError('id', $model->errorToString());
                return false;
            }
            $exteriorUserId = $model->id;
        }
        $this->exterior_user = $exteriorUserId;
        return true;
    }

    /**
     * 设置外部用户
     * @param $data
     * @return bool
     */
    public function setOrSaveUserNew($data)
    {
        if(!empty($data['pid'])){
            $userQuery = ExteriorUser::findOne(['phone' => $data['phone'],'pid' => $data['pid'],'factory'=>$data['factory'] ]);
            // $emp    = LenelEmp::findOne(['SSNO' => $data['pid']]);
        }
        else{
            $userQuery = ExteriorUser::findOne(['phone' => $data['phone'],'workid' => $data['workid'],'factory'=>$data['factory'] ]);
            // $emp    = LenelEmp::findOne(['SSNO' => $data['pid']]);
        }
             

            // print_r($userQuery->id);die;
        if (!$userQuery) {
            $model = new ExteriorUser();
            $data['get_enter_time'] = date('Y-m-d H:i:s',time());
            $model->setAttributes($data);
            
            if (!$model->save(false)) {
                $this->addError('id', $model->errorToString());
                return false;
            }
            $this->exterior_user = $model->id;
            return true;
        }
        $this->exterior_user = $userQuery ->id;
        return true;
    }

    /**
     * 得到外部人员id
     * @param $data
     * @return array|ExteriorUser|bool|\yii\db\ActiveRecord|null
     */
    public function getOrCreateExteriorUser($data)
    {
        $model = new ExteriorUser();
        $user  = $model->find()->where([ 'phone' => $data['phone'] ])->select('id')->one();
        if (!empty($user)) {
            return $user;
        }
        $model->setAttributes($data);
        if (!$model->save()) {
            $this->addError('id', $model->errorToString());
            return false;
        }
        return $model;
    }

    public function savePrepareRecord($reception_user)
    {

        $userModel = $this->getOrCreateExteriorUser([
            'name'    => $this->name,
            'phone'   => $this->phone,
            'ename'   => $this->ename,
            'company' => $this->company,
            'vehicle' => $this->vehicle,
        ]);
        if (!$userModel) {
            $this->addError('phone', '保存失败！');
        }
        $this->entrance_time && $this->entrance_time = strtotime($this->entrance_time);
        $this->get_out_time && $this->get_out_time = strtotime($this->get_out_time);
        $this->status         = self::STATUS_AWAIT;
        $this->type           = self::SCENE_TYPE;
        $this->exterior_user  = $userModel->id;
        $this->create_user    = $reception_user;
        $this->reception_user = $reception_user;
        if ($this->save()) {
            if (( new ExteriorPhoto() )->savePhoto($this)) {
                return true;
            } else {
                $this->addError('id', '请拍照！');
            }
        }
        return false;
    }

    /**
     * 整理数据
     * @param $data
     * @return bool
     */
    public function tidyData($data)
    {
        if (!is_array($data)) {
            $this->addError('id', '请求数据错误！');
            return false;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $val) {
                    if (in_array($key, [ 'get_out_time', 'entrance_time' ])) {
                        if (!empty($val)) {
                            $val = strtotime($val);
                        } else {
                            $val = null;
                        }
                    }
                    $this->safeParams[$k][$key] = $val;
                }
            }
        }
        if (!empty($this->safeParams)) {
            return true;
        }
        $this->addError('id', '参数错误！');
        return false;
    }

    /**
     * 是否有照片
     */
    public function ifThereIsImg()
    {
        return ( new ExteriorPhoto() )->ifThereIsImgByRecordId($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {

        return ArrayHelper::merge([
            'phone'   => '手机号',
            'name'    => '名称',
            'ename'   => '英文名',
            'company' => '公司名称',
        ], parent::attributeLabels());
    }

}