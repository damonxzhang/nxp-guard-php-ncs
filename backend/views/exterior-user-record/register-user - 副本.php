<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */
/* @var $form yii\widgets\ActiveForm */
$userInfo = Yii::$app->user->identity;

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
                        <label for="tel" class="layui-form-label">预计出厂时间:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text', '', !empty($model->get_out_time) ? date('Y-m-d H:i:s', $model->get_out_time) : "", [ 'class' => 'layui-input end_time', 'autocomplete' => "off", 'disabled' => true ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <?php $form = LayuiActiveForm::begin() ?>
        <strong style="margin-left: 35px;">接待人 </strong>
        <div class="border-show">
            <input type="hidden" id = "time_card" class="code" autocomplete="off">
            <input type="hidden" name="<?= Html::getInputName($model, 'reception_user') ?>" id="reception_user" class="layui-input" maxlength="255">
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">姓名:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <input type="text" name="name" id="name" disabled class="layui-input" maxlength="255">
                </div>
                <label for="tel" class="layui-form-label">PID:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <input type="text" name="pid" id="pid" disabled class="layui-input" maxlength="255">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">部门:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <input type="text" name="department_number" id="department_number" disabled class="layui-input" maxlength="255">
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
$baseUrl = Yii::$app->params['base_url'];
$js      = <<<JS

    
    $('.submit').click(function(){
        var reception_user = $("#reception_user").val();
        if(!reception_user){
            layer.msg("请接待人刷卡",{icon:2,time:1500});
            return false;
        }else {
            let form = $(this).parents('form').serialize();
            $.post('',form,function(res) {
                if(res.status == 200){
                    layer.msg(res.msg, {icon: 1,time: 1500}, function () { 
                       closeParentOpen(true);
                    })
                }else{
                    layer.msg(res.msg);
                }
            },'json') 
        }
        
    });
   
    var maxType =1;//刷卡次数
    $(document).ready(function() {
        getCard();
    });
     //扫卡
    function getCard() {
        var startTime = $('#time_card').val();
        $.ajax({
            url: 'get-register-card',
            type: 'get',
            data: {startTime: startTime},
            dataType: 'json',
            success: function (data) {
                $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                    $("#reception_user").val(data.data.ID);
                    $("#name").val(data.data.MIDNAME);
                    $("#pid").val(data.data.SSNO);
                    $("#department_number").val(data.data.department);
                }else {
                   setTimeout(getCard, 4*1000);  //设置请求api接口时间2秒
                }
            }
        });
    }
  
JS;
$this->registerJs($js);
?>
<script>
    //获取子页面的传值
    function ChooseAdidValues(fileId, filepath) {
        if (filepath != "") {
            var html = '<span style="margin-bottom:20px;margin-left:20px">' +
                       '<input type="hidden" name="file_id[]" value="' + fileId + '">' +
                       '<img src=<?php $baseUrl ?>"' + filepath + '" width="180" height="100" alt="" style="margin-bottom:20px" class="layui-btn-danger">' +
                       '</span >';
            $('#filepath').append(html);
        }
    }

    //删除图片
    $(document).on('click', '.layui-btn-danger', function () {
        var _this = $(this).parents('span');
        layer.confirm('是否确认删除?', function (index) {
            _this.remove();
            layer.closeAll();
        });
    });
</script>