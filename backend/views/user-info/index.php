<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use backend\models\UserInfo;

?>
<div class = "x-body">
    <div class = "layui-row">
        <form class = "layui-form layui-col-md12 x-so">
            <input class = "layui-input" name = "name" value = "<?php echo ArrayHelper::getValue($request, 'name') ?>" autocomplete = "off" placeholder = "人员姓名">
            <input class = "layui-input" name = "pid" value = "<?php echo ArrayHelper::getValue($request, 'pid') ?>" autocomplete = "off" placeholder = "人员PID">
            <div class = "layui-input-inline">
                <?= Html::dropDownList('type', ArrayHelper::getValue($request, 'type'), ArrayHelper::merge(['' => '人员职位'], UserInfo::$typeArr), ['name' => 'type']); ?>
            </div>
            <input class = "layui-input" name = "department_number" value = "<?php echo ArrayHelper::getValue($request, 'department_number') ?>" autocomplete = "off" placeholder = "部门编号">
            <button class = "layui-btn" lay-submit = "" lay-filter = "sreach"><i class = "layui-icon">&#xe615;</i>查询</button>
            <button class = "layui-btn" type = "button" onclick = "location.href= '<?= Url::to([$this->context->id . '/index']) ?>'">
                <i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i>刷新
            </button>
        </form>
    </div>
    <xblock>
        <button class = "layui-btn" onclick = "x_admin_show('添加用户','<?= Url::to([$this->context->id . '/create']) ?>')">
            <i class = "layui-icon"></i>添加
        </button>
        <span class = "x-right" style = "line-height:30px">共有数据：<span class = "count_num"><?= $pagination->totalCount ?></span> 条 ( <?= $pagination->getPageCount() ?> 页 )</span>
    </xblock>
    <div>
        <table class = "layui-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>员工姓名</th>
                <th>员工PID</th>
                <th>员工职位</th>
                <th>部门编号</th>
                <th>操作</th>
            </thead>
            <tbody>
            <?php foreach ($dataProvider as $list): ?>
                <tr>
                    <td><?= $list['id'] ?></td>
                    <td><?= $list['name'] ?></td>
                    <td><?= $list['pid'] ?></td>
                    <td><?= ArrayHelper::getValue(UserInfo::$typeArr, $list['type'], '') ?></td>
                    <td><?= $list['department_number'] ?></td>
                    <td class = "td-manage">
                        <button type = "button" class = "layui-btn layui-btn-xs" onclick = "x_admin_show('详情','<?= Url::to([$this->context->id . '/detail', 'id' => $list['id']]) ?>')">
                            <i class = "layui-icon">&#xe63c;</i>详情
                        </button>
                        <button class = "layui-btn layui-btn-xs layui-btn-normal" onclick = "x_admin_show('编辑','<?= Url::to([$this->context->id . '/update', 'id' => $list['id']]) ?>')">
                            <i class = "layui-icon">&#xe642;</i>编辑
                        </button>
                        <?php if ($uid == 1): ?>
                            <button class = "layui-btn layui-btn-xs layui-btn-warm" onclick = "x_admin_show('编辑角色','<?= Url::to([$this->context->id . '/change-role', 'uid' => $list['id']]) ?>')">
                                <i class = "layui-icon">&#xe642;</i>编辑角色
                            </button>
                            <button class = "layui-btn layui-btn-xs layui-bg-cyan" onclick = "x_admin_show('修改密码','<?= Url::to([$this->context->id . '/update-password', 'id' => $list['id']]) ?>',500,400)">
                                <i class = "layui-icon">&#xe642;</i>修改密码
                            </button>
                            <button class = "layui-btn layui-btn-xs" onclick = "x_admin_show('添加密码','<?= Url::to([$this->context->id . '/create-password', 'id' => $list['id']]) ?>',500,400)">
                                <i class = "layui-icon"></i>添加密码
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($dataProvider)): ?>
        <p class = "text-empty">-- 暂无数据 --</p>
    <?php endif; ?>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>
<?php $this->beginBlock('footer') ?>
<?php $this->endBlock() ?>

