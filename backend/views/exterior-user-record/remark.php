<?php

use backend\models\UserInfo;
use yii\helpers\Url;
use backend\models\Blog;

?>
<style>
    </style>
    <div class="x-body layui-anim layui-anim-up">
        <form class="layui-form">
            <input type="hidden" name ="id" value="<?= $id ?>" >
            <div class="layui-form-item">
                <label for="nickname" class="layui-form-label">
                    异常状态:
                </label>
                <div class="layui-input-inline" style="width: 600px;">
                    <input type="text" value="卡片丢失" readonly="readonly" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label for="desc" class="layui-form-label">
                    <span class="x-red">*</span>异常说明:
                </label>
                <div class="layui-input-block">
                    <textarea placeholder="请输入异常说明" lay-verify="required" name="remark" class="layui-textarea" style="width: 600px;height: 200px;"></textarea>
                </div>
            </div>
            <div class="layui-form-item"  style="margin-top: 20px;">
                <label for="" class="layui-form-label"> </label>
                <button class="layui-btn" lay-filter="add" lay-submit="">
                    保存
                </button>
            </div>
        </form>
    </div>

<?php
$createUrl = Url::to(["exterior-user-record/remark"]);
$js = <<<JS
    layui.use(['form','layer'], function(){
        $ = layui.jquery;
        var form = layui.form ,layer = layui.layer;
        //监听提交
        form.on('submit(add)', function(data){
            $.post('{$createUrl}', data.field, function (res) {
                layer.closeAll();
                if (res.status === 200) {
                    layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.location.reload();
                    })
                } else {
                    layer.msg(res.msg, {icon: 2, time: 3000})
                }
            }, 'json');
            return false;
        });
    }); 
JS;
$this->registerJs($js);
?>
