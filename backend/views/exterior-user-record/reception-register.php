<?php

use yii\helpers\Url;

?>
    <style>
        .layui-form {
            text-align: center;
        }

        .title_div {
            height: 150px;
            line-height: 160px;
            font-size: 40px
        }

        input {
            width: 300px;
            height: 40px;
            padding-left: .4rem;
            font-size: 17px;
        }

        .button-div {
            height: 150px;
            line-height: 70px;
            font-size: 40px;
        }

        .submit {
            width: 80px;
        }
    </style>
    <div class="x-body layui-anim layui-anim-up">
        <div class="layui-form">
            <div id="card-register">
                <div class="title_div"><b>扫描识别码信息</b></div>
                <input type="text" name="card" disabled class="code" autocomplete="off">
            </div>
            <div id="pid-register"  style="display: none">
                <div class="title_div"><b>请输入PID信息</b></div>
                <input name="pid" id="pid" type="text" class="code" autocomplete="off">
            </div>
            <div class="layui-form-item" style="margin-top: 15px">
                <input type="radio" name="radioType" title="识别码登记" value="1" lay-filter="radioType" checked>
                <input type="radio" name="radioType" title="PID登记" value="2" lay-filter="radioType" >
            </div>
             <input type="hidden" id = "time_card" class="code" autocomplete="off">
            <div class="button-div" id="submit-register">
                <button class="layui-btn submit">确定</button>
            </div>
        </div>
    </div>
<?php
$js = <<<JS


    
 layui.use('form', function () {
     let form = layui.form;
     let cardRegister = $('#card-register'),
         pidRegister = $('#pid-register'),
         submitRegister = $('#submit-register');
     form.on('radio(radioType)', function (data) {
         if (data.value == 1) {
             cardRegister.show();
             pidRegister.hide();
             submitRegister.hide();
             $("#pid").val("");
             getCard();
         } else if (data.value == 2) {
             pidRegister.show();
             submitRegister.show();
             cardRegister.hide();
         }
     });
 });
    $(document).ready(function() {
        getCard();
    });
    function getCard() {
        var startTime = $('#time_card').val();
        $.ajax({
            url: 'reception-get-card',
            type: 'get',
            data: {startTime: startTime},
            dataType: 'json',
            success: function (data) {
                 $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                    $("input[name='card']").val(data.data.empid);
                    layer.msg(data.msg, {icon: 1, time: 1500}, function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.x_admin_show('登记', data.data.url);
                    })
                }else {
                setTimeout(getCard, 3*1000);  //设置请求api接口时间2秒
                }
            }
        });
    }
 function submit() {
     let pid = $("input[name='pid']").val();
     var radioType = $("input[type='radio']:checked").val();
     if (radioType == 2 && !pid) {
         layer.msg('请输入PID！', {icon: 2, time: 1500});
         return false;
     }
     $.post('reception-register', {
         pid: pid
     }, function (res) {
         if (res.status === 200) {
             layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                 var index = parent.layer.getFrameIndex(window.name);
                 parent.layer.close(index);
                 parent.x_admin_show('登记', res.data);
             })
         } else {
             layer.msg(res.msg, {icon: 2, time: 1500})
         }
     }, 'json');
 }

 $('.submit').click(function () {
     submit();
 });
JS;
$this->registerJs($js);

