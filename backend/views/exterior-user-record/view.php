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
                    </div>
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">来访原因:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text', '', $model->purpose, [ 'class' => 'layui-input vehicle', 'disabled' => true ]) ?>
                        </div>
                        <label for="tel" class="layui-form-label">车辆信息:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <?= Html::input('text', '', $model->vehicle, [ 'class' => 'layui-input vehicle', 'disabled' => true ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <strong style="margin-left: 35px;">接待人 </strong>
            <div class="border-show">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">姓名:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'MIDNAME') ?>" class="layui-input" maxlength="255">
                    </div>
                    <label for="tel" class="layui-form-label">PID:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'SSNO') ?>" class="layui-input" maxlength="255">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label">部门:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" disabled value="<?= ArrayHelper::getValue($receptionUser, 'department') ?>" class="layui-input" maxlength="255">
                    </div>
                </div>
            </div>
            <strong style="margin-left: 35px;">进出厂信息 </strong>
            <div class="layui-form-item">
                <div class="border-show">
                    <div class="layui-form-item">
                        <label for="tel" class="layui-form-label">入厂时间:</label>
                        <div class="layui-input-inline" style="width: 400px;">
                            <input type="text" disabled value="<?= $model->real_entrance_time ? date('Y-m-d H:i:s',$model->real_entrance_time ) :"" ?>" class="layui-input receptionUser" autofocus="autofocus" maxlength="255">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

