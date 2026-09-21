<?php

use yii\helpers\Url;

?>
<div class="x-body layui-anim layui-anim-up">
    <form class="layui-form">
        <div class="layui-form-item">
            <label for="icon" class="layui-form-label" style="font-weight:bold;color: #b1032b">
                <span class="x-red">*</span> 请输入PID:
            </label>
            <div class="layui-input-inline" style="width: 300px;">
                <input type="text" style="border:0.5px solid #378888" name="pid" required autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="icon" class="layui-form-label" style="font-weight:bold;color: #b1032b">
                <span class="x-red">*</span> 请输入手机号:
            </label>
            <div class="layui-input-inline" style="width: 300px;">
                <input type="text" style="border:0.5px solid #378888" name="phone" maxlength="11" required autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="icon" class="layui-form-label" style="font-weight:bold;">
                请输入车牌号:
            </label>
            <div class="layui-input-inline" style="width: 300px;">
                <input type="text" style="border:0.5px solid #378888" name="car" maxlength="11" required autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="name" class="layui-form-label" style="font-weight:bold">
                姓名:
            </label>
            <div class="layui-input-inline" style="width: 300px;">
                <input type="text" id="user_name" style="border:0.5px solid #378888" disabled autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="icon" class="layui-form-label" style="font-weight:bold">
                 部门:
            </label>
            <div class="layui-input-inline" style="width: 300px;">
                <input type="text" id="department_number" style="border:0.5px solid #378888" disabled autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">
            </label>
            <input type="hidden" id="exterior_user_id" name="exterior_user_id" class="layui-input" autocomplete="off">
            <input type="hidden" id="exterior_user_record_id" name="exterior_user_record_id" class="layui-input" autocomplete="off">
            <button class="layui-btn search" id = 'search_pid'>查询</button>
            <button class="layui-btn submit" id = 'submit_pid' style="display: none">确定</button>
        </div>
    </form>
</div>

<?php $this->beginBlock('footer') ?>
<?php
$js = <<<JS

   $('.search').click(function(){
        let pid  = $("input[name='pid']").val();
        let phone  = $("input[name='phone']").val();
        let car  = $("input[name='car']").val();
        if(pid && phone){
           $.post('personnel-pid',{pid: pid,phone: phone,car: car},function(res) {
                if(res.status == 200){
                    layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                        $("#exterior_user_id").val(res.data.exterior_user_id);
                        $("#exterior_user_record_id").val(res.data.exterior_user_record_id);
                        $("#user_name").val(res.data.user_name);
                        $("#department_number").val(res.data.department_number);
                        $("#search_pid").attr("style", "display:none;");//隐藏div
                        $("#submit_pid").attr("style", "display:block;");//显示div
                    })
                }else{
                    layer.msg(res.msg, {icon: 2, time: 3000});
                }
            },'json');
            return false;
        }else{
            layer.msg('请输入PID!和手机号', {icon: 2,time: 3000});
        }
    });

    $('.submit').click(function(){
        let id        = $("input[name='exterior_user_id']").val();
        let record_id        = $("input[name='exterior_user_record_id']").val();
        let car        = $("input[name='car']").val();
        if(id){
            $.post('personnel-add',{
                id: id,record_id:record_id,car:car
            },function(res) {
                if(res.status == 200){
                    layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.location.reload();
                    })
                }else{
                    layer.msg(res.msg, {icon: 2, time: 3000});
                    return false;
                }
            },'json');
            return false; 
        }else{
            layer.msg('请先输入PID查询个人信息!', {icon: 2,time: 3000});
        }
    });
JS;
$this->registerJs($js);
?>
<?php $this->endBlock() ?>

