<?php

namespace backend\controllers;

use backend\models\ExteriorPhoto;
use yii\web\NotFoundHttpException;

class PhotoController extends BaseController
{

    /**
     * @param $phone
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPhoto($phone)
    {

        if (empty($phone)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if (\Yii::$app->request->isPost) {
            $file_ids = \Yii::$app->request->post('file_ids');
            if (!empty($file_ids)) {
                \Yii::$app->session->set('photo' . $phone, $file_ids);
            } else {
                return $this->json(100, '请拍照');
            }
            return $this->json(200, '保存成功！');
        }
        return $this->render('photo');
    }

    /**
     * 拍照
     * @param $phone
     * @return string
     */
    public function actionCamera($phone)
    {
        return $this->render('camera');
    }

    /**
     * 拍照
     * @param $phone
     * @return string
     */
    public function actionPicture()
    {
        return $this->render('camera');
    }

    /**
     * @param $id
     * @return string
     */
    public function actionShowPhoto($id)
    {
        $model = new ExteriorPhoto();
        $file  = $model->getImageById($id);
        return "<div style='text-align: center;'><a href='" . $file['path'] . "'  target='_blank'> <img src='" . $file['path'] . "'/></a></div>";
    }

}