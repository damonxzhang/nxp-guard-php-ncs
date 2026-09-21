<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use backend\models\UserInfo;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */
/* @var $form yii\widgets\ActiveForm */

?>
    <div class="x-body layui-anim layui-anim-up">
        <div class="exterior-user-form">
            <div class="layui-form">
                <div class="user-body">
                    <strong style="margin-left: 35px;">来访人员 </strong>
                    <div class="border-show">
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">中文名:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text', '', $model->exteriorUser->name, [ 'class' => 'layui-input name', 'disabled' => true ]) ?>
                            </div>
                            <label for="tel" class="layui-form-label"> 手机号码:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text', '', $model->exteriorUser->phone, [ 'class' => 'layui-input', 'disabled' => true ]) ?>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">公司名称:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text', '', $model->exteriorUser->company, [ 'class' => 'layui-input company', 'disabled' => true ]) ?>
                            </div>
                            <label for="tel" class="layui-form-label">预计出厂时间:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text', '', !empty($model->get_out_time) ? date('Y-m-d H:i:s', $model->get_out_time) : "", [ 'class' => 'layui-input end_time', 'autocomplete' => "off", 'disabled' => true ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <strong style="margin-left: 35px;">接待人 </strong>
                <div class="border-show">
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">姓名:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'MIDNAME') ?>" class="layui-input" maxlength="255">
                        </div>
                        <label for="tel" class="layui-form-label">PID:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'SSNO') ?>" class="layui-input" maxlength="255">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">部门:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'department') ?>" class="layui-input" maxlength="255">
                        </div>
                    </div>
                </div>
                <strong style="margin-left: 35px;">行程信息 </strong>
                <div class="border-show" style="margin-left: 35px;">
                    <?php if ($files): foreach ($files as $path): ?>
                        <img src="<?= $path ?>" width="180" height="100">
                    <?php endforeach;endif; ?>
                </div>
            </div>

            <?php $form = LayuiActiveForm::begin([ 'options' => [ 'id' => 'comment-form' ] ]) ?>
            <strong style="margin-left: 35px;">车牌号 </strong>
            <div class="border-show">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">车牌号:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" name="vehicle" class="layui-input" value="<?= ArrayHelper::getValue($receptionUser, 'vehicle') ?>" autocomplete="off">
                    </div>
                </div>
            </div>
            <strong style="margin-left: 35px;">入厂确认 </strong>
            <div class="border-show">
                <input type="hidden" name="id" id="id" value="<?= $model->id ?>">
                <input type="hidden" id = "time_card" class="code" autocomplete="off">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">接待人扫卡:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" id="foemal-card_name" disabled class="layui-input " autofocus="autofocus">
                        <input type="hidden" id="foemal-card"  name="receptionUserCode" class="layui-input " autofocus="autofocus">
                    </div>
                    <label for="tel" class="layui-form-label">警卫扫卡:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" id="guard-card_name" disabled class="layui-input">
                        <input type="hidden" id="guard-card"  name="entranceGuardCode" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">人员临时卡:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" id="snap-card_name" disabled class="layui-input">
                        <input type="hidden" id="snap-card"  name="user_card" class="layui-input">
                    </div>
                    <label for="tel" class="layui-form-label">车辆临时卡:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" id="car-card_name" disabled class="layui-input">
                        <input type="hidden" id="car-card"  name="car_card" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <?= Html::a('保存', 'javascript:;', [ 'class' => 'layui-btn layui-btn-lg submit' ]) ?>
            </div>
            <?php LayuiActiveForm::end(); ?>
        </div>
    </div>
<?php
$js = <<<JS
    $(document).ready(function() {
        getCard();
    });
     //扫卡
    function getCard() {
        let vehicle = $("input[name='vehicle']").val();
        var startTime = $('#time_card').val();
        var id = $("input[name='id']").val();
        $.ajax({
            url: 'get-user-card',
            type: 'get',
            data: {vehicle: vehicle,startTime: startTime,id: id},
            dataType: 'json',
            success: function (data) {
                 $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                    $("#foemal-card").val(data.data.foemal);
                    $("#guard-card").val(data.data.guard);
                    $("#snap-card").val(data.data.snap);
                    $("#car-card").val(data.data.car);
                    $("#foemal-card_name").val(data.data.foemal_name);
                    $("#guard-card_name").val(data.data.guard_name);
                    $("#snap-card_name").val(data.data.snap_name);
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
                }else {
                     setTimeout(getCard,2*1000);
                }
            }
        });
    }
    $('.submit').click(function(){
        let id = $("input[name='id']").val();
        let vehicle = $("input[name='vehicle']").val();
        let receptionUserCode = $("input[name='receptionUserCode']").val();
        let user_card = $("input[name='user_card']").val();
        let car_card = $("input[name='car_card']").val();
        let guard_card = $("input[name='entranceGuardCode']").val();
        $.post('entrance-add',{
            id: id,
            vehicle: vehicle,
            user_card: user_card,
            car_card: car_card,
            guard_card: guard_card,
            receptionUserCode: receptionUserCode
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