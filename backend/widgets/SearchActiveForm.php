<?php
namespace backend\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

class SearchActiveForm extends  ActiveForm{
    public $enableClientValidation = false;
    public $options = ['class' => "layui-form layui-col-md12 x-so"];
//    public $fieldClass = 'backend\widgets\ActiveField';

    public $fieldConfig = [
        'options' => ['style' => 'display:inline'],
        'template' => ' {input} ',
        'inputOptions' => ['class' => 'layui-input'],
        ];


}