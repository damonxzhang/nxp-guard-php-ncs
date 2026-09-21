<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class = "x-body">
    <div class = "layui-row">
        <form class = "layui-form layui-col-md12 x-so">
        <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'user_pid') ?>" autocomplete = "off" placeholder = "员工PID" name = "user_pid">
        <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'work_id') ?>" autocomplete = "off" placeholder = "包商ID" name = "work_id">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'user_name') ?>" autocomplete = "off" placeholder = "员工姓名" name = "user_name">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'user_wbi_id') ?>" autocomplete = "off" placeholder = "员工WbiId" name = "user_wbi_id">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'leader_name') ?>" autocomplete = "off" placeholder = "经理姓名" name = "leader_name">
            <button class = "layui-btn" type = "submit"><i class = "layui-icon">&#xe615;</i></button>
            <button class = "layui-btn" type = "button" onclick = "location.href= '<?= Url::to([$this->context->id . '/index']) ?>'">
                <i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i>刷新
            </button>
        </form>
    </div>
    <xblock>
        <button class="layui-btn" onclick="x_admin_show('添加人员','<?= Url::to([$this->context->id . '/create']) ?>')"><i class="layui-icon"></i>添加人员</button>
        <button class="layui-btn layui-bg-cyan" onclick="x_admin_show('一键同步','<?=  Url::to([$this->context->id . '/oneupdate', 'user_id' => Yii::$app->user->getId()]) ?>')">一键同步</button>
        <button class="layui-btn layui-bg-cyan" onclick="x_admin_show('导入数据','<?=  Url::to([$this->context->id . '/import', 'user_id' => Yii::$app->user->getId()]) ?>')">导入数据</button>
        <?= Html::a('下载模板',Url::to([$this->context->id . '/download-template']), ['class' => 'layui-btn ']) ?>
        <span class = "x-right" style = "line-height:30px">共有数据：<span class = "count_num"><?= $pagination->totalCount ?></span> 条 ( <?= $pagination->getPageCount() ?> 页 )</span>
    </xblock>
    <table class = "layui-table">
        <thead>
        <tr>
             <th>ID</th>
             <th>员工PID</th>
             <th>包商WORKId</th>
             <th>员工姓名</th>
             <th>DepID</th>
             <th>Badge</th>
             <th>Leader PID</th>
             <th>Leader Name</th>
             <th>操作</th>
        </thead>
        <tbody>
        <?php foreach ($dataProvider as $list): ?>
            <tr>
                  <td><?= $list['id'] ?></td>
                  <td><?= $list['user_pid'] ?></td>
                  <td><?= $list['work_id'] ?></td>
                  <td><?= $list['user_name'] ?></td>
                  <td><?= $list['depid'] ?></td>
                  <td><?= $list['badge'] ?></td>
                  <td><?= $list['leader_pid'] ?></td>
                  <td><?= $list['leader_name'] ?></td>
                <td>
                    <button class="layui-btn layui-btn-xs layui-bg-cyan" onclick="x_admin_show('人员编辑','<?= Url::to([$this->context->id . '/update', 'id' => $list['id']]) ?>')"><i class="layui-icon">&#xe642;</i>人员编辑</button>
                    <button class="layui-btn layui-btn-xs"  onclick="del(this, '<?= $list['id'] ?>', '<?= Url::to(['exterior-relation/del']) ?>')" ><i class="layui-icon">&#xe640;</i>删除</button>
                </td>
             </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>

<?php $this->beginBlock('footer') ?>
<script>
    function export_excel(){
        layui.use(['jquery', 'layer'], function() {
            var $ = layui.jquery;
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '<?= Url::to([$this->context->id . '/export'])?>',
                success: function(res) {
                    LAY_EXCEL.exportExcel(res.data, res.msg, 'xlsx');
                }
            });
        });
    }
</script>
<?php $this->endBlock() ?>

