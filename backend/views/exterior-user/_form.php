<?php

use backend\widgets\LayuiActiveForm;

?>
<div class = "exterior-user-form">
    <?php $form = LayuiActiveForm::begin(); ?>
    <?= $form->field($model, 'name', ['required' => true])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'phone', ['required' => true])->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ename')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'company')->textInput(['maxlength' => true]) ?>
    <div class = "layui-form-item">
        <label for = "" class = "layui-form-label"></label>
        <button class = "layui-btn" lay-filter = "add" lay-submit = "" id = "submitform" onclick = "subform()" type = "button">
            增加
        </button>
    </div>
    <?php LayuiActiveForm::end(); ?>
</div>
