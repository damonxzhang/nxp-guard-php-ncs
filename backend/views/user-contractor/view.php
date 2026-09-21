<?php
use yii\helpers\Url;
?>
<div class="x-body layui-anim layui-anim-up">
    <form class="layui-form">
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
                 EBarcode
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->ebarcode ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
                 Name
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->name ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
                WorkID
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->workid ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
                DepID
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?=  $model->depid ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
               CompID
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->compid ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
                EBDate
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->ebdate ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
                ContactID
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->contactid ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
               ContactName
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->contactname ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
               ContactDepart
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->contactdepart ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
               InputDate
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->inputdate ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="route_name" class="layui-form-label">
              DepName
            </label>
            <div class="layui-input-inline">
                <input type="text" disabled autocomplete="off" class="layui-input" value="<?= $model->depname ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label"></label>
             <img src="<?= '/photo/'. $model->empphoto ?>" width="180" height="160">
        </div>
    </form>
</div>


