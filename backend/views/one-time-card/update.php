<?php
use yii\helpers\Url;
use backend\models\LenelEmp;
use backend\models\OneTimeCard;
use backend\models\ExteriorUser;
?>
<div class="x-body layui-anim layui-anim-up">
    <form class="layui-form">
        <div class="layui-form-item">
            <label for="name" class="layui-form-label">
                 临时卡:
            </label>
            <div class="layui-input-inline">
                <input  type="hidden" name="id" value="<?= $oneTimeCard['id'] ?>">
                <input type="text"  autocomplete="off" readonly class="layui-input"  value="<?= (new LenelEmp())->getLastName($oneTimeCard['empid']) ?>">
            </div>
        </div>
       <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>厂区:</label>
            <div class="layui-input-inline">
                <select name="factory">
                    <option value="">请选择厂区</option>
                  <?php foreach (ExteriorUser::$factoryList as $key => $value): ?>
                      <option value="<?= $key ?>" <?= $key == $oneTimeCard['factory'] ? 'selected' : '' ?>><?= $value ?></option>
                  <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>厂门:</label>
            <div class="layui-input-inline">
                <select name="door">
                    <option value="">请选择厂门</option>
                  <?php foreach (OneTimeCard::$doorList as $key=> $value): ?>
                      <option value="<?= $key ?>"  <?= $key == $oneTimeCard['door'] ? 'selected' : '' ?>><?= $value ?></option>
                  <?php endforeach; ?>
                </select>
            </div>
        </div>
         <div class="layui-form-item">
            <label class="layui-form-label">状态</label>
            <div class="layui-input-block">
                <input type="radio" value="3" name="status" lay-skin="primary" title="启用" <?= $oneTimeCard['status'] == 3 ? 'checked' : '' ?>>
                <input type="radio" value="4" name="status" lay-skin="warning" title="禁用" <?= $oneTimeCard['status'] == 4 ? 'checked' : '' ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">
            </label>
            <button  class="layui-btn" lay-filter="update" lay-submit="">
                更新
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
        form.on('submit(update)', function(data){
            layer.load(3);
            $.post('<?= Url::to([$this->context->id . '/update']) ?>', data.field, function (res) {
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