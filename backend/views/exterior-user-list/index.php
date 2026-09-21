
<?php
use backend\models\ExteriorUser;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

?>

<div class = "x-body">
    <div class = "layui-row">
        <form class = "layui-form layui-col-md12 x-so">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'name') ?>" autocomplete = "off" placeholder = "姓名" name = "name">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'phone') ?>" autocomplete = "off" placeholder = "手机号" name = "phone">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'idcard') ?>" autocomplete = "off" placeholder = "身份证号" name = "idcard">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'company') ?>" autocomplete = "off" placeholder = "公司名称" name = "company">
            <button class = "layui-btn" type = "submit"><i class = "layui-icon">&#xe615;</i></button>
            <button class = "layui-btn" type = "button" onclick = "location.href= '<?= Url::to([$this->context->id . '/index']) ?>'">
                <i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i>刷新
            </button>
        </form>
    </div>
    <table class = "layui-table">
        <thead>
        <tr>
             <th>ID</th>
             <th>姓名</th>
             <th>手机号</th>
             <th>身份证</th>
             <th>公司名称</th>
             <th>车牌号</th>
             <th>厂区</th>
             <th>申请人PID</th>
             <th>申请人WorkID</th>
             <th>预计进厂时间</th>
             <th>预计出厂时间</th>
             <th>添加时间</th>
             <th>操作</th>
        </thead>
        <tbody>
        <?php foreach ($dataProvider as $list): ?>
            <tr>
                  <td><?= $list['id'] ?></td>
                  <td><?= $list['name'] ?></td>
                  <td><?= $list['phone'] ?></td>
                  <td><?= $list['idcard'] ?></td>
                  <td><?= $list['company'] ?></td>
                  <td><?= $list['vehicle'] ?></td>
                  <td><?= ArrayHelper::getValue(ExteriorUser::$factoryList, $list['factory'],'') ?></td>
                  <td><?= $list['pid'] ?></td>
                  <td><?= $list['workid'] ?></td>
                  <td><?= $list['get_enter_time'] ?></td>
                  <td><?= $list['get_out_time'] ?></td>
                  <td><?= date('Y-m-d H:i:s',$list['create_time']) ?></td>
                  <td>
                    <button class="layui-btn layui-btn-xs layui-bg-cyan" onclick="x_admin_show('人员编辑','<?= Url::to([$this->context->id . '/update', 'id' => $list['id']]) ?>')"><i class="layui-icon">&#xe642;</i>人员编辑</button>
                </td>
             </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>
