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

        <?php $form = LayuiActiveForm::begin(); ?>
        <div class="user-body" data-form='1'>
            <div class="form-div">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label"><span class="x-red">*</span>中文名:</label>
                    <div class="layui-input-inline" style="width: 300px;"  >
                        <?= Html::input('text','name','',['class' => 'layui-input name','placeholder' => '请输入中文名称','required'=>'required' ])?>
                    </div>
                    <label for="tel" class="layui-form-label"><span class="x-red">*</span>手机号码:</label>
                    <div class="layui-input-inline" style="width: 300px;"  >
                        <?= Html::input('text', 'phone','',['class' => 'layui-input phone','placeholder' => '请输入手机号','required'=>'required'  ])?>
                    </div>
                    <?= Html::a('搜索','javascript:;',['class' => 'layui-btn layui-btn-normal get-user'])?>

                </div>
                <div class="layui-form-item">

                    <label for="tel" class="layui-form-label">英文名:</label>
                    <div class="layui-input-inline" style="width: 300px;">
                        <?= Html::input('text', 'ename','',['class' => 'layui-input ename','placeholder' => '请输入英文名称','required'=>'required'  ])?>
                    </div>
                    <label for="tel" class="layui-form-label"><span class="x-red">*</span>公司名称:</label>
                    <div class="layui-input-inline" style="width: 300px;">
                        <?= Html::input('text','company','',['class' => 'layui-input company','placeholder' => '请输入公司名称'])?>
                    </div>
                </div>
                <div class="layui-form-item">

                    <label for="tel" class="layui-form-label">车辆信息:</label>
                    <div class="layui-input-inline" style="width: 300px;">
                        <?= Html::input('text','vehicle','',['class' => 'layui-input vehicle','placeholder' => '请输入车辆信息'])?>
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">备注:</label>
                        <div class="layui-input-inline" style="width: 300px;">
                            <?= Html::textarea(  'comment','',['class' => 'layui-textarea'])?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <strong  style="margin-left: 35px;">接待人 </strong>
        <div class="border-show">
            <input type="hidden" name="reception_user" id="reception_user" class="layui-input" maxlength="255">

            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">姓名:</label>
                <div class="layui-input-inline" style="width: 300px;">
                    <input type="text" name="reception-name" class="layui-input" maxlength="255">
                </div>
                <label for="tel" class="layui-form-label"><span class="x-red">*</span>PID:</label>
                <div class="layui-input-inline" style="width: 300px;">
                    <input type="text" name="reception-number" class="layui-input" maxlength="255" required="required">
                </div>
                <?= Html::a('搜索','javascript:;',['class' => 'layui-btn layui-btn-normal get-user-number'])?>
            </div>
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">部门:</label>
                <div class="layui-input-inline" style="width: 300px;">
                    <input type="text" name="reception-section" class="layui-input" maxlength="255">
                </div>
                <label for="tel" class="layui-form-label">手机号:</label>
                <div class="layui-input-inline" style="width: 300px;">
                    <input type="text" name="reception-phone" class="layui-input" maxlength="255">
                </div>

            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label"></label>
            <?= Html::a('保存','javascript:;',['class' =>'layui-btn layui-btn-lg submit'])?>
        </div>
        <?php LayuiActiveForm::end(); ?>
    </div>
</div>
<?php
$js = <<<JS
    $('.submit').click(function(){
        let form = $(this).parents('form').serialize();
        $.post('scene-register',form,function(res) {
            if(res.status == 200){
                layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                    parent.location.reload();
                })
            }else{
                layer.msg(res.msg);
            }
        },'json') 
    });

    $(document).on('click','.get-user',function() {
        let vessel = $(this).parents('.form-div');
        let phone = $(this).parent().find('input[name="phone"]').val(); 
    
        $.post('/exterior-user/get-user',{phone:phone},function(res) {
            if(res.status){ 
                 $.each(res.data,function(i,v){ 
                     console.log(i +'-'+ v);
                     vessel.find("."+i).val(v); 
                });
            }else{
                layer.msg(res.msg);
            }
        },'json');

    });
    
    $('.add-user-image').click(function(){
        let phone = $('input[name="phone"]').val();
        if(phone){
            x_admin_show('登记','/photo/photo?phone='+phone );
        }else{
            layer.msg('请先输入手机号');
        }
    });
    
   function getReceptionUser(key){
        $.post('get-reception-user',{key:key},function(res){
           if(res.status == 200){ 
               console.log(res);
               $("#reception_user").val(res.data.id);
               $("input[name='reception-name']").val(res.data.name);
               $("input[name='reception-phone']").val(res.data.tel);
               $("input[name='reception-number']").val(res.data.pid);
               $("input[name='reception-section']").val(res.data.department_number); 
          
           } else{
               layer.msg(res.msg);
           }
        },'json')
    }

    $('.get-user-phone').click(function() {
        let phone = $("input[name='reception-phone']").val();
        if(!(/^1[3456789]\d{9}$/.test(phone))){ 
            layer.msg("请输入正确的手机号");  
            return false; 
        } 
 
        getReceptionUser(phone);
    });
    $('.get-user-number').click(function() {
        let number = $("input[name='reception-number']").val();
        getReceptionUser(number); 
    }); 
    
 
JS;
$this->registerJs($js);


?>