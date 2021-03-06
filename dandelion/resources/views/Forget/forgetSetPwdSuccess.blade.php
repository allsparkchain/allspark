<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英 - 微信授权</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=1400">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <script type="text/javascript" src="/js/echarts.common.min.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
</head>
<body class="zmtdl_loginBg">
<div class="zmtdl_loginWrap">
    <header class="zmtdl_loginHeader">
        <div class="zmtdl_logo"></div>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;自媒体代理后台
        <a style="margin:0 0 0 60px;" class="zmtdl_headerLink" href="#">API接口文档</a>
        <a class="zmtdl_headerLink" href="#">合同模板</a>
    </header>
    <div class="zmtdl_loginBox">
        <div class="zmtdl_loginBoxBody">
            <i class="zmtdl_success"></i>
            <p class="zmtdl_toLogin">密码修改成功,前往<a href="{{route('s_login')}}">登录</a></p>
        </div>
        <div class="zmtdl_toRegister">已有账号?<a href="{{route('s_login')}}">返回登录</a></div>
    </div>
    <footer class="zmtdl_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</footer>
</div>
</body>
</html>