<?php
namespace backend\controllers;

use backend\models\search\UserSearch;
use Yii;
use backend\models\User;
use yii\data\Pagination;

class UserController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new UserSearch();
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
    public function actionUpdate()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $uid  = Yii::$app->user->id;
            if ($post['id'] == 1 && $uid != 1) {
                return $this->json(100, 'admin禁止修改!');
            }
            $res = (new User())->edit($post);
            if ($res['status'] != 200) {
                return $this->json(100, $res['msg']);
            }
            return $this->json(200, $res['msg']);
        }
        $id = Yii::$app->request->get('id');
        $user = User::findOne($id);
        return $this->render('update', compact('user'));
    }

}