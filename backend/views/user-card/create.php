<?php
use yii\helpers\Url;
use backend\models\UserInfo;
?>
<div class="x-body layui-anim layui-anim-up">
    <form class="layui-form">
       <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>账号:</label>
            <div class="layui-input-inline">
                <select name="uid">
                    <option value="">请选择账号</option>
                  <?php foreach (((new UserInfo())->getUserCardList()) as $value): ?>
                    <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                  <?php endforeach; ?>
                </select>
            </div>
        </div>
         <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>临时卡:</label>
            <div class="layui-input-inline">
                <select name="empid">
                    <option value="">请选择临时卡</option>
                  <?php foreach ($snapCardArr as $value): ?>
                      <option value="<?= $value['ID'] ?>"><?= $value['LASTNAME'] ?></option>
                  <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">
            </label>
            <button  class="layui-btn" lay-filter="add" lay-submit="">
                增加
            </button>
        </div>
    </form>
</div>

<?php $this->beginBlock('footer') ?>
<script>
    layui.use(['form','layer'], function(){
        $ = layui.jquery;
        var form = layui.form ,layer = layui.layer;

        //监听提交
        form.on('submit(add)', function(data){
            layer.load(3);
            $.post('<?= Url::to([$this->context->id . '/create']) ?>', data.field, function (res) {
                layer.closeAll();
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
