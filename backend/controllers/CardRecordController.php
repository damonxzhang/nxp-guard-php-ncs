<?php

namespace backend\controllers;

use app\models\UserRole;
use Yii;
use backend\models\search\CardRecordSearch;
use backend\models\search\ExteriorUserCarSearch;
use yii\data\Pagination;
use backend\models\ExteriorPhoto;
use backend\models\LevelDept;
use backend\models\ExteriorUserRecord;
use backend\models\ExteriorUserCar;
use backend\models\LenelEmp;
use backend\models\UserInfo;

class CardRecordController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new CardRecordSearch();
        $data         = $searchModel->search($request);
        $totalCount   = $data->totalCount;
        $dataProvider = $data->getModels();
        $pagination   = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => 20,
        ]);
        
        return $this->render('index', [
            'request'      => $request,
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'pagination'   => $pagination
        ]);
    }

    /**
     * @return string
     */
    public function actionDelay()
    {
        $searchModel  = new CardRecordSearch();
        $data         = $searchModel->searchDelay();
        $totalCount   = $data->totalCount;
        $dataProvider = $data->getModels();
        $pagination   = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => 20,
        ]);
      
        return $this->render('delay', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'pagination'   => $pagination
        ]);
    }

    /**
     * @return string
     */
    public function actionCarDelay()
    {
        $searchModel  = new ExteriorUserCarSearch();
        $data         = $searchModel->searchDelay();
        $totalCount   = $data->totalCount;
        $dataProvider = $data->getModels();
        // print_r($dataProvider);die;
        $pagination   = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => 20,
        ]);
      
        return $this->render('cardelay', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'pagination'   => $pagination
        ]);
    }

     /**
     * 详情
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = ExteriorUserRecord::findOne($id);
        //接待人
        $lenelEmp = LenelEmp::find()->where(['ID' => $model->reception_user])->asArray()->one();
        $department = (new LevelDept())->departmentByEmpId($model->reception_user);
        if ($department) {
            $lenelEmp['department'] = $department;
        }
        //照片
        $files = ExteriorPhoto::findOne(['record_id' => $model->id]);
        if ($files) {
            $files = $files->setFilePath();
        }
        $role = 0;
        $id= Yii::$app->user->getId();
        $userInfo = UserInfo::findOne($id);
        if ($userInfo) {
            # code...
            $getRole = UserRole::find();
            $getRole->andwhere(['user_id'=>$id]);
            $getRole->andwhere(['in', 'role_id', [1,20,21,22]]);
            $getRoleData = $getRole->asArray()->all();
            if ($getRoleData) {
                # code...
                $role = 1;
            }
        }
        return $this->render('view', [
            'model' => $model, 'receptionUser' => $lenelEmp, 'files' => $files, 'role' => $role
        ]);
    }

    /**
    * 处理
    * @param $id
    * @return string
    * @throws NotFoundHttpException
    */
   public function actionDone()
   {
    $isPost = Yii::$app->request->isPost;
    if(!$isPost) {
        return $this->render('personnel-pid');
    }
    $post  = Yii::$app->request->post();
       $model = ExteriorUserRecord::findOne($post['id']);
       if(!$model) {
           print_r($model);die;
        return $this->json(100, '数据不完整！');
        }
        $model->delay_result = 2;
        $model->status = ExteriorUserRecord::STATUS_GO_OUT;
        if(!$model->save(false)) {
            return $this->json(100, '保存失败!');
        }
        return $this->json(200, '保存成功!');

   }
   /**
   * 详情
   * @param $id
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionCarview($id)
  {
      $model = ExteriorUserCar::findOne($id);
      //接待人
      $lenelEmp = LenelEmp::find()->where(['ID' => $model->create_user])->asArray()->one();
      $lenelEmpCar = LenelEmp::find()->where(['ID' => $model->car_card])->asArray()->one();
      $department = (new LevelDept())->departmentByEmpId($model->create_user);
      if ($department) {
          $lenelEmp['department'] = $department;
      }
      $role = 0;
      $id= Yii::$app->user->getId();
      $userInfo = UserInfo::findOne($id);
      if ($userInfo) {
          # code...
          $getRole = UserRole::find();
          $getRole->andwhere(['user_id'=>$id]);
          $getRole->andwhere(['in', 'role_id', [1,20,21,22]]);
          $getRoleData = $getRole->asArray()->all();
          if ($getRoleData) {
              # code...
              $role = 1;
          }
      }
    //   print_r($lenelEmpCar);die;
      return $this->render('carview', [
          'model' => $model, 'receptionUser' => $lenelEmp, 'carname' => $lenelEmpCar, 'role' => $role
      ]);
  }

  
    /**
    * 处理
    * @param $id
    * @return string
    * @throws NotFoundHttpException
    */
    public function actionCardone()
    {
     $isPost = Yii::$app->request->isPost;
     if(!$isPost) {
         return $this->render('personnel-pid');
     }
     $post  = Yii::$app->request->post();
    //  print_r($post);die;
        $model = ExteriorUserCar::findOne($post['id']);
        if(!$model) {
            print_r($model);die;
         return $this->json(100, '数据不完整！');
         }
        //  $model->delay_result = 2;
         $model->status = 4;
         $model->remark = $post['remark'];
         if(!$model->save(false)) {
             return $this->json(100, '保存失败!');
         }
         return $this->json(200, '保存成功!');
 
    }

}
