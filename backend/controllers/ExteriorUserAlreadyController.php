<?php

namespace backend\controllers;

use backend\models\ExteriorPhoto;
use backend\models\LenelEmp;
use backend\models\LevelDept;
use backend\models\search\ExteriorUserRecordSearch;
use Yii;
use yii\web\NotFoundHttpException;
use backend\models\ExteriorUser;
use yii\helpers\ArrayHelper;
use backend\models\ExteriorUserRecord;
use backend\models\LenelBadge;
use backend\models\UserInfo;

class ExteriorUserAlreadyController extends BaseController
{
    /**
     * 已完成页面
     * @return string
     */
    public function actionIndex()
    {
        $request     = Yii::$app->request->queryParams;
        $searchModel = new ExteriorUserRecordSearch();
        $data        = $searchModel->searchHistory($request);
        return $this->render('index', [
            'request'     => $request,
            'data'        => $data
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
        $lenelEmp = LenelEmp::find()->where(['ID' => $model->reception_user])->asArray()->one();
        $department = (new LevelDept())->departmentByEmpId($model->reception_user);
        if ($department) {
            $lenelEmp['department'] = $department;
        }
        //警卫
        $guardIN = LenelEmp::find()->where(['ID' => $model->entrance_guard])->asArray()->one();
        $guardOUT = LenelEmp::find()->where(['ID' => $model->get_out_guard])->asArray()->one();

        // $guard = UserInfo::find()->where(['id' => $model->entrance_guard])->asArray()->one();
        //照片
        $files = ExteriorPhoto::findOne(['record_id' => $model->id]);
        if ($files) {
            $files = $files->setFilePath();
        }
        return $this->render('view', [
            'model' => $model
            , 'receptionUser' => $lenelEmp
            , 'files' => $files
            , 'guardIN' => $guardIN
            , 'guardOUT' => $guardOUT
        ]);
    }

    /**
     * 导出数据
     * @return string
     */
    public function actionExport()
    {
        $request          = Yii::$app->request->queryParams;
        $searchModel      = new ExteriorUserRecordSearch();
        $data             = $searchModel->searchHistory($request);
        $data->pagination = false;
        $dataProvider     = $data->getModels();
        $data             = [];
        if (!empty($dataProvider)) {
            foreach ($dataProvider as $key => $value) {
                $model = ExteriorUser::findOne($value['exterior_user']);
                // $data[$key][] = $this->getExteriorUser($value['exterior_user']);
                // $data[$key][] = $this->getCompany($value['exterior_user']);
                // $data[$key][] = $this->getPhone($value['exterior_user']);
                if ($model) {
                    # code...
                    $data[$key][] = $model->name;
                    $data[$key][] = $model->company;
                    $data[$key][] = $model->phone;
                }else{
                    $data[$key][] = '';
                    $data[$key][] = '';
                    $data[$key][] = '';
                }

                $data[$key][] = $value['vehicle'];
                // $data[$key][] = $this->getLenelEmp($value['reception_user']);
                // $data[$key][] = $this->getDepartment($value['reception_user']);
                // $data[$key][] = $this->getLenelEmpSSNO($value['reception_user']);

                // $model1 = LenelEmp::findOne(['ID' => $value['reception_user']]);
                $names = $this->getLenelEmpMore($value['reception_user'],$value['user_card'],$value['car_card']);
                $data[$key][] = $names['reception_user'];
                $data[$key][] = $this->getDepartment($value['reception_user']);
                $data[$key][] = $names['SSNO'];
                $data[$key][] = $this->getBadgeType($value['user_card']);
                // $data[$key][] = (new LenelEmp())->getLastName($value['user_card']);
                // $data[$key][] = (new LenelEmp())->getLastName($value['car_card']);
                $data[$key][] = $names['user_card'];
                $data[$key][] = $names['car_card'];
                $data[$key][] = $value['real_entrance_time'] ? date('Y年m月d日 H时', $value['real_entrance_time']) : '';
                $data[$key][] = $value['real_get_out_time'] ? date('Y年m月d日 H时', $value['real_get_out_time']) : '';
            }
        }
        $fileName = '已完成人员列表' . time() . '.xlsx';
        $header   = ['访客姓名', '公司','联系方式','车牌照', '接待人姓名', '接待人部门','接待人PID', '临时证类型', '临时证号','临时车证','进厂时间', '出厂时间'];
        array_unshift($data, $header); //添加头部标题
        return $this->json(200, $fileName, $data);
    }
   
    //获取部门
    public function getDepartment($id)
    {
        $department = (new LevelDept())->departmentByEmpId($id);
        return $department ? $department : '';
    }

     //卡类型
    public function getBadgeType($user_card)
    {
        $badge = LenelBadge::findOne(['EMPID' => $user_card]);
        if ($badge) {
            $type = [LenelBadge::TYPE_SNAP1,LenelBadge::TYPE_SNAP2,LenelBadge::TYPE_SNAP3]; 
            // if (in_array($badge->TYPE,$type)) {
            //     return '临时员工卡';
            // }
            if($badge->TYPE == 5){
                return '临时供应商';
            }
            if($badge->TYPE == 6){
                return '临时员工';
            }
            if($badge->TYPE == 7){
                return '临时访客';
            }
        }
        return '';
    }

    //获取人员信息
    public function getLenelEmp($id)
    {
        $model = LenelEmp::findOne(['ID' => $id]);
        return $model ? $model->MIDNAME : '';
    }
    //同时获取多个人的信息
    public function getLenelEmpMore($id1,$id2,$id3)
    {
        $model = LenelEmp::find();
        $model->andWhere(['in', 'ID', [$id1,$id2,$id3]]);
        $modelData = $model->asArray()->all();
        $data['reception_user'] = '';
        $data['SSNO'] = '';
        $data['user_card'] = '';
        $data['car_card'] = '';
        foreach ($modelData as $key => $value) {
            # code...
            if ($value['ID'] == $id1) {
                # code...
                $data['reception_user'] = $value['MIDNAME'];
                $data['SSNO'] = $value['SSNO'];
            }
            if ($value['ID'] == $id2) {
                # code...
                $data['user_card'] = $value['LASTNAME'];
            }
            if ($value['ID'] == $id3) {
                # code...
                $data['car_card'] = $value['LASTNAME'];
            }
        }
        return $data;
    }
    //获取人员pid
    public function getLenelEmpSSNO($id)
    {
        $model = LenelEmp::findOne(['ID' => $id]);
        return $model ? $model->SSNO : '';
    }

    //手机号
    public function getPhone($id)
    {
        $model = ExteriorUser::findOne($id);
        return $model ? $model->phone : '';
    }

    //公司
    public function getCompany($id)
    {
        $model = ExteriorUser::findOne($id);
        return $model ? $model->company : '';
    }

    //获取外部人员信息
    public function getExteriorUser($id)
    {
        $model = ExteriorUser::findOne($id);
        return $model ? $model->name : '';
    }
    //有无车辆
    public function getVehicle($id)
    {
        $model = ExteriorUser::findOne($id);
        if ($model) {
            return $model->vehicle ? '有' : '无';
        }
        return '无';
    }
}
