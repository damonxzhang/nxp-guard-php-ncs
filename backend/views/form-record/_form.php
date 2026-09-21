<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\widgets\LayuiActiveForm;
/* @var $this yii\web\View */
/* @var $model backend\models\FormRecord */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-record-form">

    <?php $form = LayuiActiveForm::begin(); ?>

    <?= $form->field($model, 'title',['required' => true])->textInput() ?>

    <?= $form->field($model, 'status')->dropDownList(\backend\models\FormRecord::$selectStatus) ?>

    <?=\backend\widgets\UploadImg::widget([
        'name' => Html::getInputName($model,'file_id'),
        'required' => true,
        'value' => json_decode($model->file_id,true)
    ])?>

    <div class="layui-form-item"></div>
    <div class="layui-form-item">
        <label for="" class="layui-form-label"></label>
        <?= Html::a('保存','javascript:;',['class' =>'layui-btn','id' => 'submit'])?>
    </div>
    <?php LayuiActiveForm::end(); ?>

</div>
