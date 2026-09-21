<?php
use yii\helpers\Url;
use yii\helpers\Html;
use backend\models\ExteriorUser;
use backend\widgets\SearchActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\ExteriorUserRecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<div class="x-body">
    <div class="layui-row">
        <?php $form = SearchActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>
        <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'name') ?>" autocomplete = "off" placeholder = "姓名" name = "name">
        <?= Html::submitButton('<i class="layui-icon"></i>查询', ['class' => 'layui-btn']) ?>
        <?= Html::a('<i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>刷新','index', ['class' => 'layui-btn']) ?>
        <?php SearchActiveForm::end(); ?>
    </div>
    <p>
        <button class="layui-btn layui-bg-cyan" onclick="x_admin_show('导入数据','<?=  Url::to([$this->context->id . '/import', 'user_id' => Yii::$app->user->getId()]) ?>')">导入数据</button>
        <?= Html::a('下载模板',Url::to([$this->context->id . '/download-template']), ['class' => 'layui-btn ']) ?>
    </p>
    <table class = "layui-table">
        <thead>
        <tr>
             <th>ID</th>
             <th>外部人员</th>
             <th>联系电话</th>
             <th>公司名称</th>
             <th>来访原因</th>
             <th>厂区</th>
             <th>预计出厂时间</th>
        </thead>
        <tbody>
        <?php foreach ($dataProvider as $list): ?>
            <tr>
                  <td><?= $list['id'] ?></td>
                  <td><?= $list['name'] ?></td>
                  <td><?= $list['phone'] ?></td>
                  <td><?= $list['company'] ?></td>
                  <td><?= $list['purpose'] ?></td>
                  <td><?= ArrayHelper::getValue(ExteriorUser::$factoryList, $list['factory'], '') ?></td>
                  <td><?= $list['get_out_time'] ?></td>
             </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>

