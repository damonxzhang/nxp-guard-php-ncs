<?php

namespace backend\controllers;

use backend\models\LenelEmp;
use backend\models\UserCard;
use backend\models\search\UserCardSearch;
use yii\data\Pagination;
use Yii;

class UserCardController extends BaseController
{
  /**
   * Lists all OneTimeCard models.
   * @return mixed
   */
  public function actionIndex()
  {
    $request      = Yii::$app->request->queryParams;
    $searchModel  = new UserCardSearch();
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
      $oneTimeCard = new UserCard();
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
    $userCard = UserCard::findOne($id);
    if(Yii::$app->request->isPost) {
      $post        = Yii::$app->request->post();
      $userCard = new UserCard();
      $res         = $userCard->edit($post);
      if($res['status'] != 200) {
        return $this->json(100, $res['msg']);
      }
      return $this->json(200, $res['msg']);
    }
    $snapCardArr = (new LenelEmp())->getSnapCardArr();
    return $this->render('update', ['userCard' => $userCard,'snapCardArr' => $snapCardArr]);
  }


}
