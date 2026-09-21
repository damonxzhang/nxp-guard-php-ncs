<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\widgets\SearchActiveForm;
use backend\models\LenelEmp;
use yii\helpers\Html;
use backend\models\LenelBadgeTyp;
use backend\models\LenelLocation;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\OneTimeCardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="x-body">
 <div class="layui-row">
      <?php $form = SearchActiveForm::begin([
           'action' => [ 'cardholder' ],
           'method' => 'get',
           'id' => 'search-form',
       ]); ?>
        <input class="layui-input" name="ssno" value="<?php echo ArrayHelper::getValue($request, 'ssno') ?>" autocomplete="off" placeholder="Work ID/PID">
        <input class="layui-input" name="midname" value="<?php echo ArrayHelper::getValue($request, 'midname') ?>" autocomplete="off" placeholder="Middle Name">
        <input class="layui-input" name="depart_name" value="<?php echo ArrayHelper::getValue($request, 'depart_name') ?>" autocomplete="off" placeholder="Department">
        <div class = "layui-input-inline">
            <?= Html::dropDownList('badge_type', ArrayHelper::getValue($request, 'badge_type'), ArrayHelper::merge(['' => 'BADGE TYPE'], LenelBadgeTyp::getStatus()), ['name' => 'badge_type']); ?>
        </div>
        
        <div class="layui-input-inline">
            <?=
                Select2::widget([
                    'name'          => "select2",
                    'data'          => LenelLocation::getStatus(),
                    'value'         => !empty($locationid) ? $locationid : [],
                    'options'       => [
                        'multiple'    => true,
                        'placeholder' => 'location',
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

        <button class="layui-btn" lay-submit="" lay-filter="sreach"><i class="layui-icon">&#xe615;</i>查询</button>
        <button class="layui-btn" type="button" onclick="location.href= '<?= Url::to([$this->context->id . '/cardholder']) ?>'">
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
            <th>Work ID/PID</th>
            <th>Middle Name</th>
            <th>ADDR1</th>
            <th>CARD NO.1</th>
            <th>CARD NO.2</th>
            <th>Department</th>
            <th>Location</th>
            <th>DepName</th>
            <th>EBDate</th>
            <th>Vendor Name</th>
            <th>ContactName</th>
            <th>IDCard</th>
            <th>BADGE TYPE</th>
        </thead>
        <tbody>
        <?php foreach($data as $list): ?>
            <tr>
                 <td><?= $list['SSNO'] ?></td>
                 <td><?= $list['MIDNAME'] ?></td>
                 <td><?= $list['ADDR1'] ?></td>
                 <td><?= (new LenelEmp())->getCardNoFirst($list['ID'],1) ?></td>
                 <td><?= (new LenelEmp())->getCardNoFirst($list['ID'],2) ?></td>
                 <td><?= $list['DEPART_NAME'] ?></td>
                 <td><?= $list['LOCATION'] ?></td>
                 <td><?= $list['TITLE_NAME'] ?></td>
                 <td><?= $list['DEACTIVATE'] ?></td>
                 <td><?= $list['BUILDING_NAME'] ?></td>
                 <td><?= $list['DIVISION_NAME'] ?></td>
                 <td><?= $list['OPHONE'] ?></td>
                 <td><?= $list['BADGE_NAME'] ?></td>
         </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>
<?php $this->beginBlock('footer') ?>
<script>

    $(document).ready(function() {
		var wbemServices = GetObject("winmgmts://./root/onguard");
		
		// run the query. this call returns a collection of a single badge (assuming no issuecodes are used).
		var badgeSet = wbemServices.ExecQuery("select * from Lnl_Badge where ID=202932");
		
		// for each badge... (should only be one)
		for ( var e = new Enumerator( badgeSet ); !e.atEnd(); e.moveNext() )
		{
			// Change the embossed field to 3 - just to validate that 
			// the badge was modified by the script.  then modify the pin to 5552
			// and the deactivate date to 12/01/10
			var badge = e.item();
			badge.Embossed = 3;
			badge.Pin = "5552";
			badge.Deactivate = "20101201000000.000000-300"
			
			// tell DataConduIT to actually modify the badge
			badge.Put_();
			WScript.Echo("Pin updated!");
		}
		WScript.Echo("Script complete.");
		
	});
	
    function export_excel() {
        var fromData = $('#search-form').serialize();
        layui.use(['jquery', 'layer'], function () {
            var $ = layui.jquery;
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '<?= Url::to([$this->context->id . '/cardholder-export'])?>?' + fromData,
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




