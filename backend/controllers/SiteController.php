<?php
namespace backend\controllers;

use backend\models\User;
use backend\models\UserInfo;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{

    /**
     * 用户登陆
     * @return string
     */
    public function actionLogin()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $user = User::findOne(['name' => $post['name']]);
            if (!$user) {
                return $this->json(100, '用户不存在!');
            }
            $res = Yii::$app->security->validatePassword($post['password'], $user->password);

            if (!$res) {
                return $this->json(100, '登陆密码不正确!');
            }

            $userInfo = UserInfo::findOne(['user_id' => $user->id]);
            if(empty($userInfo)){
                return $this->json(100, '用户数据丢失，请联系管理员!');
            }

            if ($userInfo->status == UserInfo::STATUS_CLOSE) {
                return $this->json(100, '该用户已被禁用，请联系管理员!');
            }

            Yii::$app->user->login($userInfo);

            return $this->json(200, '登录成功');
        }
        return $this->render('login');
    }

    /**
     * 登出
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['site/login']);
    }

    /**
     * ajax 返回json数据
     * @param $status
     * @param $msg
     * @param string $data
     * @return string
     */
    public function json($status, $msg, $data = '')
    {
        if ($data) {
            return Json::encode(['status' => $status, 'msg' => $msg, 'data' => $data]);
        } else {
            return Json::encode(['status' => $status, 'msg' => $msg]);
        }
    }

}
