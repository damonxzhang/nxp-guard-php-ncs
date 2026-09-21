<?php

namespace backend\controllers;

use backend\models\search\UserContractorSearch;
use backend\models\UserContractor;
use common\models\Haikang;
use common\models\Utility;
use yii\data\Pagination;
use common\models\ImgCompress;
use Yii;
use yii\helpers\ArrayHelper;

class UserContractorController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new UserContractorSearch();
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
     * @throws \yii\base\Exception
     */
    public function actionView()
    {
        $id    = Yii::$app->request->get('id');
        $model = UserContractor::findOne($id);
        return $this->render('view', [
            'model' => $model
        ]);
    }


    /**
     * 新增人员下发
     * @param $id
     * @return array
     */
    function addDownload($id)
    {
        $result          = ['status' => false, 'message' => ''];
        $baseUrl         = \Yii::$app->request->getHostInfo();//获取路由
        $query           = UserContractor::findOne($id);
        $value['name']   = $query->name;
        $filename        = $baseUrl . '/photo/' . $query->empphoto;
        $value['photo']  = base64_encode(file_get_contents($filename));
        $value['idCard'] = $query->idcard;
        $haiKang         = new Haikang();
        $addUser         = $haiKang->addPersion($value);//添加人员信息
        if ($addUser['code'] == 0) {
            $personId     = $addUser['data'];
            $operatorType = 0;//添加
            usleep(300000);
            $startTime = date('c',strtotime('-1day'));
            $endTime   = date('c',strtotime($query->ebdate));
            foreach (UserContractor::$indexCodeArr as $indexCode) {
                $haiKang->simpleDownload($indexCode, $personId, $operatorType,$startTime,$endTime);//下发人员信息到设备
            }
            $result = ['status' => true, 'message' => '人员信息下发完成'];
        } else {
            $result['message'] = '人员信息下发失败';
        }
        return $result;
    }

    /**
     * 添加人员
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {

            $transaction = Yii::$app->db->beginTransaction();
            $data        = Yii::$app->request->post();
            $empphoto    = ArrayHelper::getValue($data, 'empphoto');
            if (empty($empphoto)) {
                return $this->json(100, '请上传人脸照片!');
            }
            $idcard    = ArrayHelper::getValue($data, 'idcard');
            $check     = UserContractor::findOne(['idcard' => trim($idcard)]);
            if ($check){
                return $this->json(200, '人员已存在请查询修改!');
            }
            $model                = new UserContractor();
            $model->ebarcode      = ArrayHelper::getValue($data, 'ebarcode');
            $model->name          = ArrayHelper::getValue($data, 'name');
            $model->workid        = ArrayHelper::getValue($data, 'workid');
            $model->depid         = ArrayHelper::getValue($data, 'depid');
            $model->compid        = ArrayHelper::getValue($data, 'compid');
            $model->ebdate        = ArrayHelper::getValue($data, 'ebdate');
            $model->empphoto      = $empphoto;
            $model->contactid     = ArrayHelper::getValue($data, 'contactid');
            $model->contactname   = ArrayHelper::getValue($data, 'contactname');
            $model->contactdepart = ArrayHelper::getValue($data, 'contactdepart');
            $model->idcard        = $idcard;
            $model->inputdate     = ArrayHelper::getValue($data, 'inputdate');
            $model->depname       = ArrayHelper::getValue($data, 'depname');
            $model->is_del        = UserContractor::IS_DEL_NO;
            if ($model->save(false)) {
                $id          = $model->attributes['id'];
                $addDownload = $this->addDownload($id);
                if ($addDownload['status']) {
                    $transaction->commit();
                    return $this->json(200, '人员添加完成!');
                }
            }
            $transaction->rollBack();
            return $this->json(100, '人员添加失败!');
        }
        $baseUrl = \Yii::$app->request->getHostInfo();//获取路由
        return $this->render('create', [
            'baseUrl' => $baseUrl
        ]);

    }

    /**
     * 上传图片
     * @return string
     */
    public function actionUpload()
    {
        $fileInfo  = $_FILES['file'];
        $fileError = $fileInfo['error'];
        if ($fileError != 0) {
            return $this->json(100, '文件上传失败');
        }
        $path     = 'photo/';
        $fileName = time() . '.jpg';
        $filePath = $path . $fileName;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);//递归方式创建数组
        }
        $size     = filesize($_FILES["file"]["tmp_name"]);
        $fileSize = round($size / 1024, 2);
        if ($fileSize > 195) {
            $img_compress = new ImgCompress();
            $head_path    = $img_compress->img_move($_FILES["file"]["tmp_name"], $filePath);//将图片保存至路径，并返回带路径的文件名['path']和扩展名['ext']
            if (!$head_path) {
                return $this->json(100, '文件上传失败');
            }
            $img_compress->set_img($head_path['path']); //临时将图片转为jpg
            $img_compress->set_quality(85);             //设置图片质量
            $img_compress->set_size(0.6);               //设置缩放比例
            $img_compress->save_img($head_path['path']);//保存图片
            $img_compress->clear_cache();               //清除缓存
            clearstatcache();                           //清除文件缓存
            return $this->json(200, '上传完成！', ['file_name' => $fileName, 'img_url' => '/' . $filePath]);
        }
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $filePath)) {
            return $this->json(200, '上传完成！', ['file_name' => $fileName, 'img_url' => '/' . $filePath]);
        }
        return $this->json(100, '文件上传失败');
    }

    /**
     * 更新人员信息
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            $data        = Yii::$app->request->post();
            $empphoto    = ArrayHelper::getValue($data, 'empphoto');
            if (empty($empphoto)) {
                return $this->json(100, '请上传人脸照片!');
            }
            $id                   = ArrayHelper::getValue($data, 'id');
            $model                = UserContractor::findOne($id);
            $oldEmpphoto          = $model->empphoto;
            $model->ebarcode      = ArrayHelper::getValue($data, 'ebarcode');
            $model->name          = ArrayHelper::getValue($data, 'name');
            $model->workid        = ArrayHelper::getValue($data, 'workid');
            $model->depid         = ArrayHelper::getValue($data, 'depid');
            $model->compid        = ArrayHelper::getValue($data, 'compid');
            $model->ebdate        = ArrayHelper::getValue($data, 'ebdate');
            $model->empphoto      = $empphoto;
            $model->contactid     = ArrayHelper::getValue($data, 'contactid');
            $model->contactname   = ArrayHelper::getValue($data, 'contactname');
            $model->contactdepart = ArrayHelper::getValue($data, 'contactdepart');
            $model->idcard        = ArrayHelper::getValue($data, 'idcard');
            $model->inputdate     = ArrayHelper::getValue($data, 'inputdate');
            $model->depname       = ArrayHelper::getValue($data, 'depname');
            if ($model->save(false)) {
                $id = $model->attributes['id'];
                $this->delDownload($id);//删除人脸和照片
                $addDownload = $this->addDownload($id);
                if ($addDownload['status']) {
                    $transaction->commit();
                    return $this->json(200, '人员更新完成!');
                }
            }
            $transaction->rollBack();
            return $this->json(100, '人员更新失败!');
        }

        $id      = Yii::$app->request->get('id');
        $model   = UserContractor::findOne($id);
        $baseUrl = \Yii::$app->request->getHostInfo() . '/photo/';//获取路由
        return $this->render('update', [
            'baseUrl' => $baseUrl,
            'model'   => $model
        ]);
    }


    /**
     * 更新人员下发
     * @param $id
     * @return array
     */
    function updateDownload($id)
    {
        $result          = ['status' => false, 'message' => ''];
        $baseUrl         = \Yii::$app->request->getHostInfo();//获取路由
        $query           = UserContractor::findOne($id);
        $value['name']   = $query->name;
        $value['idCard'] = $query->idcard;
        $haiKang         = new Haikang();
        $checkPerson     = $haiKang->checkPerson($value);//检查人员信息
        usleep(300000);
        if ($checkPerson['code'] == 0 ) {
            $filename       = $baseUrl . '/photo/' . $query->empphoto;
            $value['photo'] = base64_encode(file_get_contents($filename));
            $personList     = $checkPerson['data']['list'];
            //获取人员列表
            if ($personList) {
                $value['personId'] = $personId = $personList[0]['personId'];
                $personPhoto       = $personList[0]['personPhoto'];
                if (!empty($personPhoto)) {
                    //有图片就更新
                    $value['faceId'] = $personPhoto[0]['personPhotoIndexCode'];
                    $singleUpdate    = $haiKang->singleUpdate($value);
                    if ($singleUpdate['code'] != 0) {
                        $result['message'] = '人员信息下发失败';
                        return $result;
                    }
                } else {
                    //没有图片就添加
                    $singleAdd = $haiKang->singleAdd($value);
                    if ($singleAdd['code'] != 0) {
                        $result['message'] = '人员信息下发失败';
                        return $result;
                    }
                }
                $operatorType = 1;//更新
                $startTime = date('c',strtotime('-1day'));
                $endTime   = date('c',strtotime($query->ebdate));
                foreach (UserContractor::$indexCodeArr as $indexCode) {
                    usleep(300000);
                    $haiKang->simpleDownload($indexCode, $personId, $operatorType,$startTime,$endTime);//下发人员信息到设备
                }
                $result = ['status' => true, 'message' => '人员信息下发完成'];
            }
        } else {
            $result['message'] = '人员信息下发失败';
        }
        return $result;
    }

    /**
     * 重新下发人脸信息
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionNewInsert()
    {
        $transaction   = Yii::$app->db->beginTransaction();
        $data          = Yii::$app->request->post();
        $id            = ArrayHelper::getValue($data, 'id');
        $model         = UserContractor::findOne($id);
        $model->ebdate = ArrayHelper::getValue($data, 'ebdate');
        $model->is_del = UserContractor::IS_DEL_NO;
        if ($model->save(false)) {
            $newDownload = $this->newDownload($id);
            if ($newDownload['status']) {
                $transaction->commit();
                return $this->json(200, '人员信息下发完成!');
            }
        }
        $transaction->rollBack();
        return $this->json(100, '人员信息下发失败!');
    }

    /**
     * 批量下发人员信息
     */
    public function issudeUser()
    {
        $result  = [];
        $baseUrl = \Yii::$app->request->getHostInfo();//获取路由
        $query   = UserContractor::find()->select(['id', 'name', 'empphoto','idcard'])->asArray()->all();
        if (!empty($query)) {
            foreach ($query as $key => $value) {
                if (!empty($value['empphoto']) && !empty($value['idcard'])) {
                    $filename        = $baseUrl . '/photo/' . $value['empphoto'];
                    $value['photo']  = base64_encode(file_get_contents($filename));
                    $value['idCard'] = $value['idcard'];
                    $addData         = (new Haikang())->addPersion($value);
                    $result[]        = [$value['name'], $addData];
                    usleep(300000);
                }
            }
        }
        return $result;
    }

    /**
     * 出入权限配置
     * @return false|mixed'
     */
    function insertDddition()
    {
        $orgIndexCode = '1adb0a6a-095e-42ab-88aa-be8c77635549';
        $indexCode    = '794059621e314822a89e2e776b6e20fe';
        $authAdd      = (new Haikang())->authAdd($orgIndexCode, $indexCode);
        return $authAdd;
    }


    /**
     * 删除人员信息
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDel()
    {
        $id = (int)Yii::$app->request->get('id');
        $this->delDownload($id);//删除人脸和照片
        $model = UserContractor::findOne($id);
        if (!$model->delete()) {
            return $this->json(100, '删除失败');
        }
        return $this->json(200, '删除成功');
    }
    
 /**
     * 删除下发人员
     * @param $id
     * @return array
     */
    function delDownload($id)
    {
        $result          = ['status' => false, 'message' => ''];
        $query           = UserContractor::findOne($id);
        $value['name']   = $query->name;
        $value['idCard'] = $query->idcard;
        $haiKang         = new Haikang();
        $checkPerson     = $haiKang->checkPerson($value);//检查人员信息
        usleep(300000);
        if ($checkPerson['code'] == 0 ) {
            $personList     = $checkPerson['data']['list'];
            //获取人员列表
            if ($personList) {
                $personId = $personList[0]['personId'];
                $operatorType = 2;//删除
                $startTime = date('c',strtotime('-1day'));
                $endTime   = date('c',strtotime($query->ebdate));
                foreach (UserContractor::$indexCodeArr as $indexCode) {
                    usleep(300000);
                    $haiKang->simpleDownload($indexCode, $personId, $operatorType,$startTime,$endTime);//下发删除人员信息到设备
                }
                $personIds[] = $personId;
                $haiKang->deletePerson($personIds);//删除人员
                $personPhoto = $personList[0]['personPhoto'];
                if(isset($personPhoto[0]['personPhotoIndexCode'])){
                    $faceId = $personPhoto[0]['personPhotoIndexCode'];
                    $haiKang->singleDelete($faceId);//删除人脸
                }
                $result = ['status' => true, 'message' => '人员信息删除完成'];
            }
        } else {
            $result['message'] = '人员信息删除失败';
        }
        return $result;
    }

    
    /**
     * 导出数据
     * @return string
     */
    public function actionExport()
    {
        $dataProvider =UserContractor::find()->asArray()->all();
        $data = [];
        if (!empty($dataProvider)) {
            foreach ($dataProvider as $key => $value) {
                $data[$key][] = $value['ebarcode'];
                $data[$key][] = $value['name'];
                $data[$key][] = $value['workid'];
                $data[$key][] = $value['depid'];
                $data[$key][] = $value['compid'];
                $data[$key][] = $value['ebdate'];
                $data[$key][] = $value['contactid'];
                $data[$key][] = $value['contactname'];
                $data[$key][] = $value['contactdepart'];
                $data[$key][] = $value['idcard'];
                $data[$key][] = $value['depname'];
                $data[$key][] = $value['inputdate'];
            }
        }
        $fileName = '长期包商' . time() . '.xlsx';
        $header   = ['EBarcode', 'Name', 'WorkID', 'DepID', 'CompID', 'EBDate', 'ContactID', 'ContactName', 'ContactDepart', 'IDCard', 'DepName', 'InputDate'];
        array_unshift($data, $header); //添加头部标题
        return $this->json(200, $fileName, $data);
    }

    

    /**
     * 下载模板
     */
    public function actionDownloadTemplate()
    {
        $file    = 'user_contractor.xlsx';
        $wrstr   = htmlspecialchars_decode(file_get_contents('download/' . $file));
        $outfile = time() . '.xlsx';
        header('Content-type: application/octet-stream; charset=utf8');
        Header("Accept-Ranges: bytes");
        header('Content-Disposition: attachment; filename=' . $outfile);
        echo $wrstr;
        exit();
    }


    /**
     * 批量导入数据
     * @return string
     */
    public function actionImport()
    {
        $isPost = Yii::$app->request->isPost;
        if (!$isPost) {
            return $this->render('import');
        }
        $file = $_FILES['file'];
        if (empty($file['tmp_name'])) {
            return $this->json(100, '上传文件不能为空!');
        }
        $tmp    = explode('.', $file['name']);
        $suffix = array_pop($tmp);
        if ('xlsx' !== $suffix) {
            return $this->json(100, '上传文件无法识别，请使用模版!');
        }

        if ($file['size'] > 2097152) {
            return $this->json(100, '上传文件不能超过2MB!');
        }
        $excelData = Utility::getExcelData($file['tmp_name']);
        if (empty($excelData)) {
            return $this->json(100, '上传文件无内容!');
        }
        $return    = [
            'successCount' => 0,
            'errorCount'   => 0,
            'errorList'    => '',
        ];
        $errorList = [];
        $time      = time();
        foreach ($excelData as $key => $item) {
            if (empty($item[0]) || empty($item[1]) || empty($item[3]) || empty($item[9])) {
                return $this->json(100, '必填项不能为空!');
                break;
            }
            $EBarcode = UserContractor::findOne(['ebarcode' => trim($item[0])]);
            if ($EBarcode) {
                $errorList[] = 'EBarcode:' . $item[0] . '人员已存在!';
                $return['errorCount']++;
                continue;
            }
            $workid = UserContractor::findOne(['workid' => trim($item[3])]);
            if ($workid) {
                $errorList[] = 'workid:' . $item[3] . '已存在!';
                $return['errorCount']++;
                continue;
            }
            $model           = new UserContractor();
            $model->ebarcode = trim($item[0]);
            $model->name     = trim($item[1]);
            $model->depid    = preg_replace('/_x000D_/','',trim($item[2]));
            $model->workid   = trim($item[3]);
            $model->compid   = preg_replace('/_x000D_/','',trim($item[4]));
            $ebdate          = trim($item[5]);
            if (!empty($ebdate)) {
                $gmdate = strtotime(gmdate('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($ebdate)));
                $ebdate = date('Y-m-d', $gmdate);
            }
            $model->ebdate        = $ebdate;
            $model->empphoto      = '';
            $model->contactid     = preg_replace('/_x000D_/','',trim($item[6]));
            $model->contactdepart = preg_replace('/_x000D_/','',trim($item[7]));
            $model->contactname   = preg_replace('/_x000D_/','',trim($item[8]));
            $model->idcard        = preg_replace('/_x000D_/','',$this->NumToStr(trim($item[9])));
            $model->depname       = preg_replace('/_x000D_/','',trim($item[10]));
            $inputdate            = trim($item[11]);
            if (!empty($inputdate)) {
                $gmdate    = strtotime(gmdate('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($inputdate)));
                $inputdate = date('Y-m-d', $gmdate);
            }
            $model->inputdate = $inputdate;
            if ($model->save(false)) {
                $return['successCount']++;
            } else {
                $errorList[] = '姓名:' . $item[1];
                $return['errorCount']++;
            }
        }
        $return['errorList'] = join("<br>", $errorList);
        return $this->json(200, '添加完成!', $return);
    }

    /**
     * 科学计数法转字符串
     * @param $num
     * @return string
     */
    function NumToStr($num)
    {
        if (stripos($num, 'e') === false)
            return $num;
        $num    = trim(preg_replace('/[=\'"]/', '', $num, 1), '"');//出现科学计数法，还原成字符串
        $result = "";
        while ($num > 0) {
            $v      = $num - floor($num / 10) * 10;
            $num    = floor($num / 10);
            $result = $v . $result;
        }
        return $result;
    }

    /**
     * 下载EBdate模板
     */
    public function actionDownloadTemplateDelay()
    {
        $file    = 'user_contractor_delay.xlsx';
        $wrstr   = htmlspecialchars_decode(file_get_contents('download/' . $file));
        $outfile = 'EBdate' . time() . '.xlsx';
        header('Content-type: application/octet-stream; charset=utf8');
        Header("Accept-Ranges: bytes");
        header('Content-Disposition: attachment; filename=' . $outfile);
        echo $wrstr;
        exit();
    }

    /**
     * 批量导入数据
     * @return string
     */
    public function actionImportDelay()
    {
        $isPost = Yii::$app->request->isPost;
        if (!$isPost) {
            return $this->render('import-delay');
        }
        $file = $_FILES['file'];
        if (empty($file['tmp_name'])) {
            return $this->json(100, '上传文件不能为空!');
        }
        $tmp    = explode('.', $file['name']);
        $suffix = array_pop($tmp);
        if ('xlsx' !== $suffix) {
            return $this->json(100, '上传文件无法识别，请使用模版!');
        }

        if ($file['size'] > 2097152) {
            return $this->json(100, '上传文件不能超过2MB!');
        }
        $excelData = Utility::getExcelData($file['tmp_name']);
        if (empty($excelData)) {
            return $this->json(100, '上传文件无内容!');
        }
        $return    = [
            'successCount' => 0,
            'errorCount'   => 0,
            'errorList'    => '',
        ];
        $errorList = [];
        $time      = time();
        foreach ($excelData as $key => $item) {
            $model = UserContractor::findOne(['ebarcode' => trim($item[0])]);
            if (!$model) {
                $errorList[] = 'EBarcode:' . $item[0] . '人员不存在!';
                $return['errorCount']++;
                continue;
            }
            $ebdate          = trim($item[6]);
            if (!empty($ebdate)) {
                $gmdate = strtotime(gmdate('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($ebdate)));
                $ebdate = date('Y-m-d', $gmdate);
            }
            $check = 1;
            if ($model->is_del == UserContractor::IS_DEL_YES) {
                $model->is_del = UserContractor::IS_DEL_NO;
                $check = 2;
            }
            $model->ebdate        = $ebdate;
            if ($model->save(false)) {
                if ($check == 1) {
                    $return['successCount']++;
                    continue;
                }
                $newDownload = $this->newDownload($model->id);
                if (!$newDownload['status']) {
                    $errorList[] = '姓名:' . $item[1];
                    $return['errorCount']++;
                }else{
                    $return['successCount']++;
                }
            } else {
                $errorList[] = '姓名:' . $item[1];
                $return['errorCount']++;
            }
        }
        $return['errorList'] = join("<br>", $errorList);
        return $this->json(200, '更新完成!', $return);
    }

    /**
     * 新增人员下发
     * @param $id
     * @return array
     */
    function newDownload($id)
    {
        $result          = ['status' => false, 'message' => ''];
        $baseUrl         = \Yii::$app->request->getHostInfo();//获取路由
        $query           = UserContractor::findOne($id);
        if($query){
            $value['name']   = $query->name;
            $value['idCard'] = $query->idcard;
            $haiKang         = new Haikang();
            $checkPerson     = $haiKang->checkPerson($value);//检查人员信息
            if ($checkPerson['code'] == 0 ) {
                $personList     = $checkPerson['data']['list'];//获取人员列表
                if ($personList) {
                    $personId = $personList[0]['personId'];
                    $operatorType = 1;//更新
                    $startTime = date('c',strtotime('-1day'));
                    $endTime   = date('c',strtotime($query->ebdate));
                    foreach (UserContractor::$indexCodeArr as $indexCode) {
                        $haiKang->simpleDownload($indexCode, $personId, $operatorType,$startTime,$endTime);//下发人员信息到设备
                        usleep(300000);
                    }
                    $result = ['status' => true, 'message' => '人员信息下发完成'];
                }
            }
        } 
        return $result;
    }
    /**
     * 重新下发人员信息
     */
    public function afreshUser()
    {
        $key = 0;
        $query   = UserContractor::find()->select(['name','idcard','ebdate'])->asArray()->all();
        if (!empty($query)) {
            foreach ($query as $key =>  $data) {
                $value['name']   = $data['name'];
                $value['idCard'] = $data['idcard'];
                $haiKang         = new Haikang();
                $checkPerson     = $haiKang->checkPerson($value);//检查人员信息
                if ($checkPerson['code'] == 0 ) {
                    $personList     = $checkPerson['data']['list'];//获取人员列表
                    if ($personList) {
                        $personId = $personList[0]['personId'];
                        $operatorType = 1;//更新
                        $startTime = date('c',strtotime('-1day'));
                        $endTime   = date('c',strtotime($data['ebdate']));
                        $indexCode = 'e80e362285b144418306f1c314bc7f56';
                        $haiKang->simpleDownload($indexCode, $personId, $operatorType,$startTime,$endTime);//下发人员信息到设备
                        usleep(300000);
                    }
                }
            }
        }
        return $key;
    }
}