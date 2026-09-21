<?php
use yii\helpers\Url;
?>
<div class="x-body layui-anim layui-anim-up">
    <form class = "layui-form">
        <div class="layui-col-sm6">
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                <span class = "x-red">*</span>员工姓名:
            </label>
            <div class = "layui-input-inline">
                <input type="hidden" name="id" value="<?= $model['id'] ?>">
                <input type = "text" name = "name" value="<?= $model['name'] ?>"  autocomplete = "off" lay-verify = "required" class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            <span class = "x-red">*</span>手机号:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "phone"  value="<?= $model['phone'] ?>" lay-verify = "required" autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            <span class = "x-red">*</span>身份证:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "idcard"  value="<?= $model['idcard'] ?>" lay-verify = "required"  autocomplete = "off" maxlength="20" class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            <span class = "x-red">*</span>公司名称:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "company"  value="<?= $model['company'] ?>" lay-verify = "required"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
    </div>
        <div class = "layui-form-item">
             <label for = "" class = "layui-form-label"></label>
            <button class = "layui-btn  layui-bg-cyan" lay-filter = "update" lay-submit = "">
                更新
            </button>
        </div>
    </form>
</div>
<?php $this->beginBlock('footer') ?>
<script>
     layui.use(['form', 'layer'], function () {
         $ = layui.jquery;
         var form = layui.form, layer = layui.layer;
         //监听提交
         form.on('submit(update)', function (data) {
             layer.load(3);
             $.post('<?= Url::to(['exterior-user-list/update']) ?>', data.field, function (res) {
                 layer.closeAll();
                 if (res.status === 200) {
                     layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                         var index = parent.layer.getFrameIndex(window.name);
                         parent.layer.close(index);
                         parent.location.reload();
                     })
                 } else {
                     layer.msg(res.msg, {icon: 2, time: 1500})
                 }
             }, 'json');
             return false;
         });
     });

</script>
<?php $this->endBlock() ?>