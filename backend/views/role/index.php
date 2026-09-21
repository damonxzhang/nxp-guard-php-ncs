<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
?>

<div class="x-body">
    <xblock>
        <button class="layui-btn" onclick="x_admin_show('添加','<?=  Url::to(['role/create']) ?>')"><i class="layui-icon"></i>添加</button>
        <span class="x-right" style="line-height:30px">共有数据：<span class="count_num"><?= $pagination->totalCount ?></span> 条 ( <?= $pagination->getPageCount() ?> 页 )</span>
    </xblock>
    <table class="layui-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>角色名</th>
            <th>描述</th>
            <th>操作</th>
        </thead>
        <tbody>
        <?php foreach ($dataProvider as $list): ?>
        <tr>
            <td><?= $list['id'] ?></td>
            <td><?= $list['name'] ?></td>
            <td><?= $list['description'] ?></td>
            <td class="td-manage">
                <button class="layui-btn layui-btn-xs"  onclick="x_admin_show('编辑','<?= Url::to(['role/update', 'id' => $list['id']]) ?>')"><i class="layui-icon">&#xe642;</i>编辑</button>
                <button class="layui-btn layui-btn-xs"  onclick="del(this, '<?= $list['id'] ?>', '<?= Url::to(['role/del']) ?>')" ><i class="layui-icon">&#xe640;</i>删除</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>
