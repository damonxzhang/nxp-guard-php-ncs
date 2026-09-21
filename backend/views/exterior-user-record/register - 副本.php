<?php

use yii\helpers\Html;
use backend\widgets\LayuiActiveForm;
use yii\helpers\Url;

$userInfo = Yii::$app->user->identity;
?>
<div class="x-body layui-anim layui-anim-up">
    <div class="exterior-user-form">
        <?php $form = LayuiActiveForm::begin() ?>
        <div class="user-body" data-form='1'>
            <div class="form-div">
                <div class="layui-form-item">
                    <label for="tel" class="layui-form-label"><span class="x-red">*</span>手机号码:</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <?= Html::input('text', 'phone', '', ['class' => 'layui-input phone', 'placeholder' => '请输入手机号']) ?>
                    </div>
                    <?= Html::a('搜索', 'javascript:;', ['class' => 'layui-btn layui-btn-normal get-phone']) ?>
                </div>
                <div class="layui-form-item" id="required-div" style="display:none">
                    <label for="tel" class="layui-form-label"><span class="x-red">*</span>来访原因:</label>
                    <div class="layui-input-inline" style="width: 400px; height: 150px">
                         <select name="exterior_user_id" lay-verify="required" id="purpose">
                             <option value="">请选择来访原因</option>
                         </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-form-item" id="submit-div" style="display:none;margin-top: 20px">
            <label for="" class="layui-form-label"></label>
            <?= Html::a('保存', 'javascript:;', ['class' => 'layui-btn layui-btn-lg submit']) ?>
        </div>
        <?php LayuiActiveForm::end(); ?>
    </div>
</div>

<?php $this->beginBlock('footer') ?>
<script>
    $(document).on('click', '.get-phone', function () {
        let phone = $(this).parent().find('input[name="phone"]').val();
        if (!phone) {
            layer.msg('请输入手机号！');
            return false;
        }
        $.post('get-phone', {phone: phone}, function (res) {
            $("#purpose").empty(); //保证不重复添加
            renderForm();//表单重新渲染，要不然添加完显示不出来新的option
            if (res.status === 200) {
                layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                    $("#required-div").attr("style", "display:block;");//显示div
                    $("#submit-div").attr("style", "display:block;");
                    $.each(res.data, function (i, item) {
                        $("#purpose").append("<option value='" + item.id + "'>" + item.name + '(' + item.purpose + ")</option>");
                    });
                    renderForm();//表单重新渲染，要不然添加完显示不出来新的option
                })
            } else {
                layer.msg(res.msg, {icon: 2, time: 2000});
                $("#required-div").attr("style", "display:none;");//隐藏div
                $("#submit-div").attr("style", "display:none;");
            }
        }, 'json');
    });

    //重新渲染表单
    function renderForm() {
        layui.use('form', function () {
            var form = layui.form;
            form.render();
        });
    }

    //提交保存数据
    $('.submit').click(function () {
        var id = $("select[name='exterior_user_id']").val();
        if (!id) {
            layer.msg('请选择来访原因！');
            return false;
        }
        $.post('register', {id: id}, function (res) {
            if (res.status) {
                layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.x_admin_show('来访人员签字', res.data);
                    closeParentOpen();
                })
            } else {
                layer.msg(res.msg);
            }
        }, 'json');

    });

</script>
<?php $this->endBlock() ?>
