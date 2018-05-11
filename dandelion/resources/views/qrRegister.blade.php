<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>微信登录-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <!-- Place favicon.ico in the root directory -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/D_mian.css') }}">
    <link rel="stylesheet" href="{{ mix('css/weixin_in.css') }}">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

</head>
<body>
<div class="warp">
    <header>
        <div class="header_content">
            <div class="login">
                <a href="/"><img src="/img/LoginLogo.png"></a>
            </div>
            <div class="userUp">
                <span onclick="window.location.href='/login'">登录 </span>
                <span class="activeSe">注册</span>
            </div>
        </div>
    </header>
    <div class="content">
        <div class="auro_">
            <div class="from">
                <div class="wropUser">
                    <ul>
                        <li><a href="javacript:void(0)" class="DserWei">微信验证</a></li>
                    </ul>
                </div>
                <div class="DserPhoneCentent">
                    <div class="userWei">
                        <div class="userWeiWrop">
                            <div id="code"></div>
                            <p>请使用微信扫描二维码登录</p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<footer>
    <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: black;">沪ICP备16017440号</a>　</p>
</footer>
</div>
<script src="js/vendor/jquery-3.3.1.min.js" type="text/javascript"></script>
</body>
</html>
<script type="text/javascript" src="/js/vendor/jquery-3.2.1.min.js"></script>
<script src="js/vendor/jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript" src="/js/vendor/jquery.qrcode.min.js"></script>

<!--<script src="js/sign_in.js"></script>-->
<script>
    $("#code").qrcode({
        render: "canvas", //table方式
        width: 191, //宽度
        height:191, //高度
        text: "{{ $wxHost }}auth/weixin/login/{{ $pc_jzstate }}/1" //任意内容
    });


    $(function(){
        function ajaxLogin() {
            $.ajax({
                url:"{{route('s_weixin_ajax_login')}}?_date="+new Date(),
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    code:"{{ $pc_jzstate }}",
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:600000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.type==1){
                        //登录
                        window.location = "{{route('s_user_accountInfo')}}";
                    }else if(data.type==2){
                        //注册
                        window.location = "/register";
                    }else if(data.type==3){

                    }
                }
            })
        }

        function aaa(){
            setInterval(function (args) { ajaxLogin() },5000); //循环计数
        }

        aaa();
    })
</script>
