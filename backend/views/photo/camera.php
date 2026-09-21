<?php

use yii\helpers\Url;
?>
<style>
    .coach-price{display: none}
    .input-but{display: inline-flex;margin-top: 20px;}
    #canvas{display: none; style="margin: 0 auto;text-align: center"}
    #showVideo{display: none}
    #input-picture{width:100%;}
</style>
<div class="x-body layui-anim layui-anim-up">
    <div class="ibox float-e-margins">
        <div class="ibox-content img-content"   style="margin: 0 auto;text-align: center">
            <form class="layui-form">
                <div class="form-group " id="input-picture">
                    <div class="img-box" id="results">
                        <input name="img_picture" id="img_picture" type="hidden" value=""/>
                        <canvas id="canvas" width="320" height="240"></canvas>
                    </div>
                </div>
                <div class="form-group">
                    <video id="video" controls></video>
                </div>
                <div class="form-group ">
                    <div class="input-but">
                        <button type="button" class="layui-btn" id="capture">
                            <i class="layui-icon">&#xe65d;</i>拍照
                        </button>
                        <button class="layui-btn" lay-filter="update" lay-submit="">
                            <i class="layui-icon">&#x1005;</i>确定
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $this->beginBlock('footer') ?>
<script>
    //提交图片
    layui.use(['form','layer'], function(){
        var form = layui.form ,layer = layui.layer;
        //监听提交
        form.on('submit(update)', function(data){
            var img_picture = $('#img_picture').val();//图片值
            if(!img_picture){
                layer.msg('请先拍照', {icon: 2, time: 1500});
                return false;
            }
            layer.load(3);
            $.post('<?= Url::to(['upload/camera']) ?>', data.field, function (res) {
                layer.closeAll();
                if (res.status === 200) {
                    layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                        parent.ChooseAdidValues(res.data.file_id,res.data.img_url); //这是父页面函数
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    })
                } else {
                    layer.msg(res.msg, {icon: 2, time: 1500});
                }
            }, 'json');
            return false;
        });
    });

    //访问用户媒体设备的兼容方法
    function getUserMedia(constraints, success, error) {
        if (navigator.mediaDevices.getUserMedia) {
            //最新的标准API
            navigator.mediaDevices.getUserMedia(constraints).then(success).catch(error);
        } else if (navigator.webkitGetUserMedia) {
            //webkit核心浏览器
            navigator.webkitGetUserMedia(constraints,success, error)
        } else if (navigator.mozGetUserMedia) {
            //firfox浏览器
            navigator.mozGetUserMedia(constraints, success, error);
        } else if (navigator.getUserMedia) {
            //旧版API
            navigator.getUserMedia(constraints, success, error);
        }
    }

    function success(stream) {
        //兼容webkit核心浏览器
        let CompatibleURL = window.URL || window.webkitURL;
        //将视频流设置为video元素的源
        console.log(stream);
        //video.src = CompatibleURL.createObjectURL(stream);
        video.srcObject = stream;
        video.play();
    }

    function error(error) {
        alert('访问摄像头失败!');
    }

    //从 canvas 提取图片 image
    function convertCanvasToImage(canvas) {
        //新Image对象，可以理解为DOM
        var image = new Image();
        // canvas.toDataURL 返回的是一串Base64编码的URL
        // 指定格式 PNG
        image.src = canvas.toDataURL("image/png");
        return image;
    }

    function getnavigator() {
        if (navigator.mediaDevices.getUserMedia || navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia) {
            //获取video宽高
            var v_height,v_width;
            var myvObj = document.getElementById("video");
            myvObj.addEventListener("loadedmetadata", function () {
                v_height = this.videoHeight;
                v_width =this.videoWidth;
                $('#canvas').attr('width',v_width);
                $('#canvas').attr('height',v_height);
            });
            // 前置摄像头
            //getUserMedia({  audio: false,video : {width: 320, height: 240}}, success, error);
            //调用用户媒体设备, 访问摄像头 facingMode: { exact: "environment" } 为后置拍照
            getUserMedia({  audio: true,video : {width: 320, height: 240}}, success, error);//后置拍照
        } else {
            alert('不支持访问用户媒体');
        }
    }

    getnavigator();
    function showVideo(){
        $('#results').find('img').remove();
        $('#canvas').css('display','none');
        $('#video').css('display','block');
        $('#showVideo').css('display','none');
        getnavigator();
    }

    function showpicture(picture) {
        if($('#results').find('img').attr('src')){
            $('#results').find('img').attr('src',picture);
        }else{
            $('#results').append('<img src="'+picture+'"/>');
        }
        $('#video').css('display','none');
        $('#canvas').css('display','none');
        $('#showVideo').show();
        $('.picture').val(1);
    }

    function hidepicture() {
        $('#results').find('img').remove();
        getnavigator();
        $('#video').css('display','block');
        $('#canvas').css('display','none');
        $('#showVideo').css('display','none');
    }

    $('#showVideo').click(function () {
        showVideo();
    });

    document.getElementById('capture').addEventListener('click', function () {
        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');
        let context = canvas.getContext('2d');
        context.drawImage(video, 0, 0);
        //获取网页中的canvas对象
        var mycans=$('canvas')[0];
        //调用convertCanvasToImage函数将canvas转化为img形式
        var img=convertCanvasToImage(mycans);
        if(img.src){
            $('#results').find('#img_picture').val(img.src);
            $('#video').css({
                "display":"none",
                "text-align":"center",
                "margin":"0 auto"
            });
            $('#canvas').css({
                "display":"block",
                "text-align":"center",
                "margin":"0 auto"
            });
            $('#showVideo').show();
        }
    });
</script>
<?php $this->endBlock() ?>

