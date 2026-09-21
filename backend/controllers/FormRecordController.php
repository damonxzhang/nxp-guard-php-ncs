<?php

namespace backend\controllers;

use backend\models\FileInfo;
use Yii;
use backend\models\FormRecord;
use backend\models\search\FormRecordSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * FormRecordController implements the CRUD actions for FormRecord model.
 */
class FormRecordController extends BaseController
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all FormRecord models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FormRecordSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 预览文件
     * @param $id
     */
    public function actionShowFile($id){
        $file = FileInfo::findOne($id);
        $suffix = $file->getSuffix();
        if($suffix == 'pdf'){
            header('Content-type: application/pdf');
            header('filename='.Yii::$app->urlManager->hostInfo.$file->path);
            readfile(Yii::$app->urlManager->hostInfo.$file->path);
        }else{
            $this->redirect($file->path);
        }
    }

    /**
     * Displays a single FormRecord model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $model->file_id = json_decode($model->file_id,1);
        $fileModel = (new FileInfo())->getFileInfoById($model->file_id);
        return $this->render('view', [
            'model' => $model,
            'file' => $fileModel
        ]);
    }

    /**
     * Creates a new FormRecord model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new FormRecord();

        if ($model->load(Yii::$app->request->post())) {
            if(empty($model->file_id)){
                return $this->json(false,'请上传文件！');
            }
            $model->file_id = json_encode($model->file_id);
            $model->create_user = Yii::$app->user->id;
            if($model->save()){
                return $this->json(true,'保存成功！');
            }else{
                return $this->json(false,$model->errorToString());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing FormRecord model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing FormRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the FormRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FormRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FormRecord::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
