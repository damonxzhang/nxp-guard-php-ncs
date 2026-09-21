<?php

use yii\helpers\Url;
?>
<div class="x-body layui-anim layui-anim-up">
    <form class = "layui-form">
        <div class="layui-col-sm6">
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                <span class = "x-red">*</span>姓名:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "name"  autocomplete = "off" lay-verify = "required" class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                <span class = "x-red">*</span>身份证号:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "idcard" maxlength="18"  autocomplete = "off" lay-verify = "required" class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                <span class="x-red">*</span>EBDate:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "ebdate" id="ebdate_time"  lay-verify="required"   autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                EBarcode:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "ebarcode"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                WorkID:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "workid"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"></label>
            <div class="layui-input-block">
                <button class="layui-btn layui-btn-sm" type="button"  onclick="img_confirm()">
                    <i class="layui-icon">&#xe67c;</i>上传图片
                </button>
                <input id="idUpload" type="file" style="display: none" onchange="fileUp(this)" />
                <input type="hidden" name="img" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label for = "nickname" class = "layui-form-label">
            </label>
            <div class = "layui-input-inline">
                <div id="filepath"></div>
            </div>
        </div>
        </div>

        <div class="layui-col-sm6">
         <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                DepID:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "depid"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                CompID:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "compid"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                ContactID:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "contactid"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                ContactName:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "contactname"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                ContactDepart:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "contactdepart"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        <div class = "layui-form-item">
            <label for = "nickname" class = "layui-form-label">
                DepName:
            </label>
            <div class = "layui-input-inline">
                <input type = "text" name = "depname"  autocomplete = "off"  class = "layui-input">
            </div>
        </div>
        </div>
        <div class = "layui-form-item">
             <label for = "" class = "layui-form-label"></label>
            <button class = "layui-btn  layui-bg-cyan" lay-filter = "add" lay-submit = "">
                添加
            </button>
        </div>
    </form>
</div>
<?php $this->beginBlock('footer') ?>
    <script>
     //删除图片
     $(document).on('click', '.layui-btn-danger', function () {
         var _this = $(this).parents('span');
         layer.confirm('是否确认删除?', function (index) {
             _this.remove();
             layer.closeAll();
         });
     });

     layui.laydate.render({
         elem: '#ebdate_time'
     });

     function img_confirm() {
         layer.confirm(" <div style=\"text-align: center\">\n" +
             "        <div style=\"height: 50px\"></div>\n" +
             "        <h1 style=\"font-size:30px; \">用户须知</h1>\n" +
             "    </div>\n" +
             "    <div style=\"border: #5e5e5e solid 1px;width: 80%;margin: 10px auto;padding: 10px\">\n" +
             "        来访人员须知：恩智浦半导体有限公司（以下统称“恩智浦”）一贯重视用户的个人信息及隐私的保护，在您使用恩智浦的服务时，恩智浦有可能会收集和使用您的个人信息。为此，恩智浦通过本《恩智浦用户个人信息及隐私保护政策》（以下简称“本《隐私政策》”）向您说明您在使用恩智浦的服务时，通过此系统收集的信息仅用于恩智浦内部系统。\n" +
             "        <br />\n" +
             "        <br />您如果使用或者继续使用恩智浦的服务，即视为您充分理解并完全接受本《隐私政策》；您如果对本《隐私政策》有任何疑问、异议或者不能完全接受本《隐私政策》，请联系恩智浦对应接待人。\n" +
             "        <br />\n" +
             "        <br />第一条：本《隐私政策》所述的个人信息类型包括：\n" +
             "        <br />\n" +
             "        <br />1.登记信息（如，用户名、人脸信息，及在登记过程中提供的其他信息等）；\n" +
             "        <br />2.个人联系方式（如，姓名、电话手机号码）；\n" +
             "        <br />3.其他个人、公司、单位、或社会实体信息。\n" +
             "        <br />4.疫情期间，国家防疫防控要求的行程信息和健康码\n" +
             "        <br />\n" +
             "        <br />第二条：您承诺并保证：您主动填写或者提供给恩智浦的个人信息是真实的、准确的、完整的。而且，填写或者提供给恩智浦后，如果发生了变更的，您会在第一时间内，通过原有的渠道或者恩智浦提供的新的渠道进行更新，以确保恩智浦所获得的您的这些个人信息是最新的、真实的、准确的和完整的；否则，恩智浦无须承担由此给您造成的任何损失。\n" +
             "        <br />\n" +
             "        <br />第三条：您应当重视您的个人信息的保护，您如果发现您的个人信息已经被泄露或者存在被泄露的可能，且有可能会危及您登记获得的恩智浦账户安全，或者给您造成其他的损失的，您务必在第一时间通知恩智浦，以便恩智浦采取相应的措施确保您的恩智浦账户安全，防止损失的发生或者进一步扩大；否则，恩智浦无须承担由此给您造成的任何损失（及扩大的损失）。\n" +
             "        <br />\n" +
             "        <br />第四条：您充分理解并完全接受：保护用户信息是恩智浦一贯的政策，恩智浦将会使用各种安全技术和程序存储、保护用户信息，防止其被未经授权的访问、使用、复制和泄露。同时会定期清除，我们不会向第三方出售或以其他方式共享您的个人信息，但存在下列任何一项情形除外：\n" +
             "        <br />\n" +
             "        <br />1.根据国家法律、法规及法律程序的规定；\n" +
             "        <br />2.根据政府部门（如行政机构、司法机构）的要求；\n" +
             "        <br />\n" +
             "        <br />第五条：您充分理解并完全接受：即便是恩智浦采取各种安全技术和程序存储、保护用户信息，防止其被未经授权的访问、使用、复制和泄露，但用户信息仍然有可能发生被黑客攻击、窃取，因不可抗力或者其他非恩智浦的自身原因而被泄露的情形。对此，只要是恩智浦采取了必要的措施防止上述情形之发生，并在上述情形发生之后及时通知用户信息泄露的情况，并采取必要的措施防止其损失进一步扩大，恩智浦则无须赔偿由此给您造成的任何损失。\n" +
             "        <br />\n" +
             "    </div>", {
             btn: ['确认','取消'],
             area: ['520px', '340px'], //宽高
         }, function(index){
             layer.close(index);
             $('#idUpload').click();
         });
     }

     layui.use(['form', 'layer'], function () {
         $ = layui.jquery;
         var form = layui.form, layer = layui.layer;
         //监听提交
         form.on('submit(add)', function (data) {
             layer.load(3);
             $.post('<?= Url::to(['user-contractor/create']) ?>', data.field, function (res) {
                 layer.closeAll();
                 if (res.status === 200) {
                     layer.msg(res.msg, {icon: 1, time: 1500}, function () {
                         var index = parent.layer.getFrameIndex(window.name);
                         parent.layer.close(index);
                         parent.location.reload();
                     })
                 } else {
                     layer.msg(res.msg, {icon: 2, time: 1500})
                 }
             }, 'json');
             return false;
         });
     });

     layui.use('layer', function(){
         var layer = layui.layer;
     });

     var fileUp = function (param) {
          var fileInput = document.getElementById('idUpload')
          var file = fileInput.files[0] // 获取上传图片的信息
          EXIF.getData(file, function() {
             Orientation = EXIF.getTag(this, 'Orientation'); //获取图片Orientation
             switch (Orientation) {
                 case 1: // 不需要选择，正常
                     callback(file);
                     break
                 case 6: // 顺时针（向左）90度旋转
                     rotatePhoto(file, callback);
                     break
                 case 8: // 逆时针（向右）90度旋转
                     rotatePhoto(file, callback);
                     break
                 case 3: // 180度旋转
                     break
                default:
                    callback(file);
                    break
             }
          });
     };

     // 图片旋转成功后的回调函数
     //这个imgFile就是旋转后图片
     var callback = function (imgFile) {
         var formData = new FormData();
         formData.append("file", imgFile);
         // 上传图片到服务器
         $.ajax({
             method: "POST",
             url: "upload",
             data: formData,
             processData: false,
             dataType: "json",
             contentType: false,
             success: function (res) {
                 //上传完毕
                 if (res.status == 200) {
                     var html = '<span style="margin-bottom:20px;">' +
                         '<input type="hidden" name="empphoto" value="' + res.data.file_name + '">' +
                         '<img src=<?php $baseUrl ?>"' + res.data.img_url + '" width="280" height="200" alt="" style="margin-bottom:20px"  class="layui-btn-danger">' +
                         '</span >';
                     $('#filepath').html(html);
                 } else {
                     layer.msg(res.msg);
                 }
             },
             error: function (data, e) {
                 layer.msg('上传失败');
             }
         });
     }

     //layui.use('upload', function(){
     //    var $ = layui.jquery  ,upload = layui.upload;
     //    //执行实例
     //    var uploadInst = upload.render({
     //        elem: '#idUpload', //绑定元素
     //        url: 'upload', //上传接口
     //        done: function (res) {
     //            //上传完毕
     //            if (res.status == 200) {
     //                var html = '<span style="margin-bottom:20px;">' +
     //                    '<input type="hidden" name="empphoto" value="' + res.data.file_name + '">' +
     //                    '<img src=<?php //$baseUrl ?>//"' + res.data.img_url + '" width="280" height="200" alt="" style="margin-bottom:20px"  class="layui-btn-danger">' +
     //                    '</span >';
     //                $('#filepath').html(html);
     //            } else {
     //                layer.msg(res.msg);
     //            }
     //        }
     //    });
     //});

     /**
      *对图片旋转处理
      *
      * @param {*} img
      * @param {*} direction 旋转方向，left和right
      * @param {*} canvas
      * @returns
      */
     function rotateImg(img, direction, canvas) {
         //最小与最大旋转方向，图片旋转4次后回到原方向
         var min_step = 0;
         var max_step = 3;

         //img的高度和宽度不能在img元素隐藏后获取，否则会出错
         if (img == null) {
             return;
         }
         var height = img.height;
         var width = img.width;

         // var step = 0;
         var step = 2;
         if (direction == 'right') {
             step++;
             //旋转到原位置，即超过最大值
             step > max_step && (step = min_step);
         } else {
             step--;
             step < min_step && (step = max_step);
         }
         //旋转角度以弧度值为参数
         var degree = step * 90 * Math.PI / 180;
         var ctx = canvas.getContext('2d');
         switch (step) {
             case 0:
                 canvas.width = width;
                 canvas.height = height;
                 ctx.drawImage(img, 0, 0);
                 break;
             case 1:
                 canvas.width = height;
                 canvas.height = width;
                 ctx.rotate(degree);
                 ctx.drawImage(img, 0, -height);
                 break;
             case 2:
                 canvas.width = width;
                 canvas.height = height;
                 ctx.rotate(degree);
                 ctx.drawImage(img, -width, -height);
                 break;
             case 3:
                 canvas.width = height;
                 canvas.height = width;
                 ctx.rotate(degree);
                 ctx.drawImage(img, -width, 0);
                 break;
         }
     }

     /**
      *将base64转换成file对象
      *
      * @param {*} dataurl 图片的base64编码
      * @param {*} filename file对象类型
      * @returns
      */
     function dataURLtoFile(dataurl, filename = 'file') {
         let arr = dataurl.split(',')
         let mime = arr[0].match(/:(.*?);/)[1]
         let suffix = mime.split('/')[1]
         let bstr = atob(arr[1])
         let n = bstr.length
         let u8arr = new Uint8Array(n)
         while (n--) {
             u8arr[n] = bstr.charCodeAt(n)
         }
         return new File([u8arr], `${filename}.${suffix}`, {
             type: mime
         })
     }

     /**
      *旋转手机拍照变横后的照片
      *
      * @param {*} file 传入当前的input type=file
      * @param {*} callback 回调函数，照片旋转后执行的事件
      * @returns
      */
     function rotatePhoto(file, callback) {
         var Orientation = null;
         if (file) {
             //获取照片方向角属性，用户旋转控制
             EXIF.getData(file, function () {
                 EXIF.getAllTags(this);
                 Orientation = EXIF.getTag(this, 'Orientation');
             });

             var oReader = new FileReader();
             oReader.onload = function (e) {
                 var image = new Image();
                 image.src = e.target.result;
                 image.onload = function () {
                     var expectWidth = this.naturalWidth;
                     var expectHeight = this.naturalHeight;
                     if (this.naturalWidth > this.naturalHeight && this.naturalWidth > 800) {
                         expectWidth = 800;
                         expectHeight = expectWidth * this.naturalHeight / this.naturalWidth;
                     } else if (this.naturalHeight > this.naturalWidth && this.naturalHeight > 1200) {
                         expectHeight = 1200;
                         expectWidth = expectHeight * this.naturalWidth / this.naturalHeight;
                     }
                     var canvas = document.createElement("canvas");
                     var ctx = canvas.getContext("2d");
                     canvas.width = expectWidth;
                     canvas.height = expectHeight;
                     ctx.drawImage(this, 0, 0, expectWidth, expectHeight);
                     var u = navigator.userAgent;
                     if (Orientation != "" && Orientation != 1) {
                         //alert('旋转处理');
                         switch (Orientation) {
                             case 6: //需要顺时针（向左）90度旋转

                                 rotateImg(this, 'left', canvas);
                                 break;
                             case 8: //需要逆时针（向右）90度旋转

                                 rotateImg(this, 'right', canvas);
                                 break;
                             case 3: //需要180度旋转

                                 rotateImg(this, 'right', canvas); //转两次
                                 rotateImg(this, 'right', canvas);
                                 break;
                         }
                     }
                     base64 = canvas.toDataURL("image/jpeg", 0.8);
                     var type = 'jpeg';
                     var fixtype = function (type) {
                         type = type.toLocaleLowerCase().replace(/jpg/i, 'jpeg');
                         var r = type.match(/png|jpeg|bmp|gif/)[0];
                         return 'image/' + r;
                     };
                     base64 = base64.replace(fixtype(type), 'image/jpeg');
                     // 将base64转换成file对象
                     var base64ToFile = dataURLtoFile(base64);
                     //把已经旋转过的图片file对象通过回调函数返回
                     callback(base64ToFile);
                 };
             };
             oReader.readAsDataURL(file);
         }
     }
</script>
<?php $this->endBlock() ?>