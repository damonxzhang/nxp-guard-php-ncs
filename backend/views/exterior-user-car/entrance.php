<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use backend\models\UserInfo;
use yii\helpers\ArrayHelper;

?>
<div class="x-body layui-anim layui-anim-up">
        <div class="exterior-user-form">
            <div class="layui-form">
                <div class="user-body">
                <strong style="margin-left: 35px;">来访人员 </strong>
                <div class="border-show">
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">姓名:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'MIDNAME') ?>" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">PID:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'SSNO') ?>" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">部门:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'department') ?>" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                         <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">车牌号:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'vehicle') ?>" class="layui-input">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <?php $form = LayuiActiveForm::begin(['options' => ['id' => 'comment-form']]) ?>
                <strong style="margin-left: 35px;">入厂确认 </strong>
            <div class="border-show">
                <input type="hidden" name="id" id="id" value="<?= $model->id ?>">
                <input type="hidden" id="time_card" class="code" autocomplete="off">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">警卫扫卡:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" id="guard-card_name" disabled class="layui-input">
                        <input type="hidden" id="guard-card" name="entrance_guard" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">车辆临时卡:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" id="car-card_name" disabled class="layui-input">
                        <input type="hidden" id="car-card" name="car_card" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item"  style="margin-left: 35px;">
                <?= Html::a('保存', 'javascript:;', ['class' => 'layui-btn layui-btn-lg submit']) ?>
            </div>
        <?php LayuiActiveForm::end(); ?>
        </div>
    </div>
<?php
$js = <<<JS
    var maxType =1;//刷卡次数
    $(document).ready(function() {
        getCard();
    });
     //扫卡
    function getCard() {
        var startTime = $('#time_card').val();
        $.ajax({
            url: 'get-user-card',
            type: 'get',
            data: {startTime: startTime},
            dataType: 'json',
            success: function (data) {
                 $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                    $("#guard-card").val(data.data.guard);
                    $("#guard-card_name").val(data.data.guard_name);
                    $("#car-card").val(data.data.car);
                    $("#car-card_name").val(data.data.car_name);
                }else if(data.status === 409) {
                    layer.confirm(data.msg, {
                        btn: ['确认','取消'] //按钮
                    }, function(indexyes){
                        layer.close(indexyes);
                        $("#time_card").val('');
                        setTimeout(getCard,2*1000);
                    }, function(){
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    });
                   // layer.msg(data.msg, {icon: 2, time: 3000});
                }else {
                     setTimeout(getCard,2*1000);
                }
            }
        });
    }
    $('.submit').click(function(){
        let id = $("input[name='id']").val();
        let entrance_guard = $("input[name='entrance_guard']").val();
        let car_card = $("input[name='car_card']").val();
        $.post('entrance-add',{
            id: id,
            entrance_guard: entrance_guard,
            car_card: car_card
        },function(res) {
            if(res.status == 200){
                layer.msg(res.msg, {icon: 1,time: 1500}, function () { 
                   closeParentOpen(true);
                })
            }else{
                layer.msg(res.msg, {icon: 2, time: 3000});
            }
        },'json') 
    });
   
JS;
$this->registerJs($js);
?>
