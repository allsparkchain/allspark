<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>关注我们</title>
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
    <link rel="stylesheet" type="text/css" href="{{ mix('css/commodity.css') }}">
    <style>
    </style>
</head>
<body>

<div id="contactWechat"  style=" display: none;">
   <div class="contact_custom">

    <div class="about_weChat">
            <img src="/images/erweimas.png" alt="">
            <p>长按二维码关注蒲公英
                海量原创，优选内容，效果结算，共同开启自分享传媒时代！</p>
    </div>
   </div>
</div>

</body>
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/vue.js"></script>
<script>
$(function(){
   
    var app=new Vue({ 
        el:'#contactWechat',
        data:{
            showtype:1,
            orderList:[],
            page:1
        },
        created:function(){
            this.getOrder();
        },
        mounted:function(){
            var _this=this;

            this.$nextTick(function() {
                document.getElementById("contactWechat").style.display = "block";
            });
        },
        methods:{

        }
    })
});
</script>
</html>