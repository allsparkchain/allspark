<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>注册成功-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="site.webmanifest">
    <link rel="apple-touch-icon" href="icon.png">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/registered_successfully.css') }}">

</head>
<body>
<div class="warp">
    <header>
        <div class="header_content">
            <div class="login">
                <a href="/"><img src="/img/LoginLogo.png"></a>
            </div>
            <div class="userUp">
                <span>登录 </span>
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
        <div class="content_from">
            <div class="successfully">
                <div class="successfullyFdr">
                    <p>恭喜您注册成功</p>
                </div>

            </div>
        </div>
        <div class="runBottom">
            <a href="{{Route('s_user_accountInfo')}}" class="sevew">前往个人中心</a>
            <a href="/" >返回首页</a>
        </div>
    </div>
    <footer>
        <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>
    </footer>
</div>
<script src="/js/vendor/jquery-3.2.1.min.js"></script>
<script>
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
    $(function () {
        // $("#userName").focus(function () {
        //     $("#userName").addClass("border_color");
        // });
        $("#userName").blur(function () {
            var password = document.getElementById('userName').value;
            // $("#userName").removeClass("border_color");
            console.log(password);
            if (!(/^[A-Za-z0-9]{8,18}$/.test(password))){
                $(".userPasswordA").css("display","block");
                return false;
            }
            else {
                $("#userPasswordA").css("display","none");
            }
        });
    });
</script>
</body>
</html>
