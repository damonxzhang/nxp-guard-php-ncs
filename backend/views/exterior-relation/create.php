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
                <input type = "text" name = "user_name"  autocomplete = "off" lay-verify = "required" class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            <span class = "x-red">*</span>经理姓名:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "leader_name" lay-verify = "required" autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                员工PID:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "user_pid"   autocomplete = "off" class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            员工WbiId:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "user_wbi_id"   autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            经理PID:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "leader_pid"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            DEPID:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "depid"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        
         <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            Work Id:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "work_id"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            Badge:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "badge"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        
    </div>
        <div class = "layui-form-item">
             <label for = "" class = "layui-form-label"></label>
            <button class = "layui-btn  layui-bg-cyan" lay-filter = "add" lay-submit = "">
                添加
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
         form.on('submit(add)', function (data) {
             layer.load(3);
             $.post('<?= Url::to(['exterior-relation/create']) ?>', data.field, function (res) {
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