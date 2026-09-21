<?php

namespace backend\controllers;

use Yii;
use backend\models\ExteriorUser;
use backend\models\search\ExteriorUserSearch;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use backend\models\ExteriorUserRecord;
use yii\helpers\ArrayHelper;
class ExteriorUserListController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorUserSearch();
        $data         = $searchModel->searchList($request);
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
     * 更新人员信息
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isPost) {
            $data           = Yii::$app->request->post();
            $id             = ArrayHelper::getValue($data, 'id');
            $model          = ExteriorUser::findOne($id);
            $model->name    = ArrayHelper::getValue($data, 'name');
            $model->phone   = ArrayHelper::getValue($data, 'phone');
            $model->idcard  = ArrayHelper::getValue($data, 'idcard');
            $model->company = ArrayHelper::getValue($data, 'company');
            if ($model->save(false)) {
                return $this->json(200, '人员更新完成!');
            }
            return $this->json(100, '人员更新失败!');
        }

        $id      = Yii::$app->request->get('id');
        $model   = ExteriorUser::findOne($id);
        return $this->render('update', [
            'model'   => $model
        ]);
    }
}
