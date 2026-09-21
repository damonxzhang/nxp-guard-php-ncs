<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\LenelEmp;

/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */
/* @var $form yii\widgets\ActiveForm */

?>
<div class="x-body layui-anim layui-anim-up">
        <div class="exterior-user-form">
            <form class="layui-form">
                <div class="user-body">
                    <strong style="margin-left: 35px;">借卡人员 </strong>
                    <div class="border-show">
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">中文名:</label>
                            <div class="layui-input-inline" style="width: 200px;">
                                <?= Html::input('text', '', (new LenelEmp())->getMidName($model->create_user), [ 'class' => 'layui-input name', 'disabled' => true ]) ?>
                            </div>
                            <label for="tel" class="layui-form-label">进厂时间:</label>
                            <div class="layui-input-inline" style="width: 200px;">
                                <?= Html::input('text', '', !empty($model->entrance_time) ? date('Y-m-d H:i:s', $model->entrance_time) : "", [ 'class' => 'layui-input start_time', 'autocomplete' => "off", 'disabled' => true ]) ?>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">车牌号:</label>
                            <div class="layui-input-inline" style="width: 200px;">
                                <input type="text" id="vehicle_value" value="<?= $model->vehicle ?>" disabled class="layui-input">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <?php $form = LayuiActiveForm::begin() ?>
            <strong style="margin-left: 35px;">出厂确认 </strong>
            <div class="layui-form-item">
                <input type="hidden" name="id" id="id" value="<?= $model->id ?>">
                <input type="hidden" id = "time_card" class="code" autocomplete="off">
                <div class="border-show">
                    <div class="layui-form-item">
						<label for="tel" class="layui-form-label">原车辆临时卡:</label>
                        <div class="layui-input-inline" style="width: 200px;">
                            <input type="text" disabled value="<?= ArrayHelper::getValue($user, 'carname') ?>" class="layui-input">
                        </div>
                        <label for="tel" class="layui-form-label">车辆临时卡:</label>
                        <div class="layui-input-inline" style="width: 200px;">
                            <input type="text" id="car-card-name" disabled  class="layui-input">
                            <input type="hidden" id="car-card"  name="car_card" class="layui-input">
                        </div>
                     </div>
                     <div class="layui-form-item">
						<label for="tel" class="layui-form-label">警卫扫卡:</label>
                        <div class="layui-input-inline" style="width: 200px;">
                            <input type="text" id="guard-card-name" disabled class="layui-input">
                            <input type="hidden" id="guard-card"  name="guard_card" class="layui-input">
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

    $(document).ready(function() {
        getCard();
    });
     //扫卡
    function getCard() {
        var startTime = $('#time_card').val();
        var id = $('#id').val();
        $.ajax({
            url: '/exterior-user-car/get-out-guard',
            type: 'get',
            data: {startTime: startTime,id:id},
            dataType: 'json',
            success: function (data) {
                $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                    $("#guard-card").val(data.data.guard);
                    $("#guard-card-name").val(data.data.guard_name);
                    $("#car-card").val(data.data.car);
                    $("#car-card-name").val(data.data.car_name);
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
        let guardCard = $("input[name='guard_card']").val();
        if( guardCard){
            $.post('/exterior-user-car/get-out-save',{
                id: id,guardCard:guardCard
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
            layer.msg('请刷车卡和警卫卡!', {icon: 2,time: 1500});
            return false;
        }
        
    });
JS;
$this->registerJs($js);
?>
