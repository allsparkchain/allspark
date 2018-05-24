<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英邀请函</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no, email=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/flexible.js"></script>
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/pgy_share.css') }}">
</head>
<body class="inviteBg">
    <div id="invite">
        <div class="avatorWrap">
            <div class="avator">
                <img src="{{$handurl}}">
            </div>
            <div class="avatorTxt">{{$realname}}<br>邀请你加入蒲公英,共同开启自分享传媒时代</div>
        </div>

        <div class="invite2"></div>
        <!-- <div class="invite3">
        我们这里有海量精选商品、优质原创内容就差你啦
        </div>-->
        <div class="invite4" ></div> 
    </div>
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/vue.js"></script>
    <script>
    $(function(){
      $('.invite4').click(function(){
          window.location.href = "{{route('s_share_inviteBind',['invite_code'=>$invite_code])}}"
      })


    })   
    </script>
</body>
</html>