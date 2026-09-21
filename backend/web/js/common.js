
function del(obj, id, url){
    layer.confirm('确认要删除吗？', {icon: 3},function(index){
        $.get(url, {id: id}, function (res) {
            if (res.status === 200) {
                layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                    $(obj).parents("tr").remove();
                    $count_num = $('.count_num');
                    $count_num.text(parseInt($count_num.text()) - 1);
                });
            } else {
                layer.msg(res.msg, {icon: 2, time: 2000});
            }
        }, 'json');
    });
}

function batch_del (url) {
    var data = tableCheck.getData();
    if (data.length == 0) {
        layer.msg('请选择您要批量删除的数据', {icon: 7}); return;
    }
    layer.confirm('确认要批量删除吗？', {icon: 3}, function(index){
        $.get(url, {idArr: data}, function (res) {
            if (res.status === 200) {
                // $(".layui-form-checked").not('.header').parents('tr').remove();
                layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                    location.reload();
                });
            } else {
                layer.msg(res.msg, {icon: 2, time: 2000})
            }
        }, 'json');
    });
}


layui.use('laydate',function() {
    var laydate = layui.laydate;
    $(this).removeAttr("lay-key");
    var startTime = laydate.render({
        elem: '#start_time',
        type: 'datetime',
        trigger : 'click',
        btns: ['clear', 'confirm'],
        done: function (value, date) {
            endTime.config.min = {
                year: date.year,
                month: date.month - 1,//关键
                date: date.date,
                hours: date.hours,
                minutes: date.minutes,
                seconds: date.seconds
            };
        }
    });
    var endTime = laydate.render({
        elem: '#end_time',
        type: 'datetime',
        trigger : 'click',
        btns: ['clear', 'confirm'],
        done: function (value, date) {
            startTime.config.max = {
                year: date.year,
                month: date.month - 1,//关键
                date: date.date,
                hours: date.hours,
                minutes: date.minutes,
                seconds: date.seconds
            };
        }
    });

    laydate.render({
        elem: '.endTime',
    });
    laydate.render({
        elem: '.startTime',
    });

    laydate.render({
        elem: '.end_time',
        type: 'datetime',
        trigger : 'click',
    });
    laydate.render({
        elem: '.start_time',
        type: 'datetime',
        trigger : 'click',
    });

});

//视频播放
function palyer_video(vUrl){
    layui.config({
        base: '/lib/'
    }).extend({
        ckplayer: 'ckplayer/ckplayer'
    }).use(['jquery', 'ckplayer'], function() {
        var $ = layui.$,
            ckplayer = layui.ckplayer;
        var videoObject = {
            container: '#video',
            loop: true,
            autoplay: true,
            video: [
                [vUrl, 'video/mp4']
            ]
        };
        var player = new ckplayer(videoObject);
    });
}

//显示pdf 文件及图片
$(function(){
    $('img.ez').EZView();
});


//输入框自动获取焦点
setTimeout(function(){
    $('#autofocus').focus();
},50);

//输入框焦点自动跳转到下一个
function focusNextInput(thisInput){
    var inputs = document.getElementsByTagName("input");
    for(var i = 0;i<inputs.length;i++){
        // 如果是最后一个，则焦点回到第一个
        if(i==(inputs.length-1)){
            inputs[0].focus();
            break;
        }else if(thisInput == inputs[i]){
            inputs[i+1].focus();
            break;
        }
    }
}

//修改提交表单button属性
function subform(){
    $("#submitform").attr("type","submit");
}


//上传文件
function uploadFile(obj) {
    var content = $("#demoList").html();
    if (content== null || content == 0) {
        layer.alert("请选择文件")
    }
    //$(obj).text('提交中').attr("disabled","disabled").addClass("layui-disabled");
}
layui.use('upload', function(){
    var $ = layui.jquery,upload = layui.upload;
    // console.log(upload);
    //多文件列表示例
    var demoListView = $('#demoList'),uploadListIns = upload.render({
        elem: '#fileList'
        ,url: 'upload'
        ,method: 'post'
        ,accept: 'file'
        // ,exts: 'mp4' //只允许上传.mp4
        ,multiple: true
        ,auto: false
        ,bindAction: '#testListAction'
        ,choose: function(obj){
            var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
            //读取本地文件
            obj.preview(function(index, file, result){
                var tr = $(['<tr id="upload-'+ index +'">'
                    ,'<td>'+ file.name +'</td>'
                    ,'<td>等待上传</td>'
                    ,'<td>'
                    ,'<button class="layui-btn layui-btn-xs demo-reload layui-hide">重传</button>'
                    ,'<button class="layui-btn layui-btn-xs demo-delete">删除</button>'
                    ,'</td>'
                    ,'</tr>'].join(''));
                //单个重传
                tr.find('.demo-reload').on('click', function(){
                    obj.upload(index, file);
                });
                //删除
                tr.find('.demo-delete').on('click', function(){
                    delete files[index]; //删除对应的文件
                    tr.remove();
                    uploadListIns.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                });
                demoListView.append(tr);
            });
        }
        ,done: function(res, index, upload){
            if(res.code == 200){ //上传成功
                var tr = demoListView.find('tr#upload-'+ index),tds = tr.children();
                tds.eq(1).html('<span style="color: #5FB878;">上传成功</span>');
                tds.eq(2).append('<input type="hidden" name="upload_file[]" value="'+res.path+'">');
                //tds.eq(3).html(''); //清空操作
            //    $("#testListAction").text('上传文件').attr("disabled","disabled").removeClass("layui-disabled");
            //    $("#testListAction").removeAttr('disabled');
                return delete this.files[index]; //删除文件队列已经上传成功的文件
            }
            this.error(index, upload);
        }
        ,error: function(index, upload){
            var tr = demoListView.find('tr#upload-'+ index),tds = tr.children();
            tds.eq(1).html('<span style="color: #FF5722;">上传失败</span>');
            tds.eq(2).find('.demo-reload').removeClass('layui-hide'); //显示重传
        //    $("#testListAction").text('上传文件').attr("disabled","disabled").removeClass("layui-disabled");
        //    $("#testListAction").removeAttr('disabled');
        }
    });
});
//显示大图
$(document).on('click','.show-img',function(){
    var url = $(this).attr('src');
    window.open(url);
});

$('#submit').click(function(){
    let form = $(this).parents('form')
    let data =form.serialize();
    let url = form.attr('action');
    $.post(url,data,function(res){
        if(res.status){
            layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
                parent.location.reload();
            })
            // window.location.href='index';
        }else{
            layer.msg(res.msg);
        }
    },'json');
});

$('.pop-up').click(function(){
    let url = $(this).attr('url');
    let title = $(this).attr('title');
    let w = $(this).attr('w');
    let h = $(this).attr('h');
    if (title == null || title == '') {
        title = '操作';
    }
    if (url == null || url == '') {
        url = "404.html";
    }
    if (w == null || w == '') {
        w = ($(window).width() * 0.9);
    }
    if (h == null || h == '') {
        h = ($(window).height() - 50);
    }
    layer.open({
        type: 2,
        area: [w + 'px', h + 'px'],
        fix: false, //不固定
        maxmin: true,
        shadeClose: true,
        shade: 0.4,
        title: title,
        content: url
    });
});

function closeParentOpen(reload = false){
    var index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
    if(reload){
        parent.location.reload();
    }
}

