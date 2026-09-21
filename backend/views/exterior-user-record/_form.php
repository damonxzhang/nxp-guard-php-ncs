<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use backend\models\UserInfo;
/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */
/* @var $form yii\widgets\ActiveForm */
$userInfo = Yii::$app->user->identity;
?>
<div class="exterior-user-form">

    <?php $form = LayuiActiveForm::begin(); ?>
    <div class="user-body" data-form='1'>
        <div class="form-div">
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label"><span class="x-red">*</span>中文名:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <?= Html::input('text','name[]','',['class' => 'layui-input name','placeholder' => '请输入中文名称'])?>
                </div>
                <label for="tel" class="layui-form-label"><span class="x-red">*</span>手机号码:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <?= Html::input('text', 'phone[]','',['class' => 'layui-input phone','placeholder' => '请输入手机号'])?>
                </div>
                <?= Html::a('搜索','javascript:;',['class' => 'layui-btn layui-btn-normal get-user'])?>

            </div>
            <div class="layui-form-item">

                <label for="tel" class="layui-form-label">英文名:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <?= Html::input('text', 'ename[]','',['class' => 'layui-input ename','placeholder' => '请输入英文名称'])?>
                </div>
                <label for="tel" class="layui-form-label">公司名称:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <?= Html::input('text','company[]','',['class' => 'layui-input company','placeholder' => '请输入公司名称'])?>
                </div>
            </div>
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">进厂时间:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <?= Html::input('text','entrance_time[]','',['class' => 'layui-input','id' => 'start_time_new','lay-key' => 1 ,'autocomplete'=>"off",'placeholder' => '请选择入厂时间'])?>
                </div>
                <label for="tel" class="layui-form-label">出厂时间:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <?= Html::input('text','get_out_time[]','',['class' => 'layui-input','id' => 'end_time_new','lay-key' => 2  ,'autocomplete'=>"off" ,'placeholder' => '请选择出厂时间'])?>
                </div>
            </div>


            <div class="layui-form-item">

                <label for="tel" class="layui-form-label">申报物品:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <?=Html::dropDownList('items[]','',$model::$itemsList,['class' => 'select','lay-ignore' => '','style' => 'height:34px'])?>
                </div>
                <label for="tel" class="layui-form-label">车辆信息:</label>
                <div class="layui-input-inline" style="width: 400px;">
                    <?= Html::input('text','vehicle[]','',['class' => 'layui-input vehicle','placeholder' => '请输入车辆信息'])?>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">备注:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::textarea(  'comment[]','',['class' => 'layui-textarea'])?>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <p style="margin-left: 30px;">
                    <?= Html::button('<i class="layui-icon"></i>增加',['class' =>'layui-btn layui-btn-warm add-user'])?>
                </p>
            </div>
        </div>
    </div>

    <div class="layui-form-item">
        <label for="" class="layui-form-label"></label>
        <?= Html::a('保存','javascript:;',['class' =>'layui-btn layui-btn-lg submit'])?>
    </div>
    <?php LayuiActiveForm::end(); ?>

</div>

<?php $this->beginBlock('footer') ?>
<script>
    $('.submit').click(function(){
        let form = $(this).parents('form').serialize();
        $.post('',form,function(res) {
            if(res.status){
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
        let phone = $(this).parent().find('input[name="phone[]"]').val(); 
    
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
    var laydate = '';
    layui.use('laydate', function(){
        laydate = layui.laydate;
    }); 
    $(document).on('click','.add-user',function() {
        
        let brother = $(this).parents('.user-body'); 
        
        let html = ' <div class="form-div">'+brother.children(":first").html()+'</div>';
        let addbtn = '<button type="button" class="layui-btn layui-btn-warm add-user"><i class="layui-icon"></i>增加</button>';
        let delbtn = '<button type="button" class="layui-btn layui-btn-danger del-user"><i class="layui-icon">ဆ</i>删除</button>';
        
        let form_id = brother.data('form') + 2; 
        let start_time = 'start_time' + form_id;
        let end_time = 'end_time' + form_id;
        html = html.replace('start_time', start_time);
        html = html.replace('end_time', end_time);
        
        
        
        html = html.replace(addbtn,addbtn+delbtn);
        brother.data('form',form_id);
        brother.append(html); 
         
        $('#'+start_time).attr('lay-key',form_id + 1);
        $('#'+end_time).attr('lay-key',form_id + 2);
        laydate.render({
            elem : '#'+start_time
            ,trigger : 'click'
        });
        laydate.render({
            elem : '#'+end_time,
            trigger : 'click'
        });
    });
    //开始日期
    var start = laydate.render({
        elem: '#start_time_new',
        min: minDate(),
        format: 'yyyy-MM-dd'
       
    });
    //结束日期
    var end = laydate.render({
        elem: '#end_time_new',
        format: 'yyyy-MM-dd'
       
    });
    function minDate(){
        var now = new Date();
        return now.getFullYear()+"-" + (now.getMonth()+1) + "-" + now.getDate();
    }
    $(document).on('click','.del-user',function() {
        $(this).parents('.form-div').remove()
    });
</script>
<?php $this->endBlock() ?>