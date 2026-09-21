<?php

use yii\helpers\Html;
use backend\widgets\LayuiGridView;
use backend\models\LenelEmp;
use yii\helpers\Url;
use backend\widgets\LayuiActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\ExteriorUserCar;
use backend\models\ExteriorUser;
use backend\widgets\SearchActiveForm;
use backend\models\LevelDept;
use backend\models\ExteriorUserRecord;
?>
<div class="x-body">
    <div class="layui-row">
        <?php $form = SearchActiveForm::begin([
               'action' => [ 'history' ],
               'method' => 'get',
               'id' => 'search-form',
           ]); ?>
           
            <input class = "layui-input" placeholder = "车牌号" name = "vehicle"  value = "<?= !empty($request['vehicle']) ? $request['vehicle'] : '' ?>" autocomplete = "off">
            <input class = "layui-input" placeholder = "进厂开始日期" name = "start_time" id = "start_time" value = "<?= !empty($request['start_time']) ? date('Y-m-d H:i:s', strtotime($request['start_time'])) : '' ?>" autocomplete = "off">
            <input class = "layui-input" placeholder = "进厂截止日期" name = "end_time" id = "end_time" value = "<?= !empty($request['end_time']) ? date('Y-m-d H:i:s', strtotime($request['end_time'])) : '' ?>" autocomplete = "off">
            <button class = "layui-btn" lay-submit = "" lay-filter = "sreach"><i class = "layui-icon">&#xe615;</i>查询</button>
            <button class = "layui-btn" type = "button" onclick = "location.href= '<?= Url::to([$this->context->id . '/history']) ?>'">
                <i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i>刷新
            </button>
            <?php SearchActiveForm::end(); ?>
    </div>
    <xblock>
        <!-- <button class="layui-btn layui-bg-black" onclick="export_excel()"><i class="layui-icon">&#xe62f;</i>导出数据</button> -->
        <span class = "x-right" style = "line-height:30px"></span>
    </xblock>
    <?= LayuiGridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => [
            [
                'label' => '申请人',
                'value' => function ($row) {
                    $model = LenelEmp::findOne($row->create_user);
                    return $model ? $model->MIDNAME : '';
                }
            ],
            [
                'attribute' => 'PID',
                'value'     => function ($row) {
                    return $row->pid;
                }
            ],
            [
                'attribute' => '联系方式',
                'value'     => function ($row) {
                    return $row->phone;
                }
            ],
            [
                'attribute' => '状态',
                'value'     => function ($row) {
                    return ArrayHelper::getValue(ExteriorUserCar::$statusList, $row->status,'');
                }
            ],
            [
                'attribute' => '实际进厂时间',
                'value'     => function ($row) {
                    return $row->entrance_time ? date('Y年m月d日 H时i分', $row->entrance_time) : '';
                }
            ],
            [
                'attribute' => '实际出厂时间',
                'value'     => function ($row) {
                    return $row->get_out_time ? date('Y年m月d日 H时i分', $row->get_out_time) : '';
                }
            ],
            [
                'label' => '车辆临时卡',
                'value' => function ($row) {
                    $model = LenelEmp::findOne($row->car_card);
                    return $model ? $model->LASTNAME : '';
                }
            ],
            [
                'label'  => '车牌号',
                'format' => 'raw',
                'value'  => function ($row) {
                    return $row->vehicle;
                }
            ]
        ],
    ]); ?>
</div>

<?php $this->beginBlock('footer') ?>
<script>
    function export_excel(){
        var fromData  = $('#search-form').serialize();
        layui.use(['jquery', 'layer'], function() {
            var $ = layui.jquery;
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '<?= Url::to([$this->context->id . '/export'])?>?'+fromData,
                success: function(res) {
                    LAY_EXCEL.exportExcel(res.data, res.msg, 'xlsx');
                }
            });
        });
    }
</script>
<?php $this->endBlock() ?>



