<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use backend\models\ExteriorUserRecord;

/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */
/* @var $form yii\widgets\ActiveForm */

?>
<style>
    .show-img img {
        margin-right: 20px;
    }
</style>
<div class="x-body layui-anim layui-anim-up">
    <div class="exterior-user-form">
        <form class="layui-form">
            <div class="user-body">
                <strong style="margin-left: 35px;">来访人员 </strong>
                <div class="border-show">
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">中文名:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text', '', $receptionUser['MIDNAME'], [ 'class' => 'layui-input name', 'disabled' => true ]) ?>
                        </div>
                        <label for="tel" class="layui-form-label"> 手机号码:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text', '', $model->phone, [ 'class' => 'layui-input', 'disabled' => true ]) ?>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <strong style="margin-left: 35px;">车牌信息 </strong>
            <div class="layui-form-item">
                <div class="border-show">
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">车辆信息:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text', '', $model->vehicle, [ 'class' => 'layui-input vehicle', 'disabled' => true ]) ?>
                        </div>
                        <label for="tel" class="layui-form-label">车卡信息:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text', '', $carname['LASTNAME'], [ 'class' => 'layui-input vehicle', 'disabled' => true ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <strong style="margin-left: 35px;">进出厂信息 </strong>
            <div class="layui-form-item">
                <div class="border-show">
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">入厂时间:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= $model->entrance_time ? date('Y-m-d H:i:s',$model->entrance_time ) :"" ?>" class="layui-input receptionUser" autofocus="autofocus" maxlength="255">
                        </div>
                        <label for="tel" class="layui-form-label">出厂时间:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= $model->get_out_time ? date('Y-m-d H:i:s',$model->get_out_time ):"" ?>" class="layui-input receptionUser" maxlength="255">
                        </div>
                    </div>
                </div>
            </div>
            <strong style="margin-left: 35px;">出入信息 </strong>
            <div class="border-show">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">出入状态:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" disabled value="<?= ArrayHelper::getValue(ExteriorUserRecord::$statusListData,  $model->status) ?>" class="layui-input" maxlength="255">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">备注: </label>
                    <div class="layui-input-block">
                        <textarea id='remark' name='remark'  lay-verify="required"  class="layui-textarea" style="width: 600px;height: 200px;"> <?php echo $model->remark; ?></textarea>
                    </div>
                </div>
            </div>
            <input type="hidden" id="id"  name="id" value="<?= $model->id ?>">
            <?php if ($role == 1):?>

            <div class="layui-form-item">
                <?= Html::a('已知悉', 'javascript:;', [ 'class' => 'layui-btn layui-btn-lg submit' ]) ?>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php
$js = <<<JS


    $('.submit').click(function(){
        let id = $("input[name='id']").val();
        let remark = $("textarea[name='remark']").val();
        $.post('cardone',{
            id: id,
            remark: remark
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

