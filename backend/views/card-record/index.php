<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use backend\models\ExteriorUser;
use backend\models\UserInfo;
use yii\helpers\Url;
use backend\models\ExteriorUserRecord;
use backend\models\LenelEmp;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\CardRecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>


<div class = "x-body">
      <div class = "layui-row">
        <form class = "layui-form layui-col-md12 x-so">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'user_card_name') ?>" autocomplete = "off" placeholder = "临时卡卡号" name = "user_card_name">
            <div class = "layui-input-inline">
                 <?= Html::dropDownList('badge_type', ArrayHelper::getValue($request, 'badge_type'), ArrayHelper::merge(['' => '卡类型'], [1=>'临时卡', 2=>'车卡', 3=>'临时包商']), ['name' => 'badge_type']); ?>
            </div>
            <input class = "layui-input" placeholder = "进厂开始日期" name = "start_time" id = "start_time" value = "<?= !empty($request['start_time']) ? date('Y-m-d H:i:s', strtotime($request['start_time'])) : '' ?>" autocomplete = "off">
            <input class = "layui-input" placeholder = "进厂截止日期" name = "end_time" id = "end_time" value = "<?= !empty($request['end_time']) ? date('Y-m-d H:i:s', strtotime($request['end_time'])) : '' ?>" autocomplete = "off">
            <button class = "layui-btn" lay-submit = "" lay-filter = "sreach"><i class = "layui-icon">&#xe615;</i>查询</button>
            <button class = "layui-btn" type = "button" onclick = "location.href= '<?= Url::to([$this->context->id . '/index']) ?>'">
                <i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i>刷新
            </button>
        </form>
    </div>
    <table class = "layui-table">
        <thead>
        <tr>
             <th>ID</th>
             <th>申请人</th>
             <th>接待人</th>
             <th>来访人</th>
             <th>临时卡卡号</th>
             <th>车卡卡号</th>
             <th>入场时间</th>
             <th>出厂时间</th>
             <th>操作</th>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $list): ?>
            <tr <?php if ($list['status'] == ExteriorUserRecord::STATUS_ENTER && ($list['real_entrance_time'] + 60*60*24) < time() ): ?> style="font-weight:bold;color: red;" <?php endif; ?>>
                  <td><?= $list['id'] ?></td>
                  <td><?= (new LenelEmp())->getMidName($list['create_user']) ?></td>
                  <td><?= (new LenelEmp())->getMidName($list['reception_user']) ?></td>
                  <td><?= (new ExteriorUser())->getUserName($list['exterior_user']) ?></td>
                  <td><?= (new LenelEmp())->getLastName($list['user_card']) ?></td>
                  <td><?= (new LenelEmp())->getLastName($list['car_card']) ?></td>
                  <td><?= $list['real_entrance_time'] ? date('Y-m-d H:i:s', $list['real_entrance_time']) : '' ?></td>
                  <td><?= $list['real_get_out_time'] ? date('Y-m-d H:i:s', $list['real_get_out_time']) :'' ?></td>
                  <td>
                        <button class="layui-btn layui-btn-xs layui-bg-green" onclick="x_admin_show('详情','<?= Url::to([$this->context->id . '/view', 'id' => $list['id']]) ?>')">详情</button>
                 </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>

