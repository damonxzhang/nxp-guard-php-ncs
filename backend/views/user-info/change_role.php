<?php
use yii\helpers\Url;
?>
    <div class="x-body">
        <form method="post" class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    <span class="x-red">*</span>用户
                </label>
                <div class="layui-input-inline">
                    <input type="text" required="" disabled value="<?=Yii::$app->user->identity->name?>" lay-verify="required"
                           autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">
                    角色
                </label>
                <table  class="layui-table layui-input-block">
                    <tbody>
                    <tr>
                        <td>
                            <div class="layui-input-block">
                                <?php foreach ($roleList as $key => $value): ?>

                                    <div class="layui-unselect layui-form-checkbox  <?php if(in_array($key,$userRole)){ echo 'layui-form-checked' ;}?> " data-id="<?= $key ?>" lay-skin="primary"><span><?= $value ?></span><i class="layui-icon"></i></div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn" lay-submit="" lay-filter="add">增加</button>
            </div>
        </form>
    </div>

<?php $this->beginBlock('footer') ?>
    <script>
        layui.use(['form','layer'], function(){
            $ = layui.jquery;
            var form = layui.form, layer = layui.layer;
            //监听提交
            form.on('submit(add)', function(data){
                let idlist = new Array();

                $('.layui-form-checked').each(function () {
                    if (typeof($(this).data('id')) !="undefined" && $(this).data('id')!=0){
                        idlist.push($(this).data('id'));
                    }
                });

                $.post('',{ids:idlist}, function (res) {
                    if (res.status === 200) {
                        layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.location.reload();
                        })
                    } else {
                        layer.msg(res.msg, {icon: 2, time: 1500})
                    }
                }, 'json');
                return false;
            });
        });
    </script>
<?php $this->endBlock() ?>