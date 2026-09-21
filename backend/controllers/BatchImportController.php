<?php

namespace backend\controllers;

use backend\models\ExteriorUser;
use backend\models\ExteriorUserLog;
use backend\models\ExteriorUserRecord;
use backend\models\ExteriorUserSuccessLog;
use backend\models\search\ExteriorUserSearch;
use backend\models\search\ExteriorUserSuccessLogSearch;
use common\models\Utility;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use Yii;
use backend\models\LenelEmp;
use backend\models\LenelUdfemp;
use backend\models\LevelDept;

class BatchImportController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorUserSearch();
        $data         = $searchModel->search($request);
        $totalCount   = $data->totalCount;
        $dataProvider = $data->getModels();
        $pagination   = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => 20,
        ]);
        return $this->render('index', [
            'request'      => $request,
            'dataProvider' => $dataProvider,
            'pagination'   => $pagination
        ]);
    }
    /**
     * @return string
     * @date 2021/7/12 14:07
     */
    public function actionImportSuccess()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorUserSuccessLogSearch();
        $data         = $searchModel->search($request);
        $totalCount   = $data->totalCount;
        $dataProvider = $data->getModels();
        $pagination   = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => 20,
        ]);
        return $this->render('import-success', [
            'request'      => $request,
            'dataProvider' => $dataProvider,
            'pagination'   => $pagination
        ]);
    }
    /**
     * @return string
     */
    public function actionSignature()
    {
        return $this->render('signature');
    }

    /**
     * 批量导入数据
     * @return string
     */
    public function actionImport()
    {
        $isPost = Yii::$app->request->isPost;
        if(!$isPost) {
            return $this->render('import');
        }
        $file = $_FILES['file'];
        if(empty($file['tmp_name'])) {
            return $this->json(100, '上传文件不能为空!');
        }
        $tmp    = explode('.', $file['name']);
        $suffix = array_pop($tmp);
        if('xlsx' !== $suffix) {
            return $this->json(100, '上传文件无法识别，请使用模版!');
        }

        if($file['size'] > 2097152) {
            return $this->json(100, '上传文件不能超过2MB!');
        }
        $excelData = Utility::getExcelData($file['tmp_name']);
        if(empty($excelData)) {
            return $this->json(100, '上传文件无内容!');
        }
        $return = [
            'successCount' => 0,
            'errorCount'   => 0,
            'errorList'    => '',
        ];
        //检查excel表格
        $checkExcel = $this->checkExcel($excelData);
        if($checkExcel['type'] == 1) {
            $return['errorCount'] = $checkExcel['errorCount'];
            $return['errorList']  = join(";", $checkExcel['errorList']);
            $this->addExteriorUserLog($checkExcel['errorList']);
            return $this->json(200, '添加完成!', $return);
        }
        $errorList   = [];
        $time        = time();
        $transaction = Yii::$app->db->beginTransaction();
        foreach($excelData as $item) {
            //计划进厂日期
            $getEnterTime = trim($item[5]);
            if(!empty($getEnterTime)) {
                $getEnterTime = $this->changeTime($getEnterTime);
               // $getEnterTime = strtotime(gmdate('Y-m-d ', \PHPExcel_Shared_Date::ExcelToPHP($getEnterTime))) + 86400 - 1;
            }

            //计划出厂日期
            $getOutTime = trim($item[6]);
            if(!empty($getOutTime)) {
                $getOutTime = $this->changeTime($getOutTime);
                //$getOutTime = strtotime(gmdate('Y-m-d ', \PHPExcel_Shared_Date::ExcelToPHP($getOutTime))) + 86400 - 1;
            }
            $query = ExteriorUser::findOne(['phone' => trim($item[2]), 'type' => trim($item[7])]);
            if($query) {
                if(!empty($item[10])) {
                    $updateData = $this->updateData($item, ExteriorUser::FACTORY_ATTJ, $getEnterTime, $getOutTime);
                    if(!$updateData) {
                        $errorList[] = '承包商姓名:' . $item[0] . '更新失败';
                        $return['errorCount']++;
                    }
                }
                if(!empty($item[11])) {
                    $updateData = $this->updateData($item, ExteriorUser::FACTORY_TC, $getEnterTime, $getOutTime);
                    if(!$updateData) {
                        $errorList[] = '承包商姓名:' . $item[0] . '更新失败';
                        $return['errorCount']++;
                    }
                }
                if(!empty($item[12])) {
                    $updateData = $this->updateData($item, ExteriorUser::FACTORY_IWH, $getEnterTime, $getOutTime);
                    if(!$updateData) {
                        $errorList[] = '承包商姓名:' . $item[0] . '更新失败';
                        $return['errorCount']++;
                    }
                }

            } else {
                if(!empty($item[10])) {
                    $addData = $this->addData($item, ExteriorUser::FACTORY_ATTJ, $getEnterTime, $getOutTime);
                    if(!$addData) {
                        $errorList[] = '承包商姓名:' . $item[0] . '添加失败';
                        $return['errorCount']++;
                    }
                }
                if(!empty($item[11])) {
                    $addData = $this->addData($item, ExteriorUser::FACTORY_TC, $getEnterTime, $getOutTime);
                    if(!$addData) {
                        $errorList[] = '承包商姓名:' . $item[0] . '添加失败';
                        $return['errorCount']++;
                    }
                }
                if(!empty($item[12])) {
                    $addData = $this->addData($item, ExteriorUser::FACTORY_IWH, $getEnterTime, $getOutTime);
                    if(!$addData) {
                        $errorList[] = '承包商姓名:' . $item[0] . '添加失败';
                        $return['errorCount']++;
                    }
                }
            }
        }
        if($return['errorCount'] > 0) {
            $transaction->rollBack();
            $this->addExteriorUserLog($checkExcel['errorList']);
        } else {
            $return['successCount'] = count($excelData);
            $this->addExteriorUserSuccessLog($excelData);
            $transaction->commit();
        }
        $return['errorList'] = join("<br>", $errorList);
        return $this->json(200, '添加完成!', $return);
    }

    /**
     * 添加数据
     * @param array  $item
     * @param string $getEnterTime
     * @param string $getOutTime
     * @return bool
     * @date 2021/7/7 15:04
     */
    public function addData($item = [], $key, $getEnterTime = '', $getOutTime = '')
    {
        $model                 = new ExteriorUser();
        $model->name           = trim($item[0]);
        $model->idcard         = $this->NumToStr($item[1]);
        $model->phone          = trim($item[2]);
        $model->company        = trim($item[3]);
        $model->purpose        = trim($item[4]);
        $model->get_enter_time = date("Y-m-d 00:00:00", $getEnterTime);
        $model->get_out_time   = date("Y-m-d 23:59:59", $getOutTime);
        $model->type           = trim($item[7]) == 1 ? ExteriorUser::TYPE_FORMAL : ExteriorUser::TYPE_CONTRACTOR;
        $model->pid            = trim($item[8]);
        $model->workid         = trim($item[9]);
        $model->factory        = $key;
        $model->status         = ExteriorUser::STATUS_YES;
        $model->apply_id       = trim($item[7]) == 1 ? $this->getEmpId(1, trim($item[8])) : $this->getEmpId(2, trim($item[9]));
        $model->add_user       = \Yii::$app->user->id;
        return $model->save(false);
    }

    /**
     * 更新数据
     * @param array  $item
     * @param        $key
     * @param string $getEnterTime
     * @param string $getOutTime
     * @return bool
     * @date 2021/7/7 15:31
     */
    public function updateData($item = [], $key, $getEnterTime = '', $getOutTime = '')
    {
        if(trim($item[7]) == ExteriorUser::TYPE_FORMAL){
            $query = ExteriorUser::findOne(['phone' => trim($item[2]), 'type' => trim($item[7]),'pid' => trim($item[8]), 'factory' => $key]);
            if(!$query) {
                $addData = $this->addData($item, $key, $getEnterTime, $getOutTime);
                return $addData;
            }
        }else{
            $query = ExteriorUser::findOne(['phone' => trim($item[2]), 'type' => trim($item[7]),'workid' => trim($item[9]), 'factory' => $key]);
            if(!$query) {
                $addData = $this->addData($item, $key, $getEnterTime, $getOutTime);
                return $addData;
            }
        }
        
        $query->company        = trim($item[3]);
        $query->name           = trim($item[0]);
        $query->idcard         = trim($item[1]);
        $query->purpose        = trim($item[4]);
        $query->get_enter_time = date("Y-m-d 00:00:00", $getEnterTime);
        $query->get_out_time   = date("Y-m-d 23:59:59", $getOutTime);
        $query->type           = trim($item[7]) == 1 ? ExteriorUser::TYPE_FORMAL : ExteriorUser::TYPE_CONTRACTOR;
        $query->pid            = trim($item[8]);
        $query->workid         = trim($item[9]);
        $query->factory        = $key;
        $query->status         = ExteriorUser::STATUS_YES;
        $query->apply_id       = trim($item[7]) == 1 ? $this->getEmpId(1, trim($item[8])) : $this->getEmpId(2, trim($item[9]));
        $query->add_user       = \Yii::$app->user->id;
        return $query->save(false);
    }

    /**
     * 记录日志
     * @param array $param
     * @return bool
     * @throws \yii\db\Exception
     * @date 2021/6/29 22:09
     */
    public function addExteriorUserLog($param = [])
    {
        if(empty($param)) {
            return true;
        }
        $addUser = \Yii::$app->user->id;
        $result  = ExteriorUserLog::deleteAll(['add_user' => $addUser]);
        if($result === false) {
            return false;
        }
        $data = [];
        foreach($param as $val) {
            $data[] = [$addUser, $val, time()];
        }
        $addData = Yii::$app->db->createCommand()->batchInsert(ExteriorUserLog::tableName(), ['add_user', 'error_param', 'create_time'], $data)->execute();
        if($addData > 0) {
            return true;
        }
        return false;
    }

    /**
     * 记录成功日志
     * @param array $param
     * @return bool
     * @date 2021/7/7 15:59
     */
    public function addExteriorUserSuccessLog($param = [])
    {
        if(empty($param)) {
            return true;
        }
        foreach($param as $item) {
            //计划进厂日期
            $getEnterTime = trim($item[5]);
            if(!empty($getEnterTime)) {
                $getEnterTime = $this->changeTime($getEnterTime);
               // $getEnterTime = strtotime(gmdate('Y-m-d ', \PHPExcel_Shared_Date::ExcelToPHP($getEnterTime))) + 86400 - 1;
            }

            //计划出厂日期
            $getOutTime = trim($item[6]);
            if(!empty($getOutTime)) {
                $getOutTime = $this->changeTime($getOutTime);
                //$getOutTime = strtotime(gmdate('Y-m-d ', \PHPExcel_Shared_Date::ExcelToPHP($getOutTime))) + 86400 - 1;
            }
            $model                 = new ExteriorUserSuccessLog();
            $model->name           = trim($item[0]);
            $model->idcard         = $this->NumToStr($item[1]);
            $model->phone          = trim($item[2]);
            $model->company        = trim($item[3]);
            $model->purpose        = trim($item[4]);
            $model->get_enter_time = date("Y-m-d 00:00:00", $getEnterTime);
            $model->get_out_time   = date("Y-m-d 23:59:59", $getOutTime);
            $model->type           = trim($item[7]) == 1 ? ExteriorUser::TYPE_FORMAL : ExteriorUser::TYPE_CONTRACTOR;
            $model->pid            = trim($item[8]);
            $model->workid         = trim($item[9]);
            $model->factory_attj   = trim($item[10]);
            $model->factory_tc     = trim($item[11]);
            $model->factory_iwh     = trim($item[12]);
            $model->status         = ExteriorUser::STATUS_YES;
            $model->apply_id       = trim($item[7]) == 1 ? $this->getEmpId(1, trim($item[8])) : $this->getEmpId(2, trim($item[9]));
            $model->add_user       = \Yii::$app->user->id;
            $model->create_time    = time();
            $model->update_time    = time();
            $model->save(false);
        }
        return true;
    }

    /**
     * @param array $param
     * @return array
     * @date 2021/6/29 21:50
     */
    public function checkExcel($param = [])
    {
        $result    = [
            'type'       => 0,
            'errorCount' => 0,
            'errorList'  => [],
        ];
        $errorList = [];
        foreach($param as $key => $item) {
            $list = $key + 1;
            if(empty($item[0])) {
                $errorList[] = '第' . $list . '行,承包商姓名不能为空' . "<br>";
                $result['errorCount']++;
            }
            // if(empty($item[1])) {
            //     $errorList[] = '第' . $list . '行,承包商身份证号' . $item[0] . '不能为空' . "<br>";
            //     $result['errorCount']++;
            // }
            if(empty($item[2])) {
                $errorList[] = '第' . $list . '行,承包商联系方式不能为空' . "<br>";
                $result['errorCount']++;
            }
            if(empty($item[3])) {
                $errorList[] = '第' . $list . '行,承包商公司名称不能为空' . "<br>";
                $result['errorCount']++;
            }
            if(empty($item[4])) {
                $errorList[] = '第' . $list . '行,承包商来访原因不能为空' . "<br>";
                $result['errorCount']++;
            }
            if(empty($item[5])) {
                $errorList[] = '第' . $list . '行,承包商计划入场日期不能为空' . "<br>";
                $result['errorCount']++;
            }
            if(empty($item[6])) {
                $errorList[] = '第' . $list . '行,承包商计划出厂日期不能为空' . "<br>";
                $result['errorCount']++;
            }
            //计划进厂日期
            $getEnterTime = trim($item[5]);
            $getOutTime   = trim($item[6]);
            if(!empty($getEnterTime) && !empty($getOutTime)) {
                $getEnterTime = $this->changeTime($getEnterTime);
                $getOutTime = $this->changeTime($getOutTime);
               // $getEnterTime = strtotime(gmdate('Y-m-d ', \PHPExcel_Shared_Date::ExcelToPHP($getEnterTime))) + 86400 - 1;
              //  $getOutTime   = strtotime(gmdate('Y-m-d ', \PHPExcel_Shared_Date::ExcelToPHP($getOutTime))) + 86400 - 1;
                //出厂日期-入场日期 <= 30天
                $date = floor(($getOutTime - $getEnterTime) / 86400);
                if($date > 30) {
                    $errorList[] = '第' . $list . '行,出厂日期已经超过30天' . "<br>";
                    $result['errorCount']++;
                }
            }
            if(empty($item[7])) {
                $errorList[] = '第' . $list . '行,正式员工/包商,不能为空' . "<br>";
                $result['errorCount']++;
            }
            if(empty($item[8]) && empty($item[9])) {
                $errorList[] = '第' . $list . '行,请输入对接员工PID或授权包商WorkID' . "<br>";
                $result['errorCount']++;
            }
            if(empty($item[10]) && empty($item[11]) && empty($item[12])) {
                $errorList[] = '第' . $list . '行,请输入需要进入的厂区' . "<br>";
                $result['errorCount']++;
            }
        }
        if(!empty($errorList)) {
            $result['type']      = 1;
            $result['errorList'] = $errorList;
        }
        return $result;
    }

    /**
     * 根据pid获取部门
     * @param $pid
     * @return false|mixed|null
     */
    public function departmentByPid($pid)
    {
        $emp = LenelEmp::findOne(['SSNO' => $pid]);
        if(!$emp) {
            return false;
        }
        $udfemp = LenelUdfemp::findOne(['ID' => $emp->id]);
        if(!$udfemp) {
            return false;
        }
        $dept = LevelDept::findOne(['ID' => $udfemp->DEPT]);
        if(!$dept) {
            return false;
        }
        return $dept->NAME;
    }

    /**
     * 根据workid 获取部门
     * @param $id
     * @return false|mixed|null
     */
    public function departmentByWorkId($id)
    {
        $udfemp = LenelUdfemp::findOne(['ADDR1' => $id]);
        if(!$udfemp) {
            return false;
        }
        $dept = LevelDept::findOne(['ID' => $udfemp->DEPT]);
        if(!$dept) {
            return false;
        }
        return $dept->NAME;
    }

    /**
     * 获取empid
     * @param int $type
     * @param     $id
     * @return false|mixed|null
     */
    public function getEmpId($type, $id)
    {
        if($type == 1) {
            $emp = LenelEmp::findOne(['SSNO' => $id]);
            if(!$emp) {
                return false;
            }
            return $emp->ID;
        }
        $udfemp = LenelUdfemp::findOne(['ADDR1' => $id]);
        if(!$udfemp) {
            return false;
        }
        return $udfemp->ID;
    }

    /**
     * 科学计数法转字符串
     * @param $num
     * @return string
     */
    function NumToStr($num)
    {
        if(stripos($num, 'e') === false)
            return $num;
        $num    = trim(preg_replace('/[=\'"]/', '', $num, 1), '"');//出现科学计数法，还原成字符串
        $result = "";
        while($num > 0) {
            $v      = $num - floor($num / 10) * 10;
            $num    = floor($num / 10);
            $result = $v . $result;
        }
        return $result;
    }

    /**
     * 删除
     * @param $id
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDestroy($id)
    {
        $model = ExteriorUserRecord::findOne($id);
        if($model->delete()) {
            return $this->json(200, '删除成功!');
        }
        return $this->json(100, '删除失败!');
    }

    /**
     * 下载模板
     */
    public function actionDownloadTemplate()
    {
        $file    = 'batch_import_3.xlsx';
        $wrstr   = htmlspecialchars_decode(file_get_contents('download/' . $file));
        $outfile = '模板文件_' . time() . '.xlsx';
        header('Content-type: application/octet-stream; charset=utf8');
        Header("Accept-Ranges: bytes");
        header('Content-Disposition: attachment; filename=' . $outfile);
        echo $wrstr;
        exit();
    }

      /**
     * 转换时间
     * @param string $date
     * @return false|float|int|string
     * @date 2021/8/31 11:10
     */
    public function changeTime($date )
    {
        //匹配日期格式
        if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
            //检测是否为日期,checkdate为月日年
            if(checkdate($parts[2], $parts[3], $parts[1])){
                $timeData = \PHPExcel_Style_NumberFormat::toFormattedString($date); //格式化数字
                return strtotime($timeData);
             }
            return 0;
        }
        return strtotime(gmdate('Y-m-d ', \PHPExcel_Shared_Date::ExcelToPHP($date))) + 86400 - 1;
    }

}

