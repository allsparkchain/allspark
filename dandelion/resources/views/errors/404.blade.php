<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404出错-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="icon.png">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <!-- Place favicon.ico in the root directory -->
    <link rel="stylesheet" href="{{ mix('css/D_header.css') }}">
    <link rel="stylesheet" href="{{ mix('css/404.css') }}">
</head>
<body>
<header>
    <div class="header_content">
        <div class="login">
            <a href="{{route('s_index_index')}}"><img src="/images/userLogo.png"></a>
        </div>
    </div>
</header>
<div class="centent">
    <div class="center_wrop"></div>
    <div class="font">
        <p>HI，真不巧，网页走丢了。</p>
    </div>
    <div class="bottom">
        <a href="{{route('s_index_index')}}">返回首页</a>
        <i onclick="window.location.reload()" style="cursor: pointer"></i>
    </div>
</div>
</body>
</html>
