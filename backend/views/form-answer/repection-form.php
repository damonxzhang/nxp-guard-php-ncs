<?php
use yii\helpers\Url;
?>
<style>
    .input-div{
        margin: 10px auto;
    }
    .input-div div{
        float: right
    }
    #signature-canvas {
        width: 100%;
        background-color: lightgrey;
    }
</style>
<div>
    <div style="height:80px;width: 70%;margin: 0 auto;line-height: 80px;text-align: center">
        <h1 style="font-size: 40px;">临时来访人员情况补充确认表</h1>
    </div>
    <div style="width: 70%;margin: 0 auto;font-size: 20px;">
        <div style="margin: 10px auto">
            <p>敬爱的来宾，作为一家跨国际公司，我们一直在关注新新冠状病毒肺炎的流行情况。为保护我们的员工，客户和其他来宾，请配合我们如实确认一下信息，可以隐瞒信息
                并造成不良后果将会被依法追究责任。非常感谢您的大力合作。</p>
            <p><strong>请注意：“下列2-7问题答案为‘是’，谢绝进场厂。”</strong></p>
        </div>
        <form id="form">
            <input name="_csrf-backend" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
            <div style="margin:20px 10px auto">
                <div class="input-div" >
                    1. 近14天内,您是否离开天津市?
                    <div >
                        <input type="radio" name="answer1" value="1">是 &nbsp;
                        <input type="radio" name="answer1" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    2. 近14天内,您和您的密切接触人员是否去过或接触过高,中风险地区人员?
                    <div >
                        <input type="radio" name="answer2" value="1">是 &nbsp;
                        <input type="radio" name="answer2" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    3. 近14天内,您和您的密切接触人员是否接触了来自境外的人员?
                    <div >
                        <input type="radio" name="answer3" value="1">是 &nbsp;
                        <input type="radio" name="answer3" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    4. 近14天内,您和您的密切接触人员是否去过或接触过来天津中高风险区的人员?
                    <div >
                        <input type="radio" name="answer4" value="1">是 &nbsp;
                        <input type="radio" name="answer4" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    5. 近14天内,您和您的密切接触人员是否曾接触过确诊或疑似新型冠状病毒肺炎病人?
                    <div >
                        <input type="radio" name="answer5" value="1">是 &nbsp;
                        <input type="radio" name="answer5" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    6. 近14天内,您是否曾接触过被政府及设备要求集中隔离或居家观察的人员?
                    <div >
                        <input type="radio" name="answer6" value="1">是 &nbsp;
                        <input type="radio" name="answer6" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    7. 近14天内,您是否有以下症状?
                </div>
                <div class="input-div" >
                    (a). 发热,异常的咳嗽,呼吸急促,喉咙痛,肌肉酸痛,无法解释的乏力,腹泻或嗅觉或味觉减弱等?
                    <div >
                        <input type="radio" name="answer7" value="1">是 &nbsp;
                        <input type="radio" name="answer7" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    (b). 其它疑似 新冠症状?
                    <div >
                        <input type="radio" name="answer8" value="1">是 &nbsp;
                        <input type="radio" name="answer8" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    8. 您是否有车辆需要进厂?
                </div>
                <div class="input-div" >
                    (a). 车辆14天内是否曾经去往或经停天津中高风险地区?
                    <div >
                        <input type="radio" name="answer9" value="1">是 &nbsp;
                        <input type="radio" name="answer9" value="2">否
                    </div>
                </div>
                <div class="input-div" >
                    (b). 车辆14天内是否曾经去往或经停高,中风险地区(扫描右上角二维码查询)?
                    <div >
                        <input type="radio" name="answer10" value="1">是 &nbsp;
                        <input type="radio" name="answer10" value="2">否
                    </div>
                </div>
            </div>
            <input type="hidden" name="signatureFile" id="signatureFile">
        </form>
        <div style="margin-top: 20px; ">
            <p style="margin-left: 50px;"><strong >请出示您的天津健康码及14天行程卡给警卫,均为绿色方可进厂</strong></p>
        </div>
        <div><strong>来访人员签字</strong></div>
        <div id="signature-pad" class="signature-pad" style="height:250px;padding-top: 20px">
            <div class="description tc">签名区域</div>
            <div class="signature-pad--body">
                <canvas id="signature-canvas"></canvas>
                <i class="leftup lefta"></i>
                <i class="leftup rightup"></i>
                <i class="leftup leftdown"></i>
                <i class="leftup rightdown"></i>
            </div>
            <div class="signature-pad--footer">
                <input type="button" value="保存" class="layui-btn" id="save" data-action="save-png"/>
                <input type="button" value="重签" class="layui-btn layui-btn-normal" id="clear"/>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= Url::to('@web/signature_pad/js/signature_pad.umd.js') ?>"></script>
<link rel="stylesheet" href="<?= Url::to('@web/signature_pad/css/signature-pad.css') ?>">
<script type="text/javascript" src="<?= Url::to('@web/signature_pad/js/app.js') ?>"></script>
<script type="text/javaScript">

    function submitForm(){
        let form = $('#form').serialize();
        $.post('',form,function(res){
            if(res.status == 200){
                window.location.href=res.data;
            }else{
                layer.msg(res.msg, {time: 1500}, function () {
                    window.location.reload();
                });
            }
        },'json')
    }

    var canvas = document.getElementById('signature-canvas');
    var signaturePad = new SignaturePad(canvas, {backgroundColor: '#DEDEDE', penColor: 'rgb(0, 0, 0)'});
    var cancelButton = document.getElementById('clear');
    cancelButton.addEventListener('click', function (event) {
        signaturePad.clear();
    });
    var saveButton = document.getElementById('save');
    saveButton.addEventListener('click', function (event) {
        // 签名为空的判断
        if (signaturePad.isEmpty()) {
            layer.msg('签名不能为空!');
            return false;
        }
        var answer1=$('input:radio[name="answer1"]:checked').val();
        var answer2=$('input:radio[name="answer2"]:checked').val();
        var answer3=$('input:radio[name="answer3"]:checked').val();
        var answer4=$('input:radio[name="answer4"]:checked').val();
        var answer5=$('input:radio[name="answer5"]:checked').val();
        var answer6=$('input:radio[name="answer6"]:checked').val();
        var answer7=$('input:radio[name="answer7"]:checked').val();
        var answer8=$('input:radio[name="answer8"]:checked').val();
        var answer9=$('input:radio[name="answer9"]:checked').val();
        var answer10=$('input:radio[name="answer10"]:checked').val();
        if(answer1==null || answer2==null || answer3==null || answer4==null || answer5==null){
            layer.msg('请勾选条件!');
            return false;
        }
        if(answer6==null || answer7==null || answer8==null || answer9==null || answer10==null){
            layer.msg('请勾选条件!');
            return false;
        }
        if(answer2 == 1 || answer3 == 1 || answer4 == 1 || answer5 == 1 || answer6 == 1 || answer7 == 1 || answer8 == 1){
            layer.msg('抱歉条件不符,谢绝进场厂!');
            return false;
        }
        // signaturePad.jSignature('getData');
        var imgStr = signaturePad.toDataURL('image/png');
        //获取到image的base64 可以把这个传到后台解析成图片
        //imgStr = data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfgAAAL2CAYAAA......
        //去掉data:image/png;base64,我们只要后面的部分iVBORw0KGgoAAAANSUhEUgAAAfgAAAL2CAYAAA......
        file=imgStr.substring(22,imgStr.length);
        var formData = new FormData();
        formData.append("file", file);
        $.ajax({
            url:'/upload/sign-image',
            type: "post",
            data: formData,
            mimeType: "multipart/form-data",
            dataType: "json",
            contentType: false,
            processData: false,
            success: function (res) {
                if(res.status == 200){
                    $('#signatureFile').val(res.data.id);
                    submitForm();
                }else{
                    layer.msg(res.msg);
                }
            },
            error: function (err) {
                console.log('上传失败');
            }
        });

    });

    function resizeCanvas() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1); // 清除画布
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }

    window.onresize = resizeCanvas;
    resizeCanvas();
    setTimeout("resizeCanvas()", "300");

</script>
