<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use backend\models\UserInfo;
/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */
/* @var $form yii\widgets\ActiveForm */
$userInfo = Yii::$app->user->identity;

?>
    <div class="x-body layui-anim layui-anim-up">

        <div class="exterior-user-form">
            <?php $form=LayuiActiveForm::begin(['options' => ['class' => 'form-div']])?>
                <div class="user-body">
                    <div class="border-show">
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">中文名:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text',Html::getInputName($model,'name'),'',['class' => 'layui-input name', ])?>
                            </div>
                            <label for="tel" class="layui-form-label"> 手机号码:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text', Html::getInputName($model,'phone'), '',['class' => 'layui-input', ])?>
                            </div>
                            <?= Html::a('搜索','javascript:;',['class' => 'layui-btn layui-btn-normal get-user'])?>

                        </div>
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">英文名:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text', Html::getInputName($model,'ename'),$model->ename,['class' => 'layui-input ename',])?>
                            </div>
                            <label for="tel" class="layui-form-label">公司名称:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text',Html::getInputName($model,'company'),$model->company,['class' => 'layui-input company',])?>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">进厂时间:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text',Html::getInputName($model,'entrance_time'),!empty($model->entrance_time)? date('Y-m-d H:i:s',$model->entrance_time ):"",['class' => 'layui-input start_time' ,'autocomplete'=>"off",])?>
                            </div>
                            <label for="tel" class="layui-form-label">出厂时间:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text',Html::getInputName($model,'get_out_time'),!empty($model->get_out_time)? date('Y-m-d H:i:s',$model->get_out_time ):"",['class' => 'layui-input end_time' ,'autocomplete'=>"off" ,])?>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">申报物品:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?=Html::dropDownList(Html::getInputName($model,'items'),$model->items,$model::$itemsList,['class' => 'select','lay-ignore' => '','style' => 'height:34px'])?>
                            </div>
                            <label for="tel" class="layui-form-label">车辆信息:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::input('text',Html::getInputName($model,'vehicle'),$model->vehicle,['class' => 'layui-input vehicle',])?>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label for="tel" class="layui-form-label">备注:</label>
                            <div class="layui-input-inline" style="width: 400px;">
                                <?= Html::textarea(  Html::getInputName($model,'comment'),$model->comment,['class' => 'layui-textarea',])?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php LayuiActiveForm::end(); ?>

            <form class="layui-form form-div">

            <strong  style="margin-left: 35px;">接待人 </strong>
            <div class="border-show">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">姓名:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" value="<?=$reception_user->name?>" disabled class="layui-input" maxlength="255">
                    </div>
                    <label for="tel" class="layui-form-label">工号:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" value="<?=$reception_user->badge_code?>" class="layui-input" maxlength="255">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">部门:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" value="<?=$reception_user->department_number?>" class="layui-input" maxlength="255">
                    </div>
                    <label for="tel" class="layui-form-label">手机号:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" value="<?=$reception_user->tel?>" class="layui-input" maxlength="255">
                    </div>
                </div>
            </div>
                <strong  style="margin-left: 35px;">拍摄图片 </strong>
                <div class="border-show">
                    <div>
                        <div id="filepath"> </div>
                    </div>
                </div>
            </form>
            <div class="layui-form-item">
                <?= Html::a('保存','javascript:;',['class' =>'layui-btn layui-btn-lg submit'])?>
                <?= Html::a('拍照','javascript:;',['class' =>'layui-btn layui-btn-lg layui-btn-normal add-user-image photo'])?>
            </div>
        </div>
    </div>
<?php
$url = \yii\helpers\Url::to(['photo/camera']);
$js = <<<JS
    $('.submit').click(function(){
        let form = $('.form-div').serialize(); 
        $.post('',form,function(res) {
            if(res.status){
                layer.msg(res.msg, {icon: 1,time: 1500}, function () { 
                   closeParentOpen(true);
                })
            }else{
                layer.msg(res.msg);
            }
        },'json') 
    });

    $('.photo').click(function(){
        let phone = $('input[name="ExteriorUserRecordServes[phone]"]').val();
        if(!phone){
            layer.msg('请先填写手机号');
            return false;
        }
        
        x_admin_show('登记',  '{$url}?phone=' + phone); 
    });
    
    
    $(document).on('click','.get-user',function() {
        let vessel = $(this).parents('.form-div');
        let phone = $(this).parent().find('input[name="ExteriorUserRecordServes[phone]"]').val(); 
 
        $.post('/exterior-user/get-user',{phone:phone},function(res) {
            if(res.status){ 
                 $.each(res.data,function(i,v){  
                     vessel.find("."+i).val(v); 
                });
            }else{
                layer.msg(res.msg);
            }
        },'json');

    });
    function getReceptionUser(key){
        $.post('get-reception-user',{key:key},function(res){
           if(res.status == 200){ 
               console.log(res);
               $("#reception_user").val(res.data.id);
               $("input[name='name']").val(res.data.name);
               $("input[name='phone']").val(res.data.tel);
               $("input[name='number']").val(res.data.badge_code);
               $("input[name='department_number']").val(res.data.department_number);
          
           } else{
               layer.msg(res.msg);
           }
        },'json')
    }

    $('.get-user-phone').click(function() {
        let phone = $("input[name='phone']").val();
        if(!(/^1[3456789]\d{9}$/.test(phone))){ 
            layer.msg("请输入正确的手机号");  
            return false; 
        } 
 
        getReceptionUser(phone);
    });
    $('.get-user-number').click(function() {
        let number = $("input[name='number']").val();
        getReceptionUser(number); 
    }); 
    
    $(function(){ 
        $('.receptionUser').focus();
    })
   
JS;
$this->registerJs($js);
?>
<script>
    //获取子页面的传值
    function ChooseAdidValues(fileId,filepath) {
        if (filepath != "") {
            var html = '<span style="margin-bottom:20px;margin-left:20px">' +
                '<input type="hidden" name="file_id[]" value="'+fileId+'">'+
                '<img src=<?php $baseUrl ?>"' + filepath + '" width="180" height="100" alt="" style="margin-bottom:20px">' +
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
