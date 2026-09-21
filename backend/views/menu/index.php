<?php
use yii\helpers\Url;
?>
<div class="x-body">
    <xblock>
        <button class="layui-btn" onclick="x_admin_show('添加','<?=  Url::to([$this->context->id . '/create']) ?>',500,400)"><i class="layui-icon"></i>添加</button>
        <span class="x-right" style="line-height:30px">共有数据：<span class="count_num"><?= $pagination->totalCount ?></span> 条 ( <?= $pagination->getPageCount() ?> 页 )</span>
    </xblock>
    <table class="layui-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>菜单名称</th>
            <th>菜单图标</th>
            <th>菜单排序</th>
            <th>菜单状态</th>
            <th>创建时间</th>
            <th>操作</th>
        </thead>
        <tbody>
        <?php foreach ($models as $list): ?>
            <tr>
                <td><?= $list['id'] ?></td>
                <td><?= $list['name'] ?></td>
                <td> <i class="layui-icon <?= $list['icon'] ?>"></i></td>
                <td><?= $list['sort'] ?></td>
                <td><?= $list['is_show'] == 1 ? '显示' : '隐藏' ?></td>
                <td><?= date('Y-m-d H:i:s', $list['create_time'] ) ?></td>
                <td class="td-manage">
                    <button class="layui-btn layui-btn-xs"  onclick="x_admin_show('子菜单','<?= Url::to([$this->context->id . '/submenu', 'id' => $list['id']])?>')"><i class="iconfont iconzicaidan"></i>子菜单</button>
                    <button class="layui-btn layui-btn-xs"  onclick="x_admin_show('编辑','<?= Url::to([$this->context->id . '/update', 'id' => $list['id']])?>',500,400)"><i class="layui-icon">&#xe642;</i>编辑</button>
                    <button class="layui-btn layui-btn-xs"   onclick="del(this,'<?= $list['id'] ?>', '<?= Url::to([$this->context->id . '/del']) ?>')"><i class="layui-icon">&#xe640;</i>删除</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>

<?php $this->beginBlock('footer') ?>
<?php $this->endBlock() ?>
