<?php
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;
?>
<div class="x-body layui-anim layui-anim-up">
    <?php ActiveForm::begin(['action' => 'ajax-upload', 'method' => 'post', 'options' => ['enctype' => 'multipart/form-data', 'id' => 'upload-form']]); ?>
    <div class="layui-form-item">
        <label class="layui-form-label">
        </label>
        <?= Html::fileInput('file') ?>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">
        </label>
        <?= Html::button('导入数据', ['class' => 'layui-btn layui-btn-normal', 'id' => 'upload-btn']) ?>
    </div>
    <?php ActiveForm::end() ?>
    <div class="layui-form-item">
        <label class="layui-form-label">
            操作结果:
        </label>
        <div id="upload-result" style="height:220px;width:60%;overflow:auto;overflow-x:hidden;border:1px solid #ccc;padding:5px"></div>
    </div>
</div>

<?php $this->beginBlock('footer') ?>
<script>
    $(function () {
        // 导入数据
        $('#upload-btn').click(function () {
            var btn = $('#upload-btn');
            var show = $('#upload-result');
            $.post({
                url: '<?= Url::to(['exterior-relation/import']) ?>',
                cache: false,
                dataType: 'json',
                data: new FormData($('#upload-form')[0]),
                processData: false,
                contentType: false,
                beforeSend: function () {
                    btn.html('<i class="fa fa-refresh fa-spin"></i> 导入数据中，请勿关闭页面');
                    btn.attr('disabled', true);
                    show.html('');
                },
                success: function (result) {
                    if (result.status == 100) {
                        show.css('color', 'red');
                        show.html(result.msg);
                    } else {
                        // 显示导入结果
                        show.css('color', 'red');

                        var tips = '<div style="margin-bottom:5px;font-weight: bold">' +
                            '<span style="color:green">导入成功:' + result.data.successCount + '</span>&nbsp;&nbsp;' +
                            '<span style="color:red">导入失败:' + result.data.errorCount + '</span></div>';
                        show.html(tips + result.data.errorList);
                    }
                    btn.html('导入数据');
                    btn.attr('disabled', false);
                }
            });
        });
    });
</script>
<?php $this->endBlock() ?>

