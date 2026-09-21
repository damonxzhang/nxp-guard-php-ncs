<?php

namespace backend\controllers;

use Yii;
use backend\models\ExteriorUser;
use backend\models\search\ExteriorUserSearch;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use backend\models\ExteriorUserRecord;

class ExteriorUserController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorUserSearch();
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
     * Displays a single ExteriorUser model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ExteriorUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ExteriorUser();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * 保存用户
     * @return string
     */
    public function actionGetUser(){
        $phone = Yii::$app->request->post('phone');
        if(empty($phone) || !ExteriorUser::validatePhone($phone)){
            return $this->json(false,'请输入正确的手机号');
        }
        $user = ExteriorUser::findOne(['phone' => $phone]);
        if($user){
            $checkUser = ExteriorUserRecord::find()->where(['exterior_user' => $user->id])->andWhere(['status'=>ExteriorUserRecord::STATUS_ENTER])->one();
            if($checkUser) {
                return $this->json(false, '您已申请完进厂,不能重复申请!');
            }
            return $this->json(true,'',$user);
        }else{
            return $this->json(false,'此号码未录入用户信息！');
        }

    }


    /**
     * Updates an existing ExteriorUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return ExteriorUser|null
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = ExteriorUser::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
