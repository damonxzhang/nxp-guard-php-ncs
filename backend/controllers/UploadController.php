<?php

namespace backend\controllers;

use Yii;
use backend\models\FileInfo;
use common\models\GlobalFun;
use yii\helpers\Json;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadController extends BaseController
{

    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file                       = UploadedFile::getInstanceByName('file');
        if (!$file) {
            return ['code' => 100, 'message' => '文件上传失败，请稍后再尝试!'];
        }
        //查询是否存在表中
        $fileModel = FileInfo::findOne(['name' => $file->name, 'size' => $file->size]);
        //检查图片是否存在，路径是否正确
        if (!empty($fileModel) && GlobalFun::file_url_exists($fileModel->path)) {
            return ['code' => 200, 'message' => '上传成功!', 'path' => $fileModel->path, 'id' => $fileModel->id];
        }
        $path     = 'uploads/' . date('Ymd') . '/';
        $fileName = $file->baseName . time() . '.' . $file->extension;
        $filePath = $path . $fileName;
        if (file_exists($filePath)) {
            return ['code' => 100, 'message' => '此文件已经存在啦!'];
        }
        if (!is_dir($path)) {
            mkdir($path, 0777, true);//递归方式创建数组
            chmod($path, 0777);      //添加权限
        }
        $uploadResult = $file->saveAs($filePath);
        if ($uploadResult) {
            $fileModel = new FileInfo();
            $fileModel->setAttributes([
                'name' => $file->name,
                'type' => $file->type,
                'size' => $file->size,
                'path' => '/' . $filePath
            ]);
            $fileModel->save();
            return ['code' => 200, 'message' => '上传成功!', 'path' => '/' . $filePath, 'id' => $fileModel->id];
        }
        return ['code' => 100, 'message' => '文件上传失败，请稍后再尝试!'];
    }

    /**
     * 摄像头拍照
     * @return string
     */
    public function actionCamera()
    {
        $post       = Yii::$app->request->post();
        $imgPicture = str_replace('data:image/png;base64,', '', $post['img_picture']);
        $imgPicture = str_replace(' ', '+', $imgPicture);
        $data       = base64_decode($imgPicture);
        $path       = 'uploads/' . date('Ymd') . '/camera/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            chmod($path, 0777);//添加权限
        }
        $fileName = time() . '.png';
        $filePath = $path . $fileName;
        $fp       = fopen($filePath, 'w');
        fwrite($fp, $data);
        fclose($fp);
        $fileModel = new FileInfo();
        $fileModel->setAttributes([
            'name' => $fileName,
            'type' => '.png',
            'size' => '',
            'path' => '/' . $filePath
        ]);
        if (!$fileModel->save(false)) {
            return $this->json(100, '图片上传失败!');
        }
        return $this->json(200, '拍照完成！', ['file_id' => $fileModel->id, 'img_url' => '/' . $filePath]);
    }

    /**
     * 上传签名
     * @return string
     */
    public function actionSignImage()
    {
        $post       = Yii::$app->request->post();
        $imgPicture = str_replace('data:image/png;base64,', '', $post['file']);
        $imgPicture = str_replace(' ', '+', $imgPicture);
        $data       = base64_decode($imgPicture);
        $path       = 'uploads/' . date('Ymd') . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            chmod($path, 0777);//添加权限
        }
        $fileName = time() . '.png';
        $filePath = $path . $fileName;
        $fp       = fopen($filePath, 'w');
        fwrite($fp, $data);
        fclose($fp);
        $fileModel = new FileInfo();
        $fileModel->setAttributes([
            'name' => $fileName,
            'type' => '.png',
            'size' => '',
            'path' => '/' . $filePath
        ]);
        if (!$fileModel->save(false)) {
            return $this->json(100, '签名上传失败!');
        }
        return $this->json(200, '签名上传成功！', ['id' => $fileModel->id]);
    }

}