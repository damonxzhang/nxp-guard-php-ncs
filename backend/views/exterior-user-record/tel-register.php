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
    <div class="title_div" ><b><?=$text?></b></div>
    <input type="text" name="<?=$name?>" class="code" autocomplete="off">
    <div class="button-div">
        <button class="layui-btn submit" >确定</button>
    </div>
</div>
</body>
<script>
    function submit(){
        let phone = $("input[name='<?=$name?>']").val();
        if(!phone){
            layer.msg('<?=$text?>！');
            return false;
        }
        $.post('',{<?=$name?>:phone},function (res) {
            if(res.status){
                layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.x_admin_show('登记',res.data );

                    closeParentOpen();
                })
            }else{
                layer.msg(res.msg);
            }
        },'json')

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
