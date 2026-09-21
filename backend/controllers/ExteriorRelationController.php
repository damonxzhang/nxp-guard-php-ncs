<?php

namespace backend\controllers;

use Yii;
use backend\models\search\ExteriorRelationSearch;
use yii\data\Pagination;
use backend\models\ExteriorRelation;
use backend\models\UserContractor;
use yii\helpers\ArrayHelper;
use common\models\Utility;
use backend\models\LenelBadge;
use backend\models\LenelEmp;
class ExteriorRelationController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorRelationSearch();
        $data         = $searchModel->search($request);
        $totalCount   = $data->totalCount;
        $dataProvider = $data->getModels();
        $pagination   = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => 20,
        ]);
        return $this->render('index', [
            'request'      => $request,
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'pagination'   => $pagination
        ]);
        // $badge = LenelBadge::findOne(['ID' => '221613']);
        // if(!$badge) {
        //     return false;
        // }
        // $postData = [
        //     'opt'  => 'SET',
        //     'data' => [[
        //         'badgeNumber'   => 202650,
        //         'activateDate'  => '2020-09-15',
        //         'deactivateDate'=> '2120-09-16',
        //         'type'          => $badge->TYPE,
        //         'status'        => $badge->STATUS,
        //         'personID'      => 3,
        //         'accessLevels'  => []
        //     ]]
        // ];// var_dump( $postData);die;
        // $url      = 'http://10.192.166.234:18080/LenelWMIAdapter/';
        // $result   = $this->sendPost($url, json_encode($postData));
        // echo"<pre>";var_dump($result);die;
    }

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
    /**
     * 删除人员信息
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDel()
    {
        $id    = (int)Yii::$app->request->get('id');
        $model = ExteriorRelation::findOne($id);
        if (!$model->delete()) {
            return $this->json(100, '删除失败');
        }
        return $this->json(200, '删除成功');
    }

    /**
     * 添加人员
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {
            $data        = Yii::$app->request->post();
            $userName    = ArrayHelper::getValue($data, 'user_name');
            $leaderName  = ArrayHelper::getValue($data, 'leader_name');
            $check       = ExteriorRelation::findOne(['user_name' => trim($userName), 'leader_name' => $leaderName]);
            if ($check) {
                return $this->json(200, '人员已存在请查询修改!');
            }
            $model                = new ExteriorRelation();
            $model->user_pid      = ArrayHelper::getValue($data, 'user_pid');
            $model->user_wbi_id   = ArrayHelper::getValue($data, 'user_wbi_id');
            $model->user_name     = ArrayHelper::getValue($data, 'user_name');
            $model->leader_pid    = ArrayHelper::getValue($data, 'leader_pid');
            $model->depid         = ArrayHelper::getValue($data, 'depid');
            $model->work_id       = ArrayHelper::getValue($data, 'work_id');
            $model->badge         = ArrayHelper::getValue($data, 'badge');
            $model->leader_name   = ArrayHelper::getValue($data, 'leader_name');
            if (!$model->save(false)) {
                return $this->json(100, '人员添加失败!');
            }
            return $this->json(200, '人员添加完成!');
        }
        return $this->render('create');
    }

      /**
     * 更新人员信息
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isPost) {
            $data                 = Yii::$app->request->post();
            $id                   = ArrayHelper::getValue($data, 'id');
            $model                = ExteriorRelation::findOne($id);
            $model->user_pid      = ArrayHelper::getValue($data, 'user_pid');
            $model->user_wbi_id   = ArrayHelper::getValue($data, 'user_wbi_id');
            $model->user_name     = ArrayHelper::getValue($data, 'user_name');
            $model->leader_pid    = ArrayHelper::getValue($data, 'leader_pid');
            $model->depid         = ArrayHelper::getValue($data, 'depid');
            $model->work_id       = ArrayHelper::getValue($data, 'work_id');
            $model->badge         = ArrayHelper::getValue($data, 'badge');
            $model->leader_name   = ArrayHelper::getValue($data, 'leader_name');
            if ($model->save(false)) {
                return $this->json(200, '人员更新完成!');
            }
            return $this->json(100, '人员更新失败!');
        }

        $id      = Yii::$app->request->get('id');
        $model   = ExteriorRelation::findOne($id);
        return $this->render('update', [
            'model'   => $model
        ]);
    }

      /**
     * 下载模板
     */
    public function actionDownloadTemplate()
    {
        $file    = 'exterior_relation_import.xlsx';
        $wrstr   = htmlspecialchars_decode(file_get_contents('download/' . $file));
        $outfile = '部门模板文件_' . time() . '.xlsx';
        header('Content-type: application/octet-stream; charset=utf8');
        Header("Accept-Ranges: bytes");
        header('Content-Disposition: attachment; filename=' . $outfile);
        echo $wrstr;
        exit();
    }

      /**
     * 一键同步
     */
    public function actionOneupdate()
    {
        $all = ExteriorRelation::find()->asArray()->all();
        foreach($all as $value){
            // if($value['user_pid'] == '91001963')
            // {
                if(!empty($value['user_pid'])){
                    
                    $empModel = LenelEmp::findOne(['SSNO' => $value['user_pid']]);
                    
                    // print_r($value['work_id']);die;
                    if(!$empModel) {
                        if(!empty($value['work_id'])){
                            $empModel = LenelEmp::findOne(['SSNO' => $value['work_id']]);
                            // print_r($empModel);die;
                            if(!$empModel) {
                                // print_r($value);die;
                                $model                = ExteriorRelation::findOne($value['id']);
                                $model->del           = 'YES';
                                if ($model->save(false)) {
                                    // continue;
                                }
                            }
                        }else{
                            $model                = ExteriorRelation::findOne($value['id']);
                            $model->del           = 'YES';
                            if ($model->save(false)) {
                                // continue;
                            }
                        }
                    }
                }else{
                    if(!empty($value['work_id'])){
                        $empModel = LenelEmp::findOne(['SSNO' => $value['work_id']]);
                        // print_r($empModel);die;
                        if(!$empModel) {
                            // print_r($value);die;
                            $model                = ExteriorRelation::findOne($value['id']);
                            $model->del           = 'YES';
                            if ($model->save(false)) {
                                // continue;
                            }
                        }
                    }
                }
            // }
            
            
        }
        return $this->json(200, '同步完成!');
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
        foreach ($excelData as $item) {
            if (empty($item[0])) {
                continue;
            }
            
            if($item[8] == 1){
                $query = ExteriorRelation::findOne(['user_pid' => trim($item[0])]);
                if ($query) {
                    $query->user_wbi_id = trim($item[2]);
                    $query->user_name   = trim($item[3]);
                    $query->leader_name = trim($item[4]);
                    $query->leader_pid  = trim($item[5]);
                    $query->depid       = trim($item[6]);
                    $query->badge       = trim($item[1]);
                    $query->location    = trim($item[7]);
                    // $query->user_wbi_id= trim($item[1]);
                    // $query->user_name  = trim($item[2]);
                    // $query->leader_name= trim($item[3]);
                    // $query->depid      = trim($item[4]);
                    // $query->badge      = trim($item[5]);
                    $query->update_time=time();
                    if (!$query->save(false)) {
                        $errorList[] = 'EMPLOYEE ID:' . $item[0];
                        $return['errorCount']++;
                        continue;
                    } else {
                        $return['successCount']++;
                        continue;
                    }
                }else{
                    // print_r($item);die;
                    $model                  = new ExteriorRelation();
                    $model->user_pid        = trim($item[0]);
                    $model->user_wbi_id     = trim($item[2]);
                    $model->user_name       = trim($item[3]);
                    $model->leader_name     = trim($item[4]);
                    $model->leader_pid      = trim($item[5]);
                    $model->depid           = trim($item[6]);
                    $model->badge           = trim($item[1]);
                    $model->location        = trim($item[7]);

                    // $model->user_wbi_id  = trim($item[1]);
                    // $model->user_name    = trim($item[2]);
                    // $model->leader_name  = trim($item[3]);
                    // $model->depid        = trim($item[4]);
                    // $model->badge        = trim($item[5]);
                    $model->create_time  =time();
                    if (!$model->save(false)) {
                        $errorList[] = 'EMPLOYEE ID:' . $item[0];
                        $return['errorCount']++;
                        continue;
                    } else {
                        $return['successCount']++;
                    }
                }
            }
            if($item[8] == 2){
                $query = ExteriorRelation::findOne(['work_id' => trim($item[0])]);
                if ($query) {
                    $query->user_name  = trim($item[3]);
                    $query->leader_name= trim($item[4]);
                    $query->depid      = trim($item[6]);
                    $query->update_time=time();
                    if (!$query->save(false)) {
                        $errorList[] = 'WORK ID:' . $item[0];
                        $return['errorCount']++;
                        continue;
                    } else {
                        $return['successCount']++;
                        continue;
                    }
                }else{

                    $model               = new ExteriorRelation();
                    $model->work_id      = trim($item[0]);
                    $model->user_name    = trim($item[3]);
                    $model->leader_name  = trim($item[4]);
                    $model->depid        = trim($item[6]);
                    $model->create_time  =time();
                    if (!$model->save(false)) {
                        $errorList[] = 'WORK ID:' . $item[0];
                        $return['errorCount']++;
                        continue;
                    } else {
                        $return['successCount']++;
                    }
                }
            }
            
        }
        $return['errorList'] = join("<br>", $errorList);
        return $this->json(200, '添加完成!', $return);
    }

}
