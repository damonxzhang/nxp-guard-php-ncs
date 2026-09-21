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
                <div class="title_div"><b>请刷临时人员卡</b></div>
                <input type="text" disabled name="empid" class="code" autocomplete="off">
                <input type="hidden" id = "time_card" class="code" autocomplete="off">
            </div>
        </div>
    </div>
<?php
$js = <<<JS

    
    $(document).ready(function() {
        getCard();
    });
     //扫卡
    function getCard() {
        var startTime = $('#time_card').val();
        $.ajax({
            url: 'get-snap-card',
            type: 'get',
            data: {startTime: startTime},
            dataType: 'json',
            success: function (data) {
                $("#time_card").val(data.data.startTime);
                if (data.status === 200) {
                   $("input[name='empid']").val(data.data.empid);
                   layer.msg(data.msg, {icon: 1, time: 1500}, function () {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.x_admin_show('登记', data.data.url);
                   })
                }else {
                    setTimeout(getCard,2*1000);
                }
            }
        });
    }
 
JS;
$this->registerJs($js);


