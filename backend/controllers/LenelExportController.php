<?php

namespace backend\controllers;

use backend\models\LenelAccesslvl;
use backend\models\LenelAcclvlink;
use backend\models\LenelBadge;
use backend\models\LenelBadgelink;
use backend\models\LenelBadgeTyp;
use backend\models\LenelBuilding;
use backend\models\LenelDivision;
use backend\models\LenelEmp;
use backend\models\LenelEvents;
use backend\models\LenelReader;
use backend\models\LenelTitle;
use backend\models\LenelUdfemp;
use backend\models\LevelDept;
use backend\models\LenelLocation;
use yii\data\Pagination;
use Yii;
use yii\helpers\ArrayHelper;

class LenelExportController extends BaseController
{
    /**
     * @return string
     */
    public function actionHistoryReport()
    {

        // $dataArray = array(
        //     'badgeNumber'   => $badgeNumber,
        //     // 'activateDate'  => $badge->ACTIVATE,
        //     // 'deactivateDate'=> $badge->DEACTIVATE,
        //     'activateDate'  => date('Y-m-d H:i:s',time()),
        //     'deactivateDate'=> date('Y-m-d H:i:s',strtotime('+2day')),
        //     'type'          => $badge->TYPE,
        //     'status'        => $status,
        //     'personID'      => $personID
        // );
        // $postData = [
        //     'opt'  => 'SET',
        //     'data' => [$dataArray]
        // ];
        // $url      = 'http://10.192.147.27:8080/bcopenapi/out/getChargebf';
        //  $result   = $this->sendPost($url, json_encode($postData));
        // if(!$result) return false;

        echo "数据升级中，暂停使用！";exit;
        // $request    = Yii::$app->request->queryParams;
        // $doorArr = $request['readeresc'] = [];
        // if(isset($request['doorstr']) && !empty($request['doorstr'])){
        //     $doorArr = $request['readeresc'] = explode(',', $request['doorstr']);
        // }
        // $query      = $this->historyReportSearch($request);
        // $queryData  = clone $query;
        // $pagination = new Pagination(['totalCount' => $queryData->count()]);
        // $data       = $query->offset($pagination->offset)
        //                     ->limit($pagination->limit)
        //                     ->asArray()
        //                     ->all();
                         
        // return $this->render('history-report', [
        //     'doorArr'    => $doorArr,
        //     'query'      => $query,
        //     'data'       => $data,
        //     'request'    => $request,
        //     'totalCount' => $queryData->count(),
        //     'pagination' => $pagination
        // ]);
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
            'Content-Type: application/json; charset=utf-8'
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
     * @param $params
     * @return \yii\db\ActiveQuery
     * @date 2021/7/13 13:37
     */
    public function historyReportSearch($params)
    {
        $query = LenelEvents::find();
        $query->alias('e');
        $query->select(['emp.ID', 'e.EVENT_TIME_UTC', 'emp.SSNO', 'emp.MIDNAME', 'emp.LASTNAME', 'r.READERDESC', 'd.NAME', 'di.NAME as DIVISIONNAME']);
        $query->leftJoin(['emp' => LenelEmp::tableName()], 'e.EMPID = emp.ID');
        $query->leftJoin(['ud' => LenelUdfemp::tableName()], 'ud.ID = emp.ID');
        $query->leftJoin(['d' => LevelDept::tableName()], 'd.ID = ud.DEPT');
        $query->leftJoin(['di' => LenelDivision::tableName()], 'ud.DIVISION = di.ID');
        $query->leftJoin(['r' => LenelReader::tableName()], 'e.DEVID = r.READERID and e.MACHINE = r.PANELID');
        $query->leftJoin(['acl' => LenelAcclvlink::tableName()], 'acl.READERID = r.READERID AND acl.PANELID = r.PANELID');
        $query->leftJoin(['acs' => LenelAccesslvl::tableName()], 'acl.ACCESSLVID = acs.ACCESSLVID');
        $query->andWhere(['>', 'e.EMPID', 0]);
        $query->andWhere(['e.EVENTTYPE' => 0]);
        $query->andFilterWhere(['in', 'r.READERDESC', ArrayHelper::getValue($params, 'readeresc', [])]);
        $query->andFilterWhere(['acs.DESCRIPT' => ArrayHelper::getValue($params, 'DESCRIPT', '')]);
        $query->andFilterWhere(['emp.SSNO' => ArrayHelper::getValue($params, 'ssno', '')]);
        $query->andFilterWhere(['emp.MIDNAME' => ArrayHelper::getValue($params, 'midname', '')]);
        $query->andFilterWhere(['d.NAME' => ArrayHelper::getValue($params, 'name', '')]);
        $startTime = ArrayHelper::getValue($params, 'start_time', '');
        $endTime   = ArrayHelper::getValue($params, 'end_time', '');
        if(!empty($startTime)){
            $query->andFilterWhere(['>', 'e.EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime("$startTime -28800second"))]);
        }else{
            $query->andFilterWhere(['>', 'e.EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime("-1 day"))]);
        }
        if(!empty($endTime)){
            $query->andFilterWhere(['<=', 'e.EVENT_TIME_UTC', date('Y-m-d H:i:s', strtotime("$endTime -28800second"))]); 
        }
       // $query->orderBy('e.EVENT_TIME_UTC DESC');
        $query->groupBy(['emp.ID','e.EVENT_TIME_UTC', 'emp.SSNO', 'emp.MIDNAME', 'emp.LASTNAME', 'r.READERDESC', 'd.NAME', 'di.NAME']);
        
        // $queryData = $query->asArray()->all();

        // $arr = [];
        // foreach ($queryData as $value){
        //     $arr[] = $value['SERIALNUM'];
        // }
        // // print_r($arr);die;
        
        // $query = LenelEvents::find();
        // $query->alias('e');
        // $query->select(['emp.ID','e.SERIALNUM', 'e.EVENT_TIME_UTC', 'emp.SSNO', 'emp.MIDNAME', 'emp.LASTNAME', 'r.READERDESC', 'd.NAME', 'di.NAME as DIVISIONNAME']);
        // $query->leftJoin(['emp' => LenelEmp::tableName()], 'e.EMPID = emp.ID');
        // $query->leftJoin(['ud' => LenelUdfemp::tableName()], 'ud.ID = emp.ID');
        // $query->leftJoin(['d' => LevelDept::tableName()], 'd.ID = ud.DEPT');
        // $query->leftJoin(['di' => LenelDivision::tableName()], 'ud.DIVISION = di.ID');
        // $query->leftJoin(['r' => LenelReader::tableName()], 'e.DEVID = r.READERID and e.MACHINE = r.PANELID');
        // $query->leftJoin(['acl' => LenelAcclvlink::tableName()], 'acl.READERID = r.READERID AND acl.PANELID = r.PANELID');
        // $query->leftJoin(['acs' => LenelAccesslvl::tableName()], 'acl.ACCESSLVID = acs.ACCESSLVID');
        // $query->andWhere(['in', 'e.SERIALNUM', $arr]);
        // $query->orderBy('e.EVENT_TIME_UTC DESC');
        
        $sql =  $query->createCommand()->getRawSql();
        // var_dump($sql);die;
        return $query;
    }

    /**
     * 导出数据
     * @return string
     */
    public function actionHistoryExport()
    {
        
        // set_time_limit(0);
        // ini_set('memory_limit', '256M');
        $request = Yii::$app->request->queryParams;
        $doorArr = $request['readeresc'] = [];
        if(isset($request['select2']) && !empty($request['select2'])){
            $doorArr = $request['readeresc'] = $request['select2'];
        }
        $query      = $this->historyReportSearch($request);
        $queryData  = clone $query;
        $number     =  $queryData->count();
        $data         = [];
        $dataProvider = $query->asArray()->all();
        if(!empty($dataProvider)) {
            foreach($dataProvider as $key => $value) {
                $data[$key][] = date('Y-m-d H:i:s', strtotime($value['EVENT_TIME_UTC'])+28800);
                $data[$key][] = $value['READERDESC'];
                $data[$key][] = $value['SSNO'];
                $data[$key][] = $value['MIDNAME'];
                $idArr = LenelBadge::find()->select([ 'ID','DEACTIVATE' ])->where(['EMPID' => $value['ID']])->asArray()->all();
                if(empty($idArr)){
                    $data[$key][] = '';
                    $data[$key][] = '';
                    $data[$key][] = '';
                    $data[$key][] = '';
                }else{
                    if(isset($idArr[0])){

                        $data[$key][] = $idArr[0]['ID'];
                        $data[$key][] = $idArr[0]['DEACTIVATE'];

                    }else{
                        $data[$key][] = '';
                        $data[$key][] = '';
                    }
                    if(isset($idArr[1])){
                        $data[$key][] = $idArr[1]['ID'];
                        $data[$key][] = $idArr[1]['DEACTIVATE'];
                    }else{
                        $data[$key][] = '';
                        $data[$key][] = '';
                    }
                }
                $data[$key][] = $value['NAME'];
                $data[$key][] = $value['DIVISIONNAME'];
            }
        }
        $fileName = 'HistoryReport_' . date('Y-m-d H:i:s', time()) . '.csv';
        $header   = ['Time', 'Door NO.', 'Work ID/PID', 'Chinese Name',  'CARD NO.1','EBDate NO.1', 'CARD NO.2','EBDate NO.2', 'Department', 'ContactName'];
        array_unshift($data, $header); //添加头部标题
        return $this->json(200, $fileName, $data);
    }


    /**
     * @return string
     */
    public function actionZoneReport()
    {
       echo "数据升级中，暂停使用！";exit;
        $request    = Yii::$app->request->queryParams;
        $doorArr = $request['readeresc'] = [];
        if(isset($request['doorstr']) && !empty($request['doorstr'])){
            $doorArr = $request['readeresc'] = explode(',', $request['doorstr']);
        }
        $query      = $this->zoneReportSearch($request);
        $queryData  = clone $query;
        $pagination = new Pagination(['totalCount' => $queryData->count()]);
        $data       = $query->offset($pagination->offset)
                            ->limit($pagination->limit)
                            ->asArray()
                            ->all();
        return $this->render('zone-report', [
            'doorArr'    => $doorArr,
            'data'       => $data,
            'request'    => $request,
            'totalCount' => $queryData->count(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @param $params
     * @return \yii\db\ActiveQuery
     * @date 2021/7/13 13:39
     */
    public function zoneReportSearch($params)
    {
        $query = LenelAcclvlink::find();
        $query->alias('acl');
        $query->select(['e.ID', 'b.DEACTIVATE', 'r.READERDESC', 'e.SSNO', 'e.MIDNAME', 'e.LASTNAME', 'd.NAME', 'di.NAME as DIVISIONNAME', 'bt.NAME as BADGE_NAME']);
        $query->leftJoin(['r' => LenelReader::tableName()], 'acl.READERID = r.READERID AND acl.PANELID = r.PANELID');
        $query->leftJoin(['acs' => LenelAccesslvl::tableName()], 'acl.ACCESSLVID = acs.ACCESSLVID');
        $query->leftJoin(['bl' => LenelBadgelink::tableName()], 'acs.ACCESSLVID = bl.ACCLVLID');
        $query->leftJoin(['b' => LenelBadge::tableName()], 'bl.BADGEKEY = b.BADGEKEY');
        $query->leftJoin(['e' => LenelEmp::tableName()], 'e.ID = b.EMPID');
        $query->leftJoin(['bt' => LenelBadgeTyp::tableName()], 'b.TYPE = bt.ID');
        $query->leftJoin(['ud' => LenelUdfemp::tableName()], 'ud.ID = e.ID');
        $query->leftJoin(['d' => LevelDept::tableName()], 'd.ID = ud.DEPT');
        $query->leftJoin(['di' => LenelDivision::tableName()], 'ud.DIVISION = di.ID');
        // $query->andFilterWhere(['r.READERDESC' => ArrayHelper::getValue($params, 'readeresc', '')]);
        $query->andFilterWhere(['bt.ID' => ArrayHelper::getValue($params, 'badge_type', '')]);
        $query->andFilterWhere(['in', 'r.READERDESC', ArrayHelper::getValue($params, 'readeresc', [])]);
        $query->andFilterWhere(['acs.DESCRIPT' => ArrayHelper::getValue($params, 'DESCRIPT', '')]);
        $query->andFilterWhere(['e.SSNO' => ArrayHelper::getValue($params, 'ssno', '')]);
        $query->andFilterWhere(['e.MIDNAME' => ArrayHelper::getValue($params, 'midname', '')]);
        $query->andFilterWhere(['d.NAME' => ArrayHelper::getValue($params, 'name', '')]);
        $query->groupBy(['e.ID', 'b.DEACTIVATE', 'r.READERDESC', 'e.SSNO', 'e.MIDNAME', 'e.LASTNAME', 'd.NAME','di.NAME', 'bt.NAME']);
        $sql =  $query->createCommand()->getRawSql();
        var_dump($sql);die;
        return $query;
    }

    /**
     * 导出数据
     * @return string
     * @date 2021/7/13 17:22
     */
    public function actionZoneReportExport()
    {
        $request = Yii::$app->request->queryParams;
        $doorArr = $request['readeresc'] = [];
        if(isset($request['select2']) && !empty($request['select2'])){
            $doorArr = $request['readeresc'] = $request['select2'];
        }
        $query      = $this->zoneReportSearch($request);
        $queryData  = clone $query;                  
        $number     =  $queryData->count();
        // if($number >= 10000) {
        //     return $this->json(100, '最多只能导出1万条数据');
        // }
        $data       = [];
        $dataProvider       = $query->asArray()->all();
        if(!empty($dataProvider)) {
            foreach($dataProvider as $key => $value) {
                $data[$key][] = $value['READERDESC'];
                $data[$key][] = $value['SSNO'];
                $data[$key][] = $value['MIDNAME'];
                $idArr = LenelBadge::find()->select([ 'ID','DEACTIVATE' ])->where(['EMPID' => $value['ID']])->asArray()->all();
                if(empty($idArr)){
                    $data[$key][] = '';
                    $data[$key][] = '';
                    $data[$key][] = '';
                    $data[$key][] = '';
                }else{
                    if(isset($idArr[0])){

                        $data[$key][] = $idArr[0]['ID'];
                        $data[$key][] = $idArr[0]['DEACTIVATE'];

                    }else{
                        $data[$key][] = '';
                        $data[$key][] = '';
                    }
                    if(isset($idArr[1])){
                        $data[$key][] = $idArr[1]['ID'];
                        $data[$key][] = $idArr[1]['DEACTIVATE'];
                    }else{
                        $data[$key][] = '';
                        $data[$key][] = '';
                    }
                }
                $data[$key][] = $value['NAME'];
                $data[$key][] = $value['DIVISIONNAME'];
            }
        }
        $fileName = 'ZoneReport_' .date('Y-m-d H:i:s', time()) . '.xlsx';
        $header   = ['Door NO.', 'Work ID/PID', 'Chinese Name',  'CARD NO.1','EBDate NO.1', 'CARD NO.2','EBDate NO.2', 'Department', 'ContactName'];
        array_unshift($data, $header); //添加头部标题
        return $this->json(200, $fileName, $data);
    }

    /**
     * @return string
     */
    public function actionCardholder()
    {
        // echo "数据升级中，暂停使用！";exit;
        $request    = Yii::$app->request->queryParams;
        $locationid = $request['locationid'] = [];

        if(isset($request['doorstr']) && !empty($request['doorstr'])){
            $locationid = $request['locationid'] =explode(',', $request['doorstr']);;
        }
       
        $query      = $this->cardholderSearch($request);
       
        $queryData  = clone $query;
        $dataProvider = $queryData->asArray()->all();
        $pagination = new Pagination(['totalCount' => count($dataProvider)]);
        $data       = $query->offset($pagination->offset)
                            ->limit($pagination->limit)
                            ->asArray()
                            ->all();
        return $this->render('cardholder', [
            'data'       => $data,
            'request'    => $request,
            'locationid' => $locationid,
            'totalCount' => count($dataProvider),
            'pagination' => $pagination
        ]);
    }

    /**
     * @param $params
     * @return \yii\db\ActiveQuery
     * @date 2021/7/13 13:40
     */
    public function cardholderSearch($params)
    {
        $query = LenelEmp::find();
        $query->alias('e');
        $query->select(['e.SSNO', 'll.NAME as LOCATION', 'e.MIDNAME', 'e.LASTNAME', 'ud.ADDR1', 'e.ID', 'de.NAME as DEPART_NAME', 't.NAME as TITLE_NAME', 'bu.NAME as BUILDING_NAME', 'di.NAME as DIVISION_NAME', 'bd.DEACTIVATE', 'ud.OPHONE', 'bd.TYPE', 'bt.NAME as BADGE_NAME']);
        $query->leftJoin(['ud' => LenelUdfemp::tableName()], 'e.ID = ud.ID');
        $query->leftJoin(['bd' => LenelBadge::tableName()], 'e.ID = bd.EMPID');
        $query->leftJoin(['de' => LevelDept::tableName()], 'ud.DEPT = de.ID');
        $query->leftJoin(['t' => LenelTitle::tableName()], 'ud.TITLE = t.ID');
        $query->leftJoin(['bu' => LenelBuilding::tableName()], 'ud.BUILDING = bu.ID');
        $query->leftJoin(['di' => LenelDivision::tableName()], 'ud.DIVISION = di.ID');
        $query->leftJoin(['bt' => LenelBadgeTyp::tableName()], 'bd.TYPE = bt.ID');
        $query->leftJoin(['ll' => LenelLocation::tableName()], 'ud.LOCATION = ll.ID');
		$query->andFilterWhere(['bt.ID' => ArrayHelper::getValue($params, 'badge_type', '')]);
        $query->andFilterWhere(['in', 'll.ID', ArrayHelper::getValue($params, 'locationid', [])]);
        $query->andFilterWhere(['e.SSNO' => ArrayHelper::getValue($params, 'ssno', '')]);
        $query->andFilterWhere(['e.MIDNAME' => ArrayHelper::getValue($params, 'midname', '')]);
        $query->andFilterWhere(['de.NAME' => ArrayHelper::getValue($params, 'depart_name', '')]);
        return $query;
    }

    /**
     *  echo "<pre/>";var_dump($query->count());die;
     * 导出数据
     * @return string
     * @date 2021/7/13 17:22
     */
    public function actionCardholderExport()
    {
        $request      = Yii::$app->request->queryParams;
        $request['locationid'] = [];

        if(isset($request['select2']) && !empty($request['select2'])){
            $request['locationid'] = $request['select2'];
        }
       
        $query        = $this->cardholderSearch($request);
        $data         = [];
        $dataProvider = $query->asArray()->all();
        if(count($dataProvider) >= 10000) {
            return $this->json(100, '最多只能导出1万条数据');
        }
 
        if(!empty($dataProvider)) {
            foreach($dataProvider as $key => $value) {
                $data[$key][] = isset($value['SSNO']) ? $value['SSNO'] : '';
                $data[$key][] = isset($value['MIDNAME']) ? $value['MIDNAME'] : '';
                $data[$key][] = isset($value['ADDR1']) ? $value['ADDR1'] : '';
                // $data[$key][] = (new LenelEmp())->getCardNoFirst($value['ID'], 1);
                // $data[$key][] = (new LenelEmp())->getCardNoFirst($value['ID'], 2);
                
                // $idArr = LenelBadge::find()->select([ 'ID','DEACTIVATE' ])->where(['EMPID' => $value['ID']])->column();
                $idArr = LenelBadge::find()->select([ 'ID','DEACTIVATE' ])->where(['EMPID' => $value['ID']])->asArray()->all();
                // print_r($idArr);die;
                if(empty($idArr)){
                    $data[$key][] = '';
                    $data[$key][] = '';
                    $data[$key][] = '';
                    $data[$key][] = '';
                }else{
                    if(isset($idArr[0])){

                        $data[$key][] = $idArr[0]['ID'];
                        $data[$key][] = $idArr[0]['DEACTIVATE'];

                    }else{
                        $data[$key][] = '';
                        $data[$key][] = '';
                    }
                    if(isset($idArr[1])){
                        $data[$key][] = $idArr[1]['ID'];
                        $data[$key][] = $idArr[1]['DEACTIVATE'];
                    }else{
                        $data[$key][] = '';
                        $data[$key][] = '';
                    }
                }
                $data[$key][] = isset($value['DEPART_NAME']) ? $value['DEPART_NAME'] : '';
                $data[$key][] = isset($value['LOCATION']) ? $value['LOCATION'] : '';
                $data[$key][] = isset($value['TITLE_NAME']) ? $value['TITLE_NAME'] : '';
                // $data[$key][] = $value['DEACTIVATE'];
                $data[$key][] = isset($value['BUILDING_NAME']) ? $value['BUILDING_NAME'] : '';
                $data[$key][] = isset($value['DIVISION_NAME']) ? $value['DIVISION_NAME'] : '';
                $data[$key][] = isset($value['OPHONE']) ? $value['OPHONE'] : '';
                $data[$key][] = isset($value['BADGE_NAME']) ? $value['BADGE_NAME'] : '';
            }
        }
        $fileName = 'CardholderExport_' . date('Y-m-d H:i:s', time()) . '.xlsx';
        $header   = ['Work ID/PID', 'Middle Name', 'ADDR1', 'CARD NO.1','EBDate NO.1', 'CARD NO.2','EBDate NO.2', 'Department', 'LOCATION','DepName', 'Vendor Name', 'ContactName', 'IDCard', 'BADGE TYPE'];
        array_unshift($data, $header); //添加头部标题
        return $this->json(200, $fileName, $data);
    }

}