<?php

namespace backend\controllers;

use backend\models\LenelBadge;
use backend\models\LenelEmp;
use backend\models\search\ExteriorUserPeopleSearch;
use yii\data\Pagination;
use Yii;
use backend\models\ExteriorUserPeople;


class ExteriorUserPeopleController extends BaseController
{
    /**
     * Lists all OneTimeCard models.
     * @return mixed
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new ExteriorUserPeopleSearch();
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
     * 添加卡
     * @return string
     * @date 2021/6/8 20:31
     */
    public function actionCreate()
    {
        if(Yii::$app->request->isPost) {
            $post        = Yii::$app->request->post();
            $model       = new ExteriorUserPeople();
            $res         = $model->create($post['empid'][0]);
            if($res['status'] != 200) {
                return $this->json(100, $res['msg']);
            }
            return $this->json(200, $res['msg']);
        }
        $query = LenelEmp::find();
        $query->alias('emp');
        $query->select([ 'emp.ID','emp.MIDNAME' ,'emp.SSNO']);
        $query->leftJoin(LenelBadge::tableName() . ' lb', 'emp.ID = lb.EMPID');
        $query->andWhere(['in','lb.TYPE', [LenelBadge::TYPE_GUARD,LenelBadge::TYPE_AUTH]]);
        // $query->andWhere(['lb.TYPE' => LenelBadge::TYPE_GUARD]);
        $query->orderBy('lb.TYPE ASC');
        $query->asArray();
        $snapCardArr = $query->all();
        return $this->render('create', ['snapCardArr' => $snapCardArr]);
    }

    /**
     * 删除人员信息
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDel()
    {
        $id = (int)Yii::$app->request->get('id');
        $model = ExteriorUserPeopleSearch::findOne($id);
        if (!$model->delete()) {
            return $this->json(100, '删除失败');
        }
        return $this->json(200, '删除成功');
    }

}

