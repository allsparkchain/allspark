<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英 - 登录</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=1400">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="./js/vue.js"></script>
    <!-- <script type="text/javascript" src="/js/jquery.qrcode.min.js"></script> -->
    <script type="text/javascript" src="/js/qrcode.min.js"></script>
</head>
<body class="zmtdl_loginBg">
<div id="login" class="zmtdl_loginWrap">
    <header class="zmtdl_loginHeader">
        <div class="zmtdl_logo"></div>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
    </header>
    <div class="zmtdl_loginBox">
        <div class="zmtdl_loginBoxTab">
            <span class="on" style="float:left;">微信登录</span>
            <span>手机登录</span>
            <span style=" float:right;">邮箱登录</span>
        </div>

        <div id="qrcode" class="zmtdl_loginBoxBody">
            <div class="qrcode" id="loginCode"></div>
        </div>

        <div id="zmtdlLogin" class="zmtdl_loginBoxBody">
            <div style="position: relative;">
                <input id="username" class="zmtdl_input" maxlength="11" type="tel" v-model="username" placeholder="手机号">
            </div>
            <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg}}</div>
            <div>
                <input id="userpassword"class="zmtdl_input" maxlength="18" v-model="password" style="margin: 0;" type="password" placeholder="输入密码">
            </div>
            <a class="zmtdl_forgetpwd" href="{{route('s_forgetVerifyTel')}}">忘记密码</a>
            <input id="zmtdlLoginbtn" class="zmtdl_btn" type="submit" @click="toLogin" value="立 即 登 录">
        </div>

        <div id="zmtdlEmailLogin" class="zmtdl_loginBoxBody">
            <div style="position: relative;">
                <input  class="zmtdl_input" type="text" v-model="email" placeholder="请输入邮箱">
            </div>
            <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg2}}</div>
            <div>
                <input class="zmtdl_input" maxlength="18" v-model="passwd" style="margin: 0;" type="password" placeholder="输入密码">
            </div>
            <a class="zmtdl_forgetpwd" href="{{route('s_forgetVerifyTel')}}">忘记密码</a>
            <input id="zmtdlLoginbtn" class="zmtdl_btn" type="submit" @click="toLogin2" value="立 即 登 录">
        </div>

        <div class="zmtdl_toRegister">还没有账号?<a href="{{route('s_register')}}">立即注册</a></div>

    </div>
    <footer class="zmtdl_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</footer>
</div>
<script type="text/javascript">
    $(function(){
        var app = new Vue({
            el: '#login',
            data: {
                username:"",
                password:"",
                email:"",
                passwd:"",
                errMsg:"",
                errMsg2:""
            },
            created:function(){
                var _this=this;

            },
            mounted:function(){
                var _this=this;

            },
            methods:{
                tabSwitch:function(name){
                    if(name==="bindBank"){
                        this.bankShow=true;
                        this.passwordShow=false;
                    }else{
                        this.bankShow=false;
                        this.passwordShow=true;
                    }
                },
                toLogin:function(){
                    var _this=this;
                    if ( (/^1[34578]\d{9}$/.test(this.username)) && (/^[A-Za-z0-9]{8,18}$/.test(this.password)) ){
                        $.ajax({
                            url:"{{ route('s_auth_login') }}",
                            type:'POST',
                            async:true,
                            data:{
                                username:_this.username,
                                password:_this.password
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    location.href="{{route('s_user_accountInfo')}}";
                                }else{
                                    _this.errMsg=res.msg;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);                                       
                                }
                            }
                        });
                    }
                    else {
                        _this.errMsg="请输入正确格式的手机号或密码";
                        setTimeout(function(){
                            _this.errMsg="";
                        }, 3000);
                    }  
                },
                toLogin2:function(){
                    var _this=this;
                    if ( (/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/.test(this.email)) && (/^[A-Za-z0-9]{8,18}$/.test(this.passwd))){
                        $.ajax({
                            url:"{{ route('s_login_emailLogin') }}",
                            type:'POST',
                            async:true,
                            data:{
                                email:_this.email,
                                passwd:_this.passwd
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    location.href="{{route('s_user_accountInfo')}}";
                                }else{
                                    _this.errMsg2=res.message;
                                    setTimeout(function(){
                                        _this.errMsg2="";
                                    }, 3000);                                       
                                }
                            }
                        });
                    }
                    else {
                        _this.errMsg2="请输入正确格式的邮箱或密码";
                        setTimeout(function(){
                            _this.errMsg2="";
                        }, 3000);
                    }  
                }
            }
        })
        $(".zmtdl_logo").on("click",function(){
            var urls = "{{$wx_qrurl}}";
            //window.open(urls);
        });

        $(".zmtdl_loginBoxTab span").on("click",function(){
            $(this).addClass('on');
            $(this).siblings().removeClass('on');
            if($(this).index()===0){
                $("#qrcode").show();
                $("#zmtdlLogin").hide();
                $("#zmtdlEmailLogin").hide();
            }else if($(this).index()===1){
                $("#qrcode").hide();
                $("#zmtdlLogin").show();
                $("#zmtdlEmailLogin").hide();                   
            }else if($(this).index()===2){
                $("#qrcode").hide();
                $("#zmtdlLogin").hide();
                $("#zmtdlEmailLogin").show();
            }
        });

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

        //微信登录，，定时尝试自动登录或者注册
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



    })
</script>
</body>
</html>