
<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;

?>

<div class = "x-body">
    <div class = "layui-row">
        <form class = "layui-form layui-col-md12 x-so">
            <input class = "layui-input" value = "<?php echo ArrayHelper::getValue($request, 'name') ?>" autocomplete = "off" placeholder = "姓名" name = "name">
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
             <th>联系电话</th>
             <th>公司名称</th>
             <th>添加时间</th>
        </thead>
        <tbody>
        <?php foreach ($dataProvider as $list): ?>
            <tr>
                  <td><?= $list['id'] ?></td>
                  <td><?= $list['name'] ?></td>
                  <td><?= $list['phone'] ?></td>
                  <td><?= $list['company'] ?></td>
                  <td><?= date('Y-m-d H:i:s',$list['create_time']) ?></td>
             </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->render('@app/views/layouts/pagination', compact('pagination')) ?>
</div>
