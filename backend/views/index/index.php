<?php

use yii\helpers\Url;

?>
<!-- 顶部开始 -->
<div class = "container" onload = "bind();">
    <div class = "left_open" style="margin-left: 20px;">
        <i title = "展开左侧栏" class = "iconfont">&#xe699;</i>
    </div>
    <ul class = "layui-nav right" lay-filter = "">
        <li class = "layui-nav-item to-index"><a href = "javascript:;" title = "刷新" onclick = "clear_cache()"><i class = "layui-icon layui-icon-refresh" style = "line-height:30px"></i></a></li>
        <li class = "layui-nav-item">
            <div id = "show_lable"></div>
        </li>
        <li class = "layui-nav-item">
            <a href = "javascript:;" style = "color: #fcff07"><i class = "layui-icon" style = "margin-right: 6px;color: #5FB878">&#xe66f;</i><?= $name ?></a>
            <dl class = "layui-nav-child"> <!-- 二级菜单 -->
                <dd><a href = "<?= Url::to(['site/logout']) ?>">退出</a></dd>
            </dl>
        </li>
    </ul>
</div>
<!-- 顶部结束 -->

<!-- 中部开始 -->

<!-- 左侧菜单开始 -->
<div class = "left-nav">
    <div id = "side-nav">
        <ul id = "nav">
            <?php foreach ($menu as $list): ?>
                <li>
                <a href = "javascript:;">
                    <i class = "layui-icon <?= $list['icon'] ?>"></i>
                    <cite><?= $list['name'] ?></cite>
                    <i class = "iconfont nav_right">&#xe697;</i>
                </a>
                <ul class = "sub-menu">
                    <?php foreach ($list['submenu'] as $item): ?>
                        <li>
                        <a _href = "<?= Url::to([$item['route']]) ?>">
                            <i class = "iconfont">&#xe6a7;</i>
                            <cite><?= $item['route_name'] ?></cite>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<!-- 左侧菜单结束 -->

<!-- 右侧主体开始 -->
<div class = "page-content">
    <div class = "layui-tab tab" lay-filter = "xbs_tab" lay-allowclose = "false">
        <ul class = "layui-tab-title"></ul>
        <div class = "layui-unselect layui-form-select layui-form-selected" id = "tab_right">
            <dl>
                <dd data-type = "this">关闭当前</dd>
                <dd data-type = "other">关闭其它</dd>
                <dd data-type = "all">关闭全部</dd>
            </dl>
        </div>
        <div class = "layui-tab-content"></div>
    </div>
</div>
<div class = "page-content-bg"></div>
<!-- 右侧主体结束 -->
<!-- 中部结束 -->
<!-- 底部开始 -->
<div class = "footer">
 <div class = "copyright">Copyright © NXP</div>
</div>
<?php $this->beginBlock('footer') ?>
<script>
    function clear_cache() {
        layer.load(3);
        $.post('<?= Url::to(['index/clear-cache']) ?>', {id: 1}, function (res) {
            layer.closeAll();
            if (res.status === 200) {
                layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                    location.reload();
                })
            } else {
                layer.msg(res.msg, {icon: 2, time: 1500})
            }
        }, 'json');
    }
</script>
<?php $this->endBlock() ?>
