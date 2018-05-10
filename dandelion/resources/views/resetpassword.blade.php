@extends('layout')
@section("title", "重置密码")
@section("content")
@section("content")
@section("css")
<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
<link rel="stylesheet" href="{{ mix('css/reset_password.css') }}">
@endsection

        <p class="title_t">忘记密码</p>
        <div class="content_from">
            <p class="from_span">创建登录密码</p>
            <div class="user_messgin">
                <div class="mgint_T">
                    <i class="password_long"></i>
                    <input id="userName" type="password" class="inputstyle" placeholder="输入你的密码" maxlength="20">
                    <span id="userNameT" class="userNameT" style="display: none;">请输入正确密码</span>
                </div>
                <div class="mgint_T">
                    <i class="password_long"></i>
                    <input id="userPassword" type="password" class="inputstyle password" placeholder="再次输入密码" maxlength="20">
                    <span id="userNameT1" class="userNameT1" style="display: none;">两次输入密码不一样</span>
                </div>


                {{--<p id="failure" class="failure">验证码失效</p>--}}
                <p id="userPasswordT" class="user_title">密码必须8位至20位，由英文与数字组成</p>
                {{--<span id="userNameT1" class="userNameT1" style="display: none;">请输入验证码</span>--}}
            </div>
        </div>

        <div class="runBottom">
            <a href="javascript:;" class="run_lgion" >重新登录</a>
        </div>
@endsection
@section("script")
    <script type="text/javascript">
        window.onload=function () {
            var userName= document.getElementById("userName");
            var userPassword = document.getElementById("userPassword");
            userName.onfocus=function () {
                // userName.setAttribute("class", "border_color");
                userName.classList.add("border_color");
            }
            userName.onkeyup=function () {//用户输入文字触发时间
                // useruserNameT.style.opacity=1;//透明
            }
            userName.onblur=function () {
                // useruserNameT.style.opacity=0;
                userName.classList.remove("border_color");
            }
            userPassword.onfocus=function () {
                userPassword.classList.add("border_color");
            }
            userPassword.onblur=function () {
                // useruserNameT.style.opacity=0;
                userPassword.classList.remove("border_color");
            }
        }
        $("#userName").blur(function () {
            var Reg=/^[A-Za-z0-9]{8,20}$/;
            var str=$("#userName").val();
            if (Reg.test(str)){
                $(".userNameT").css("display","none");
            }
            else {
                $(".userNameT").css("display","block");
                $(".userNameT").html("输入正确的密码");
            }
        });
        $("#userPassword").blur(function () {
            var userName=$("#userName").val();
            var userPassword=$("#userPassword").val();
            if (userName===userPassword){
                $(".userNameT1").css("display","none");
            }
            else {
                $(".userNameT1").css("display","block");
                $(".userNameT1").html("两次输入密码不一致");
            }
        });
        $(".run_lgion").click(function () {
            // if($("#userName").val()===""){
            //     $("#userNameT").css("display","block");
            var userName=$("#userName").val();
            if(userName===""){
                $("#userNameT").show();
                $("#userNameT").html("请输入密码");

            }
            else {
                var Reg=/^[A-Za-z0-9]{8,20}$/;
                if (Reg.test(userName)){
                    var userPassword=$("#userPassword").val();
                    if  (userPassword===""){
                        $(".userNameT1").show();
                        $(".userNameT1").html("请输入密码");

                    }
                    else {
                        if(userName===userPassword){
                            resetpasswd();
                        }
                        // resetpasswd();

                    }
                }
            }


            // var str=$("#userName").val();
            // var userPassword=$("#userPassword").val();
            // if (Reg.test(str)){
            //     $(".userNameT").css("display","none");
            //     if(str===userPassword){
            //         resetpasswd();
            //     }
            //
            // }
            // }
        });
        function resetpasswd() {
            $.ajax({
                url:'/restsetPasswordPost',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    newpass:$("#userName").val(),
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        window.location="{{route('s_user_accountInfo')}}";
                    }else{
                        $("#userNameT1").css("display","block");
                        $("#userNameT1").html(data['message']);
                    }
                }
            })
        }
    </script>


@endsection

