<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\FormRecord */

?>
<div class="x-body layui-anim layui-anim-up">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
