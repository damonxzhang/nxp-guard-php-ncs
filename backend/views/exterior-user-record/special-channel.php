<?php
use yii\helpers\Url;
?>
    <div class="x-body layui-anim layui-anim-up">
    <form class="layui-form">
        <div class="layui-form-item">
            <label for="icon" class="layui-form-label" style="font-weight:bold;color: #b1032b">
                <span class="x-red">*</span> 请输入PID:
            </label>
            <div class="layui-input-inline" style="width: 300px;">
                <input type="text" style="border:0.5px solid #378888" name="pid" required autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="name" class="layui-form-label" style="font-weight:bold">
                <span class="x-red">*</span> 临时卡号:
            </label>
            <div class="layui-input-inline"  style="width: 300px;">
                <input type="hidden" id="snap-card"  name="snap_card" class="layui-input">
                <input type="text" id="snap-name" style="border:0.5px solid #378888" disabled autocomplete="off" class="layui-input" >
            </div>
        </div>
        <div class="layui-form-item">
            <label for="icon" class="layui-form-label" style="font-weight:bold">
                <span class="x-red">*</span> 警卫卡号:
            </label>
            <div class="layui-input-inline" style="width: 300px;">
                <input type="hidden" id="guard-card"  name="guard_card" class="layui-input">
                <input type="text" id="guard-name" style="border:0.5px solid #378888" disabled  autocomplete="off" class="layui-input" >
            </div>
        </div>
        <div class="layui-form-item">
            <label for="" class="layui-form-label">
            </label>
            <input type="hidden" id = "time_card" class="code" autocomplete="off">
            <button class="layui-btn submit">确定</button>
        </div>
    </form>
</div>

<?php $this->beginBlock('footer') ?>
<?php
$js = <<<JS

    $(document).ready(function() {
        getCard();
    });

    function getCard() {
        var startTime = $('#time_card').val();
        $.ajax({
            url: 'special-channel-card',
            type: 'get',
            data: {startTime: startTime},
            dataType: 'json',
            success: function (data) {
                $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                    $("#guard-card").val(data.data.guard);
                    $("#snap-card").val(data.data.snap);
                    $("#guard-name").val(data.data.guard_name);
                    $("#snap-name").val(data.data.snap_name);
                }else {
                    setTimeout(getCard,4*1000);
                }
            }
        });
    }

    $('.submit').click(function(){
        let pid        = $("input[name='pid']").val();
        let snap_card  = $("input[name='snap_card']").val();
        let guard_card = $("input[name='guard_card']").val();
      
        if( snap_card && guard_card ){
            $.post('special-channel',{
                pid: pid,snap_card: snap_card,guard_card: guard_card
            },function(res) {
                if(res.status == 200){
                    layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.location.reload();
                    })
                }else{
                    layer.msg(res.msg, {icon: 2, time: 3000});
                }
            },'json')
        }else{
            layer.msg('请刷临时卡和警卫卡!', {icon: 2,time: 3000});
        }
    });
JS;
$this->registerJs($js);
?>
<?php $this->endBlock() ?>

