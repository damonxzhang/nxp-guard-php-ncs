<?php

use yii\helpers\Html;
use backend\widgets\LayuiGridView;
use backend\models\LenelEmp;
use yii\helpers\Url;
use backend\widgets\LayuiActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\ExteriorUserCar;
use backend\models\ExteriorUser;
?>
<div class="x-body">
    <?php  $id = \Yii::$app->user->id;
     if (!in_array($id,ExteriorUser::$guardArr)): ?>
        <div class="exterior-user-form">
            <?php $form = LayuiActiveForm::begin(); ?>
            <div class="user-body" data-form='1'>
                <div class="form-div">
                    <div class="layui-form-item">
                        <label for="icon" class="layui-form-label"><span class="x-red">*</span>PID:</label>
                        <div class="layui-input-inline" style="width: 300px;">
                            <?= Html::input('text', 'pid', '', ['class' => 'layui-input name','id'=>'pid','disabled'=>'disabled', 'placeholder' => '请刷卡']) ?>
                        </div>
                        <label for="tel" class="layui-form-label"><span class="x-red">*</span>车牌号:</label>
                        <div class="layui-input-inline" style="width: 300px;">
                            <?= Html::input('text', 'vehicle', '', ['class' => 'layui-input vehicle', 'placeholder' => '车牌号', 'required' => 'required']) ?>
                        </div>
                    </div>
                    <div class="layui-form-item">    
                        <label for="tel" class="layui-form-label"><span class="x-red">*</span>手机号:</label>
                        <div class="layui-input-inline" style="width: 300px;">
                            <?= Html::input('text', 'phone', '', ['class' => 'layui-input vehicle', 'placeholder' => '手机号', 'maxlength' => '11', 'required' => 'required']) ?>
                        </div>
                        <label for="tel" class="layui-form-label">姓名:</label>
                        <div class="layui-input-inline" style="width: 300px;">
                            <input type="hidden" id = "time_card" class="code" autocomplete="off">
                            <input type="text" disabled id="username" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">部门:</label>
                        <div class="layui-input-inline" style="width: 300px;">
                            <input type="text" disabled id="department" class="layui-input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <label for="" class="layui-form-label"></label>
                <input type="hidden" name="empid" id="empid" class="layui-input">
                <input type="hidden" name="userpid" id="userpid" class="layui-input">
                <!-- <input type="hidden" name="factory" id="factory" class="layui-input"> -->
                <?= Html::a('提交', 'javascript:;', ['class' => 'layui-btn layui-btn-lg submit']) ?>
            </div>
            <?php LayuiActiveForm::end(); ?>
        </div>
    <?php endif; ?>
    <?= LayuiGridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => [
            [
                'label' => '申请人',
                'value' => function ($row) {
                    $model = LenelEmp::findOne($row->create_user);
                    return $model ? $model->MIDNAME : '';
                }
            ],
            [
                'attribute' => 'PID',
                'value'     => function ($row) {
                    return $row->pid;
                }
            ],
            [
                'attribute' => '联系方式',
                'value'     => function ($row) {
                    return $row->phone;
                }
            ],
            [
                'attribute' => '状态',
                'value'     => function ($row) {
                    return ArrayHelper::getValue(ExteriorUserCar::$statusList, $row->status,'');
                }
            ],
            [
                'attribute' => '实际进厂时间',
                'value'     => function ($row) {
                    return $row->entrance_time ? date('Y年m月d日 H时i分', $row->entrance_time) : '';
                }
            ],
            [
                'label' => '车辆临时卡',
                'value' => function ($row) {
                    $model = LenelEmp::findOne($row->car_card);
                    return $model ? $model->LASTNAME : '';
                }
            ],
            [
                'label'  => '车牌号',
                'format' => 'raw',
                'value'  => function ($row) {
                    return $row->vehicle;
                }
            ],
            [
                'class'    => 'backend\grids\LayuiActionColumn',
                'template' => '{entrance} {delete}',
                'buttons'  => [
                    'entrance' => function ($url, $row) {
                        $id = \Yii::$app->user->id;
                        if(in_array($id, ExteriorUser::$guardArr)) {
                            if($row->status ==  ExteriorUserCar::STATUS_WAITE) {
                                return Html::a('警卫确认', '#', ['class' => 'layui-btn layui-btn-xs layui-bg-cyan', 'onclick' => "x_admin_show('警卫确认','" . $url . "')"]);
                            }
                        }
                        return '';
                    },
                    'delete'   => function ($url, $row) {
                        $id = \Yii::$app->user->id;
                        if(in_array($id,ExteriorUser::$guardArr)) {
                            if($row->status == ExteriorUserCar::STATUS_WAITE || $row->status == ExteriorUserCar::STATUS_GREEN_LIGHT) {
                                return Html::a('删除', '#', ['class' => 'layui-btn layui-btn-xs layui-btn-danger', 'onclick' => "del(this,'" . $row->id . "','" . Url::to(['exterior-user-car/delete']) . "')"]);
                            }
                        }
                        return '';
                    },
                ],
            ],
        ],
    ]); ?>
</div>
<?php
$id = \Yii::$app->user->id;
$checkGuard = 1;//是警卫
if (!in_array($id,ExteriorUser::$guardArr)){
    $checkGuard = 2;//不是警卫
}
$js = <<<JS
    $('.submit').click(function(){
        let pid  = $("input[name='pid']").val();
        if(!pid){
            layer.msg('请刷卡', {icon: 2,time: 3000});
            return false;
        }
        let vehicle  = $("input[name='vehicle']").val();
        if(!vehicle){
            layer.msg('请输入车牌号', {icon: 2,time: 3000});
            return false;
        }
        let phone  = $("input[name='phone']").val();
        if(!phone){
            layer.msg('请输入手机号', {icon: 2,time: 3000});
            return false;
        }
        let form = $(this).parents('form').serialize();
        $.post('scene-register',form,function(res) {
            if(res.status == 200){
                layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                    window.location.href= 'index';
                })
            }else{
                layer.msg(res.msg);
            }
        },'json');
        return false;
    });

    // $('.get-user').click(function() {
    //     let pid = $(this).parent().find('input[name="pid"]').val();
    //     $.post('/exterior-user-car/get-user',{pid:pid},function(res) {
    //        if(res.status == 200){
    //            $("#empid").val(res.data.empid);
    //            $("#username").val(res.data.username);
    //            $("#department").val(res.data.department);
    //        } else{
    //            layer.msg(res.msg);
    //        }
    //     },'json');
    //     return false;
    // });
    $(document).ready(function() {
        if({$checkGuard} == 2){
            getCard();
        }
    });
     //扫卡
    function getCard() {
        var startTime = $('#time_card').val();
        $.ajax({
            url: '/exterior-user-car/get-user',
            type: 'get',
            data: {startTime: startTime},
            dataType: 'json',
            success: function (data) {
                $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                    $("#pid").val(data.data.pid);
                    $("#userpid").val(data.data.pid);
                    $("#empid").val(data.data.empid);
                    $("#username").val(data.data.username);
                    $("#department").val(data.data.department);
                }else {
                    setTimeout(getCard,2*1000);
                }
            }
        });
    }
JS;
$this->registerJs($js);
?>

