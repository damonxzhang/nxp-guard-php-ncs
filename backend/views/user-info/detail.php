<?php

?>
<div class="x-body layui-anim layui-anim-up">
    <form class="layui-form">
        <div class="layui-row">
            <div class="layui-col-sm6">
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        中文名:
                    </label>
                    <div class="layui-input-inline">
                        <input type="text"  value="<?= $model['name'] ?>" readonly="readonly" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">英文名:</label>
                    <div class="layui-input-inline">
                        <input type="text"  autocomplete="off"  value="<?= $model['ename'] ?>" readonly="readonly" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">PID:</label>
                    <div class="layui-input-inline">
                        <input type="text" autocomplete="off" value="<?= $model['pid'] ?>" readonly="readonly" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">部门号:</label>
                    <div class="layui-input-inline">
                        <input type="text" value="<?= $model['department_number'] ?>" readonly="readonly" autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


