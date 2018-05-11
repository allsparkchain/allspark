@extends('layout')

@section("title", "忘记密码")
@section("content")
@section("css")
<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
<link rel="stylesheet" href="{{ mix('css/password.css') }}">
@endsection
    <!-- Place favicon.ico in the root directory -->

        <p class="title_t">忘记密码</p>
    <from id="commentForm"  action="">
        <div class="content_from">
            <p class="from_span">手机号验证</p>
            <div class="user_messgin">
                <div class="mgint_T">
                    <i class="pUser"></i>
                    <input id="userName" type="text" class="inputstyle" placeholder="输入你的手机号" maxlength="11">
                </div>
                <span id="userNameT" class="userNameT"  style="display: none;">请输入正确的手机号</span>
                <div class="mgint_T">

                    <i class="yancode"></i>
                    <input id="userPassword" type="text" maxlength="4" class="inputstyle password" placeholder="输入验证码">
                    <a class="user_runA" href="javascript:;" >发送验证码</a>
                </div>

                <div class="y_hied" style="display: none;"></div>

                <div class="y_hied" style="display: none;"></div>

                <span id="userPasswordT" class="userPasswordT" style="display: none;">请输入正确验证码</span>
            </div>
        </div>
        <div class="runBottom">
            <a href="javascript:;" class="run_restpassword" > 继&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;续</a>
        </div>
    </from>
@endsection
@section("script")
    <script type="text/javascript">
        // $(function () {
        //         $("#commentForm").validate(function () {
        //             submitHandler:function(){
        //                 validatorSms();
        //             }
        //             rules: {
        //
        //
        //             }
        //             messages:{
        //
        //             }
        //
        //         });
        // });
        window.onload = function () {
            var userName = document.getElementById("userName");
            var userPassword = document.getElementById("userPassword");
            userName.onfocus = function () {
                // userName.setAttribute("class", "border_color");
                userName.classList.add("border_color");
            }
            userName.onkeyup = function () {//用户输入文字触发时间
                // useruserNameT.style.opacity=1;//透明
            }
            userName.onblur = function () {
                // useruserNameT.style.opacity=0;
                userName.classList.remove("border_color");
            }
            userPassword.onfocus = function () {
                userPassword.classList.add("border_color");
            }
            userPassword.onblur = function () {
                // useruserNameT.style.opacity=0;
                userPassword.classList.remove("border_color");
            }
        }
        $(function () {
        clearAES();
        $("#userName").blur(function () {
                    var Reg=/^1[34578]\d{9}$/;
                    var str=$("#userName").val();
                    if(Reg.test(str)){
                        $("#userNameT").css("display","none");
                        sendForgetSms();
                    }
                    else {
                        $("#userNameT").css("display","block");
                        // $("#userNameT").html("请输入正确电话号码");
                    }
        });
        //点击发送验证码
        $(".user_runA").click(function () {
            var str= $("#userName").val();
            if(str===""){
                $("#userNameT").css("display","block");
                $("#userNameT").html("请输入手机号");
            }
            var Reg=/^1[34578]\d{9}$/;
            var str=$("#userName").val();
            if (Reg.test(str)){
                sendCode();
            }
        });
        $("#userPassword").blur(function () {
            var Reg= /[0-9]{4}/;
            var str=$("#userPassword").val();
            if(Reg.test(str)){
                $("#userPasswordT").css("display","none");
            }
            else {
                $("#userPasswordT").css("display","block");
                $("#userPasswordT").html("验证码不对");
            }
        });

        $("#userPassword").click(function () {
            var str=$("#userPassword").val();
            if (str===""){
                $("#userPasswordT").css("display","block");
            }
        });
        $(".run_restpassword").click(function () {
            var userName= $("#userName").val();
            if(userName===""){
                $(".userNameT").show();
                $(".userNameT").html("请输入手机号");
            }
            else {
                var userPassword= $("#userPassword").val();
                if(userPassword===""){
                    $(".userPasswordT").show();
                    $(".userPasswordT").html("请输入验证码");
                }
                else {
                    validatorSms();
                }
            }
        });
        //60秒后执行
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



        //判断手机号是否存在
        function sendForgetSms() {
            $.ajax({
                url:'checkMobile',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    mobile:$("#userName").val()
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    //201 已存在  200 可以创建
                    if(data.status == 201){
                        // $("#userNameT").css("display","block");
                        // $("#userNameT").html("手机号已经存在");
                        return false;
                    }else{
                        $("#userNameT").css("display","block");
                        $("#userNameT").html("手机不存在");
                    }
                }
            })
        }
        //发送验证码
        function sendCode() {
                $.ajax({
                    url:'checkMobile',
                    type:'POST', //GET
                    async:true,    //或false,是否异步
                    data:{
                        mobile:$("#userName").val()
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    timeout:5000,    //超时时间
                    dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                    success:function(data,textStatus,jqXHR){
                        console.log(data)
                        //201 已存在  200 可以创建
                        if(data.status == 201){
                            $.ajax({
                                url:'/sendForgetSms',
                                type:'POST', //GET
                                async:true,    //或false,是否异步
                                data:{
                                    mobile:secretWithString($("#userName").val())
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                timeout:5000,    //超时时间
                                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                                success:function(data,textStatus,jqXHR){
                                    clearAES();
                                    if(data.status==200){
                                        yzm = true;
                                        sendemail();
                                        $(".y_hied").show();
                                        setTimeout('$(".y_hied").hide()',60000);
                                        // console.log("yzm"+yzm);
                                    }
                                    else {
                                        $("#userPasswordT").css("display","block");
                                        // $("#userPasswordT").html(data['message']);
                                        $(".userPasswordT").show();
                                        $(".userPasswordT").html(data['message']);
                                    }
                                }
                            })
                        }else{
                            $("#userNameT").css("display","block");
                            $("#userNameT").html("手机不存在");
                        }
                    }
                })
        }
        function validatorSms() {
            $.ajax({
                url:'/validatorForgetSms',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    mobile:$("#userName").val(),
                    code:$("#userPassword").val()
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        window.location="{{route('s_restsetPassword')}}";
                    }else{
                        $("#userPasswordT").css("display","block");
                        // $("#userPasswordT").html(data['message']);
                        $(".userPasswordT").show();
                        $(".userPasswordT").html(data['message']);
                    }
                }
            })
        }

        });
    </script>

@endsection



