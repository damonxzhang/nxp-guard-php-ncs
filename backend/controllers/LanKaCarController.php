<?php
namespace backend\controllers;

use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use backend\models\LanKaCar;
class LanKaCarController extends Controller
{

    public $enableCsrfValidation = false;
    /**
     * 心跳包
     */
    public function actionHeartbeat()
    {
        if (Yii::$app->request->isPost) {
            $headers = Yii::$app->request->headers;
            $request = Yii::$app->request->post ();
            $model                 = new LanKaCar();
            $model->test           = Json::encode($request);
            $model->create_time    = time();
            $model->create_times   = date('Y-m-d H:i:s',time());
            $model->save(false);
            $data = ['status' => 'success', 'datas' => ['timeStamp' => time()]];
            return Json::encode($data);
        }else{
            $data = ['status' => 'fail', 'errorCode' => '请求方式不正确'];
            return Json::encode($data);  
        }
       
    }
     /**
     * 入厂登记
     * @return false
     */
    public function actionEntrance()
    {
        $plate     = Yii::$app->request->get('plate', '');
        $starttime = date('Y-m-d H:i:s',time());
        $endtime   = date('Y-m-d H:i:s',strtotime('+24 hours'));
        $dataArray = array(
            'plate'           => $plate,
            'start'           => $starttime,
            'end'             => $endtime,
            'areaId'          => '0',
            'flag'            => '1',
            'charge'          => '0',
            'bookOrderId'     => '',
            'bookOrderTime'   => $starttime,
            'letInStartTime'  => $starttime,
            'letInEndTime'    => $endtime,
            'memo'            => '车辆入厂',
            'isFree'          => 1,
            'isAllowManyTimes'=> 1,
            'areasAndValidity'=> []
        );
        $postData = [
            'parkNumber'  => 'p220206171700',
            'datas' => [$dataArray]
        ];
        $url      = 'http://10.192.147.27:9001/bcopenapi/out/synBookingCar';
        $result   = $this->sendPost($url, json_encode($postData));
        if(!$result) return false;
        return $result;
    }

    /**
     * 出厂登记
     * @return false
     */
    public function actionDeparture()
    {
        $plate     = Yii::$app->request->get('plate', '');
        $starttime = date('Y-m-d H:i:s',time());
        $endtime   = date('Y-m-d H:i:s',strtotime('+24 hours'));
        $dataArray = array(
            // 'plate'           => 'Aqm586',
            'plate'           => $plate,
            'start'           => $starttime,
            'end'             => $endtime,
            'areaId'          => '0',
            'flag'            => '0',
            'charge'          => '0',
            'bookOrderId'     => '',
            'bookOrderTime'   => $starttime,
            'letInStartTime'  => $starttime,
            'letInEndTime'    => $endtime,
            'memo'            => '车辆出厂',
            'isFree'          => 1,
            'isAllowManyTimes'=> 1,
            'areasAndValidity'=> []
        );
        $postData = [
            'parkNumber'  => 'p220206171700',
            'datas' => [$dataArray]
        ];
        $url      = 'http://10.192.147.27:9001/bcopenapi/out/synBookingCar';
        $result   = $this->sendPost($url, json_encode($postData));
        if(!$result) return false;
        return true;
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
        $sign = '4587671ed9b546ba617418bb4459782fp220206171700';
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'sign: '.strtolower(md5($sign))
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

