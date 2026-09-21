<?php

use yii\helpers\Html;
use backend\widgets\SearchActiveForm;
use yii\helpers\ArrayHelper;

?>

<div class="x-body">
    <div class="layui-row">
        <?php $form = SearchActiveForm::begin([
            'action' => ['import-success'],
            'method' => 'get',
        ]); ?>
        <input class="layui-input" value="<?php echo ArrayHelper::getValue($request, 'name') ?>" autocomplete="off" placeholder="承包商姓名" name="name">
        <?= Html::submitButton('<i class="layui-icon"></i>查询', ['class' => 'layui-btn']) ?>
        <?= Html::a('<i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>刷新', 'import-success', ['class' => 'layui-btn']) ?>
        <?php SearchActiveForm::end(); ?>
    </div>
    <xblock>
        <span class="x-right" style="line-height:30px">共有数据：<span class="count_num"><?= $pagination->totalCount ?></span> 条 ( <?= $pagination->getPageCount() ?> 页 )</span>
    </xblock>
    <table class="layui-table">
        <thead>
        <tr>
             <th>ID</th>
             <th>承包商姓名</th>
             <th>承包商身份证号</th>
             <th>承包商联系方式</th>
             <th>承包商公司名称</th>
             <th>来访原因</th>
             <th>计划入厂日期</th>
             <th>计划出厂日期</th>
             <th>正式员工/包商</th>
             <th>对接员工PID</th>
             <th>对接授权包商WorkId</th>
             <th>ATTJ</th>
             <th>TC</th>
             <th>IWH</th>
             <th>添加时间</th>
        </thead>
        <tbody>
        <?php foreach($dataProvider as $list): ?>
            <tr>
              <td><?= $list['id'] ?></td>
              <td><?= $list['name'] ?></td>
              <td><?= $list['idcard'] ?></td>
              <td><?= $list['phone'] ?></td>
              <td><?= $list['company'] ?></td>
              <td><?= $list['purpose'] ?></td>
              <td><?= $list['get_enter_time'] ?></td>
              <td><?= $list['get_out_time'] ?></td>
              <td><?= $list['type'] ?></td>
              <td><?= $list['pid'] ?></td>
              <td><?= $list['workid'] ?></td>
              <td><?= $list['factory_attj'] ?></td>
              <td><?= $list['factory_tc'] ?></td>
              <td><?= $list['factory_iwh'] ?></td>
              <td><?= date('Y-m-d H:i:s',$list['create_time']) ?></td>
             </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>

