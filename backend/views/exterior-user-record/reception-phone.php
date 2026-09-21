<!doctype html>
<html lang="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    .tbody{
        text-align: center;
    }
    .title_div{
        height: 150px;
        line-height: 150px;
        font-size:40px
    }
    input{
        width: 300px;
        height: 40px;
        padding-left: .4rem;
        font-size: 17px;
    }
    .button-div{
        height: 150px;
        line-height: 150px;
        font-size:40px;
    }
    .submit{
        width: 80px;
    }
</style>
<body>
<div class="tbody" >
    <div class="title_div"><b>请输入手机号码:</b></div>
    <input type="text" name="phone" class="code" autocomplete="off">
    <div class="button-div">
        <button class="layui-btn submit" >确定</button>
    </div>
</div>
</body>
<script>
    function submit(){
        let phone = $("input[name='phone']").val();
        if(!phone){
            layer.msg('请输入手机号！');
            return false;
        }
        $.post('reception-phone', {
            phone: phone
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

    $('.submit').click(function(){
        submit();
    });
    $('input[name="card"]').keyup(function(event){
        if(event.keyCode ==13){
            submit();
        }
    });
    $(function(){

        setTimeout(function(){
            $(".code").focus();
        },50)
    })
</script>
</html>