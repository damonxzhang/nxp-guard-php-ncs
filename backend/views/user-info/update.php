<?php

use backend\models\UserInfo;
use yii\helpers\Url;

?>
<div class="x-body layui-anim layui-anim-up">
    <form class="layui-form">
        <div class="layui-row">
            <div class="layui-col-sm6">
                <div class="layui-form-item">
                    <label class="layui-form-label">
                        中文名:
                    </label>
                    <div class="layui-input-inline">
                        <input type="hidden" name="id" value="<?= $model['id'] ?>">
                        <input type="text" name="name" value="<?= $model['name'] ?>" lay-verify="required"
                               autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid layui-word-aux">
                        <span class="x-red">*</span> 请输入中文名称
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-block">
                        <input type="radio" value="1" name="status" lay-skin="primary"
                               title="启用" <?= $model['status'] == 1 ? 'checked' : '' ?>>
                        <input type="radio" value="2" name="status" lay-skin="warning"
                               title="禁用" <?= $model['status'] == 2 ? 'checked' : '' ?>>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">英文名:</label>
                    <div class="layui-input-inline">
                        <input type="text" name="ename" autocomplete="off" value="<?= $model['ename'] ?>"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">PID:</label>
                    <div class="layui-input-inline">
                        <input type="text" name="pid" autocomplete="off" maxlength="8" value="<?= $model['pid'] ?>"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">人员职位:</label>
                    <div class="layui-input-inline">
                        <select name="type">
                            <option value="">请选择人员职位</option>
                            <?php foreach (UserInfo::$typeArr as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $key == $model['type'] ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">部门号:</label>
                    <div class="layui-input-inline">
                        <input type="text" name="department_number" value="<?= $model['department_number'] ?>"
                             autocomplete="off" class="layui-input">
                    </div>
                </div>
               
                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-block">
                        <img id="pre_img" style="height: 30px;"/>
                        <button class="layui-btn layui-btn-sm" onclick="return false;" id="upload_img">
                            <i class="layui-icon">&#xe67c;</i>上传图片
                        </button>
                        <input type="hidden" name="img" value="">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">拍摄照片:</label>
                    <div class="layui-input-inline">
                        <button type="button" class="layui-btn layui-btn-normal "
                                onclick="x_admin_show('拍照','<?= Url::to(['photo/picture']) ?>')"><i class="layui-icon">&#xe65d;</i>拍照
                        </button>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">
                        <div id="filepath">
                            <?php if ($files): ?>
                                <?php foreach ($files as $path): ?>
                                    <img src="<?= $path ?>" width="180" height="100" class="layui-btn-photo">
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label"></label>
            <button class="layui-btn" lay-filter="update" lay-submit="">
                修改
            </button>
        </div>
    </form>
</div>

<?php $this->beginBlock('footer') ?>
<script>
    layui.use(['form', 'layer'], function () {
        $ = layui.jquery;
        var form = layui.form, layer = layui.layer;
        //监听提交
        form.on('submit(update)', function (data) {
            layer.load(3);
            $.post('<?= Url::to(['user-info/update']) ?>', data.field, function (res) {
                layer.closeAll();
                if (res.status === 200) {
                    layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.location.reload();
                    })
                } else {
                    layer.msg(res.msg, {icon: 2, time: 1500});
                }
            }, 'json');
            return false;
        });
    });

    //获取子页面的传值
    function ChooseAdidValues(fileId, filepath) {
        if (filepath != "") {
            var html = '<span style="margin-bottom:20px;margin-left:20px">' +
                '<input type="hidden" name="file_data[]" value="' + fileId + '">' +
                '<img src=<?php $baseUrl ?>"' + filepath + '" width="220" height="140" alt="" style="margin-bottom:20px"  class="layui-btn-danger">' +
                '</span >';
            $('#filepath').html(html);
        }
    }

    //删除图片
    $(document).on('click', '.layui-btn-danger', function () {
        var _this = $(this).parents('span');
        layer.confirm('是否确认删除?', function (index) {
            _this.remove();
            layer.closeAll();
        });
    });
    layui.use(['upload', 'jquery'], function () {
        $ = layui.jquery;
        var upload = layui.upload;
        //执行实例
        var uploadInst = upload.render({
            elem: '#upload_img', //绑定元素
            url: 'upload', //上传接口
            done: function (res) {
                //上传完毕
                if (res.status == 200) {
                    var html = '<span style="margin-bottom:20px;margin-left:20px">' +
                        '<input type="hidden" name="file_data[]" value="' + res.data.file_id + '">' +
                        '<img src=<?php $baseUrl ?>"' + res.data.img_url + '" width="220" height="140" alt="" style="margin-bottom:20px"  class="layui-btn-danger">' +
                        '</span >';
                    $('#filepath').html(html);
                } else {
                    layer.msg(res.msg);
                }
            }
        });
    });
</script>
<?php $this->endBlock() ?>
