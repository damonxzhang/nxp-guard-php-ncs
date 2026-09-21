<?php

namespace backend\controllers;

use backend\models\ExteriorUser;
use backend\models\ExteriorUserCar;
use backend\models\LenelUdfemp;
use backend\models\LenelBadge;
use backend\models\LenelEmp;
use backend\models\LevelDept;
use backend\models\search\ExteriorUserCarSearch;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use backend\models\LenelEvents;
use backend\models\UserInfo;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;
use backend\models\UserCard;
use Exception;

class ExteriorUserCarController extends BaseController
{
    /**
     * 登记列表
     * @return string
     */
    public function actionIndex()
    {
        
        $query        = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorUserCarSearch();
        $dataProvider = $searchModel->search($query);
        // print_r($dataProvider);die;

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 登记列表
     * @return string
     */
    public function actionHistory()
    {
        $request        = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorUserCarSearch();
        $dataProvider = $searchModel->history($request);
        return $this->render('history', [
            'request'      => $request,
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * 查询用户
     * @return string
     */
    // public function actionGetUser()
    // {
    //     if(Yii::$app->request->isPost) {
    //         $pid = Yii::$app->request->post('pid');
    //         if(empty($pid)) {
    //             return $this->json(100, '请输入PID');
    //         }
    //         $emp = LenelEmp::findOne(['SSNO' => trim($pid)]);
    //         if(!$emp) {
    //             return $this->json(100, '未查询到PID信息');
    //         }
    //         $udfemp = LenelUdfemp::findOne(['ID' => $emp->ID]);
    //         if(!$udfemp) {
    //             return $this->json(100, '未查询到PID相关信息');
    //         }
    //         $dept                 = LevelDept::findOne(['ID' => $udfemp->DEPT]);
    //         $return['department'] = !$dept ? '' : $dept->NAME;
    //         $return['username']   = $emp ? $emp->MIDNAME : '';
    //         $return['empid']      = $emp ? $emp->ID : '';
    //         return $this->json(200, 'success！', $return);
    //     }
    // }

    /**
     * 查询用户
     * @return string
     */
    public function actionGetUser()
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
        $data['department'] = '';
        $data['username']   = ''; 
        $data['empid']      = ''; 
        $data['pid']        = ''; 
        // 获取接待人刷卡记录
        $device   = Json::decode($userInfo->device);
        $empIdArr = (new LenelEvents())->getEmpIdData($startTime, $device);
        if($empIdArr) {
            foreach($empIdArr as $empId) {
                // $model    = LenelBadge::findOne(['EMPID' => $empId]);
                $model    = LenelBadge::find()
                ->andwhere(['EMPID' => $empId])
                ->andwhere(['STATUS' => 1])
                ->andwhere(['>','DEACTIVATE' , date('Y-m-d H:i:s.000',time())])
                ->asArray()->one();
                // print_r($model);
                $empModel = LenelEmp::findOne(['ID' => $empId]);
                if($model && ($model['TYPE'] == LenelBadge::TYPE_FOEMAL || $model['TYPE'] == LenelBadge::TYPE_AUTH || $model['TYPE'] == LenelBadge::TYPE_GUARD)) {
                    $data['username']   = $empModel->MIDNAME;
                    $data['empid']      = $empId;
                    $data['pid']        = $empModel->SSNO;
                    $udfemp = LenelUdfemp::findOne(['ID' => $empId]);
                    if(!$udfemp) {
                        continue;
                    }
                    $dept               = LevelDept::findOne(['ID' => $udfemp->DEPT]);
                    $data['department'] = !$dept ? '' : $dept->NAME;
                }
            }
        }

        //判断刷卡信息
        if(empty($data['empid'])) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        return $this->json(200, '操作成功', $data);
    }

    /**
     * 现场登记
     * @return string
     */
    public function actionSceneRegister()
    {
        $post = Yii::$app->request->post();
        if($post['userpid'] == null)
            return $this->json(100, 'PID不能为空');
        if($post['vehicle'] == null)
            return $this->json(100, '车牌号不能为空');
        if($post['empid'] == null)
            return $this->json(100, '请点查询按钮查询');
        $checkcar = $this->isCarLicense(mb_strtoupper($post['vehicle']));
        if(!$checkcar){
            return $this->json(100, '车牌号格式不正确！！！');
        }    
        //$query              = ExteriorUserCar::findOne(['pid' => trim($post['userpid']),'status' => ExteriorUserCar::STATUS_WAITE]);
        // $query              = ExteriorUserCar::findOne(['pid' => trim($post['userpid']),'status' => ExteriorUserCar::STATUS_WAITE]);
        $query = ExteriorUserCar::find()->where(['vehicle' => trim($post['vehicle'])])->andwhere(['in','status',[1,2]])->one();
        if($query){
            return $this->json(100, '您已申请过车牌信息,请警卫确认!');
        }
       
        $model              = new ExteriorUserCar();
        $model->pid         = $post['userpid'];
        $model->vehicle     = mb_strtoupper($post['vehicle']);
        $model->phone       = $post['phone'];
        $model->create_user = $post['empid'];
        $model->factory     = (new ExteriorUser())->getFactory();
        $model->status      = ExteriorUserCar::STATUS_WAITE;
        $model->create_time = time();
        if($model->save(false)) {
           // $this->getEntrance($post['vehicle']);
            return $this->json(200, '保存成功！');
        } else {
            return $this->json(100, $model->errorToString());
        }
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
        $data['guard']      = '';  //警卫
        $data['guard_name'] = '';  //警卫
        $data['car']        = '';  //车卡
        $data['car_name']   = '';  //车卡

        //获取刷卡记录
        $device   = Json::decode($userInfo->device);
        try {
            $empIdArr = (new LenelEvents())->getEmpIdData($startTime, $device);
        } catch (Exception $e) {var_dump(123);die;
            return $this->json(400, '等待数据库表解锁', $data);
        }
       // $empIdArr = (new LenelEvents())->getEmpIdData($startTime, $device);
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
        if(empty($data['car']) || empty($data['guard'])) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        //检查临时卡是否属于此厂区和门
        $checkUserCard = (new UserCard())->checkEmpidCard(['uid' => $uid, 'empid' => $data['car']]);
        if(!$checkUserCard) {
            return $this->json(409, '此卡不属于此厂区和门', $data);
        }
        return $this->json(200, '操作成功', $data);
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
        preg_match($regular, $license, $match);
        if (isset($match[0]))
        {
             return true;
        }

        return false;
    }

    /**
     * 警卫确认
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEntrance($id)
    {
        $model         = ExteriorUserCar::findOne(['id' => $id]);
        $receptionUser = LenelEmp::find()->where(['ID' => $model->create_user])->asArray()->one();
        if(!$receptionUser) {
            return $this->json(500, '未查询到人员信息！');
        }
        $department = (new LevelDept())->departmentByEmpId($model->create_user);
        if(!$department) {
            return $this->json(500, '未查询到部门信息！');
        }
        $receptionUser['department'] = $department;
        $receptionUser['vehicle']    = $model->vehicle;
        return $this->render('entrance', [
            'model'         => $model,
            'receptionUser' => $receptionUser,
        ]);
    }

    /**
     * 警卫保存数据
     * @return string
     */
    public function actionEntranceAdd()
    {
        $post  = Yii::$app->request->post();
        $model = ExteriorUserCar::findOne($post['id']);
        $plate = $model->vehicle;
        if(!$model) {
            return $this->json(100, '数据不完整！');
        }
        if(!$post['entrance_guard'] || !$post['car_card']){
            return $this->json(100, '请确认扫卡信息完整！');

        }
        
        if(!empty($post['car_card'])) {
            $checkCar = ExteriorUserCar::findOne(['car_card' => $post['car_card'], 'status' => ExteriorUserCar::STATUS_ENTER]);
            if ($checkCar) {
                return $this->json(100, '此 车卡 已被借出!');
            }
        }

        $model->entrance_guard = $post['entrance_guard'];
        $model->car_card       = $post['car_card'];
        $model->status         = ExteriorUserCar::STATUS_ENTER;
        $model->entrance_time  = time();
        if(!$model->save(false)) {
            return $this->json(100, '保存失败!');
        }
        $this->getEntrance($plate);
        return $this->json(200, '保存成功');
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
     * 车卡出厂登记
     * @return string
     */
    public function actionSnapCar()
    {
        return $this->render('snap-car');
    }

     /**
     * 抓取出卡片信息
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
        $data['empid']        = '';  //车卡
        // 获取接待人刷卡记录
        $device   = Json::decode($userInfo->device);
        $empIdArr = (new LenelEvents())->getEmpIdData($startTime, $device);
        if($empIdArr) {
            foreach($empIdArr as $empId) {
                $model    = LenelBadge::findOne(['EMPID' => $empId]);
                if($model && $model->TYPE == LenelBadge::TYPE_CAR) {
                    $data['empid']      = $model->EMPID;
                }
            }
        }

        //判断刷卡信息
        if(empty($data['empid'])) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        //获取接待人信息
        $exteriorUserCar = ExteriorUserCar::findOne(['car_card' => $data['empid'], 'status' => ExteriorUserCar::STATUS_ENTER]);
        if(!$exteriorUserCar) {
            return $this->json(400, '未查询到人员信息！', $data);
        }
        $data['url']   = Url::to(['get-out-register', 'id' => $exteriorUserCar->id]);
        return $this->json(200, '刷卡成功！', $data);
    }
    /**
     * 警卫抓取出卡片信息
     * @return string
     */
    public function actionGetOutGuard()
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
        $checkCar = ExteriorUserCar::findOne($request['id']);
        if(!$checkCar) {
            return $this->json(100, '未查询到车卡信息');
        }
       $data['guard']      = '';  //警卫
       $data['guard_name'] = '';  //警卫
       $data['car']        = '';  //车卡
       $data['car_name']   = '';  //车卡

        // 获取接待人刷卡记录
        $type = 1;//标记临时卡否正确
        $device   = Json::decode($userInfo->device);
        $empIdArr = (new LenelEvents())->getEmpIdData($startTime, $device);
        if($empIdArr) {
            foreach($empIdArr as $empId) {
                $model    = LenelBadge::findOne(['EMPID' => $empId]);
                $empModel = LenelEmp::findOne(['ID' => $empId]);
                if($model) {
                    switch($model->TYPE) {
                        case LenelBadge::TYPE_CAR: //车卡
                            if($checkCar->car_card != $empId){
                                $type = 2;
                                break;
                            }else{
                                $type = 1;
                                $data['car']      = $empId;
                                $data['car_name'] = $empModel->LASTNAME;
                                break;
                            }
                        case LenelBadge::TYPE_GUARD: //警卫
                            $data['guard']      = $empId;
                            $data['guard_name'] = $empModel->MIDNAME;
                            break;
                    }
                }
            }
        }
        if($type == 2){
            return $this->json(409, '临时卡信息不正确', $data);
        }
        //判断刷卡信息
        if(empty($data['guard']) || empty($data['car'])) {
            return $this->json(400, '未查询到刷卡记录！', $data);
        }
        return $this->json(200, '刷卡成功！', $data);
    }

    /**
     * 出厂登记
     * @param $id
     * @return string
     */
    public function actionGetOutRegister($id)
    {
        $model = ExteriorUserCar::findOne($id);
        if(empty($model->car_card)) {
            return $this->json(500, '没有车卡信息！');
        }
        $user['carname'] = '';//车卡
        $lenelCarModel   = LenelEmp::findOne(['ID' => $model->car_card]);
        if($lenelCarModel) {
            $user['carname'] = $lenelCarModel->LASTNAME;
        }
        return $this->render('get-out-register', [
            'model' => $model, 'user' => $user
        ]);
    }

    /**
     * 保存出厂信息
     * @return string
     */
    public function actionGetOutSave()
    {
        $id    = Yii::$app->request->post('id', '');
        $model = ExteriorUserCar::findOne($id);
        $plate = $model->vehicle;
        if(!$model) {
            return $this->json(100, '数据不完整！');
        }
        $guardCard    = Yii::$app->request->post('guardCard', '');
        $model->status        = ExteriorUserCar::STATUS_GO_OUT;
        $model->get_out_time  = time();
        $model->get_out_guard = $guardCard;
        if(!$model->save(false)) {
            return $this->json(100, '放行失败!');
        }
        $this->getDeparture($plate);
        return $this->json(200, '放行成功!');
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
        $model = ExteriorUserCar::findOne($id);
        if(!$model->delete()) {
            return $this->json(100, '删除失败');
        }
        return $this->json(200, '删除成功');
    }


}

