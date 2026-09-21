<?php

namespace backend\actions;

use Yii;
use backend\models\FileInfo;
use backend\behaviors\Upload;
use yii\base\Action;
use common\models\GlobalFun;
use yii\helpers\Json;

class UploadAction extends Action
{
    public $type;

    public function run()
    {
        $file  = $_FILES['file'];
        $model = FileInfo::findOne(['name' => $file['name'], 'size' => $file['size']]);
        //图片存在，直接返回
        if (!empty($model) && GlobalFun::file_url_exists($model->path)) {
            $res = [
                'code'    => 200,
                'message' => '上传成功!',
                'path'    => $model->path,
                'size'    => $model->size,
                'id'      => $model->id
            ];
        } else {
            $upload = Yii::createObject([
                'class' => Upload::className(),
                'type'  => $this->type,
            ]);
            //保存图片
            $res = $upload->save();
            if ($res['code'] === 200) {
                $model = new FileInfo();
                if ($model->saveFileInfo($file, $res['path'])) {
                    $res = ['id' => $model->id] + $res;
                } else {
                    $res = ['code' => 200, 'message' => '保存失败！请稍后重试！',];
                }
            }
        }
        return Json::encode($res);
    }

}