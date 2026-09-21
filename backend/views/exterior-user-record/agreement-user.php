<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
    <div style="text-align: center">
        <div style="height: 50px"></div>
        <h1 style="font-size:30px; ">用户须知</h1>
    </div>
    <div style="border: #5e5e5e solid 1px;width: 80%;margin: 10px auto;padding: 10px">
        来访人员须知：恩智浦半导体有限公司（以下统称“恩智浦”）一贯重视用户的个人信息及隐私的保护，在您使用恩智浦的服务时，恩智浦有可能会收集和使用您的个人信息。为此，恩智浦通过本《恩智浦用户个人信息及隐私保护政策》（以下简称“本《隐私政策》”）向您说明您在使用恩智浦的服务时，通过此系统收集的信息仅用于恩智浦内部系统。
        <br />
        <br />您如果使用或者继续使用恩智浦的服务，即视为您充分理解并完全接受本《隐私政策》；您如果对本《隐私政策》有任何疑问、异议或者不能完全接受本《隐私政策》，请联系恩智浦对应接待人。
        <br />
        <br />第一条：本《隐私政策》所述的个人信息类型包括：
        <br />
        <br />1.登记信息（如，用户名、人脸信息，及在登记过程中提供的其他信息等）；
        <br />2.个人联系方式（如，姓名、电话手机号码）；
        <br />3.其他个人、公司、单位、或社会实体信息。
        <br />4.疫情期间，国家防疫防控要求的行程信息和健康码
        <br />
        <br />第二条：您承诺并保证：您主动填写或者提供给恩智浦的个人信息是真实的、准确的、完整的。而且，填写或者提供给恩智浦后，如果发生了变更的，您会在第一时间内，通过原有的渠道或者恩智浦提供的新的渠道进行更新，以确保恩智浦所获得的您的这些个人信息是最新的、真实的、准确的和完整的；否则，恩智浦无须承担由此给您造成的任何损失。
        <br />
        <br />第三条：您应当重视您的个人信息的保护，您如果发现您的个人信息已经被泄露或者存在被泄露的可能，且有可能会危及您登记获得的恩智浦账户安全，或者给您造成其他的损失的，您务必在第一时间通知恩智浦，以便恩智浦采取相应的措施确保您的恩智浦账户安全，防止损失的发生或者进一步扩大；否则，恩智浦无须承担由此给您造成的任何损失（及扩大的损失）。
        <br />
        <br />第四条：您充分理解并完全接受：保护用户信息是恩智浦一贯的政策，恩智浦将会使用各种安全技术和程序存储、保护用户信息，防止其被未经授权的访问、使用、复制和泄露。同时会定期清除，我们不会向第三方出售或以其他方式共享您的个人信息，但存在下列任何一项情形除外：
        <br />
        <br />1.根据国家法律、法规及法律程序的规定；
        <br />2.根据政府部门（如行政机构、司法机构）的要求；
        <br />
        <br />第五条：您充分理解并完全接受：即便是恩智浦采取各种安全技术和程序存储、保护用户信息，防止其被未经授权的访问、使用、复制和泄露，但用户信息仍然有可能发生被黑客攻击、窃取，因不可抗力或者其他非恩智浦的自身原因而被泄露的情形。对此，只要是恩智浦采取了必要的措施防止上述情形之发生，并在上述情形发生之后及时通知用户信息泄露的情况，并采取必要的措施防止其损失进一步扩大，恩智浦则无须赔偿由此给您造成的任何损失。
        <br />
    </div>
    <div style="margin:20px auto;width: 80%">
       
        <?= Html::a('接受','javascript:;',['class' =>'layui-btn submit'])?>
        <?= Html::a('不接受','javascript:;',['class' => 'layui-btn layui-btn-primary no']) ?>
    </div>
<?php

$js = <<<JS
    $('.no').click(function(){
        closeParentOpen();
    });
    $('.submit').click(function(){
    
        $.ajax({
            url: 'reception-agreement-user',
            type: 'get',
            data: {id: $id},
            dataType: 'json',
            success: function (res) {
                if(res.status == 200){
                layer.msg(res.msg, {icon: 1,time: 1500}, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.x_admin_show('登记', 'reception-user?id=' + res.data.id);
                
                })
                }else{
                    layer.msg(res.msg);
                }
            }
        });
    });
JS;

$this->registerJs($js);
