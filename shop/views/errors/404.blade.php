<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 - 蒲公英</title>
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
    <link rel="stylesheet" type="text/css" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/error.css') }}">
    <style>

    </style>
</head>
<body>
<div class="pgy_error">
    <div class="pgy_center_icon"></div>
    <div class="pgy_font">
        <p>哎呀！出错了</p>
        <p>您要访问的页面暂时未找到</p>
    </div>
    
    <div class="pgy_bottoms">
        <a href="{{route('s_order_orderHistoryList')}}">重新加载</a>
    </div>
    
</div>

</body>
</html>