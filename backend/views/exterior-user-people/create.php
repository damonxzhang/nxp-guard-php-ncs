<?php
use yii\helpers\Url;
use backend\models\UserInfo;
?>
<div class="x-body layui-anim layui-anim-up">
    <form class="layui-form">
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>临时卡:</label>
            <div class="layui-input-inline">
                <div id="empid"   class="xm-select-demo"></div>
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">
            </label>
            <button  type="button" id="submit-data" class="layui-btn" >
                增加
            </button>
        </div>
    </form>
</div>

<?php $this->beginBlock('footer') ?>
<script>
    var snapCard = JSON.parse('<?php echo json_encode($snapCardArr); ?>');
    var selectCard = Array();
    for(var j = 0; j < snapCard.length; j++) {
        var snapVal = new Object();
        snapVal.name  = snapCard[j]['MIDNAME'];
        snapVal.value = snapCard[j]['ID'];
        selectCard.push(snapVal);
    }

    var empidData = xmSelect.render({
        el: '#empid',
        filterable: true,
        radio: true,
        clickClose: true,
        data: selectCard
    });

    $("#submit-data").click(function(){
        var empid = empidData.getValue('value');
        if(empid.length  == 0){
            layer.msg('请选择临时卡',{anim: 6});
            return false;
        }

        $.ajax({
            url:'<?= Url::to([$this->context->id . '/create']) ?>',
            type: 'post',
            data: {empid: empid},
            dataType: 'json',
            success: function (res) {
                if (res.status === 200) {
                    layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                       var index = parent.layer.getFrameIndex(window.name);
                       parent.layer.close(index);
                       parent.location.reload();
                    })
                } else {
                    layer.msg(res.msg, {icon: 2, time: 1500})
                }
            }
        });
        return false;
    });
</script>
<?php $this->endBlock() ?>

