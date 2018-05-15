<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>商品详情</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no, email=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--强制竖屏-->
    <meta name="screen-orientation" content="portrait">
    <!--点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">

    <link rel="stylesheet" type="text/css" href="{{ mix('css/header.css') }}">

    <script src="/js/vendor/jquery-3.2.1.min.js"></script>
    <title>@yield('title')</title>
</head>

<body>
<header>
    <div class="header_nav">
        <div class="header_nav_goBack">
            <div class="i" onclick="history.go(-1)">返回</div>
        </div>
        支付成功
        <title>@yield('header_title')</title>
        <div class="colse"></div>
    </div>
</header>




</body>
</html>