<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英邀请函</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no, email=no">
    <script src="/js/flexible.js"></script>
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/pgy_share.css') }}">
</head>
<body class="inviteSuccess">
    <div class="inviteSuccess"></div>
    <div class="inviteTxt">
        你还可以用电脑打开网站<br>www.pugongying.link
    </div>
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script>
    $(function(){
        $(".inviteSuccess").on("click",function(){
            location.href="{{$url}}";
        });
    })    
    </script>
</body>
</html>