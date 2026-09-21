<?php

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>摄像头测试</title>
</head>
<style>
    /*#camera{*/
    /*float: left;*/
    /*margin: 20px;*/
    /*}*/
    #contentHolder{
        width: 300px;
        height: 300px;
        margin: 0 auto;
        margin-bottom: 10px;
    }
    #contentImage{
        width: 300px;
        height: 300px;
        margin: 0 auto;
        margin-bottom: 10px;
    }
    #btn_snap{
        margin: 0 auto;
        text-align: center;
        cursor: pointer;
        /*禁止选择*/
        -webkit-touch-callout: none; /* iOS Safari */
        -webkit-user-select: none; /* Chrome/Safari/Opera */
        -khtml-user-select: none; /* Konqueror */
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
        user-select: none; /* Non-prefixed version, currently not supported by any browser */
    }
    .default_hidden {
        display: none;
    }
    /*#imgBoxxx{*/
    /*width: 200px;*/
    /*margin: 60px 20px 20px;*/
    /*}*/
</style>
<body>
<div id="camera">
    <div id="contentHolder">
        <video id="video" width="300" height="300" autoplay></video>
        <canvas style="display:none;" id="canvas" width="300" height="300"></canvas>
    </div>
    <div id="contentImage" style="display: none"> </div>
    <div id="images"></div>

    <div style="margin: 0 auto;text-align: center">
        <a class="layui-btn" id="btn_snap">拍照</a>
        <a class="layui-btn default_hidden" id="saveImg" >保存</a>
        <a class="layui-btn default_hidden" id="rephotograph" >重拍</a>
    </div>
</div>
</body>
<script>
    var canvas = document.getElementById("canvas"),
        pzBtn = document.getElementById("btn_snap"),
        context = canvas.getContext("2d"),
        video = document.getElementById("video");

    // 旧版本浏览器可能根本不支持mediaDevices，我们首先设置一个空对象
    if (navigator.mediaDevices === undefined) {
        navigator.mediaDevices = {};
    }
    // 一些浏览器实现了部分mediaDevices，我们不能只分配一个对象
    // 使用getUserMedia，因为它会覆盖现有的属性。
    // 这里，如果缺少getUserMedia属性，就添加它。
    if (navigator.mediaDevices.getUserMedia === undefined) {
        navigator.mediaDevices.getUserMedia = function (constraints) {
            // 首先获取现存的getUserMedia(如果存在)
            var getUserMedia = navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
            // 有些浏览器不支持，会返回错误信息
            // 保持接口一致
            if (!getUserMedia) {
                return Promise.reject(new Error('getUserMedia is not implemented in this browser'));
            }
            //否则，使用Promise将调用包装到旧的navigator.getUserMedia
            return new Promise(function (resolve, reject) {
                getUserMedia.call(navigator, constraints, resolve, reject);
            });
        }
    }

    function setShowPage(img){
        $('#images').append(img);
        $('#saveImg').css('display','inline-block');
        $('#rephotograph').css('display','inline-block');
    }

    $(document).on('click','#rephotograph',function(){
        $('#images').children('img').remove();
        $('#images').children('input').remove();
        $('#contentHolder').show();
        $('#rephotograph').hide();
    });

    function saveImage(formData){
        $.ajax({
            url:'/upload/upload',
            type: "post",
            data: formData,
            mimeType: "multipart/form-data",
            dataType: "json",
            contentType: false,
            processData: false,
            success: function (res) {
                if(res.code == 200){
                    //保存数据
                    $('#images').append("<input type='hidden' name='file_ids[]' value='"+res.id+"'>");
                }else{
                    layer.msg(res.msg);
                }
            },
            error: function (err) {
                console.log('上传失败');
            }
        })
    }

    $(document).on('click','#saveImg',function(){
        let file_ids = new Array();
        $('input[name="file_ids[]"]').each(function(){
            file_ids.push($(this).val());
        });

        $.post('',{file_ids:file_ids},function(result){
            if(result.status){
                layer.msg(result.msg, {icon: 1,time: 1500}, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                })
            }else{
                layer.msg(result.msg);
            }
        },'json')
    });

    var constraints = { audio: false, video: {width: 720,height:720, facingMode: { exact: "environment" }} };
    navigator.mediaDevices.getUserMedia(constraints)
        .then(function (stream) {
            var video = document.querySelector('video');
            // 旧的浏览器可能没有srcObject
            if ("srcObject" in video) {
                video.srcObject = stream;
            } else {
                //避免在新的浏览器中使用它，因为它正在被弃用。
                video.src = window.URL.createObjectURL(stream);
            }
            video.onloadedmetadata = function (e) {
                video.play();
            };
        })
        .catch(function (err) {
            console.log(err.name + ": " + err.message);
        });



    pzBtn.addEventListener("click", function () {
        // 点击，canvas画图
        context.drawImage(video, 0, 0, 300, 300);
        // 获取图片base64链接
        var image = canvas.toDataURL('image/png');
        // 定义一个img
        var img = new Image();
        //设置属性和src
        img.id = "imgBoxxx";
        img.src = image;
        img.width = 300;
        img.height= 300;
        //将图片添加到页面中
        // document.body.appendChild(img);

        setShowPage(img);

        // base64转文件
        function dataURLtoFile(dataurl, filename) {
            var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new File([u8arr], filename, {type: mime});
        }
        let time = (new Date()).getTime();
        let file = dataURLtoFile(image, time +'.png');
        let formData = new FormData();
        formData.append('file', file);//将 部分文件 塞入FormData
        saveImage(formData);
    });
</script>
</html>