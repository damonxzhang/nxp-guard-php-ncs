<?php
use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use backend\models\UserInfo;
/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */
$userInfo = Yii::$app->user->identity;
?>
<div class="x-body layui-anim layui-anim-up">
    <div class="exterior-user-form">
        <?php $form=LayuiActiveForm::begin()?>
        <div class="user-body" data-form='1'>
            <div class="form-div">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label"><span class="x-red">*</span>中文名:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::input('text','name',$user->name,['class' => 'layui-input name','placeholder' => '请输入中文名称'])?>
                    </div>
                    <label for="tel" class="layui-form-label"><span class="x-red">*</span>手机号码:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::input('text', 'phone',$user->phone,['class' => 'layui-input phone','placeholder' => '请输入手机号'])?>
                    </div>
                    <?= Html::a('搜索','javascript:;',['class' => 'layui-btn layui-btn-normal get-user'])?>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">英文名:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::input('text', 'ename',$user->ename,['class' => 'layui-input ename','placeholder' => '请输入英文名称'])?>
                    </div>
                    <label for="tel" class="layui-form-label">公司名称:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::input('text','company',$user->company,['class' => 'layui-input company','placeholder' => '请输入公司名称'])?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">进厂时间:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::input('text','entrance_time',$model->entrance_time,['class' => 'layui-input','id' => 'start_time','lay-key' => 1 ,'autocomplete'=>"off",'placeholder' => '请选择入厂时间'])?>
                    </div>
                    <label for="tel" class="layui-form-label">出厂时间:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::input('text','get_out_time',$model->get_out_time,['class' => 'layui-input','id' => 'end_time','lay-key' => 2  ,'autocomplete'=>"off" ,'placeholder' => '请选择出厂时间'])?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">申报物品:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?=Html::dropDownList('items',$model->items,$model::$itemsList,['class' => 'select','lay-ignore' => '','style' => 'height:34px'])?>
                    </div>
                    <label for="tel" class="layui-form-label">车辆信息:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::input('text','vehicle',$user->vehicle,['class' => 'layui-input vehicle','placeholder' => '请输入车辆信息'])?>
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">备注:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::textarea(  'comment',$model->comment,['class' => 'layui-textarea'])?>
                        </div>
                    </div>
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
   
JS;
$this->registerJs($js);
