<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>设置密码-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="site.webmanifest">
    <link rel="apple-touch-icon" href="icon.png">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/set_password.css') }}">

</head>
<body>
<div class="warp">
    <header>
        <div class="header_content">
            <div class="login">
                <a href="index.html"><img src="/img/LoginLogo.png"></a>
            </div>
            <div class="userUp">
                <span onclick="window.location.href='{{route('s_login')}}'">登录</span>
                <span>注册</span>
            </div>
        </div>
    </header>
    <div class="remindNav">
        <ul>
            <li>1</li>
            <li></li>
            <li>2</li>
            <li></li>
            <li>3</li>
        </ul>
    </div>
    <div class="remindNavT">
        <ul>
            <li>手机号验证</li>
            <li></li>
            <li>设置密码</li>
            <li></li>
            <li>注册成功</li>
        </ul>
    </div>
    <div class="content">
        @include('layouts.errors')
        <form action="{{ route("s_register") }}"  id="form_1" method="post">
            {{ csrf_field() }}
        <div class="content_from">
            <div class="user_messgin">
                <input id="passwd" name="passwd" type="password" class="inputstyle" placeholder="输入你的密码">
                <span id="userNameT" style="display: none ">密码必须为8-18位，不能有特殊符号</span>
                <span class="userPasswordA" style="display: none;">密码必须为8-18位，不能有特殊符号</span>

                <input id="passwd1" type="password" name="passwd_confirmation" class="inputstyle password" placeholder="再次输入密码">
                <span id="userPasswordT" style="display: none;">两次输入的密码不一样</span>
                <span class="userPasswordB" style="display: none;">密码不一致</span>
                <!--<p id="userPasswordT" class="user_title">&lt;!&ndash;密码必须8位至16位，由英文与数字组成&ndash;&gt;</p>-->
            </div>
        </div>
        <div class="runBottom">
            <a href="javascript:void(0);" id="run_a">下一步</a>
        </div>
        </form>
    </div>
    <footer>
        <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>
    </footer>
</div>
<script src="/js/vendor/jquery-3.2.1.min.js"></script>
<script>
    $("#passwd").focus(function () {
        $("#passwd").addClass("border_color");
    });
    $("#passwd").blur(function () {
        $("#passwd").removeClass("border_color");
        var password = document.getElementById('passwd').value;
        if(password===""){
                $("#userNameT").hide();
        }
        else {
            $("#userNameT").show();
            // console.log(password);
            if ((/^[A-Za-z0-9]{8,18}$/.test(password))){
                $("#userNameT").css("display","none");

            }
            else {
                $("#userNameT").css("display","block");
                $("#userNameT").html("请输入正确的密码");

            }

        }



    });
    //二次输入密码
    $("#passwd1").focus(function () {
        $("#userPasswordT").addClass("border_color");
    });
    $("#passwd1").blur(function () {
        $("#userPasswordT").removeClass("border_color");
        var password = document.getElementById('passwd').value;
        var password1 = document.getElementById('passwd1').value;
        if (password===password1){
            $("#userPasswordT").css("display","none");//正确
        }
        else {
            $("#userPasswordT").css("display","block");//正确
        }
        // console.log(password);
    });

    $("#run_a").click(function () {
        var passwd=$("#passwd").val();
        if(passwd===""){
            $("#userNameT").show();
            $("#userNameT").html("请输入密码");
        }
        else {
            $("#userNameT").hide();
            var Reg=/^[A-Za-z0-9]{8,20}$/;
            if (Reg.test(passwd)){
                // var passwd1=$("#passwd1").val();
                // if(password1===""){
                //     $("#userPasswordT").show();
                //     $("#userPasswordT").html("请输入密码");
                // }
                // else {
                // }
                var passwd1=$("#passwd1").val();
                if (passwd1==passwd){
                    register();
                }
            }
            else {
                $("#userNameT").show();
                $("#userNameT").html("请输入正确密码");
            }

        }
        // var password = document.getElementById('passwd').value;
        // var password1 = document.getElementById('passwd1').value;
        // if (password==""){
        //     return false;
        // }
        // $("#passwd").removeClass("border_color");
        // var password = document.getElementById('passwd').value;
        // var Reg=/^[A-Za-z0-9]{8,18}$/;
        // if (Reg.test(password)){
        //     if(password1===password){
        //
        //     }
        //     else {
        //         $("#userPasswordT").show();
        //
        //     }
        //
        // }
        // else {
        //     $("#userNameT").css("display","block");
        // }
    });


    function register() {
        $("#form_1").submit();
    }

//    window.onload=function () {
//        var userName= document.getElementById("userName");
//        var userPassword = document.getElementById("userPassword");
//        userName.onfocus=function () {
//            // userName.setAttribute("class", "border_color");
//            userName.classList.add("border_color");
//        }
//        userName.onkeyup=function () {//用户输入文字触发时间
//            // useruserNameT.style.opacity=1;//透明
//        }
//        userName.onblur=function () {
//            // useruserNameT.style.opacity=0;
//            userName.classList.remove("border_color");
//        }
//        userPassword.onfocus=function () {
//            userPassword.classList.add("border_color");
//        }
//        userPassword.onblur=function () {
//            // useruserNameT.style.opacity=0;
//            userPassword.classList.remove("border_color");
//        }
//    }
//    $(function () {
//        // $("#userName").focus(function () {
//        //     $("#userName").addClass("border_color");
//        // });
//        $("#userName").blur(function () {
//            var password = document.getElementById('userName').value;
//            // $("#userName").removeClass("border_color");
//            console.log(password);
//            if (!(/^[A-Za-z0-9]{8,18}$/.test(password))){
//                $(".userPasswordA").css("display","block");
//                return false;
//            }
//            else {
//                $("#userPasswordA").css("display","none");
//            }
//        });
//    });
</script>
</body>
</html>
