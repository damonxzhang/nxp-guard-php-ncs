<?php

namespace backend\controllers;

use backend\models\LocalAccesslvl;
use backend\models\LocalAcclvlink;
use backend\models\LocalBadge;
use backend\models\LocalBadgelink;
use backend\models\LocalBadgeTyp;
use backend\models\LocalBuilding;
use backend\models\LocalDivision;
use backend\models\LocalEmp;
use backend\models\LocalEvents;
use backend\models\LocalReader;
use backend\models\LocalTitle;
use backend\models\LocalUdfemp;
use backend\models\LocalDept;
use backend\models\LocalLocation;
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
        

        $request    = Yii::$app->request->queryParams;
        $doorArr = $request['readeresc'] = [];
        if(isset($request['doorstr']) && !empty($request['doorstr'])){
            $doorArr = $request['readeresc'] = explode(',', $request['doorstr']);
        }
        $query      = $this->historyReportSearch($request);
        $queryData  = clone $query;
        $pagination = new Pagination(['totalCount' => $queryData->count()]);
        $data       = $query->offset($pagination->offset)
                            ->limit($pagination->limit)
                            ->asArray()
                            ->all();
                         
        return $this->render('history-report', [
            'doorArr'    => $doorArr,
            'query'      => $query,
            'data'       => $data,
            'request'    => $request,
            'totalCount' => $queryData->count(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @param $params
     * @return \yii\db\ActiveQuery
     * @date 2021/7/13 13:37
     */
    public function historyReportSearch($params)
    {
        $query = LocalEvents::find();
        $query->alias('e');
        $query->select(['emp.ID', 'e.EVENT_TIME_UTC', 'emp.SSNO', 'emp.MIDNAME', 'emp.LASTNAME', 'r.READERDESC', 'd.NAME', 'di.NAME as DIVISIONNAME']);
        $query->leftJoin(['emp' => LocalEmp::tableName()], 'e.EMPID = emp.ID');
        $query->leftJoin(['ud' => LocalUdfemp::tableName()], 'ud.ID = emp.ID');
        $query->leftJoin(['d' => LocalDept::tableName()], 'd.ID = ud.DEPT');
        $query->leftJoin(['di' => LocalDivision::tableName()], 'ud.DIVISION = di.ID');
        $query->leftJoin(['r' => LocalReader::tableName()], 'e.DEVID = r.READERID and e.MACHINE = r.PANELID');
        $query->leftJoin(['acl' => LocalAcclvlink::tableName()], 'acl.READERID = r.READERID AND acl.PANELID = r.PANELID');
        $query->leftJoin(['acs' => LocalAccesslvl::tableName()], 'acl.ACCESSLVID = acs.ACCESSLVID');
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
       
        $query->groupBy(['emp.ID','e.EVENT_TIME_UTC', 'emp.SSNO', 'emp.MIDNAME', 'emp.LASTNAME', 'r.READERDESC', 'd.NAME', 'di.NAME']);
    
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
                $idArr = LocalBadge::find()->select([ 'ID','DEACTIVATE' ])->where(['EMPID' => $value['ID']])->asArray()->all();
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
        $fileName = 'HistoryReport_' . date('Y-m-d H:i:s', time()) . '.xlsx';
        $header   = ['Time', 'Door NO.', 'Work ID/PID', 'Chinese Name',  'CARD NO.1','EBDate NO.1', 'CARD NO.2','EBDate NO.2', 'Department', 'ContactName'];
        array_unshift($data, $header); //添加头部标题
        return $this->json(200, $fileName, $data);
    }


    /**
     * @return string
     */
    public function actionZoneReport()
    {
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
        $query = LocalAcclvlink::find();
        $query->alias('acl');
        $query->select(['e.ID', 'b.DEACTIVATE', 'r.READERDESC', 'e.SSNO', 'e.MIDNAME', 'e.LASTNAME', 'd.NAME', 'di.NAME as DIVISIONNAME', 'bt.NAME as BADGE_NAME']);
        $query->leftJoin(['r' => LocalReader::tableName()], 'acl.READERID = r.READERID AND acl.PANELID = r.PANELID');
        $query->leftJoin(['acs' => LocalAccesslvl::tableName()], 'acl.ACCESSLVID = acs.ACCESSLVID');
        $query->leftJoin(['bl' => LocalBadgelink::tableName()], 'acs.ACCESSLVID = bl.ACCLVLID');
        $query->leftJoin(['b' => LocalBadge::tableName()], 'bl.BADGEKEY = b.BADGEKEY');
        $query->leftJoin(['e' => LocalEmp::tableName()], 'e.ID = b.EMPID');
        $query->leftJoin(['bt' => LocalBadgeTyp::tableName()], 'b.TYPE = bt.ID');
        $query->leftJoin(['ud' => LocalUdfemp::tableName()], 'ud.ID = e.ID');
        $query->leftJoin(['d' => LocalDept::tableName()], 'd.ID = ud.DEPT');
        $query->leftJoin(['di' => LocalDivision::tableName()], 'ud.DIVISION = di.ID');
        // $query->andFilterWhere(['r.READERDESC' => ArrayHelper::getValue($params, 'readeresc', '')]);
        $query->andFilterWhere(['bt.ID' => ArrayHelper::getValue($params, 'badge_type', '')]);
        $query->andFilterWhere(['in', 'r.READERDESC', ArrayHelper::getValue($params, 'readeresc', [])]);
        $query->andFilterWhere(['acs.DESCRIPT' => ArrayHelper::getValue($params, 'DESCRIPT', '')]);
        $query->andFilterWhere(['e.SSNO' => ArrayHelper::getValue($params, 'ssno', '')]);
        $query->andFilterWhere(['e.MIDNAME' => ArrayHelper::getValue($params, 'midname', '')]);
        $query->andFilterWhere(['d.NAME' => ArrayHelper::getValue($params, 'name', '')]);
        $query->groupBy(['e.ID', 'b.DEACTIVATE', 'r.READERDESC', 'e.SSNO', 'e.MIDNAME', 'e.LASTNAME', 'd.NAME','di.NAME', 'bt.NAME']);
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
                $idArr = LocalBadge::find()->select([ 'ID','DEACTIVATE' ])->where(['EMPID' => $value['ID']])->asArray()->all();
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
        $request    = Yii::$app->request->queryParams;
        $locationid = $request['locationid'] = [];

        if(isset($request['doorstr']) && !empty($request['doorstr'])){
            $locationid = $request['locationid'] =explode(',', $request['doorstr']);;
        }
        // print_r($request);
        $query      = $this->cardholderSearch($request);

        $queryData  = clone $query;
        $pagination = new Pagination(['totalCount' =>  $queryData->count()]);
        $data       = $query->offset($pagination->offset)
                            ->limit($pagination->limit)
                            ->asArray()
                            ->all();
        return $this->render('cardholder', [
            'data'       => $data,
            'request'    => $request,
            'locationid' => $locationid,
            'totalCount' => $queryData->count(),
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
        $query = LocalEmp::find();
        $query->alias('e');
        $query->select(['e.SSNO', 'll.NAME as LOCATION', 'e.MIDNAME', 'e.LASTNAME', 'ud.ADDR1', 'e.ID', 'de.NAME as DEPART_NAME', 't.NAME as TITLE_NAME', 'bu.NAME as BUILDING_NAME', 'di.NAME as DIVISION_NAME', 'bd.DEACTIVATE', 'ud.OPHONE', 'bd.TYPE', 'bt.NAME as BADGE_NAME']);
        $query->leftJoin(['ud' => LocalUdfemp::tableName()], 'e.ID = ud.ID');
        $query->leftJoin(['bd' => LocalBadge::tableName()], 'e.ID = bd.EMPID');
        $query->leftJoin(['de' => LocalDept::tableName()], 'ud.DEPT = de.ID');
        $query->leftJoin(['t' => LocalTitle::tableName()], 'ud.TITLE = t.ID');
        $query->leftJoin(['bu' => LocalBuilding::tableName()], 'ud.BUILDING = bu.ID');
        $query->leftJoin(['di' => LocalDivision::tableName()], 'ud.DIVISION = di.ID');
        $query->leftJoin(['bt' => LocalBadgeTyp::tableName()], 'bd.TYPE = bt.ID');
        $query->leftJoin(['ll' => LocalLocation::tableName()], 'ud.LOCATION = ll.ID');
		$query->andFilterWhere(['bt.ID' => ArrayHelper::getValue($params, 'badge_type', '')]);
		// $query->andFilterWhere(['ll.ID' => ArrayHelper::getValue($params, 'location', '')]);
        $query->andFilterWhere(['in', 'll.ID', ArrayHelper::getValue($params, 'locationid', [])]);
        $query->andFilterWhere(['e.SSNO' => ArrayHelper::getValue($params, 'ssno', '')]);
        $query->andFilterWhere(['e.MIDNAME' => ArrayHelper::getValue($params, 'midname', '')]);
        $query->andFilterWhere(['de.NAME' => ArrayHelper::getValue($params, 'depart_name', '')]);
        return $query;
    }

    /**
     * 导出数据
     * @return string
     * @date 2021/7/13 17:22
     */
    public function actionCardholderExport()
    {
        $request      = Yii::$app->request->queryParams;
        $locationid = $request['locationid'] = [];

        if(isset($request['select2']) && !empty($request['select2'])){
            $locationid = $request['locationid'] =$request['select2'];
        }
        $query        = $this->cardholderSearch($request);
        $data         = [];
        $dataProvider = $query->asArray()->all();
        if(count($dataProvider) >= 10000) {
            return $this->json(100, '最多只能导出1万条数据');
        }
        if(!empty($dataProvider)) {
            foreach($dataProvider as $key => $value) {
                $data[$key][] = $value['SSNO'];
                $data[$key][] = $value['MIDNAME'];
                $data[$key][] = $value['ADDR1'];
                // $data[$key][] = (new LocalEmp())->getCardNoFirst($value['ID'], 1);
                // $data[$key][] = (new LocalEmp())->getCardNoFirst($value['ID'], 2);
                
                // $idArr = LocalBadge::find()->select([ 'ID','DEACTIVATE' ])->where(['EMPID' => $value['ID']])->column();
                $idArr = LocalBadge::find()->select([ 'ID','DEACTIVATE' ])->where(['EMPID' => $value['ID']])->asArray()->all();
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
                $data[$key][] = $value['DEPART_NAME'];
                $data[$key][] = $value['LOCATION'];
                $data[$key][] = $value['TITLE_NAME'];
                // $data[$key][] = $value['DEACTIVATE'];
                $data[$key][] = $value['BUILDING_NAME'];
                $data[$key][] = $value['DIVISION_NAME'];
                $data[$key][] = $value['OPHONE'];
                $data[$key][] = $value['BADGE_NAME'];
            }
        }
        $fileName = 'CardholderExport_' . date('Y-m-d H:i:s', time()) . '.xlsx';
        $header   = ['Work ID/PID', 'Middle Name', 'ADDR1', 'CARD NO.1','EBDate NO.1', 'CARD NO.2','EBDate NO.2', 'Department', 'LOCATION','DepName', 'Vendor Name', 'ContactName', 'IDCard', 'BADGE TYPE'];
        array_unshift($data, $header); //添加头部标题
        return $this->json(200, $fileName, $data);
    }

}