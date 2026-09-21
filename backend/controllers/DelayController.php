<?php
namespace backend\controllers;

use backend\models\LenelBadge;
use backend\models\ExteriorUserRecord;
use yii\web\Controller;

class DelayController extends Controller
{

    public function actionIndex()
    {
        //查询所有正式进场人员信息
        $query = ExteriorUserRecord::find();
        $query->select(['user_card','real_entrance_time']);
        $query->andWhere(['status' => ExteriorUserRecord::STATUS_ENTER]);
        $query->asArray();
        $userCardArr = $query->all();
        if(!empty($userCardArr)){
            foreach($userCardArr as $value){
                //实际进厂时间加24小时
                $entranceTime = strtotime("+24hour",$value['real_entrance_time']);
                //判断当前时间是否大于进厂时间+24小时
                if(time() > $entranceTime){
                    $badgeIdArr = LenelBadge::find()->select('ID')->where(['EMPID' => $value['user_card']])->column();
                    if(!empty($badgeIdArr)) {
                        //循环获取badge表信息
                        foreach($badgeIdArr as $id){
                            $model    = LenelBadge::findOne(['ID' => $id]);
                            if($model){
                                print_r($model->EMPID.'--');
                                // $model->STATUS = 3;
                                // $model->save(false);
                                $this->addEditCard($id,$value['user_card'],3);//推送数据
                            }
                        }
                    }
                }
            }
        }
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
            'deactivateDate'=> date('Y-m-d H:i:s',strtotime('+2day')),
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
}

