<?php

use backend\models\LenelEmp;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use backend\models\LenelReader;
use backend\models\LenelAccesslvl;
use backend\widgets\SearchActiveForm;
use kartik\select2\Select2;
/* @var $request yii\web\View */

?>
<div class="x-body">
    <div class="layui-row">
        <?php $form = SearchActiveForm::begin([
               'action' => [ 'history-report' ],
               'method' => 'get',
               'id' => 'search-form',
           ]); ?>
           <div class="layui-input-inline">
            <?=
                Select2::widget([
                    'name'          => "select2",
                    'data'          => LenelReader::getReader(),
                    'value'         => !empty($doorArr) ? $doorArr : [],
                    'options'       => [
                        'multiple'    => true,
                        'placeholder' => 'Door',
                        'id'          => 'selectdoor',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width'      => '220px',
                    ],
                ]);
            ?>
            </div>
            <input class="layui-input" type="hidden" name="doorstr" id="doorstr" value="" autocomplete="off">
            <input class="layui-input" name="ssno" value="<?php echo ArrayHelper::getValue($request, 'ssno') ?>" autocomplete="off" placeholder="Work ID/PID">
            <input class="layui-input" name="midname" value="<?php echo ArrayHelper::getValue($request, 'midname') ?>" autocomplete="off" placeholder="Chinese Name">
            <div class = "layui-input-inline">
                 <?= Html::dropDownList('DESCRIPT', ArrayHelper::getValue($request, 'DESCRIPT'), ArrayHelper::merge(['' => 'DESCRIPT'], LenelAccesslvl::getDESCRIPT()), ['name' => 'DESCRIPT']); ?>
             </div>
            <input class="layui-input" name="name" value="<?php echo ArrayHelper::getValue($request, 'name') ?>" autocomplete="off" placeholder="Department">
            <input class="layui-input" placeholder="开始时间" name="start_time" id="start_time" value="<?= !empty($request['start_time']) ? date('Y-m-d H:i:s',strtotime($request['start_time'])) : '' ?>" autocomplete="off">
            <input class="layui-input" placeholder="截止时间" name="end_time" id="end_time" value="<?= !empty($request['end_time']) ? date('Y-m-d H:i:s',strtotime($request['end_time'])) : '' ?>" autocomplete="off">
            <button class="layui-btn" lay-submit="" lay-filter="sreach"><i class="layui-icon">&#xe615;</i>查询</button>
            <button class="layui-btn" type="button" onclick="location.href= '<?= Url::to([$this->context->id . '/history-report']) ?>'">
                <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>刷新
            </button>
        <?php SearchActiveForm::end(); ?>
    </div>
    <xblock>
        <button class="layui-btn layui-bg-black" onclick="export_excel()"><i class="layui-icon">&#xe62f;</i>导出数据</button>
        <span class="x-right" style="line-height:30px">共有数据：<span class="count_num"><?= $totalCount ?></span> 条 ( <?= $pagination->getPageCount() ?> 页 )</span>
    </xblock>
    <table class="layui-table">
        <thead>
        <tr>
            <th>Time</th>
            <th>Door NO.</th>
            <th>Work ID/PID</th>
            <th>Chinese Name</th>
            <th>CARD NO.1</th>
            <th>CARD NO.2</th>
            <th>Department</th>
            <th>ContactName</th>
        </thead>
        <tbody>
        <?php foreach($data as $list): ?>
            <tr>
                <td><?= date('Y-m-d H:i:s', strtotime($list['EVENT_TIME_UTC'])+28800) ?></td>
                <td><?= $list['READERDESC'] ?></td>
                <td><?= $list['SSNO'] ?></td>
                <td><?= $list['MIDNAME'] ?></td>
                <td><?= (new LenelEmp())->getCardNoFirst($list['ID'],1) ?></td>
                <td><?= (new LenelEmp())->getCardNoFirst($list['ID'],2) ?></td>
                <td><?= $list['NAME'] ?></td>
                <td><?= $list['DIVISIONNAME'] ?></td>
         </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>
<?php $this->beginBlock('footer') ?>
<script>
    //$(".layui-unselect").hide();
    function export_excel() {
        var fromData = $('#search-form').serialize();
        layui.use(['jquery', 'layer'], function () {
            var $ = layui.jquery;
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '<?= Url::to([$this->context->id . '/history-export'])?>?' + fromData,
                success: function (res) {
                    if (res.status === 200) {
                        LAY_EXCEL.exportExcel(res.data, res.msg, 'xlsx');
                    }else {
                        layer.msg(res.msg);
                    }
                }
            });
        });
    }

    $(document).ready(function () {
       $(".select2").next().hide();
       $("#selectdoor").change(function () {
           var doorvalue = $("#selectdoor").val();
           $("#doorstr").val(doorvalue);
       });
    });
</script>
<?php $this->endBlock() ?>


