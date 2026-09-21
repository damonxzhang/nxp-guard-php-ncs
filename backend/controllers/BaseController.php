<?php

namespace backend\controllers;

use backend\models\Role;
use backend\models\Route;
use backend\models\User;
use common\models\GlobalFun;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\Controller;
use Yii;
use yii\web\UploadedFile;

class BaseController extends Controller
{
    //不验证表单的action
    protected $CsrfValidationNo = [
        'upload'
    ];

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect(['site/login']);
            return false;
        }
        if (in_array($action->id, $this->CsrfValidationNo)) {
            $action->controller->enableCsrfValidation = false;
        }
        $permission    = Role::getUserRolePermission();
        $routes        = Route::find()->where(['in', 'id', $permission])->asArray()->all();
        $permissionArr = ArrayHelper::getColumn($routes, 'route');
        $allowUrl      = ['index/index', 'index/welcome', 'index/clear-cache'];
        if (!in_array($this->route, $allowUrl) && !in_array($this->route, $permissionArr) && RBAC) {
            echo $this->json(100, '抱歉，没有权限');
            return false;
        }
        return parent::beforeAction($action);
    }

    /**
     * ajax 返回json数据
     * @param int $status 状态码
     * @param string $msg 提示信息
     * @param string $data
     * @return string
     */
    public function json($status, $msg, $data = '')
    {
        if ($data) {
            return Json::encode(['status' => $status, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
        } else {
            return Json::encode(['status' => $status, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 返回数组信息
     * @param $status
     * @param $msg
     * @return array
     */
    public function arrData($status, $msg)
    {
        return ['status' => $status, 'msg' => $msg];
    }

    /**
     * CSV文件导出
     * @param $data
     * @param string $name
     */
    public function csvExport($data, $name = '')
    {
        $csvFileName = $name ? $name . '.csv' : date('YmdHis') . rand(111111, 999999) . '.csv';
        header('Content-Type: application/octet-stream');
        header("Accept-Ranges:bytes");
        header('Content-Disposition: attachment; filename="' . $csvFileName . '"');
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        header("Content-Type: application/msexcel");
        header('Expires: Mon,26 Jul 1997 05:00:00 GMT');
        header('Content-Transfer-Encoding: binary');
        echo implode("\r\n", $data);
        exit;
    }

    /**
     * 批量添加数据
     * @param $tableName
     * @param $tableList
     * @param $insertData
     * @return bool
     * @throws \yii\db\Exception
     */
    public function insertData($tableName, $tableList, $insertData)
    {
        if (!is_array($tableList) || !is_array($insertData)) {
            return false;
        }
        $result = Yii::$app->db->createCommand()->batchInsert($tableName, $tableList, $insertData)->execute();
        return $result > 0 ? true : false;
    }

    /**
     * 获取登陆ID
     * @return int|string
     */
    public function getUid()
    {
        $id= Yii::$app->user->getId();
        $userModel = User::findOne($id);
        return $userModel ? $userModel ->user_id : '';
    }

    /**
     * 向用户发送邮件的方法
     * @param $toMember
     * 用户的邮件地址
     * @param $subject
     * 邮件主题
     * @param $body
     * 邮件内容
     * @return bool
     * 发送结果
     */
    public function sendEmail($toMember, $subject, $body)
    {
        //1构造对象
        $emailObj = Yii::$app->mailer->compose();
        $result   = $emailObj->setTo($toMember)//设置收件人的地址
        ->setSubject($subject)//设置邮件主题
        ->setHtmlBody($body)  //设置邮件的内容的html
        ->send();//发送方法
        return $result;
    }

    /**
     *  返回layui数据表格选中状态值
     * @param $id
     * @param $data
     * @return array
     */
    public function setLayuiTableSelect($ids, $data)
    {
        if (is_array($ids) && count($ids) > 0) {
            return array_map(function ($value) use ($ids) {
                if (in_array($value['id'], $ids)) {
                    $value['LAY_CHECKED'] = true;
                }
                return $value;
            }, $data);
        } else {
            return $data;
        }
    }

    /**
     * 将$model->getErrors得到的错误信息转换
     * @param $errors
     * @return string
     */
    public function errorToString($errors)
    {
        if (empty($errors)) {
            return '未定义错误！';
        }
        $errorStr = '';
        foreach ($errors as $value) {
            $errorStr .= "," . implode("\r\n", $value);
        }
        return trim($errorStr, ',');
    }

}
