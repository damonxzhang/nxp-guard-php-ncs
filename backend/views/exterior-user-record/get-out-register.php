<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */
/* @var $form yii\widgets\ActiveForm */

?>
    <div class="x-body layui-anim layui-anim-up">
        <div class="exterior-user-form">
            <form class="layui-form">
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
                            <label for="tel" class="layui-form-label">进厂时间:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text', '', !empty($model->real_entrance_time) ? date('Y-m-d H:i:s', $model->real_entrance_time) : "", [ 'class' => 'layui-input start_time', 'autocomplete' => "off", 'disabled' => true ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <strong style="margin-left: 35px;">接待人 </strong>
                <div class="border-show">
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">姓名:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($user, 'MIDNAME') ?>" class="layui-input" maxlength="255">
                        </div>
                        <label for="tel" class="layui-form-label">PID:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($user, 'SSNO') ?>" class="layui-input" maxlength="255">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">部门:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($user, 'department') ?>" class="layui-input" maxlength="255">
                        </div>
                    </div>
                </div>
                <strong style="margin-left: 35px;">行程信息 </strong>
                <div class="border-show" style="margin-left: 35px;">
                    <?php if ($files): foreach ($files as $path): ?>
                        <img src="<?= $path ?>" width="180" height="100">
                    <?php endforeach;endif; ?>
                </div>
            </form>
            <strong style="margin-left: 35px;">车牌号 </strong>
            <div class="border-show">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">车牌号:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" id="vehicle_value" value="<?= $model->vehicle ?>" disabled class="layui-input">
                    </div>
                </div>
            </div>
            <?php $form = LayuiActiveForm::begin() ?>
            <strong style="margin-left: 35px;">出厂确认 </strong>
            <div class="layui-form-item">
                <input type="hidden" name="id" id="id" value="<?= $model->id ?>">
                <input type="hidden" id = "time_card" class="code" autocomplete="off">
                <div class="border-show">
                    <div class="layui-form-item">
						<label for="tel" class="layui-form-label">原人员临时卡:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($user, 'snapname') ?>" class="layui-input">
                        </div>
                        <label for="tel" class="layui-form-label">人员临时卡:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" id="snap-card_name" disabled class="layui-input">
                            <input type="hidden" id="snap-card"  name="user_card" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
						<label for="tel" class="layui-form-label">原车辆临时卡:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($user, 'carname') ?>" class="layui-input">
                        </div>
                        <label for="tel" class="layui-form-label">车辆临时卡:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" id="car-card_name" disabled  class="layui-input">
                            <input type="hidden" id="car-card"  name="car_card" class="layui-input">
                        </div>
                    </div>
					<div>
						<label for="tel" class="layui-form-label">警卫扫卡:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" id="guard-card_name" disabled class="layui-input">
                            <input type="hidden" id="guard-card"  name="entranceGuardCode" class="layui-input">
                        </div>
					</div>
                </div>
                <label for="" class="layui-form-label"></label>
                <?= Html::a('放行', 'javascript:;', [ 'class' => 'layui-btn layui-btn-lg submit' ]) ?>
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
        let vehicle = $("#vehicle_value").val();
        $.ajax({
            url: 'get-out-card',
            type: 'get',
            data: {vehicle: vehicle,startTime: startTime},
            dataType: 'json',
            success: function (data) {
                $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                    $("#guard-card").val(data.data.guard);
                    $("#snap-card").val(data.data.snap);
                    $("#car-card").val(data.data.car);
                    $("#guard-card_name").val(data.data.guard_name);
                    $("#snap-card_name").val(data.data.snap_name);
                    $("#car-card_name").val(data.data.car_name);
                }else if(data.status === 409) {
                    layer.confirm(data.msg, {
                        btn: ['确认','取消'] //按钮
                    }, function(indexyes){
                        layer.close(indexyes);
                        $("#time_card").val('');
                        setTimeout(getCard,4*1000);
                    }, function(){
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    });
                 //   layer.msg(data.msg, {icon: 2, time: 3000});
                }else {
                     setTimeout(getCard,4*1000);
                }
            }
        });
    }
    $('.submit').click(function(){
        let id = $("input[name='id']").val();
        let entranceGuardCode = $("input[name='entranceGuardCode']").val();
        let user_card = $("input[name='user_card']").val();
        if( entranceGuardCode && user_card ){
            $.post('get-out-save',{
                id: id,
                guard_card: entranceGuardCode
            },function(res) {
                if(res.status == 200){
                    layer.msg(res.msg, {icon: 1,time: 1500}, function () { 
                       closeParentOpen(true);
                    })
                }else{
                    layer.msg(res.msg, {icon: 2, time: 3000});
                }
            },'json') 
        }else{
            layer.msg('请刷人员信息卡和警卫卡!', {icon: 2,time: 1500});
            return false;
        }
        
    });
JS;
$this->registerJs($js);
?>