<?php
namespace backend\controllers;

use backend\models\Role;
use backend\models\search\RoleSearch;
use backend\models\Submenu;
use backend\models\UserInfo;
use Yii;
use yii\data\Pagination;

class RoleController extends BaseController
{
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new RoleSearch();
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
     */
    public function actionCreate()
    {
        $submenus = Submenu::find()->joinWith('route')
            ->where(['is_show' => 1])->orderBy('sort desc')
            ->asArray()->all();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if (!isset($post['ids'])) {
                return $this->json(100, '拥有权限不能为空');
            }
            $post['ids'] = array_values($post['ids']);
            foreach ($post['ids'] as &$val) {
                $val = (int)$val;
            }
            $model = new Role();
            $res = $model->create($post);
            if ($res['status'] != 200) {
                return $this->json(100, $res['msg']);
            }
            return $this->json(200, $res['msg']);
        }
        return $this->render('create', compact('submenus'));
    }

    /**
     * @return string
     */
    public function actionUpdate()
    {
        $id = (int)Yii::$app->request->get('id');
        $role = Role::findOne($id);
        $submenus = Submenu::find()->joinWith('route')
            ->where(['is_show' => 1])->orderBy(['sort'=>SORT_DESC, 'id'=>SORT_DESC])
            ->asArray()->all();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if (!isset($post['ids'])) {
                return $this->json(100, '拥有权限不能为空');
            }
            $post['ids'] = array_values($post['ids']);
            foreach ($post['ids'] as &$val) {
                $val = (int)$val;
            }
            $model = new Role();
            $res = $model->edit($post);
            if ($res['status'] != 200) {
                return $this->json(100, $res['msg']);
            }

            if(RBAC_UPDATE){

                Yii::$app->cache->set('menu',null);
            }
            return $this->json(200, $res['msg']);
        }
        return $this->render('update', compact('submenus', 'role'));
    }

    /**
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDel()
    {
        $id = (int)Yii::$app->request->get('id');
        if ($id == 1) {
            return $this->json(100, '超级管理员不能删除');
        }
        $user = UserInfo::findOne($this->getUid());
        if ($id == $user->role_id) {
            return $this->json(100, '不能删除自身所属的角色');
        }
        $model = UserInfo::findOne(['role_id' => $id]);
        if ($model) {
            return $this->json(100, '该角色下含有用户，不能删除');
        }
        $model = Role::findOne($id);
        $res = $model->delete();
        if (!$res) {
            return $this->json(100, '删除失败');
        }
        return $this->json(200, '删除成功');
    }
}