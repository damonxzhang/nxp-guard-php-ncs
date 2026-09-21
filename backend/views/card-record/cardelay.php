<?php

use backend\models\ExteriorUser;
use backend\models\ExteriorUserRecord;
use backend\models\LenelEmp;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\CardRecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class = "x-body">
    <table class = "layui-table">
        <thead>
        <tr>
             <th>ID</th>
             <th>申请人</th>
             <th>车卡卡号</th>
             <th>入场时间</th>
             <th>出厂时间</th>
             <th>操作</th>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $list): ?>
            <tr <?php if ($list['status'] == 2 && ($list['entrance_time'] + 60*60*24) < time() ): ?> style="font-weight:bold;color: red;" <?php endif; ?>>
                  <td><?= $list['id'] ?></td>
                  <td><?= (new LenelEmp())->getMidName($list['create_user']) ?></td>
                  <td><?= (new LenelEmp())->getLastName($list['car_card']) ?></td>
                  <td><?= $list['entrance_time'] ? date('Y-m-d H:i:s', $list['entrance_time']) : '' ?></td>
                  <td><?= $list['get_out_time'] ? date('Y-m-d H:i:s', $list['get_out_time']) :'' ?></td>
                  <td>
                      <button class="layui-btn layui-btn-xs layui-bg-green" onclick="x_admin_show('详情','<?= Url::to([$this->context->id . '/carview', 'id' => $list['id']]) ?>')">详情</button>
                 </td>
             </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>

