@extends('layout')

@section("title", "注册")

@section("content")
@section("css")
<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
<link rel="stylesheet" href="{{ mix('css/sign_up.css') }}">
@endsection


    <form id="register" action="">
    <p class="title_t">我要注册</p>

        <div class="content_from">
            <p class="from_span">手机注册</p>
            <div class="user_messgin" id="user_messgin">
                <div style="margin-top: 22px;">
                    <i class="login_na"></i>
                    <input id="mobile" type="text" name="mobile" class="inputstyle" maxlength="11" placeholder="输入你的手机号">
                </div>
                <div style="margin-top: 20px">
                    <a class="user_runA" id="user_runA" href="javascript:;" >发送验证码</a>
                    <i class="pUser"></i>
                    <div style="width:  130px !important; overflow:hidden;"><input id="code" type="text" name="code" class="inputstyle password" placeholder="输入验证码" maxlength="4"></div>
                </div>
                <div class="y_hied" style="display: none"></div>
                <span id="userPasswordT" style="display: none">请输入正确验证码</span>
            </div>
        </div>
    <div class="runBottom">
        <span onclick="window.location.href='{{route('s_login')}}'">已有账号？去登录></span>
        <a href="javascript:;" id="run_login">继&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;续</a>
    </div>
    <div class="content_chckbox">
        <div>
                <span class="record" >典星网络科技有限公司<span onclick="window.location.href='{{route('s_index_useragreement')}}'">《注册协议》</span></span>

        <input type="checkbox" id="unChecked"  name="unChecked" style="margin-top: 6px;float: left;"></div>
    </div>
        <p class="zu_C"style="display: none">请同意注册协议</p>
    </form>
@endsection
@section("script")
    <script type="text/javascript">


        var yzm = false;
        $(function () {
            clearAES();
            $("#register").validate({
                submitHandler:function(){
                    validatorRegisterSms();
                },
                rules: {
                    mobile: {
                        required: true,
                        f_mobile: true,
                        remote: {
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            timeout:5000,    //超时时间
                            type: "post",
                            //请求方式
                            url:'/checkMobile',
                            data: {
                                username: function() {
                                    return $("#mobile").val();     //id名为“txtUserName”中的值
                                }
                            },
                            dataType: "json",        //发送的数据类型
                            dataFilter: function(data, type) { //返回结果
                                data = eval('(' + data + ')');
                                if (data['status'] == 200){
                                    return true;
                                    // console.log("手机号");
                                }
                                else{
                                     return false;
                                }
                            }
                        }
                    },
                    code: {
                        required: true,
                        f_code: true
                    }
                    ,
                    unChecked: "required"

                },
                messages: {
                    mobile: {
                        required: "请输入用正确的手机号",
                        remote:"手机号已经注册",
                    },
                    code: {
                        required: "请输入验证码"
                    },
                    unChecked: "请同意注册协议"
                },


            });
            $(".user_runA").click(function () {
                $(".user_runA").disabled=true;
                var userNAmeV= $("#mobile").val();
                if (userNAmeV===""){
                    $("#userNameT").css("display","block");
                    $("#userNameT").text("请输入用正确的手机号");
                }
                else {
                    var userName = document.getElementById('mobile').value;
                    if(!(/^1[34578]\d{9}$/.test(userName))){
                        $("#userNameT").css("display","block");
                        // $("#userNameT").text("请输入正确的手机号");
                        $("#userPasswordT").hide();
                    }
                    else {
                        $("#userNameT").css("display","none");
                        // var mobileError= $("#mobile-error").class;
                        // console.log(mobileError);
                        // if(mobile-error){
                        //
                        // }

                        sendRegisterSms();
                        sendemail();

                        $(".y_hied").show();
                        setTimeout('$(".y_hied").hide()',60000);
                    }
                }
            });
        });

        $("#run_login").click(function () {
            $("#code-error").hide();
            if (!yzm) {
                var mobile=$("#mobile").val();
                if(mobile!=""){
                    $("#code-error").hide();
                    $("#userPasswordT").show();
                    $("#userPasswordT").html("手机号码错误或未发送验证码");
                    return;
                }
            }
            else {
                $("#code-error").hide();
                $("#userPasswordT").hide();
            }
            $("#register").submit();
        });
        // 倒计时
        var countdown=60;
        function sendemail(){
            var obj = $(".user_runA");
            settime(obj);
        }
        function settime(obj) { //发送验证码倒计时
            if (countdown == 0) {
                obj.prop('disabled',false);
                //obj.removeattr("disabled");
                obj.text("重新获取验证码");
                countdown = 60;
                $('.user_runA').css("backgroundColor","#ff7241");
                $('.user_runA').css("cursor","pointer");
                return;
            } else {
                obj.prop('disabled',true);
                obj.text("(" + countdown + "s)重新发送");
                countdown--;
                $('.user_runA').css("backgroundColor","#bebebe");
                $('.user_runA').css("cursor","auto");
            }
            setTimeout(function() {
                    settime(obj) }
                ,1000)
        }


        function sendRegisterSms() {//发送验证码
            $.ajax({
                url:'/sendRegisterSms',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    mobile: secretWithString($("#mobile").val())
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    clearAES();
                    yzm = true;
                }
            })
        }
        function unRegisterSms() {
            $.ajax({
                url:'/checkMobile',
                type:'POST', //POST
                async:true,    //或false,是否异步
                data:{
                    mobile:$("#userName").val(),
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data['status']==200){
                    }
                    else {
                        // alert(data['message']);
                        // alert("号码已经注册");
                        // alert(data['message']);
                        $("#userNameT").css("display","block");
                        $("#userNameT").html("账号已经注册");
                    }
                }
            })
        }
        function validatorRegisterSms() {
            $.ajax({
                url:'/validatorRegisterSms',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    mobile:$("#mobile").val(),
                    code:$("#code").val()
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        window.location = '{{ route("s_setpassword") }}';
                        // var moves= document.getElementById("user_").getElementsByTagName("span");
                        // moves.removeChild();
                    } else {
                        $("#userPasswordT").css("display","block");
                        $("#userPasswordT").html(data['message']);
                    }
                }
            });
        }
        //线条
        $("#mobile").focus(function () {
            $("#mobile").addClass("border_color");
            // console.log("xian");
        });
        $("#mobile").blur(function () {
            $("#mobile").removeClass("border_color");
        });
        $(".password").focus(function () {
            $(".password").addClass("border_color");
        });
        $(".password").blur(function () {
            $(".password").removeClass("border_color");
        });




    </script>
@endsection
