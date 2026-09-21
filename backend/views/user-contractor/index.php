<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use backend\models\UserContractor;
?>

<div class = "x-body">
    <div class = "layui-row">
        <form class = "layui-form layui-col-md12 x-so">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'name') ?>" autocomplete = "off" placeholder = "Name" name = "name">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'workid') ?>" autocomplete = "off" placeholder = "WorkId" name = "workid">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'depid') ?>" autocomplete = "off" placeholder = "DepId" name = "depid">
            <button class = "layui-btn" type = "submit"><i class = "layui-icon">&#xe615;</i></button>
            <button class = "layui-btn" type = "button" onclick = "location.href= '<?= Url::to([$this->context->id . '/index']) ?>'">
                <i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i>刷新
            </button>
        </form>
    </div>
    <xblock>
        <button class="layui-btn" onclick="x_admin_show('添加包商','<?= Url::to([$this->context->id . '/create']) ?>')"><i class="layui-icon"></i>添加包商</button>
        <button class="layui-btn layui-bg-black" onclick="export_excel()"><i class="layui-icon">&#xe62f;</i>导出人员</button>
        <?= Html::a('下载模板',Url::to([$this->context->id . '/download-template']), ['class' => 'layui-btn ']) ?>
        <button class="layui-btn layui-btn-danger" onclick="x_admin_show('导入数据','<?=  Url::to([$this->context->id . '/import']) ?>')">导入数据</button>
        <?= Html::a('下载EBdate模板',Url::to([$this->context->id . '/download-template-delay']), ['class' => 'layui-btn ']) ?>
        <button class="layui-btn layui-btn-danger" onclick="x_admin_show('导入EBdate数据','<?=  Url::to([$this->context->id . '/import-delay']) ?>')">导入EBdate数据</button>
        <span class = "x-right" style = "line-height:30px">共有数据：<span class = "count_num"><?= $pagination->totalCount ?></span> 条 ( <?= $pagination->getPageCount() ?> 页 )</span>
    </xblock>
    <table class = "layui-table">
        <thead>
        <tr>
             <th>ID</th>
             <th>EBarcode</th>
             <th>Name</th>
             <th>WorkID</th>
             <th>DepID</th>
             <th>CompID</th>
             <th>ContactName</th>
             <th>ContactDepart</th>
             <th>人脸信息</th>
             <th>操作</th>
        </thead>
        <tbody>
        <?php foreach ($dataProvider as $list): ?>
            <tr>
                  <td><?= $list['id'] ?></td>
                  <td><?= $list['ebarcode'] ?></td>
                  <td><?= $list['name'] ?></td>
                  <td><?= $list['workid'] ?></td>
                  <td><?= $list['depid'] ?></td>
                  <td><?= $list['compid'] ?></td>
                  <td><?= $list['contactname'] ?></td>
                  <td><?= $list['contactdepart'] ?></td>
                  <td><?= ArrayHelper::getValue(UserContractor::$isDelArr, $list['is_del'], '') ?></td>
                <td>
                    <button class="layui-btn layui-btn-xs layui-btn-normal" onclick="x_admin_show('人员详情','<?= Url::to([$this->context->id . '/view', 'id' => $list['id']]) ?>')">人员详情</button>
                    <button class="layui-btn layui-btn-xs layui-bg-cyan" onclick="x_admin_show('人员编辑','<?= Url::to([$this->context->id . '/update', 'id' => $list['id']]) ?>')"><i class="layui-icon">&#xe642;</i>人员编辑</button>
                    <button class="layui-btn layui-btn-xs"  onclick="del(this, '<?= $list['id'] ?>', '<?= Url::to(['user-contractor/del']) ?>')" ><i class="layui-icon">&#xe640;</i>删除</button>
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

