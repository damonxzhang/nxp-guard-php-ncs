<?php
namespace backend\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

class LayuiActiveForm extends  ActiveForm{
    public $enableClientValidation = false;
    public $options = ['class' => "layui-form"];
    public $fieldClass = 'backend\widgets\ActiveField';

    public $fieldConfig = [
        'template' => '<div class="layui-form-item">
                            <label for="tel" class="layui-form-label">{required}{label}:</label>
                            <div class="layui-input-inline" style="width: 800px;">{input}</div>
                            <div class="layui-col-md4" style="color:red">{error}</div>
                            </div>',
        'inputOptions' => ['class' => 'layui-input'],
        ];


}