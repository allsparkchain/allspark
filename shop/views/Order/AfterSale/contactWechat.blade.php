<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>联系客服</title>
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
    <ul>
            <li>-Step1-</li>
            <li>-Step2-</li>
    </ul>
    <ul>
            <li><img src="/images/step1.jpg" alt=""></li>
            <li><img src="/images/step2.jpg" alt=""></li>
    </ul>
    <div class="about_weChat">
            <img src="/images/erweimas.png" alt="">
            <p>长按二维码关注后与我们沟通</p>
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