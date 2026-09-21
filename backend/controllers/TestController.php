<?php

namespace backend\controllers;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\swiftmailer\Mailer;
/**
 * Site controller
 */
class TestController extends Controller
{
    /**
     * @return string
     */
    public function actionEmail()
    {
          
        $mail = Yii::$app->mailer->compose();
        $mail->setHtmlBody('测试警卫发邮件');
        $mail->setTo(['894698693@qq.com','zhx703@163.com']); //要发送给那个人的邮箱
        $mail->setSubject('测试警卫发邮件'); //邮件主题		
        $mail->send();
       
        return 1;
    }
        /**
     * 发送GET入厂车辆请求
     */
    public function actionEntrance()
    {
     
      $url = 'http://onenxpadmin.ap.freescale.net:9010/lan-ka-car/entrance?plate='. mb_strtoupper('津A123456');
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
}