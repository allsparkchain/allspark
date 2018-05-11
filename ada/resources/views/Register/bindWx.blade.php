<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英 - 微信授权</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=1400">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="./js/echarts.common.min.js"></script>
    <!-- <script type="text/javascript" src="/js/jquery.qrcode.min.js"></script> -->
    <script type="text/javascript" src="/js/qrcode.min.js"></script>
</head>
<body class="zmtdl_loginBg">
<div class="zmtdl_loginWrap">
    <header class="zmtdl_loginHeader">
        <div class="zmtdl_logo"></div>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
    </header>
    <div class="zmtdl_loginBox">
        <div class="zmtdl_loginBoxTab">
            <span>微信授权</span>
        </div>
        <div class="zmtdl_loginBoxBody">
            <div class="qrcode" id="loginCode"></div>
        </div>
        <div class="zmtdl_toRegister">已有账号?<a href="{{route('s_login')}}">返回登录</a></div>
    </div>
    <footer class="zmtdl_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</footer>
</div>
<script type="text/javascript">
    $(function(){
        //生成qr 图片
        // $(".qrcode").qrcode({
        //     render: "canvas", //table方式
        //     width: 191, //宽度
        //     height:191, //高度
        //     text: "{{ $wxHost }}auth/weixin/login/{{ $pc_jzstate }}/3" //任意内容
        // });
         //生成qr 图片
         var loginCode = new QRCode(document.getElementById('loginCode'), {
            width: 191,
            height: 191,
        });
        loginCode.makeCode("{{ $wxHost }}auth/weixin/login/{{ $pc_jzstate }}/3");

        //微信登录，，定时尝试自动登录
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
                        window.location = "/regBindMobile";
                    }else if(data.type==3){

                    }
                }
            })
        }

        function aaa(){
            setInterval(function (args) { ajaxLogin() },5000); //循环计数
        }


        aaa();

    });

</script>
</body>
</html>