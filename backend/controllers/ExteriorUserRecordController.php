<?php

namespace backend\controllers;

use backend\models\ExteriorRelation;
use backend\models\LenelUdfemp;
use common\models\FtpUpdate;
use backend\models\LenelBadge;
use backend\models\LenelEmp;
use backend\models\LevelDept;
use backend\models\search\ExteriorUserRecordSearch;
use backend\models\serves\ExteriorUserRecordServes;
use backend\models\ExteriorUserRecord;
use yii\web\NotFoundHttpException;
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
use backend\models\ExteriorUserCar;
use backend\models\ExteriorUserPeople;

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
        } elseif($getFactory == ExteriorUser::FACTORY_TC) {
            $factory = ExteriorUser::FACTORY_TC;
        } else {
            $factory = ExteriorUser::FACTORY_IWH;
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
        // 通过账号获取厂区信息
        $uid      = \Yii::$app->user->id;
        $factory = 1;
        if(in_array($uid, ExteriorUser::$attjUserInfoArr)){
            $factory = 1;
        }
        if(in_array($uid, ExteriorUser::$tcUserInfoArr)){
            $factory = 2;
        }
        if(in_array($uid, ExteriorUser::$iwhUserInfoArr)){
            $factory = 3;
        }

        //查询来访人员
        $phone    = Yii::$app->request->post('phone', '');
        $model = ExteriorUser::find();
        $model->andWhere(['phone'=>$phone]);
        $model->andWhere(['<=','get_enter_time',date('Y-m-d H:i:s',time())]);
        $model->andWhere(['>=','get_out_time',date('Y-m-d H:i:s',time())]);
        $model->andWhere(['factory'=>$factory]);
        $modelData = $model->asArray()->all();

        if(!$modelData) {
            return $this->json(false, '未查询到入场申请信息！');
        }
        foreach ($modelData as $key => $value) {
            # code...
            // $checkUser = ExteriorUserRecord::find()->andWhere(['exterior_user' => $value['id']])->andWhere(['status'=>ExteriorUserRecord::STATUS_ENTER])->one();
            $checkUser = ExteriorUserRecord::find()->andWhere(['exterior_user' => $value['id']])->andWhere(['status'=>ExteriorUserRecord::STATUS_ENTER])->andWhere(['factory'=>$factory])->one();
            if($checkUser) {
                return $this->json(false, '您已申请完进厂,不能重复申请!');
            }
        }
        
        //获取登记人员状态
//        $check = (new ExteriorUserRecord())->exteriorUserCheck($model->id);
//        if($check) {
//            return $this->json(false, '手机号已登记进厂！');
//        }
        //添加进厂人员信息
//        $recordId = (new ExteriorUserRecord())->add($model->id);
//        if(!$recordId) {
//            return $this->json(false, '数据添加失败！');
//        }
		return $this->json(true, 'Success！', Url::to(['register-user','id' => $modelData[0]['id']]));

    }
//
//    /**
//     * 手机号登记
//     * @return string
//     */
//    public function actionRegister()
//    {
//        $isPost = Yii::$app->request->isPost;
//        if(!$isPost) {
//            return $this->render('register');
//        }
//        //查询来访人员
//        $id    = Yii::$app->request->post('id', '');
//        $model = ExteriorUser::findOne(['id' => $id]);
//        if(!$model) {
//            return $this->json(false, '未查询到入场申请信息！');
//        }
//        //        //预计进厂时间
//        //        if(strtotime($model->get_enter_time) < time()) {
//        //            return $this->json(false, '抱歉未到申请入场时间,暂时不能进厂!');
//        //        }
//        //预计出厂时间
//        if(strtotime($model->get_out_time) < time()) {
//            return $this->json(false, '抱歉申请出场时间小于当前时间,请重新申请!');
//        }
//        //获取登记人员状态
//        $check = (new ExteriorUserRecord())->exteriorUserCheck($model->id);
//        if($check) {
//            return $this->json(false, '手机号已登记进厂！');
//        }
//        //添加进厂人员信息
//        $recordId = (new ExteriorUserRecord())->add($model->id);
//        if(!$recordId) {
//            return $this->json(false, '数据添加失败！');
//        }
//        return $this->json(true, 'Success！', Url::to(['register-user', 'id' => $recordId]));
//
//    }

    /**
     * 获取部门经理名称
     */
    public function getRelationLeaderName($id)
    {
        $emp    = LenelEmp::findOne(['ID' => $id]);
        if ($emp) {
            $relation = ExteriorRelation::findOne(['user_pid' => $emp->SSNO]);
            if(!$relation){
                $relation1 = ExteriorRelation::findOne(['work_id' => $emp->SSNO]);
                return $relation1? trim($relation1->leader_pid) : '';

            }else{
                return $relation? trim($relation->leader_pid) : '';

            }
		}else{
            $udfemp = LenelUdfemp::findOne(['ID' => $id]);
            if ($udfemp) {
                $relation = ExteriorRelation::findOne(['work_id' => $udfemp->ADDR1]);
                return $relation? trim($relation->leader_pid) : '';
            }
        }
        return '';
    }

    /**
     * 入场登记
     * @param $id
     * @return string
     */
    public function actionRegisterUser($id)
    {
        $query = ExteriorUser::findOne($id);

        $isPost = Yii::$app->request->isPost;
        if($isPost) {
            $post = Yii::$app->request->post();
            $tran = Yii::$app->db->beginTransaction();
            try {
                // 判断刷卡人是否符合同部门的条件
                // 获取接待人信息
                $receptionDepartment = $this->getRelationLeaderName($post['ExteriorUser']['reception_user']);
                // 获取申请人信息 - 数组
                // 通过账号获取厂区信息
                $uid      = \Yii::$app->user->id;
                $factory = 1;
                if(in_array($uid, ExteriorUser::$attjUserInfoArr)){
                    $factory = 1;
                }
                if(in_array($uid, ExteriorUser::$tcUserInfoArr)){
                    $factory = 2;
                }
                if(in_array($uid, ExteriorUser::$iwhUserInfoArr)){
                    $factory = 3;
                }

                //查询来访人员
                $phone    = Yii::$app->request->post('phone', '');
                $model = ExteriorUser::find();
                $model->andWhere(['phone'=>$phone]);
                $model->andWhere(['status'=>1]);
                $model->andWhere(['<=','get_enter_time',date('Y-m-d H:i:s',time())]);
                $model->andWhere(['>=','get_out_time',date('Y-m-d H:i:s',time())]);
                $model->andWhere(['factory'=>$factory]);
                $modelData = $model->orderBy(['update_time'=>'DESC'])->asArray()->all();

                $createDepartment = [];
                foreach ($modelData as $value){
                    if (!empty($value['pid'])){
                        $queryGetUserId = ExteriorRelation::findOne(['user_pid' => $value['pid']]);
                        if (!empty($queryGetUserId)){
                            $createDepartment[$value['id']]['Department'] = trim($queryGetUserId->leader_pid);
                        }
                    }
                    if (!empty($value['workid'])){
                        $queryGetUserId = ExteriorRelation::findOne(['work_id' => $value['workid']]);
                        if (!empty($queryGetUserId)){
                            $createDepartment[$value['id']]['Department'] = trim($queryGetUserId->leader_pid);
                        }
                    }
                }
                $E_id = 0;
                foreach ($createDepartment as $key => $valueD){
                    if ($receptionDepartment == $valueD['Department']){
                        $E_id = $key;
                    }
                }
                if (!$E_id){
                    return $this->json(400, '无权接此人，请确认申请信息！');
                }
                // print_r($post);
                $queryE = ExteriorUser::findOne(['id'=>$E_id]);
                $model = new ExteriorUserRecord();
                $model->exterior_user = $E_id;
                $model->reception_user = $post['ExteriorUser']['reception_user'];
                $model->status = $model::STATUS_GREEN_LIGHT;
                $model->create_time = time();
                $model->update_time = time();
                $model->factory = $factory;
                if($post['vehicle']){
                    $model->vehicle = $post['vehicle'];
                    $checkcar = $this->isCarLicense(mb_strtoupper($post['vehicle']));
                    if(!$checkcar){
                        return $this->json(100, '车牌号格式不正确');
                    }    
                }
                    
                else
                    $model->vehicle = $queryE->vehicle;

                $model->get_out_time = strtotime($queryE->get_out_time);
                if($model->save(false)) {
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
        return $this->render('register-user',[
            "model" => $query
        ]);
    }

// /**
//     * 入场登记
//     * @param $id
//     * @return string
//     */
//    public function actionRegisterUser($id)
//    {
//        $model = ExteriorUserRecordServes::findOne($id);
//        $model->setScenario($model::ENTRANCE_REGISTER);
//        $isPost = Yii::$app->request->isPost;
//        if($isPost) {
//            $post = Yii::$app->request->post();
//            // $fileIdArr = ArrayHelper::getValue($post, 'file_id', []);
//            // if (empty($fileIdArr)) {
//            //     return $this->json(100, '请拍照!');
//            // }
//            $model->load($post);
//            $tran = Yii::$app->db->beginTransaction();
//            try {
//                $model->status = $model::STATUS_GREEN_LIGHT;
//                //if ($model->checkDepartment() && $model->save(false)) {
//                if($model->save(false)) {
//                    // $photoModel = new ExteriorPhoto();
//                    // if (!$photoModel->saveCamera($model, $fileIdArr)) {
//                    //     throw new Exception($photoModel->errorToString());
//                    // }
//                } else {
//                    throw new Exception($model->errorToString());
//                }
//                $tran->commit();
//                return $this->json(200, '保存成功！');
//            } catch(\Exception $e) {
//                $tran->rollBack();
//                return $this->json(100, $e->getMessage());
//            }
//        }
//        return $this->render('register-user', [
//            'model' => $model
//        ]);
//    }
//

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
        $record = ExteriorUserRecord::findOne(['id' => $model->id]);
        $receptionUser['vehicle'] = $record->vehicle;
        $this->getEntrance($record->vehicle);
        return $this->render('entrance', [
            'model'         => $model,
            'files'         => $files,
            'receptionUser' => $receptionUser,
        ]);
    }
    /**
     * 发送GET入厂车辆请求
     */
    public function getEntrance($plate = '')
    {
      if(empty($plate)) return true;
      $url = 'http://onenxpadmin.ap.freescale.net:9010/lan-ka-car/entrance?plate='. mb_strtoupper($plate);
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 500);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_URL, $url);
      $res = curl_exec($curl);
      curl_close($curl);
      return $res;
    }

    /**
     * 发送GET出厂车辆请求
     */
    public function getDeparture($plate = '')
    {
      if(empty($plate)) return true;
      $url = 'http://onenxpadmin.ap.freescale.net:9010/lan-ka-car/departure?plate='. mb_strtoupper($plate);
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 500);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_URL, $url);
      $res = curl_exec($curl);
      curl_close($curl);
      return $res;
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

        // 判断车卡是否已借出
        // print_r($post['car_card']);die;

        if(!empty($post['car_card'])) {
            $checkCar = ExteriorUserCar::findOne(['car_card' => $post['car_card'], 'status' => ExteriorUserCar::STATUS_ENTER]);
            if ($checkCar) {
                return $this->json(100, '此 车卡 已被借出!');
            }
        }

        //保存数据
        if(empty($post['user_card'])) {
            return $this->json(100, '请刷人员临时卡!');
        }
        //正式员工不能自己解自己
        // $emp1 = LenelBadge::findone(['ID'=>$post['foemal_card']]);
        // $emp2 = LenelBadge::findone(['ID'=>$model->reception_user]);
        // if($post['receptionUserCode'] == $model->reception_user){
            // return $this->json(100, '不能自己接自己!');

        // }
        if($model->pid_type == 1){
            if($model->create_user == $post['receptionUserCode']){
                return $this->json(100, '接待人和申请人不能是同一人!');
            }
            $model->reception_user = $post['receptionUserCode'];
        }
        $model->entrance_guard     = $post['guard_card'];
        $model->vehicle            = $post['vehicle'];
        $model->user_card          = $post['user_card'];
        $model->car_card           = $post['car_card'];
        $model->status             = ExteriorUserRecord::STATUS_ENTER;
        $model->real_entrance_time = time();
        if(!$model->save(false)) {
            return $this->json(100, '保存失败!');
        }
        
        $query = LenelBadge::findAll(['EMPID'=>$post['user_card']]);
        foreach ($query as $value){
            $this->addEditCard($value->ID,$post['user_card'],1);
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

                $exteriorUserArr = ExteriorUserPeople::find()->select('empid')->asArray()->column();

                // $exteriorUserArr = ExteriorUser::$specialArr;

                if(in_array($empId, $exteriorUserArr)){
                    $data['foemal']      = $model->EMPID;
                    $data['foemal_name'] = $empModel->MIDNAME;
                }
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
            return $this->json(400, '未查询到刷卡记录!!', $data);
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

        $EMPCHECK = LenelBadge::findAll(['EMPID'=>$data['snap']]);
        foreach ($EMPCHECK as $valuecheck){
            if($valuecheck->STATUS == 2)
            {
                return $this->json(409, '此卡为丢失卡，不得外借!');
            }
        }

        // //判断临时卡是否重复借出
        $checkCard = ExteriorUserRecord::findOne(['user_card' => $data['snap'], 'status' => ExteriorUserRecord::STATUS_ENTER]);
        if ($checkCard) {
            return $this->json(409, '此临时卡已被领用', $data);
        }
        // if(!empty($data['foemal']) && !in_array($data['foemal'],ExteriorUser::$specialArr)) {
        //     $id = ArrayHelper::getValue($request, 'id', '');
        //     $checkRecord = ExteriorUserRecord::findOne(['id'=>$id]);
        //     if(!$checkRecord){
        //         return $this->json(409, '无此记录！');
        //     }
        //     if($checkRecord->reception_user > 0 ){
        //         if($checkRecord->reception_user != $data['foemal']){
        //             return $this->json(409, '此卡无法接此人，请重新确认',$data);
        //         }
        //     }
        // }
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
        $emp  = LenelEmp::findOne(['SSNO' => $post['pid']]);
        if(!$emp) {
            return $this->json(100, '未查询到人员信息！');
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
     * 需要接待人列表
     * @return string
     */
    public function actionAgreementUser()
    {
        $id     = Yii::$app->request->get('id');
        return $this->render('agreement-user', ['id' => $id]);
    }

    /**
     * 输入手机号
     * @return string
     */
    public function actionReceptionPhone()
    {
        // print_r(111);die;
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('reception-phone');
        }
        $post              = Yii::$app->request->post();
         // 通过账号获取厂区信息
         $uid      = \Yii::$app->user->id;
         $factory = 1;
         if(in_array($uid, ExteriorUser::$attjUserInfoArr)){
             $factory = 1;
         }
         if(in_array($uid, ExteriorUser::$tcUserInfoArr)){
             $factory = 2;
         }
         if(in_array($uid, ExteriorUser::$iwhUserInfoArr)){
             $factory = 3;
         }
 
         //查询来访人员
         $phone    = Yii::$app->request->post('phone', '');
         $model = ExteriorUser::find();
         $model->andWhere(['phone'=>$phone]);
         $model->andWhere(['status'=>ExteriorUser::STATUS_YES]);
         $model->andWhere(['<=','get_enter_time',date('Y-m-d H:i:s',time())]);
         $model->andWhere(['>=','get_out_time',date('Y-m-d H:i:s',time())]);
         $model->andWhere(['factory'=>$factory]);
         $exteriorUserModel = $model->asArray()->all();
        if(!$exteriorUserModel) {
            return $this->json(false, '未查询到登记信息！');
        }
        //获取登记人员状态
        foreach( $exteriorUserModel as $key => $valueE){
            $query = ExteriorUserRecord::find();
            $query->andWhere(['exterior_user' => $valueE['id']]);
            $query->andWhere(['in', 'status', ExteriorUserRecord::$showStatus]);
            $result = $query->all();
            if($result) {
                return $this->json(false, '您已申请完进厂,不能重复申请!!！');
            }
        }
        
        // $check = (new ExteriorUserRecord())->exteriorUserCheck($exteriorUserModel->id);
       
     
      //  return $this->json(200, '操作成功！', Url::to(['/form-answer/repection-form', 'eurid' => $id]));
        return $this->json(200, '操作成功！', Url::to(['/exterior-user-record/reception-register-user', 'id' => $exteriorUserModel[0]['id']]));
    }

    /**
     * 入场登记
     * @param $id
     * @return string
     */
    public function actionReceptionRegisterUser()
    {
        $request      = Yii::$app->request->queryParams;
        $id           = ArrayHelper::getValue($request, 'id', '');
        $isPost = Yii::$app->request->isPost;
        if($isPost) {
            $exteriorUser          = ExteriorUser::findOne($id);
            $model                 = new ExteriorUserRecord();
            $model->type           = $exteriorUser->type;
            $model->purpose        = $exteriorUser->purpose;
            $model->pid            = $exteriorUser->pid;
            $model->workid         = $exteriorUser->workid;
            if( $exteriorUser->type == 1) {
                $empModel = LenelEmp::findOne(['SSNO' => $exteriorUser->pid]);
                if(!$empModel) {
                    return $this->json(406, '未查询到人员信息!');
                }
                $model->reception_user         = $empModel->ID;
            }else{
                $empModel = LenelUdfemp::findOne(['ADDR1' =>$exteriorUser->workid]);
                if(!$empModel) {
                    return $this->json(407, '未查询到人员信息!!');
                }
                $model->reception_user = $empModel->ID;
            }
            $model->exterior_user  = $exteriorUser->id;
            $model->create_user    = $exteriorUser->apply_id;
            $model->factory        = $exteriorUser->factory;
            $model->status         = ExteriorUserRecord::STATUS_GREEN_LIGHT;
            $model->get_enter_time = strtotime($exteriorUser->get_enter_time);//计划入厂日期
            $model->get_out_time   = strtotime($exteriorUser->get_out_time);  //计划出厂日期
            if(!$model->save(false)) {
                return $this->json(500, '数据添加失败！');
            }
            return $this->json(200, '保存成功！',Url::to(['reception-user', 'id' => $empModel->ID]));
        }
       
        $exteriorUser = ExteriorUser::findOne(['id' => $id]);
        if(!$exteriorUser) {
            return $this->json(500, '未查询到登记信息！');
        }
        if( $exteriorUser->type == 1) {
            $empModel = LenelEmp::findOne(['SSNO' => $exteriorUser->pid]);
            if(!$empModel) {
                return $this->json(401, '未查询到人员信息!');
            }
            $empId = $empModel->ID;
        }else{
            $empModel = LenelUdfemp::findOne(['ADDR1' =>$exteriorUser->workid]);
            if(!$empModel) {
                return $this->json(402, '未查询到人员信息!!');
            }
            $empId = $empModel->ID;
        }
        $userData = LenelEmp::find()->where(['ID' => $empId])->asArray()->one();
        if(!$userData) {
            return $this->json(403, '未查询到人员信息!!!');
        }
        $department = (new LevelDept())->departmentByEmpId($empId);
        if(!$department) {
            return $this->json(500, '未查询到部门信息！');
        }
        $userData['department'] = $department;
        return $this->render('reception-register-user', [
            'model' => $exteriorUser, 'userData' => $userData
        ]);
    }

    /**
     * 入场登记
     * @param $id
     * @return string
     */
    public function actionReceptionAgreementUser()
    {
        $request      = Yii::$app->request->queryParams;
        $id           = ArrayHelper::getValue($request, 'id', '');
    
        $exteriorUser          = ExteriorUser::findOne($id);
        $model                 = new ExteriorUserRecord();
        $model->type           = $exteriorUser->type;
        $model->purpose        = $exteriorUser->purpose;
        $model->pid            = $exteriorUser->pid;
        $model->workid         = $exteriorUser->workid;
        if( $exteriorUser->type == 1) {
            $empModel = LenelEmp::findOne(['SSNO' => $exteriorUser->pid]);
            if(!$empModel) {
                return $this->json(406, '未查询到人员信息!');
            }
           $model->reception_user         = $empModel->ID;
        }else{
            $empModel = LenelEmp::findOne(['SSNO' =>$exteriorUser->workid]);
            // $empModel = LenelUdfemp::findOne(['ADDR1' =>$exteriorUser->workid]);
            if(!$empModel) {
                return $this->json(407, '未查询到人员信息!!');
            }
           $model->reception_user = $empModel->ID;
        }
        $model->exterior_user  = $exteriorUser->id;
        $model->create_user    = $exteriorUser->apply_id;
        $model->factory        = $exteriorUser->factory;
        $model->vehicle        = $exteriorUser->vehicle;
        $model->status         = ExteriorUserRecord::STATUS_GREEN_LIGHT;
        $model->get_enter_time = strtotime($exteriorUser->get_enter_time);//计划入厂日期
        $model->get_out_time   = strtotime($exteriorUser->get_out_time);  //计划出厂日期
        if(!$model->save(false)) {
            return $this->json(500, '数据添加失败！');
        }
        $data['id'] = $empModel->ID;
        return $this->json(200, '保存成功！', $data);



        // $isPost = Yii::$app->request->isPost;
        // if($isPost) {
        //     $exteriorUser          = ExteriorUser::findOne($id);
        //     $model                 = new ExteriorUserRecord();
        //     $model->type           = $exteriorUser->type;
        //     $model->purpose        = $exteriorUser->purpose;
        //     $model->pid            = $exteriorUser->pid;
        //     $model->workid         = $exteriorUser->workid;
        //     if( $exteriorUser->type == 1) {
        //         $empModel = LenelEmp::findOne(['SSNO' => $exteriorUser->pid]);
        //         if(!$empModel) {
        //             return $this->json(406, '未查询到人员信息!');
        //         }
        //        $model->reception_user         = $empModel->ID;
        //     }else{
        //         $empModel = LenelUdfemp::findOne(['ADDR1' =>$exteriorUser->workid]);
        //         if(!$empModel) {
        //             return $this->json(407, '未查询到人员信息!!');
        //         }
        //        $model->reception_user = $empModel->ID;
        //     }
        //     $model->exterior_user  = $exteriorUser->id;
        //     $model->create_user    = $exteriorUser->apply_id;
        //     $model->factory        = $exteriorUser->factory;
        //     $model->status         = ExteriorUserRecord::STATUS_GREEN_LIGHT;
        //     $model->get_enter_time = strtotime($exteriorUser->get_enter_time);//计划入厂日期
        //     $model->get_out_time   = strtotime($exteriorUser->get_out_time);  //计划出厂日期
        //     if(!$model->save(false)) {
        //         return $this->json(500, '数据添加失败！');
        //     }
        //     $data['id'] = $empModel->ID;
        //     return $this->json(200, '保存成功！', $data);
        // }
       
        // $exteriorUser = ExteriorUser::findOne(['id' => $id]);
        // if(!$exteriorUser) {
        //     return $this->json(500, '未查询到登记信息！');
        // }
        // if( $exteriorUser->type == 1) {
        //     $empModel = LenelEmp::findOne(['SSNO' => $exteriorUser->pid]);
        //     if(!$empModel) {
        //         return $this->json(401, '未查询到人员信息!');
        //     }
        //     $empId = $empModel->ID;
        // }else{
        //     $empModel = LenelUdfemp::findOne(['ADDR1' =>$exteriorUser->workid]);
        //     if(!$empModel) {
        //         return $this->json(402, '未查询到人员信息!!');
        //     }
        //     $empId = $empModel->ID;
        // }
        // $userData = LenelEmp::find()->where(['ID' => $empId])->asArray()->one();
        // if(!$userData) {
        //     return $this->json(403, '未查询到人员信息!!!');
        // }
        // $department = (new LevelDept())->departmentByEmpId($empId);
        // if(!$department) {
        //     return $this->json(500, '未查询到部门信息！');
        // }
        // $userData['department'] = $department;
        // return $this->render('reception-agreement-user', [
        //     'model' => $exteriorUser, 'userData' => $userData
        // ]);
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
            $LenelEmp = LenelEmp::find();//['SSNO'=>$key]
            $LenelEmp->alias('le');
            $LenelEmp->select(['le.SSNO as pid', 'le.MIDNAME as name','ld.NAME as department_number']);
            $LenelEmp->leftJoin(['lue' => LenelUdfemp::tableName()], 'lue.ID = le.ID');
            $LenelEmp->leftJoin(['ld' => LevelDept::tableName()], 'lue.DEPT = ld.ID');
            $LenelEmp->andWhere(['le.SSNO'=>$key]);
            $user = $LenelEmp->asArray()->one();

            if(!empty($user)) {
                return $this->json(200, '操作成功', $user);
            }
        }

        return $this->json(100, '用户不存在！');
    }
    /*
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
    */
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
        $guard_card    = Yii::$app->request->post('guard_card', '');
        $model->get_out_guard     = $guard_card;

        
        $query = LenelBadge::findAll(['EMPID'=>$model->user_card]);
        foreach ($query as $value){
            $this->addEditCard($value->ID,$model->user_card,3);
        }
        
        
        if(!$model->save(false)) {
            return $this->json(100, '放行失败!');
        }
        $this->getDeparture($model->vehicle);
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

            if(!preg_match("/^1[3456789]\d{9}$/", $post['phone'])){ 
                return $this->json(300, '请输入正确的手机号');  
            } 
            
            if($post['reception-number'] == null)
                return $this->json(300, 'PID不能为空');

            if($post['vehicle'] != null){
                $checkcar = $this->isCarLicense(mb_strtoupper($post['vehicle']));
                if(!$checkcar){
                    return $this->json(100, '车牌号格式不正确');
                }    
            }
            

            // 需要校验一下是不是lenel里面有的
            $SSNO = LenelEmp::findOne(['SSNO' => $post['reception-number']]);
            if(!$SSNO)
                return $this->json(300, '请输入正确的PID');

            if($post['name'] == null)
                return $this->json(300, '中文名不能为空');
            if($post['company'] == null)
                return $this->json(300, '公司名称不能为空');
            $post['factory']     = (new ExteriorUser())->getFactory();

            // 获取卡类型
            $BADGETYPE = LenelBadge::findone(['EMPID'=>$SSNO->ID]);
            if($BADGETYPE->TYPE == 9){
                $post['workid'] = $post['reception-number'];
                $post['pid'] = '';
                $post['type'] = 2;

            }
            else{
                $post['workid'] = '';
                $post['pid'] = $post['reception-number'];
                $post['type'] = 1;


            }
            $userInfoId                = \Yii::$app->user->id;
            $factory                   = '';
            if(in_array($userInfoId, ExteriorUser::$attjUserInfoArr)) {
                $factory = ExteriorUser::FACTORY_ATTJ;
            }
            if(in_array($userInfoId, ExteriorUser::$tcUserInfoArr)) {
                $factory = ExteriorUser::FACTORY_TC;
            }
            if(in_array($userInfoId, ExteriorUser::$iwhUserInfoArr)){
                $factory = ExteriorUser::FACTORY_IWH;
            }
            $post['factory'] = $factory;
            $post['get_enter_time'] = date('Y-m-d H:i:s',time());
            $model->setScenario($model::SCENE_REGISTER);
            $model->setAttributes($post);
            if($model->setOrSaveUserNew($post) && $model->saveScene($post['type'],$post['reception-number'], $post['reception_user'], $post['vehicle'],$post['factory'],$post['name'])) {
                // if($model->setOrSaveUserNew($post) && $model->saveScene($post['type'],$post['reception-number'], $post['reception_user'], $post['vehicle'])) {
                    return $this->json(200, '保存成功！');
            } else {
                return $this->json(310, $model->errorToString());
            }
        }
        return $this->render('scene-register', [
            'model' => $model
        ]);
    }


    /*
    * Notes: 判断车牌号是否合法
    * @param: $license 车牌号
    * return bool true:合法 false:不合法
    */
    public function isCarLicense($license)
    {
        //参数判断
        if (empty($license))
        {
            return false;
        }
        //匹配民用车牌和使馆车牌
        //判断标准
        //1.第一位为汉子省份缩写
        //2.第二位为大写字母城市编码
        //3.后面是5位仅含字母和数字的组合
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5}$/u";
        preg_match($regular, $license, $match);
        if (isset($match[0]))
        {
            return true;
        }
        //匹配特种车牌(挂,警,学,领,港,澳)
        $regular = '/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{4}[挂警学领港澳]{1}$/u';
        preg_match($regular, $license, $match);
        if (isset($match[0]))
        {
            return true;
        }
        //匹配武警车牌
        $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]?[0-9a-zA-Z]{5}$/ui';
        preg_match($regular, $license, $match);
        if (isset($match[0]))
        {
            return true;
        }
        //匹配军牌
        $regular = "/[A-Z]{2}[0-9]{5}$/";
        preg_match($regular, $license, $match);
        if (isset($match[0]))
        {
            return true;
        }
        //匹配新能源车辆7位车牌
        //小型新能源车
        $regular = "/^(([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z](([0-9]{5}[DF])|([A-Z]([A-HJ-NP-Z0-9])[0-9]{4})))|([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z][A-HJ-NP-Z0-9]{4}[A-HJ-NP-Z0-9挂学警港澳使领]))$/u";
        // $regular = "/^(([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z](([0-9]{5}[DF])|([DF]([A-HJ-NP-Z0-9])[0-9]{4})))|([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z][A-HJ-NP-Z0-9]{4}[A-HJ-NP-Z0-9挂学警港澳使领]))$/u";
        // print_r($regular);die;
        preg_match($regular, $license, $match);
        if (isset($match[0]))
        {
             return true;
        }

        return false;
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
            $userRecordModel = ExteriorUserRecord::findone($post['record_id']);
            $userRecordModel->vehicle        = $post['car'];

            // $userRecordModel                 = new ExteriorUserRecord();
            // $userRecordModel->type           = 1;
            // $userRecordModel->pid            = $userModel->pid;
            // $userRecordModel->exterior_user  = $post['id'];
            // $userRecordModel->vehicle        = $userModel->vehicle;
            // $userRecordModel->create_user    = $userModel->apply_id;
            // $userRecordModel->reception_user = $userModel->apply_id;
            // $userRecordModel->status         = ExteriorUserRecord::STATUS_GREEN_LIGHT;
            // $userRecordModel->create_time    = time();
            // $userRecordModel->update_time    = time();

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
	/*
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
//        $userModel                 = ExteriorUser::findOne(['personnel_pid' => $post['pid']]);
//        if(!$userModel) {
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
            $exteriorUserModel->phone          = $post['phone'];
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

//        } else {
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
	*/
	
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
        $userInfoId                = \Yii::$app->user->id;
        $factory                   = '';
        if(in_array($userInfoId, ExteriorUser::$attjUserInfoArr)) {
            $factory = ExteriorUser::FACTORY_ATTJ;
        }
        if(in_array($userInfoId, ExteriorUser::$tcUserInfoArr)) {
            $factory = ExteriorUser::FACTORY_TC;
        }
        if(in_array($userInfoId, ExteriorUser::$iwhUserInfoArr)){
            $factory = ExteriorUser::FACTORY_IWH;
        }
        $userQuery = ExteriorUser::findOne(['phone' => $post['phone'],'pid' => $post['pid'],'factory'=>$factory]);
        if (!$userQuery) {
            $exteriorUserModel                 = new ExteriorUser();
            $exteriorUserModel->name           = $emp->MIDNAME;
            $exteriorUserModel->type           = ExteriorUser::TYPE_FORMAL;
            $exteriorUserModel->company        = 'NXP';
            $exteriorUserModel->vehicle        = $post['car'];
            $exteriorUserModel->status         = ExteriorUser::STATUS_YES;
            $exteriorUserModel->pid            = $post['pid'];
            $exteriorUserModel->add_user       = $userInfoId;
            $exteriorUserModel->apply_id       = $emp->ID;
            $exteriorUserModel->phone          = $post['phone'];
            $exteriorUserModel->personnel_pid  = $post['pid'];
            $exteriorUserModel->purpose        = '正式员工进厂';
            $exteriorUserModel->get_enter_time = date("Y-m-d H:i:s", time());
            $exteriorUserModel->get_out_time   = date("Y-m-d H:i:s", strtotime("+24 hour"));
            $exteriorUserModel->create_time    = time();
            $exteriorUserModel->update_time    = time();
            $exteriorUserModel->factory = $factory;
            if(!$exteriorUserModel->save(false)) {
                return $this->json(100, '保存基本信息失败！');
            }
            $exteriorUserId = $exteriorUserModel->id;
        }else{
            $userQuery->vehicle        = $post['car'];
            $userQuery->add_user       = $userInfoId;
            $userQuery->personnel_pid  = $post['pid'];
            $userQuery->get_enter_time = date("Y-m-d H:i:s", time());
            $userQuery->get_out_time   = date("Y-m-d H:i:s", strtotime("+24 hour"));
            if(!$userQuery->save(false)) {
                return $this->json(102, '保存基本信息失败！!');
            }
            $exteriorUserId = $userQuery->id;
        }
        $data['exterior_user_id'] = $exteriorUserId;
       
        //获取登记人员状态
        $check = ExteriorUserRecord::find();
        $check->where(['exterior_user' => $exteriorUserId]);
        $check->andWhere(['in', 'status', ExteriorUserRecord::$showStatus]);
        $result = $check->one();
        if($result) {
            return $this->json(100, '您已登记进厂！');
        }

        $LenelEmp = LenelEmp::findOne(['SSNO' => $post['pid']]);
        if(!$LenelEmp) {
            return $this->json(100, 'LENEL中无此人！');
        }

        $model = new ExteriorUserRecord();
        $model->exterior_user  = $exteriorUserId;
        $model->pid_type       = 1;//用于判断申请人和接待人是否同一人
        $model->create_user    = $LenelEmp->ID;
        $model->reception_user = $LenelEmp->ID;
        $model->status = $model::STATUS_GREEN_LIGHT;
        $model->create_time = time();
        $model->update_time = time();
        $model->factory = $factory;
        $model->get_out_time = strtotime(date("Y-m-d H:i:s", strtotime("+24 hour")));

        if(!$model->save(false)) {
            throw new Exception('保存失败!');
        }
        $data['exterior_user_record_id'] = $model->id;

        return $this->json(200, 'Success!', $data);
    }


   /**
     * 新增修改
     * @param        $badgeNumber
     * @param        $personID
     * @param array $accessLevels
     * @return false
     * @date 2021/8/13 9:44
     */
    public function addEditCard($badgeNumber, $personID, $status)
    {
        if(empty($badgeNumber) && empty($personID)) {
            return false;
        }
        $badge = LenelBadge::findOne(['ID' => $badgeNumber]);
        if(!$badge) {
            return false;
        }
        $dataArray = array(
            'badgeNumber'   => $badgeNumber,
            // 'activateDate'  => $badge->ACTIVATE,
            // 'deactivateDate'=> $badge->DEACTIVATE,
            'activateDate'  => date('Y-m-d H:i:s',time()),
            'deactivateDate'  => date('Y-m-d H:i:s',strtotime('+2day')),
            'type'          => $badge->TYPE,
            'status'        => $status,
            'personID'      => $personID
        );
        $postData = [
            'opt'  => 'SET',
            'data' => [$dataArray]
        ];
        $url      = 'http://10.31.37.9:18080/LenelWMIAdapter/';
     
         $result   = $this->sendPost($url, json_encode($postData));
        // if(!$result) return false;
        return true;
    }
    // /**
    //   * 新增修改
    //   * @param        $badgeNumber
    //   * @param        $personID
    //   * @param array $accessLevels
    //   * @return false
    //   * @date 2021/8/13 9:44
    //   */
    //  public function addEditCard($badgeNumber, $personID, $status, $accessLevels = [])
    //  {
    //      if(empty($badgeNumber) && empty($personID)) {
    //          return false;
    //      }
    //      $badge = LenelBadge::findOne(['ID' => $badgeNumber]);
    //      if(!$badge) {
    //          return false;
    //      }
    //      $postData = array(
    //          'opt'  => 'SET',
    //          'data' => [
    //              'badgeNumber'   => $badgeNumber,
    //              'activateDate'  => $badge->ACTIVATE,
    //              'deactivateDate'=> $badge->DEACTIVATE,
    //              'type'          => $badge->TYPE,
    //              'status'        => $status,
    //              'personID'      => $personID,
    //              'accessLevels'  => $accessLevels
    //          ]
    //          );
    //      $url      = 'http://10.192.166.234:18080/LenelWMIAdapter/';
    //      $result   = $this->sendPost($url, $postData);
    //      // $result   = $this->sendPost($url, json_encode($postData));
    //      if(!$result) return false;
    //      return true;
    //  }

    // /**
    //  * 删除
    //  * @param $badgeNumber
    //  * @return bool
    //  * @date 2021/8/13 9:51
    //  */
    // public function deleteCard($badgeNumber)
    // {
    //     if(empty($badgeNumber)) {
    //         return false;
    //     }
    //     $badge = LenelBadge::findOne(['ID' => $badgeNumber]);
    //     if(!$badge) {
    //         return false;
    //     }
    //     $postData = [
    //         'opt'  => 'DEL',
    //         'data' => [
    //             'badgeNumber'  => $badgeNumber
    //         ]
    //     ];
    //     $url      = 'http://192.168.1.9:18080/LenelWMIAdapter/';
    //     $result   = $this->sendPost($url, json_encode($postData));
    //     if(!$result) return false;
    //     return true;
    // }

    /**
     * 发送post请求
     * @param       $url      //post 请求地址
     * @param       $postData //post 请求键值对传json
     * @param int   $returnTransfer
     * @param int   $timeOut
     * @param false $header
     * @return bool|string
     * @date 2021/8/6 14:37
     */
    // public function sendPost($url, $postData, $returnTransfer = 1, $timeOut = 60, $header = true)
    // {
    //     $postData = http_build_query($postData);
    //     $ch       = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     if(!empty($postData)) {
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    //     }
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returnTransfer);
    //     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);
    //     //开启header
    //     curl_setopt($ch, CURLOPT_HEADER, $header);
    //     //类型为json
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
    //     $file_contents = curl_exec($ch);
    //     $errorCode     = curl_errno($ch); // 获取错误码
    //     curl_close($ch);
    //     if(0 !== $errorCode) {
    //         return false;
    //     }
    //     return $file_contents;
    // }
/**
     * 发送post请求
     * @param       $url      //post 请求地址
     * @param       $postData //post 请求键值对传json
     * @param int   $returnTransfer
     * @param int   $timeOut
     * @param false $header
     * @return bool|string
     * @date 2021/8/6 14:37
     */
    public function sendPost($url, $postData, $returnTransfer = 1, $timeOut = 60, $header = true)
    {
       // $postData = http_build_query($postData);
        $ch       = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if(!empty($postData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returnTransfer);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);
        //开启header
        curl_setopt($ch, CURLOPT_HEADER, $header);
        //类型为json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            // 'Content-Length: ' . strlen($postData)
        )
        );
        $file_contents = curl_exec($ch);
        $errorCode     = curl_errno($ch); // 获取错误码
        curl_close($ch);
        if(0 !== $errorCode) {
            return false;
        }
        return $file_contents;
    }
}
