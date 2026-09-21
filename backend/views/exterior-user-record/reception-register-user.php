<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

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
                            <?= Html::input('text','',$model->name,['class' => 'layui-input name', 'disabled' => true])?>
                        </div>
                        <label for="tel" class="layui-form-label"> 手机号码:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text', '', $model->phone,['class' => 'layui-input','disabled' => true ])?>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">公司名称:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text','',$model->company,['class' => 'layui-input company','disabled' => true])?>
                        </div>
                        <label for="tel" class="layui-form-label">预计出厂时间:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text','',$model->get_out_time,['class' => 'layui-input end_time' ,'autocomplete'=>"off" ,'disabled' => true])?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php $form=LayuiActiveForm::begin()?>
        <strong  style="margin-left: 35px;">接待人 </strong>
        <div class="border-show">
            <input type="hidden" id = "id"  value=<?= $model->id ?> class="code" autocomplete="off">
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">姓名:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <input type="text" name="name" value="<?= ArrayHelper::getValue($userData,'MIDNAME')?>" class="layui-input" maxlength="255">
                </div>
                <label for="tel" class="layui-form-label">PID:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <input type="text" name="pid" value="<?= ArrayHelper::getValue($userData,'SSNO')?>" class="layui-input" maxlength="255">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">部门:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <input type="text" name="department_number" value="<?= ArrayHelper::getValue($userData,'department')?>"  class="layui-input" maxlength="255">
                </div>
            </div>
        </div>
        <!-- <strong  style="margin-left: 35px;">拍摄图片 </strong>
        <div class="border-show">
            <div>
                <div id="filepath"> </div>
            </div>
        </div> -->
        <div class="layui-form-item">
            <?= Html::a('保存','javascript:;',['class' =>'layui-btn layui-btn-lg submit'])?>
           
        </div>
        <?php LayuiActiveForm::end(); ?>
    </div>
</div>
<?php
$baseUrl = Yii::$app->params['base_url'];

$js = <<<JS
    $('.submit').click(function(){
        let form = $(this).parents('form').serialize();
        $.post('',form,function(res) {
            if(res.status == 200){
                layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                    closeParentOpen(true); //先全部关闭
                    // parent.layer.close(index);
                    // parent.x_admin_show('登记', res.data);
                    // window.history.go(-2);
                  //  console.log(123456);
                  x_admin_show('接待人登记','reception-register',600,500);//再执行再次打开就解决了
                })
            }else{
                layer.msg(res.msg);
            }
        },'json')
    });
   
    // var maxType =1;//刷卡次数
    // $(document).ready(function() {
    //     getCard();
    // });
    //  //扫卡
    // function getCard() {
    //     var startTime = $('#time_card').val();
    //     $.ajax({
    //         url: 'get-user-info',
    //         type: 'get',
    //         data: {startTime: startTime},
    //         dataType: 'json',
    //         success: function (data) {
    //             $("#time_card").val(data.data.startTime);
    //             if (data.status === 200) {
    //                 $("#reception_user").val(data.data.ID);
    //                 $("input[name='name']").val(data.data.MIDNAME);
    //                 $("input[name='pid']").val(data.data.SSNO);
    //                 $("input[name='department_number']").val(data.data.department);
    //             }else {
    //                   setTimeout(getCard,2*1000);  
    //             }
    //         }
    //     });
    // }
JS;
$this->registerJs($js);
?>
<script>
    //获取子页面的传值
    function ChooseAdidValues(fileId,filepath) {
        if (filepath != "") {
            var html = '<span style="margin-bottom:20px;margin-left:20px">' +
                '<input type="hidden" name="file_id[]" value="'+fileId+'">'+
                '<img src=<?php $baseUrl ?>"' + filepath + '" width="220" height="140" alt="" style="margin-bottom:20px">' +
                '</span >';
            $('#filepath').append(html);
        }
    }

    //删除图片
    $(document).on('click','.layui-btn-danger',function(){
        var _this = $(this).parents('span');
        layer.confirm('是否确认删除?',function(index){
            _this.remove();
            layer.closeAll();
        });
    });
</script>
