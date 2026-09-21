<?php
use yii\helpers\Url;
?>
<div class="x-nav">
      <span class="layui-breadcrumb">
        <a href="javascript:;">首页</a>
        <a href="javascript:;">后台测试</a>
        <a href="javascript:;">
          <cite>手写测试</cite>
        </a>
      </span>
</div>
<style type="text/css">
    div {
        margin-top: 1em;
        margin-bottom: 1em;
    }
    input {
        padding: .5em;
        margin: .5em;
    }
    select {
        padding: .5em;
        margin: .5em;
    }
    #signature {
        width: 1600px;
        background-color: lightgrey;
    }
</style>
<div class="x-body layui-anim layui-anim-up" >
    <div>
        <div id="signature" ></div>
        <p style="text-align: center">
            <b style="color: red">请按着鼠标写字签名。</b>
        </p>
        <input type="button" value="保存" id="yes"/>
        <input type="button" value="下载" id="download"/>
        <input type="button" value="重写" id="reset"/>
        <p id="someelement"></p>
    </div>
</div>
<?php $this->beginBlock('footer') ?>
<script type="text/javascript" src="<?= Url::to('@web/signature/libs/flashcanvas.js') ?>"></script>
<script type="text/javascript" src="<?= Url::to('@web/signature/libs/jSignature.min.js') ?>"></script>
<script>
    function dataURLtoFile(dataurl, filename) {
        let arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new File([u8arr], filename.split('.')[0] + '.png', {type: mime});
    }
    $(document).ready(function () {
        var $sigp = $("#signature");
        $sigp.jSignature({
            UndoButton: true,
            width: '1600px',  //宽
            height: '200px',//高
            signatureLine: false,//去除默认画布上那条横线
            lineWidth: '3',    //字体粗细
            cssclass:'zx',    //这个参数是给画板设置css样式   class为zx
            color:'#000',    //画板笔颜色
        });
        $("#yes").click(function () {
            // 没签名
            if ($sigp.jSignature("getData", "native").length == 0) {
                return;
            }
            //获取签名内容
            var basedata = "data:" +  $sigp.jSignature('getData', "image");
            // base64转file对象代码

            var file = dataURLtoFile(basedata, "signName.png");
            var formData = new FormData();
            formData.append("file", file);
            $.ajax({
                url:'/upload/upload',
                type:'POST',
                data:formData,
                cache: false,
                contentType: false,
                processData: false,
                success:function(result){
                    console.log(result);
                }
            });
        });
        $("#download").click(function () {
            var img = $('img').attr("src");
            var alink = document.createElement("a");
            alink.href = img;
            var timestamp=new Date().getTime();//生成当前时间戳
            alink.download = timestamp + ".png";
            alink.click();
        });
        $("#reset").click(function () {
            $sigp.jSignature("reset"); //重置画布，可以进行重新作画.
            $("#someelement").html("");
        });
    });
</script>
<?php $this->endBlock() ?>
