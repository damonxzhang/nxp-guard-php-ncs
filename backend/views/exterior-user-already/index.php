<?php

use yii\helpers\Html;
use backend\widgets\LayuiGridView;
use backend\widgets\SearchActiveForm;
use backend\models\LevelDept;
use backend\models\LenelEmp;
use backend\models\ExteriorUser;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use backend\models\ExteriorUserRecord;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\ExteriorUserRecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="x-body">
    <div class="layui-row">
        <?php $form = SearchActiveForm::begin([
               'action' => [ 'index' ],
               'method' => 'get',
               'id' => 'search-form',
           ]); ?>
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'user_card_name') ?>" autocomplete = "off" placeholder = "临时卡号" name = "user_card_name">
            <div class = "layui-input-inline">
                 <?= Html::dropDownList('badge_type', ArrayHelper::getValue($request, 'badge_type'), ArrayHelper::merge(['' => '卡类型'], [1=>'临时卡', 2=>'车卡', 3=>'临时包商']), ['name' => 'badge_type']); ?>
             </div>
             <div class = "layui-input-inline">
                 <?= Html::dropDownList('factory', ArrayHelper::getValue($request, 'factory'), ArrayHelper::merge(['' => '厂区'], [1=>'ATTJ', 2=>'TC', 3=>'IWH']), ['name' => 'factory']); ?>
             </div>
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'txterior_user') ?>" autocomplete = "off" placeholder = "外部人员姓名" name = "txterior_user">
            <input class = "layui-input" placeholder = "进厂开始日期" name = "start_time" id = "start_time" value = "<?= !empty($request['start_time']) ? date('Y-m-d H:i:s', strtotime($request['start_time'])) : '' ?>" autocomplete = "off">
            <input class = "layui-input" placeholder = "进厂截止日期" name = "end_time" id = "end_time" value = "<?= !empty($request['end_time']) ? date('Y-m-d H:i:s', strtotime($request['end_time'])) : '' ?>" autocomplete = "off">
            <button class = "layui-btn" lay-submit = "" lay-filter = "sreach"><i class = "layui-icon">&#xe615;</i>查询</button>
            <button class = "layui-btn" type = "button" onclick = "location.href= '<?= Url::to([$this->context->id . '/index']) ?>'">
                <i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i>刷新
            </button>
            <?php SearchActiveForm::end(); ?>
    </div>
    <xblock>
        <button class="layui-btn layui-bg-black" onclick="export_excel()"><i class="layui-icon">&#xe62f;</i>导出数据</button>
        <span class = "x-right" style = "line-height:30px"></span>
    </xblock>
    <?= LayuiGridView::widget([
        'dataProvider' => $data,
        'columns' => [
            [ 'class' => 'yii\grid\SerialColumn' ],
            [
                'attribute' => 'exterior_user',
                'value'     => function($row) {
                    $model = ExteriorUser::findOne($row->exterior_user);
                    return $model ? $model->name : '';
                }
            ],
            [
                'label' => '申请人',
                'value' => function($row) {
                    $model = LenelEmp::findOne($row->create_user);
                    return $model ? $model->MIDNAME : '';
                }
            ],
            [
                'label' => '临时卡号',
                'value' => function($row) {
                    return (new LenelEmp())->getLastName($row->user_card);
                }
            ],
            [
                'label' => '车卡卡号',
                'value' => function($row) {
                    return (new LenelEmp())->getLastName($row->car_card);
                }
            ],
            [
                'label' => '接待人',
                'value' => function($row) {
                    $model = LenelEmp::findOne($row->reception_user);
                    return $model ? $model->MIDNAME : '';
                }
            ],
            [
                'label' => '接待人部门',
                'value' => function($row) {
                    $department = (new LevelDept())->departmentByEmpId($row->reception_user);
                    return $department ? $department : '';
                }
            ],
            [
                'attribute' => 'real_entrance_time',
                'value' => function($row){
                    return $row->real_entrance_time ? date('Y年m月d日 H时i分s秒', $row->real_entrance_time) : '';
                }
            ],
            [
                'attribute' => 'real_get_out_time',
                'value' => function($row){
                    return $row->real_get_out_time ? date('Y年m月d日 H时i分s秒', $row->real_get_out_time) : '';
                }
            ],
            [
                'label' => '出入状态',
                'format' => 'raw',
                'value' => function($row){
                    return ArrayHelper::getValue(ExteriorUserRecord::$statusListData, $row->status,'');
                }
            ],
            [
                'label' => '车辆信息',
                'format' => 'raw',
                'value' => function($row){
                    return $row->vehicle ? $row->vehicle : '';
                }
            ],
            [
                'label' => '厂区',
                'format' => 'raw',
                'value' => function($row){
                    return ArrayHelper::getValue(ExteriorUser::$factoryList, $row->factory,'');
                }
            ],
            [
                'class'    => 'backend\grids\LayuiActionColumn',
                'template' => '{view}',
                'buttons'  => [
                    'view'           => function($url, $row) {
                        return Html::a('详情', '#', [ 'class' => 'layui-btn layui-btn-xs layui-btn-green', 'onclick' => "x_admin_show('详情','" . $url . "')" ]);
                    },
                ],
            ],
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

