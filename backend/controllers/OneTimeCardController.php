<?php

namespace backend\controllers;

use backend\models\LenelEmp;
use backend\models\OneTimeCard;
use backend\models\search\OneTimeCardSearch;
use yii\data\Pagination;
use Yii;

class OneTimeCardController extends BaseController
{
  /**
   * Lists all OneTimeCard models.
   * @return mixed
   */
  public function actionIndex()
  {
    $request      = Yii::$app->request->queryParams;
    $searchModel  = new OneTimeCardSearch();
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
   * 添加临时卡
   * @return string
   * @date 2021/6/8 20:31
   */
  public function actionCreate()
  {
    if(Yii::$app->request->isPost) {
      $post        = Yii::$app->request->post();
      $oneTimeCard = new OneTimeCard();
      $res         = $oneTimeCard->create($post);
      if($res['status'] != 200) {
        return $this->json(100, $res['msg']);
      }
      return $this->json(200, $res['msg']);
    }
    $snapCardArr = (new LenelEmp())->getSnapCardArr();
    return $this->render('create', ['snapCardArr' => $snapCardArr]);
  }

  public function actionUpdate()
  {
    $id          = Yii::$app->request->get('id');
    $oneTimeCard = OneTimeCard::findOne($id);
    if(Yii::$app->request->isPost) {
      $post        = Yii::$app->request->post();
      $oneTimeCard = new OneTimeCard();
      $res         = $oneTimeCard->edit($post);
      if($res['status'] != 200) {
        return $this->json(100, $res['msg']);
      }
      return $this->json(200, $res['msg']);
    }
    return $this->render('update', ['oneTimeCard' => $oneTimeCard]);
  }

}
