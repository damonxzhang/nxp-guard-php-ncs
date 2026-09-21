<?php

namespace common\models;

use backend\models\Notice;
use backend\models\UserInfo;
use Yii;

class SendEmail
{

    /**
     * 用户批量发邮件
     * @param array $param
     * @return bool
     */
    public function sendEmailUserList($param = [])
    {
        $sendUserEmailList = (new UserInfo())->getUserEmail($param['user_list']);
        if (!$sendUserEmailList) {
            return false;
        }
        $result = $this->sendBatchEmail($sendUserEmailList, $param);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * 批量发送邮件
     * @param $userEmail
     * 用户邮箱数组
     * @param $param
     * 邮件发送类型
     * @return bool
     * 发送结果
     */
    public function sendBatchEmail($userEmail, $param)
    {
        $messages = [];
        foreach ($userEmail as $value) {
            if (empty($value['email'])) continue;
            $data       = $this->getData($param, $value['chinese_name']);
            $messages[] = Yii::$app->mailer->compose()
                ->setTo($value['email'])//设置收件人的地址
                ->setSubject($data['subject'])//设置邮件主题
                ->setHtmlBody($data['body']);//设置邮件的内容的html
        }
        return Yii::$app->mailer->sendMultiple($messages);
    }

    /**
     * 获取邮件主题及内容
     * @param $param
     * @param $name  //邮件接收人
     * @return array
     */
    public function getData($param, $name)
    {
        $result = ['subject' => '', 'body' => ''];
        if ($param['type'] == Notice::TYPE_TRAIN) { //培训
            $result['subject'] = 'You have a new Training delivery message';
            $result['body']    = $this->getTrainStr($name);
        } elseif ($param['type'] == Notice::TYPE_APPROVAL) {//审批
            $result['subject'] = 'You have a new Approval delivery message';
            $result['body']    = $this->getApprovalStr($name);
        } elseif ($param['type'] == Notice::TYPE_BLOG) {//日志
            $result['subject'] = 'You have a new log delivery message';
            $result['body']    = $this->getLogStr($param['message'], $name);
        }
        return $result;
    }

    /**
     * 获取日志的发送内容
     * @param $name  //接收人名字
     * @param $message  //消息内容
     * @return string
     */
    public function getLogStr($message = '', $name = '')
    {
        $urlLink = Yii::$app->request->getHostInfo();
        $body    = '
            <div class="verify-email">
                <p>Hello : ' . $name . ' </p> <br/>
                <p>' . $message . ' </p> <br/> <br/>
                <a href="' . $urlLink . '" > ' . $urlLink . '</a> 
            </div>';
        return $body;
    }

    /**
     * 获取培训的发送内容
     * @param $name  //接收人名字
     * @return string
     */
    public function getTrainStr($name = '')
    {
        $urlLink = Yii::$app->request->getHostInfo(); // 跳转链接地址
        $body    = '
            <div class="verify-email">
                <p>Hello : ' . $name . ' </p>
                <p>You have new training, please check the website.</p>
                <a href="' . $urlLink . '" > ' . $urlLink . '</a> 
            </div>';
        return $body;
    }

    /**
     * 获取审批的发送内容
     * @param $name //接收人名字
     * @return string
     */
    public function getApprovalStr($name = '')
    {
        $urlLink = Yii::$app->request->getHostInfo(); // 跳转链接地址
        $body    = '
            <div class="verify-email">
                <p>Hello : ' . $name . ' </p>
                <p>You have new approval, please visit the website to check.</p>
                <a href="' . $urlLink . '" > ' . $urlLink . '</a> 
            </div>';
        return $body;
    }

    /**
     * 批量发送邮件
     */
    public function sendEmails($userEmail, $param)
    {
        $messages = [];
        foreach ($userEmail as $value) {
            if (empty($value['email'])) continue;
            $messages[] = Yii::$app->mailer->compose()
                ->setTo($value['email'])//设置收件人的地址
                ->setSubject($param['subject'])//设置邮件主题
                ->setHtmlBody($param['body']);//设置邮件的内容的html
        }
        return Yii::$app->mailer->sendMultiple($messages);
    }


}

