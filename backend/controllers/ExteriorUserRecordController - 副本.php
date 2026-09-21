<?php

namespace backend\controllers;

use common\models\FtpUpdate;
use backend\models\LenelBadge;
use backend\models\LenelEmp;
use backend\models\LevelDept;
use backend\models\search\ExteriorUserRecordSearch;
use backend\models\serves\ExteriorUserRecordServes;
use backend\models\ExteriorUserRecord;
use yii\web\NotFoundHttpException;
use backend\models\ExteriorMark;
use backend\models\ExteriorPhoto;
use backend\models\ExteriorUser;
use backend\models\FormAnswer;
use backend\models\LenelEvents;
use backend\models\UserInfo;
use xj\qrcode\widgets\Text;
use yii\helpers\ArrayHelper;
use yii\db\Exception;
use yii\helpers\Json;
use yii\helpers\Url;
use xj\qrcode\QRcode;
use Yii;
use backend\models\UserCard;

class ExteriorUserRecordController extends BaseController
{
    /**
     * 登记列表
     * @return string
     */
    public function actionIndex()
    {

        $query        = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorUserRecordSearch();
        $dataProvider = $searchModel->search($query);
        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 用户须知
     * @return string
     */
    public function actionAgreement()
    {
        return $this->render('agreement');
    }

    /**
     * 根据手机号抓取入场信息
     * @return string
     * @date 2021/7/8 10:21
     */
    public function actionGetPhone()
    {
        $phone = Yii::$app->request->post('phone', '');
        if(empty($phone)) {
            return $this->json(100, '手机号不能为空！');
        }
        $getFactory = (new ExteriorUser())->getFactorByPhone($phone);
        if(!$getFactory) {
            return $this->json(100, '未查询到入场申请信息！');
        }
        if($getFactory == ExteriorUser::FACTORY_ATTJ) {
            $factory = ExteriorUser::FACTORY_ATTJ;
        } else {
            $factory = ExteriorUser::FACTORY_TC;
        }
        $data = ExteriorUser::find()->select(['id', 'purpose', 'name'])->where(['phone' => $phone, 'status' => ExteriorUser::STATUS_YES, 'factory' => $factory])->asArray()->all();
        if(!$data) {
            return $this->json(100, '未查询到入场申请数据！');
        }
        return $this->json(200, '查询成功!', $data);
    }

    /**
     * 手机号登记
     * @return string
     */
    public function actionRegister()
    {
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('register');
        }
        //查询来访人员
        $id    = Yii::$app->request->post('id', '');
        $model = ExteriorUser::findOne(['id' => $id]);
        if(!$model) {
            return $this->json(false, '未查询到入场申请信息！');
        }
        //        //预计进厂时间
        //        if(strtotime($model->get_enter_time) < time()) {
        //            return $this->json(false, '抱歉未到申请入场时间,暂时不能进厂!');
        //        }
        //预计出厂时间
        if(strtotime($model->get_out_time) < time()) {
            return $this->json(false, '抱歉申请出场时间小于当前时间,请重新申请!');
        }
        //获取登记人员状态
        $check = (new ExteriorUserRecord())->exteriorUserCheck($model->id);
        if($check) {
            return $this->json(false, '手机号已登记进厂！');
        }
        //添加进厂人员信息
        $recordId = (new ExteriorUserRecord())->add($model->id);
        if(!$recordId) {
            return $this->json(false, '数据添加失败！');
        }
        return $this->json(true, 'Success！', Url::to(['register-user', 'id' => $recordId]));

    }

    /**
     * 入场登记
     * @param $id
     * @return string
     */
    public function actionRegisterUser($id)
    {
        $model = ExteriorUserRecordServes::findOne($id);
        $model->setScenario($model::ENTRANCE_REGISTER);
        $isPost = Yii::$app->request->isPost;
        if($isPost) {
            $post = Yii::$app->request->post();
            // $fileIdArr = ArrayHelper::getValue($post, 'file_id', []);
            // if (empty($fileIdArr)) {
            //     return $this->json(100, '请拍照!');
            // }
            $model->load($post);
            $tran = Yii::$app->db->beginTransaction();
            try {
                $model->status = $model::STATUS_GREEN_LIGHT;
                //if ($model->checkDepartment() && $model->save(false)) {
                if($model->save(false)) {
                    // $photoModel = new ExteriorPhoto();
                    // if (!$photoModel->saveCamera($model, $fileIdArr)) {
                    //     throw new Exception($photoModel->errorToString());
                    // }
                } else {
                    throw new Exception($model->errorToString());
                }
                $tran->commit();
                return $this->json(200, '保存成功！');
            } catch(\Exception $e) {
                $tran->rollBack();
                return $this->json(100, $e->getMessage());
            }
        }
        return $this->render('register-user', [
            'model' => $model
        ]);
    }


    /**
     * 获取刷卡记录
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionGetRegisterCard()
    {
        //提交时间
        $request   = Yii::$app->request->queryParams;
        $startTime = ArrayHelper::getValue($request, 'startTime', '');
        if(empty($startTime)) {
            $startTime = time();
        }
        $data['startTime'] = $startTime;
        //查询卡机数据
        $uid      = \Yii::$app->user->id;
        $userInfo = UserInfo::findOne(['id' => $uid]);
        if(!$userInfo) {
            return $this->json(100, '登陆人员信息不正确！', $data);
        }
        //获取接待人刷卡记录
        $device = Json::decode($userInfo->device);
        $empId  = (new LenelEvents())->getEmpIdFoemal($startTime, $device);
        if(!$empId) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        $lenelEmp = LenelEmp::find()->where(['ID' => $empId])->asArray()->one();
        if(!$lenelEmp) {
            return $this->json(500, '未查询到人员信息！', $data);
        }
        $department = (new LevelDept())->departmentByEmpId($empId);
        if(!$department) {
            return $this->json(500, '未查询到部门信息！', $data);
        }

        $lenelEmp['department'] = $department;
        $lenelEmp['startTime']  = $startTime;
        return $this->json(200, '刷卡成功！', $lenelEmp);
    }

    /**
     * 警卫确认
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEntrance($id)
    {
        $model = $this->findModel($id);
        //照片
        $files = ExteriorPhoto::findOne(['record_id' => $model->id]);
        if($files) {
            $files = $files->setFilePath();
        }
        $receptionUser = LenelEmp::find()->where(['ID' => $model->reception_user])->asArray()->one();
        if(!$receptionUser) {
            return $this->json(500, '未查询到人员信息！');
        }
        $department = (new LevelDept())->departmentByEmpId($model->reception_user);
        if(!$department) {
            return $this->json(500, '未查询到部门信息！');
        }
        $receptionUser['department'] = $department;
        return $this->render('entrance', [
            'model'         => $model,
            'files'         => $files,
            'receptionUser' => $receptionUser,
        ]);
    }

    /**
     * 详情
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = ExteriorUserRecord::findOne($id);
        //接待人
        $lenelEmp   = LenelEmp::find()->where(['ID' => $model->reception_user])->asArray()->one();
        $department = (new LevelDept())->departmentByEmpId($model->reception_user);
        if($department) {
            $lenelEmp['department'] = $department;
        }
        return $this->render('view', [
            'model' => $model, 'receptionUser' => $lenelEmp
        ]);
    }

    /**
     * 警卫保存数据
     * @return string
     */
    public function actionEntranceAdd()
    {
        $post  = Yii::$app->request->post();
        $model = ExteriorUserRecord::findOne($post['id']);
        if(!$model) {
            return $this->json(100, '数据不完整！');
        }
        // $check = ExteriorUserRecord::findOne(['car_card' => $post['car_card'], 'status' => ExteriorUserRecord::STATUS_ENTER]);
        // if ($check) {
        //     return $this->json(100, '此卡已被领用!');
        // }
        //保存数据
        if(empty($post['user_card'])) {
            return $this->json(100, '请刷人员临时卡!');
        }
        $model->entrance_guard     = \Yii::$app->user->id;
        $model->vehicle            = $post['vehicle'];
        $model->user_card          = $post['user_card'];
        $model->car_card           = $post['car_card'];
        $model->status             = ExteriorUserRecord::STATUS_ENTER;
        $model->real_entrance_time = time();
        if(!$model->save(false)) {
            return $this->json(100, '保存失败!');
        }
        return $this->json(200, '保存成功');
    }

    /**
     * 抓取人员信息
     * @return string
     */
    public function actionGetUserCard()
    {
        //提交时间
        $request   = Yii::$app->request->queryParams;
        $startTime = ArrayHelper::getValue($request, 'startTime', '');
        if(empty($startTime)) {
            $startTime = time();
        }
        $data['startTime'] = $startTime;
        //查询卡机数据
        $uid      = \Yii::$app->user->id;
        $userInfo = UserInfo::findOne(['id' => $uid]);
        if(!$userInfo) {
            return $this->json(100, '登陆人员信息不正确！', $data);
        }

        //返回的是EMPID
        $data['foemal']      = '';  //正式员工
        $data['guard']       = '';  //警卫
        $data['snap']        = '';  //临时员工
        $data['car']         = '';  //车卡
        $data['foemal_name'] = '';  //正式员工
        $data['guard_name']  = '';  //警卫
        $data['snap_name']   = '';  //临时员工
        $data['car_name']    = '';  //车卡

        //获取接待人刷卡记录
        $device   = Json::decode($userInfo->device);
        $empIdArr = (new LenelEvents())->getEmpIdData($startTime, $device);
        if($empIdArr) {
            foreach($empIdArr as $empId) {
                $badgeId = LenelBadge::find()->where(['EMPID' => $empId])->max('ID');
                if($badgeId == null) {
                    continue;
                }
                $model    = LenelBadge::findOne(['EMPID' => $empId, 'ID' => $badgeId]);
                $empModel = LenelEmp::findOne(['ID' => $empId]);
                if($model) {
                    switch($model->TYPE) {
                        case LenelBadge::TYPE_FOEMAL: //正式员工
                            $data['foemal']      = $model->EMPID;
                            $data['foemal_name'] = $empModel->MIDNAME;
                            break;
                        case LenelBadge::TYPE_AUTH: //包商可接待人
                            $data['foemal']      = $model->EMPID;
                            $data['foemal_name'] = $empModel->MIDNAME;
                            break;
                        case LenelBadge::TYPE_SNAP1: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_SNAP2: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_SNAP3: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_CAR: //车卡
                            $data['car']      = $model->EMPID;
                            $data['car_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_GUARD: //警卫
                            $data['guard']      = $model->EMPID;
                            $data['guard_name'] = $empModel->MIDNAME;
                            break;
                    }
                }
            }
        }

        //判断刷卡信息
        if(empty($data['foemal']) || empty($data['snap']) || empty($data['guard'])) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }

        $vehicle = ArrayHelper::getValue($request, 'vehicle');
        if(empty($vehicle)) {
            $data['car']      = '';
            $data['car_name'] = '';
        } else {
            if(empty($data['car'])) {
                return $this->json(400, '车卡不能为空', $data);
            }
        }

        //检查临时卡是否属于此厂区和门
        $checkUserCard = (new UserCard())->checkEmpidCard(['uid' => $uid, 'empid' => $data['snap']]);
        if(!$checkUserCard) {
            return $this->json(409, '此卡不属于此厂区和门', $data);
        }
        return $this->json(200, '操作成功', $data);
    }

    /**
     * 接待人登记
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionReceptionRegister()
    {
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('reception-register');
        }
        $post = Yii::$app->request->post();
        $type = ExteriorMark::TYPE_PID;
        $emp  = LenelEmp::findOne(['SSNO' => $post['pid']]);
        if(!$emp) {
            return $this->json(100, '未查询到人员信息！');
        }
        $uid        = \Yii::$app->user->id;
        $insertData = (new ExteriorMark())->insertData(['id' => $uid, 'type' => $type]);
        if(!$insertData) {
            return $this->json(100, '查询数据失败！');
        }
        return $this->json(200, '操作成功！', Url::to(['reception-user', 'id' => $emp->ID]));
    }

    /**
     * 获取刷卡记录表
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionReceptionGetCard()
    {
        //提交时间
        $request   = Yii::$app->request->queryParams;
        $startTime = ArrayHelper::getValue($request, 'startTime', '');
        if(empty($startTime)) {
            $startTime = time();
        }
        $data['startTime'] = $startTime;
        //查询卡机数据
        $uid      = \Yii::$app->user->id;
        $userInfo = UserInfo::findOne(['id' => $uid]);
        if(!$userInfo) {
            return $this->json(100, '登陆人员信息不正确！', $data);
        }
        //获取卡记录
        $device = Json::decode($userInfo->device);
        $empId  = (new LenelEvents())->getEmpIdFoemal($startTime, $device);
        if(!$empId) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        //添加刷卡标志位表
        $insertData = (new ExteriorMark())->insertData(['id' => $uid, 'type' => ExteriorMark::TYPE_CARD]);
        if(!$insertData) {
            return $this->json(100, '数据添加失败！', $data);
        }
        $data['empid'] = $empId;
        $data['url']   = Url::to(['reception-user', 'id' => $empId]);
        return $this->json(200, '刷卡成功！', $data);
    }

    /**
     * 接待人员列表
     * @param $id
     * @return string
     */
    public function actionReceptionUser()
    {
        $id     = Yii::$app->request->get('id');
        $search = new ExteriorUserRecordSearch();
        $data   = $search->getExteriorUserData($id);
        return $this->render('reception-user', ['model' => $search, 'data' => $data, 'id' => $id]);
    }

    /**
     * 需要接待人列表
     * @return string
     */
    public function actionReceptionAgreement()
    {
        return $this->render('reception-agreement');
    }

    /**
     * 输入手机号
     * @return string
     */
    public function actionReceptionPhone()
    {
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('reception-phone');
        }
        $post              = Yii::$app->request->post();
        $exteriorUserModel = ExteriorUser::findOne(['phone' => $post['phone'], 'status' => ExteriorUser::STATUS_YES]);
        if(!$exteriorUserModel) {
            return $this->json(false, '未查询到登记信息！');
        }
        //预计出厂时间小于当前时间
        if(strtotime($exteriorUserModel->get_out_time) < time()) {
            return $this->json(false, '未查询到登记信息！');
        }
        //获取登记人员状态
        $check = (new ExteriorUserRecord())->exteriorUserCheck($exteriorUserModel->id);
        if($check) {
            return $this->json(false, '此手机号已登记进厂！');
        }
        $checkFactory = (new ExteriorUser())->checkUser($exteriorUserModel->id);
        if(!$checkFactory) {
            return $this->json(false, '未能获取进入此厂区权限！');
        }
        //没有就添加
        $id = (new ExteriorUserRecord())->add($exteriorUserModel->id);
        if(!$id) {
            return $this->json(false, '数据获取失败！');
        }
        return $this->json(200, '操作成功！', Url::to(['/form-answer/repection-form', 'eurid' => $id]));
    }

    /**
     * 入场登记
     * @param $id
     * @return string
     */
    public function actionReceptionRegisterUser($id)
    {
        $model = ExteriorUserRecordServes::findOne($id);
        $model->setScenario($model::ENTRANCE_REGISTER);

        $userData = [];
        //接待人是扫卡还是PID登陆
        $uid  = \Yii::$app->user->id;
        $mark = ExteriorMark::findOne(['userinfo_id' => $uid]);
        if($mark && $mark->type == ExteriorMark::TYPE_CARD) {
            $userData = LenelEmp::find()->where(['ID' => $model->create_user])->asArray()->one();
            if(!$userData) {
                return $this->json(500, '未查询到人员信息！');
            }
            $department = (new LevelDept())->departmentByEmpId($model->create_user);
            if(!$department) {
                return $this->json(500, '未查询到部门信息！');
            }
            $userData['department'] = $department;
        }
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('reception-register-user', [
                'model' => $model, 'userData' => $userData
            ]);
        }
        $post      = Yii::$app->request->post();
        $fileIdArr = ArrayHelper::getValue($post, 'file_id', []);
        if(empty($fileIdArr)) {
            return $this->json(100, '请拍照!');
        }
        if($mark && $mark->type == ExteriorMark::TYPE_CARD) {
            $post['ExteriorUserRecordServes']['reception_user'] = $model->create_user;
        }
        $model->load($post);
        $tra = Yii::$app->db->beginTransaction();
        try {
            $model->status = $model::STATUS_GREEN_LIGHT;
            //PID接待
            // if ($mark && $mark->type == ExteriorMark::TYPE_PID) {
            //     //if (!$model->checkDepartment()) {
            //     if (!$model->newCheckDepartment()) {
            //         throw new Exception('接待人必须与创建人是相同分部！');
            //     }
            // }
            //扫卡接待
            if($model->save()) {
                $photoModel = new ExteriorPhoto();
                if(!$photoModel->saveCamera($model, $fileIdArr)) {
                    throw new Exception($photoModel->errorToString());
                }
            } else {
                throw new Exception($model->errorToString());
            }
            $tra->commit();
            return $this->json(200, '保存成功！');
        } catch(\Exception $e) {
            $tra->rollBack();
            return $this->json(100, $e->getMessage());
        }
    }

    /**
     * 抓取人员信息
     * @return string
     */
    public function actionGetUserInfo()
    {
        //提交时间
        $request   = Yii::$app->request->queryParams;
        $startTime = ArrayHelper::getValue($request, 'startTime', '');
        if(empty($startTime)) {
            $startTime = time();
        }
        $data['startTime'] = $startTime;

        //查询卡机数据
        $uid      = \Yii::$app->user->id;
        $userInfo = UserInfo::findOne(['id' => $uid]);
        if(!$userInfo) {
            return $this->json(100, '登陆人员信息不正确！', $data);
        }
        //获取卡记录
        $device = Json::decode($userInfo->device);
        $empId  = (new LenelEvents())->getEmpIdFoemal($startTime, $device);
        if(!$empId) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        $LenelEmp = LenelEmp::find()->where(['ID' => $empId])->asArray()->one();
        if(!$LenelEmp) {
            return $this->json(500, '未查询到人员信息！', $data);
        }
        $department = (new LevelDept())->departmentByEmpId($empId);
        if(!$department) {
            return $this->json(500, '未查询到部门信息！', $data);
        }
        $LenelEmp['department'] = $department;
        $LenelEmp['startTime']  = $startTime;
        return $this->json(200, '操作成功', $LenelEmp);
    }

    /**
     * 接待人登记列表
     * @param $id
     * @return string
     */
    public function actionReception($id)
    {
        $search = new ExteriorUserRecordSearch();
        $data   = $search->getRegisterUserList(Yii::$app->request->queryParams, $id);
        return $this->render('reception', ['model' => $search, 'data' => $data, 'id' => $id]);
    }


    /**
     * @return string
     */
    public function actionSignature()
    {
        return $this->render('signature');
    }

    public function actionGetReceptionUser()
    {
        $key = Yii::$app->request->post('key');
        if(!empty($key)) {
            $user = (new UserInfo())->getUserByPhoneOrCode($key);
            if(!empty($user)) {
                return $this->json(200, '操作成功', $user);
            }
        }

        return $this->json(100, '用户不存在！');
    }

    /**
     * 临时人员刷卡记录
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionGetSnapCard()
    {
        //提交时间
        $request   = Yii::$app->request->queryParams;
        $startTime = ArrayHelper::getValue($request, 'startTime', '');
        if(empty($startTime)) {
            $startTime = time();
        }
        $data['startTime'] = $startTime;
        //查询卡机数据
        $uid      = \Yii::$app->user->id;
        $userInfo = UserInfo::findOne(['id' => $uid]);
        if(!$userInfo) {
            return $this->json(100, '登陆人员信息不正确！', $data);
        }
        //获取临时人员刷卡记录
        $device = Json::decode($userInfo->device);
        $empId  = (new LenelEvents())->getEmpIdSnap($startTime, $device);
        if(!$empId) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        //获取接待人信息
        $exteriorUserRecord = ExteriorUserRecord::findOne(['user_card' => $empId, 'status' => ExteriorUserRecord::STATUS_ENTER]);
        if(!$exteriorUserRecord) {
            return $this->json(100, '未查询到人员信息！', $data);
        }
        $data['empid'] = $empId;
        $data['url']   = Url::to(['get-out-register', 'id' => $exteriorUserRecord->id]);
        return $this->json(200, '刷卡成功！', $data);
    }

    /**
     * 临时人员出厂登记
     * @return string
     */
    public function actionSnapRegister()
    {
        return $this->render('snap-register');
    }

    /**
     * 抓取出厂人员信息
     * @return string
     */
    public function actionGetOutCard()
    {
        //提交时间
        $request   = Yii::$app->request->queryParams;
        $startTime = ArrayHelper::getValue($request, 'startTime', '');
        if(empty($startTime)) {
            $startTime = time();
        }
        $data['startTime'] = $startTime;
        //查询卡机数据
        $uid      = \Yii::$app->user->id;
        $userInfo = UserInfo::findOne(['id' => $uid]);
        if(!$userInfo) {
            return $this->json(100, '登陆人员信息不正确！', $data);
        }

        //返回的是EMPID
        $data['guard']      = '';  //警卫
        $data['snap']       = '';  //临时员工
        $data['car']        = '';  //车卡
        $data['guard_name'] = '';  //警卫
        $data['snap_name']  = '';  //临时员工
        $data['car_name']   = '';  //车卡

        // 获取接待人刷卡记录
        $device   = Json::decode($userInfo->device);
        $empIdArr = (new LenelEvents())->getEmpIdData($startTime, $device);
        if($empIdArr) {
            foreach($empIdArr as $empId) {
                $model    = LenelBadge::findOne(['EMPID' => $empId]);
                $empModel = LenelEmp::findOne(['ID' => $empId]);
                if($model) {
                    switch($model->TYPE) {
                        case LenelBadge::TYPE_SNAP1: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_SNAP2: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_SNAP3: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_CAR: //车卡
                            $data['car']      = $model->EMPID;
                            $data['car_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_GUARD: //警卫
                            $data['guard']      = $model->EMPID;
                            $data['guard_name'] = $empModel->MIDNAME;
                            break;
                    }
                }
            }
        }

        //判断刷卡信息
        if(empty($data['snap']) || empty($data['guard'])) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        $vehicle = ArrayHelper::getValue($request, 'vehicle');
        if(empty($vehicle)) {
            $data['car']      = '';
            $data['car_name'] = '';
        } else {
            if(empty($data['car'])) {
                return $this->json(400, '车卡不能为空', $data);
            }
        }
        //检查临时卡是否属于此厂区和门
        $checkUserCard = (new UserCard())->checkEmpidCard(['uid' => $uid, 'empid' => $data['snap']]);
        if(!$checkUserCard) {
            return $this->json(409, '此卡不属于此厂区和门', $data);
        }
        return $this->json(200, '操作成功', $data);
    }

    /**
     * 出厂登记
     * @param $id
     * @return string
     */
    public function actionGetOutRegister($id)
    {
        $model = ExteriorUserRecord::findOne($id);
        //接待人
        $user = LenelEmp::find()->where(['ID' => $model->reception_user])->asArray()->one();
        if(!$user) {
            return $this->json(500, '未查询到人员信息！');
        }
        $department = (new LevelDept())->departmentByEmpId($model->reception_user);
        if(!$department) {
            return $this->json(500, '未查询到部门信息！');
        }

        $user['snapname'] = '';//人员临时卡
        $lenelModel       = LenelEmp::findOne(['ID' => $model->user_card]);
        if($lenelModel) {
            $user['snapname'] = $lenelModel->LASTNAME;
        }
        $user['carname'] = '';//车卡
        if(!empty($model->car_card)) {
            $lenelCarModel = LenelEmp::findOne(['ID' => $model->car_card]);
            if($lenelCarModel) {
                $user['carname'] = $lenelCarModel->LASTNAME;
            }
        }

        $user['department'] = $department;
        //照片
        $files = ExteriorPhoto::findOne(['record_id' => $model->id]);
        if($files) {
            $files = $files->setFilePath();
        }
        return $this->render('get-out-register', [
            'model' => $model, 'user' => $user, 'files' => $files
        ]);
    }

    /**
     * 保存出厂信息
     * @return string
     */
    public function actionGetOutSave()
    {
        $id    = Yii::$app->request->post('id', '');
        $model = ExteriorUserRecord::findOne($id);
        if(!$model) {
            return $this->json(100, '数据不完整！');
        }
        $model->status            = ExteriorUserRecord::STATUS_GO_OUT;
        $model->real_get_out_time = time();
        $model->get_out_guard     = \Yii::$app->user->id;
        if(!$model->save(false)) {
            return $this->json(100, '放行失败!');
        }
        return $this->json(200, '放行成功!');
    }

    /**
     * 现场登记
     * @return string
     */
    public function actionSceneRegister()
    {
        $model = new ExteriorUserRecordServes();
        if(Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if($post['phone'] == null)
                return $this->json(300, '手机号不能为空');
            if($post['reception-number'] == null)
                return $this->json(300, 'PID不能为空');
            if($post['name'] == null)
                return $this->json(300, '中文名不能为空');
            if($post['company'] == null)
                return $this->json(300, '公司名称不能为空');
            $post['factory']     = (new ExteriorUser())->getFactory();
            $model->setScenario($model::SCENE_REGISTER);
            $model->setAttributes($post);
            if($model->setOrSaveUser($post) && $model->saveScene($post['reception-number'], $post['reception_user'])) {
                return $this->json(200, '保存成功！');
            } else {
                return $this->json(300, $model->errorToString());
            }
        }
        return $this->render('scene-register', [
            'model' => $model
        ]);
    }


    /**
     * @param $id
     * @param $reception_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRegisterListUser($id, $reception_id)
    {
        $formModel      = FormAnswer::findOne($id);
        $reception_user = UserInfo::findOne($reception_id);

        if(!$formModel || !$reception_user) {
            throw new NotFoundHttpException('非法请求！');
        }

        $model = new ExteriorUserRecordServes();
        $model->setScenario($model::CREATE);
        $isPost = Yii::$app->request->isPost;
        if($isPost) {
            $post = Yii::$app->request->post();
            $model->load($post);
            $tra = Yii::$app->db->beginTransaction();
            try {
                if($model->savePrepareRecord($reception_user->id)) {
                    $formModel->setAttributes([
                        'exterior_id' => $model->id, 'create_user' => $model->create_user
                    ]);
                    if(!$formModel->save()) {
                        throw new Exception($formModel->errorToString());
                    }

                } else {
                    throw new Exception($model->errorToString());
                }
                $tra->commit();
                return $this->json(true, '保存成功！');
            } catch(\Exception $e) {
                $tra->rollBack();
                return $this->json(false, $e->getMessage());
            }
        }
        return $this->render('register-list-user', [
            'model' => $model, 'reception_user' => $reception_user
        ]);
    }


    /**
     * 更新人员信息
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $user  = ExteriorUser::findOne($model->exterior_user);
        $model->setScenario($model::CREATE);
        if(Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->setAttributes($post);
            $model->get_out_time && $model->get_out_time = strtotime($model->get_out_time);
            $model->entrance_time && $model->entrance_time = strtotime($model->entrance_time);
            if($model->phone != $user['phone']) {
                $model->setOrSaveUser($post);
            }
            if($user->vehicle != $model->vehicle) {
                $user->vehicle = $model->vehicle;
                $user->save();
            }
            if($model->save()) {
                return $this->json(true, '保存成功！');
            }

            return $this->json(false, $model->errorToString());
        }
        $model->get_out_time && $model->get_out_time = date('Y-m-d H:i:s', $model->get_out_time);
        $model->entrance_time && $model->entrance_time = date('Y-m-d H:i:s', $model->entrance_time);
        return $this->render('update', [
            'model' => $model, 'user' => $user
        ]);
    }

    /**
     * 删除人员信息
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        $id    = (int)Yii::$app->request->get('id');
        $model = ExteriorUserRecord::findOne($id);
        if(!$model->delete()) {
            return $this->json(100, '删除失败');
        }
        return $this->json(200, '删除成功');
    }

    /**
     * 查看二维码
     * @param $code
     * @return string
     * @throws \Exception
     */
    public function actionQrCodeView($code)
    {
        return '<div style="text-align: center;">' . Text::widget([
                'outputDir' => '@webroot/uploads/qrcode', 'outputDirWeb' => '@web/uploads/qrcode', 'ecLevel' => QRcode::QR_ECLEVEL_L, 'text' => $code, 'size' => 6,
            ]) . '</div>';
    }

    /**
     * @param $id
     * @return ExteriorUserRecordServes|null
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if(($model = ExteriorUserRecordServes::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 现场登记
     * @return string
     */
    public function actionRemark()
    {
        if(Yii::$app->request->isPost) {
            $post  = Yii::$app->request->post();
            $model = ExteriorUserRecord::findOne($post['id']);
            if(!$model) {
                return $this->json(100, '数据不完整！');
            }
            $model->status            = ExteriorUserRecord::STATUS_REMARK;
            $model->real_get_out_time = time();
            $model->remark            = trim($post['remark']);
            if(!$model->save(false)) {
                return $this->json(100, '保存失败!');
            }
            return $this->json(200, '保存成功！');
        }
        $request = Yii::$app->request->queryParams;
        return $this->render('remark', [
            'id' => $request['id']
        ]);
    }


    /**
     * 特殊通道
     * @return string
     * @date 2021/6/7 22:56
     */
    public function actionSpecialChannel()
    {
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('special-channel');
        }
        $post = Yii::$app->request->post();
        $emp  = LenelEmp::findOne(['SSNO' => $post['pid']]);
        if(!$emp) {
            return $this->json(100, '未能查询到PID人员信息！');
        }
        $check = ExteriorUserRecord::findOne(['pid' => $post['pid']]);
        if($check && $check->status == ExteriorUserRecord::STATUS_ENTER) {
            return $this->json(100, '您已刷卡进厂,无需重复刷卡进厂！');
        }
        $tra = Yii::$app->db->beginTransaction();
        try {
            $userModel = ExteriorUser::findOne(['pid' => $post['pid']]);
            if(!$userModel) {
                $exteriorUserModel               = new ExteriorUser();
                $exteriorUserModel->name         = '管理人员';
                $exteriorUserModel->type         = ExteriorUser::TYPE_FORMAL;
                $exteriorUserModel->company      = 'NXP';
                $exteriorUserModel->status       = ExteriorUser::STATUS_YES;
                $exteriorUserModel->pid          = $post['pid'];
                $exteriorUserModel->add_user     = \Yii::$app->user->id;
                $exteriorUserModel->apply_id     = $post['guard_card'];
                $exteriorUserModel->get_out_time = date("Y-m-d H:i:s", strtotime("+24 hour"));
                $exteriorUserModel->create_time  = time();
                $exteriorUserModel->update_time  = time();
                if(!$exteriorUserModel->save(false)) {
                    throw new Exception('保存失败!');
                }
                $exteriorUser = $exteriorUserModel->id;
            } else {
                $exteriorUser = $userModel->id;
            }
            $userRecordModel                     = new ExteriorUserRecord();
            $userRecordModel->type               = 1;
            $userRecordModel->pid                = $post['pid'];
            $userRecordModel->exterior_user      = $exteriorUser;
            $userRecordModel->create_user        = $post['guard_card'];
            $userRecordModel->reception_user     = $post['guard_card'];
            $userRecordModel->user_card          = $post['snap_card'];
            $userRecordModel->entrance_guard     = $post['guard_card'];
            $userRecordModel->status             = ExteriorUserRecord::STATUS_ENTER;
            $userRecordModel->real_entrance_time = time();
            $userRecordModel->create_time        = time();
            $userRecordModel->update_time        = time();
            if(!$userRecordModel->save(false)) {
                throw new Exception('保存失败!!!');
            }
            $tra->commit();
        } catch(\Exception $e) {
            $tra->rollBack();
            return $this->json(100, '保存失败!');
        }
        return $this->json(200, '保存成功');
    }

    /**
     * 特殊通道刷卡记录
     * @return string
     * @date 2021/6/7 21:59
     */
    public function actionSpecialChannelCard()
    {
        //提交时间
        $request   = Yii::$app->request->queryParams;
        $startTime = ArrayHelper::getValue($request, 'startTime', '');
        if(empty($startTime)) {
            $startTime = time();
        }
        $data['startTime'] = $startTime;
        //查询卡机数据
        $uid      = \Yii::$app->user->id;
        $userInfo = UserInfo::findOne(['id' => $uid]);
        if(!$userInfo) {
            return $this->json(100, '登陆人员信息不正确！', $data);
        }

        //返回的是EMPID
        $data['guard']      = '';   //警卫
        $data['snap']       = '';   //临时员工
        $data['guard_name'] = '';    //警卫
        $data['snap_name']  = '';  //临时员工
        // 获取接待人刷卡记录
        $device   = Json::decode($userInfo->device);
        $empIdArr = (new LenelEvents())->getEmpIdData($startTime, $device);
        if($empIdArr) {
            foreach($empIdArr as $empId) {
                $model    = LenelBadge::findOne(['EMPID' => $empId]);
                $empModel = LenelEmp::findOne(['ID' => $empId]);
                if($model) {
                    switch($model->TYPE) {
                        case LenelBadge::TYPE_SNAP1: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_SNAP2: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_SNAP3: //临时员工
                            $data['snap']      = $model->EMPID;
                            $data['snap_name'] = $empModel->LASTNAME;
                            break;
                        case LenelBadge::TYPE_GUARD: //警卫
                            $data['guard']      = $model->EMPID;
                            $data['guard_name'] = $empModel->MIDNAME;
                            break;
                    }
                }
            }
        }
        //判断刷卡信息
        if(empty($data['snap']) || empty($data['guard'])) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        return $this->json(200, '操作成功', $data);
    }


    /**
     * 正式员工
     * @return string
     * @date 2021/6/7 22:56
     */
    public function actionPersonnelAdd()
    {
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('personnel-pid');
        }
        $post  = Yii::$app->request->post();
        $check = ExteriorUserRecord::find()->where(['exterior_user' => $post['id']])->andWhere(['in', 'status', ExteriorUserRecord::$showStatus])->one();
        if($check && $check->status == ExteriorUserRecord::STATUS_ENTER) {
            return $this->json(100, '您已进厂,无需重复申请进厂！');
        }
        $tra = Yii::$app->db->beginTransaction();
        try {
            $userModel = ExteriorUser::findOne(['id' => $post['id']]);
            if(!$userModel) {
                throw new Exception('人员基本信息查询失败!');
            }
            $userRecordModel                 = new ExteriorUserRecord();
            $userRecordModel->type           = 1;
            $userRecordModel->pid            = $userModel->pid;
            $userRecordModel->exterior_user  = $post['id'];
            $userRecordModel->create_user    = $userModel->apply_id;
            $userRecordModel->reception_user = $userModel->apply_id;
            $userRecordModel->status         = ExteriorUserRecord::STATUS_GREEN_LIGHT;
            $userRecordModel->create_time    = time();
            $userRecordModel->update_time    = time();
            if(!$userRecordModel->save(false)) {
                throw new Exception('添加入场信息失败!');
            }
            $tra->commit();
        } catch(\Exception $e) {
            $tra->rollBack();
            return $this->json(100, '保存失败!');
        }
        return $this->json(200, '保存成功');
    }

    /**
     * 特殊通道刷卡记录
     * @return string
     * @date 2021/6/7 21:59
     */
    public function actionPersonnelPid()
    {
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('personnel-pid');
        }
        $post = Yii::$app->request->post();
        $emp  = LenelEmp::findOne(['SSNO' => $post['pid']]);
        if(!$emp) {
            return $this->json(100, '未能查询到PID人员信息！');
        }
        $department = (new LevelDept())->departmentByEmpId($emp->ID);
        if(!$department) {
            return $this->json(100, '未查询到部门信息！');
        }
        $data['department_number'] = $department;
        $data['user_name']         = $emp->MIDNAME;
        $userModel                 = ExteriorUser::findOne(['personnel_pid' => $post['pid']]);
        if(!$userModel) {
            $userInfoId                        = \Yii::$app->user->id;
            $exteriorUserModel                 = new ExteriorUser();
            $exteriorUserModel->name           = $emp->MIDNAME;
            $exteriorUserModel->type           = ExteriorUser::TYPE_FORMAL;
            $exteriorUserModel->company        = 'NXP';
            $exteriorUserModel->status         = ExteriorUser::STATUS_YES;
            $exteriorUserModel->pid            = $post['pid'];
            $exteriorUserModel->add_user       = $userInfoId;
            $exteriorUserModel->apply_id       = $emp->ID;
            $exteriorUserModel->personnel_pid  = $post['pid'];
            $exteriorUserModel->purpose        = '正式员工进厂';
            $exteriorUserModel->get_enter_time = date("Y-m-d H:i:s", time());
            $exteriorUserModel->get_out_time   = date("Y-m-d H:i:s", strtotime("+24 hour"));
            $exteriorUserModel->create_time    = time();
            $exteriorUserModel->update_time    = time();
            $factory                           = '';
            if(in_array($userInfoId, ExteriorUser::$attjUserInfoArr)) {
                $factory = ExteriorUser::FACTORY_ATTJ;
            }
            if(in_array($userInfoId, ExteriorUser::$tcUserInfoArr)) {
                $factory = ExteriorUser::FACTORY_TC;
            }
            $exteriorUserModel->factory = $factory;
            if(!$exteriorUserModel->save(false)) {
                return $this->json(100, '保存基本信息失败！');
            }
            $data['exterior_user_id'] = $exteriorUserModel->id;

        } else {
            //获取登记人员状态
            $check = (new ExteriorUserRecord())->exteriorUserCheck($userModel->id);
            if($check) {
                return $this->json(100, '您已登记进厂！');
            }
            $userInfoId                = \Yii::$app->user->id;
            $userModel->get_enter_time = date("Y-m-d H:i:s", time());
            $userModel->get_out_time   = date("Y-m-d H:i:s", strtotime("+24 hour"));
            $userModel->update_time    = time();
            $factory                   = '';
            if(in_array($userInfoId, ExteriorUser::$attjUserInfoArr)) {
                $factory = ExteriorUser::FACTORY_ATTJ;
            }
            if(in_array($userInfoId, ExteriorUser::$tcUserInfoArr)) {
                $factory = ExteriorUser::FACTORY_TC;
            }
            $userModel->factory = $factory;
            if(!$userModel->save(false)) {
                throw new Exception('保存失败!');
            }
            $data['exterior_user_id'] = $userModel->id;
        }

        return $this->json(200, 'Success!', $data);
    }
}
