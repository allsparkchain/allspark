<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>我要分享</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no, email=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <!--强制竖屏-->
    <meta name="screen-orientation" content="portrait">
    <!--点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
    <!--<link rel="stylesheet" type="text/css" href="css/header.css">-->
    <link rel="stylesheet" type="text/css" href="{{ mix('css/share.css') }}">
</head>
<body>
<div class="centent">
    <p class="title">{{$info['name']}}</p>
    <p class="timeSers">{{date('Y-m-d H:i',$info['add_time'])}}</p>
    <div class="cententFont">
        {!! html_entity_decode($info['content']) !!}
    </div>

    <div class="erS">
        <div class="erS_img">
            <div id="code"></div>
        </div>
        <div class="bttoms">长按识别二维码进行购买</div>
    </div>
    
</div>
<script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
<!-- <script type="text/javascript" src="/js/vue.js"></script> -->
<script type="text/javascript" src="/js/qrcode.min.js"></script>
<script src="{{config('params.httpType')}}://res.wx.qq.com/open/js/jweixin-1.3.2.js" type="text/javascript" charset="utf-8"></script>
<script>
$(function(){
    wx.config(<?php echo $app->jssdk->buildConfig(array('onMenuShareTimeline', 'onMenuShareAppMessage'), false) ?>);
    wx.ready(function () {
        wx.onMenuShareTimeline({
            title: "{{$info['name']}}",
            link: window.location.href,
            imgUrl: "{{$img_path}}",
            success: function success() {},
            cancel: function cancel() {}
        });
        wx.onMenuShareAppMessage({
            title: "{{$info['name']}}",
            desc: "{{$info['summary']}}",
            link: window.location.href,
            imgUrl: "{{$img_path}}",
            type: '',
            dataUrl: '',
            success: function success() {},
            cancel: function cancel() {}
        });
    });

    //生成qr 图片
    var loginCode = new QRCode(document.getElementById('code'), {
        width: 191,
        height: 191,
    });
    loginCode.makeCode("{!! $url !!}");

    $("img").attr("style","");
    var deviceWidth=$(document).width();
    $(".cententFont section").each(function(){
        if($(this).width()>deviceWidth){
            $(this).css("width","100%");
        }
    });

    $(".cententFont div").each(function(){
        if($(this).width()>deviceWidth){
            $(this).css("width","100%");
        }
    });

    $(".Powered-by-XIUMI .V5").css("width","auto");

    $(".cententFont img").each(function(){
        var w = $(this)[0].naturalWidth;
        var h = $(this)[0].naturalHeight;
        if(w>deviceWidth){
            $(this).css("width","100%")
        }else{

        }
    });
})





</script>
</body>
</html>
