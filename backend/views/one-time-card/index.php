<?php

use backend\models\OneTimeCard;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\models\LenelEmp;
use backend\models\ExteriorUser;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\OneTimeCardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class = "x-body">
 <div class = "layui-row">
        <form class = "layui-form layui-col-md12 x-so">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'id') ?>" autocomplete = "off" placeholder = "Number" name = "id">
            <button class = "layui-btn" type = "submit"><i class = "layui-icon">&#xe615;</i></button>
            <button class = "layui-btn" type = "button" onclick = "location.href= '<?= Url::to([$this->context->id . '/index']) ?>'">
                <i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i>刷新
            </button>
        </form>
    </div>
    <xblock>
        <button class="layui-btn" onclick="x_admin_show('添加','<?=  Url::to([$this->context->id . '/create']) ?>',500,400)"><i class="layui-icon"></i>添加</button>
        <span class="x-right" style="line-height:30px">共有数据：<span class="count_num"><?= $pagination->totalCount ?></span> 条 ( <?= $pagination->getPageCount() ?> 页 )</span>
    </xblock>
    <table class = "layui-table">
        <thead>
        <tr>
             <th>ID</th>
             <th>卡号</th>
             <th>厂区</th>
             <th>门</th>
             <th>状态</th>
             <th>操作</th>
        </thead>
        <tbody>
        <?php foreach ($dataProvider as $list): ?>
            <tr>
                  <td><?= $list['id'] ?></td>
                  <td><?= (new LenelEmp())->getLastName($list['empid']) ?></td>
                  <td><?= ArrayHelper::getValue(ExteriorUser::$factoryList, $list['factory'], '') ?></td>
                  <td><?= ArrayHelper::getValue(OneTimeCard::$doorList, $list['door'], '') ?></td>
                  <td><?= ArrayHelper::getValue(OneTimeCard::$statusList, $list['status'], '') ?></td>
                  <td class="td-manage">
                    <button class="layui-btn layui-btn-xs"  onclick="x_admin_show('编辑','<?= Url::to([$this->context->id . '/update', 'id' => $list['id']])?>',500,400)"><i class="layui-icon">&#xe642;</i>编辑</button>
                  </td>
             </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>

