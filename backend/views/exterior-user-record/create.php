<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ExteriorUserRecord */

$this->title = 'Create Exterior User Record';
$this->params['breadcrumbs'][] = ['label' => 'Exterior User Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="x-body layui-anim layui-anim-up">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
