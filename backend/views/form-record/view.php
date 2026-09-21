<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\FormRecord */

?>
<style>
    .border-hidden{
        border: none;
    }
</style>
<div class="x-body form-record-view">


    <div class="form-record-form">


        <div class="layui-form-item">
            <label for="tel" class="layui-form-label">标题:</label>
            <div class="layui-input-inline" style="width: 800px;">
                <?=Html::input('text','',$model->title,['class' => 'layui-input border-hidden']) ?>
            </div>
        </div>

        <div class="layui-form-item">
            <label for="tel" class="layui-form-label">状态:</label>
            <div class="layui-input-inline" style="width: 800px;">
                <?=Html::input('text','',\backend\models\FormRecord::$selectStatus[$model->status] ,['class' => 'layui-input border-hidden']) ?>

            </div>
        </div>
        <label class="layui-form-label" for="tel">文件:</label>
        <div class="layui-upload" style="margin-left: 110px;">

            <div class="layui-upload-list" style="width: 800px;">
                <table class="layui-table">
                    <thead><tr>
                        <th>文件</th>
                        <th>文件名</th>
                    </tr></thead>
                        <?php
                            if($file):
                                foreach ($file as $val):
                                    $url = \yii\helpers\Url::to(['show-file','id' => $val['id']]);
                        ?>
                        <tr>
                            <td><a href="<?=$url?>"><img src="<?=(new \backend\models\FileInfo())->getPathUrl($val['path']);?>"></a></td>
                            <td><a href="<?=$url?>"><?=$val['name']?></a></td>
                        </tr>

                        <?php endforeach; endif;?>
                </table>
            </div>

        </div>
    </div>


</div>
