<?php
use yii\helpers\Url;
?>
<div class="x-body">
    <xblock>
        <button class="layui-btn" onclick="x_admin_show('添加','<?=  Url::to([$this->context->id . '/submenu-create', 'menu_id' => $id]) ?>',500,400)"><i class="layui-icon"></i>添加</button>
        <span class="x-right" style="line-height:30px">共有数据：<span class="count_num"><?= $count ?></span> 条 </span>
    </xblock>
    <table class="layui-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>子菜单名</th>
            <th>路由</th>
            <th>排序</th>
            <th>状态</th>
            <th>创建时间</th>
            <th>操作</th>
        </thead>
        <tbody>
        <?php foreach ($models as $list): ?>
            <tr>
                <td><?= $list['id'] ?></td>
                <td><?= $list['route_name'] ?></td>
                <td><?= $list['route'] ?></td>
                <td><?= $list['sort'] ?></td>
                <td><?= $list['is_show'] == 1 ? '显示' : '隐藏' ?></td>
                <td><?= date('Y-m-d H:i:s',$list['create_time']) ?></td>
                <td class="td-manage">
                    <button class="layui-btn layui-btn-xs"  onclick="x_admin_show('子路由','<?= Url::to([$this->context->id . '/route', 'id' => $list['id']])?>')"><i class="iconfont iconjiagou" ></i>子路由</button>
                    <button class="layui-btn layui-btn-xs"  onclick="x_admin_show('编辑','<?= Url::to([$this->context->id . '/submenu-update', 'id' => $list['id']])?>',500,400)"><i class="layui-icon">&#xe642;</i>编辑</button>
                    <button class="layui-btn layui-btn-xs"  onclick="del(this,'<?= $list['id'] ?>', '<?= Url::to([$this->context->id . '/submenu-del']) ?>')" ><i class="layui-icon">&#xe640;</i>删除</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>