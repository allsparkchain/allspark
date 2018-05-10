<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>登录-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <!-- Place favicon.ico in the root directory -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/D_mian.css') }}">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="{{ mix('css/sign_in.css') }}">
</head>
<body>
<div class="warp">
    <header>
        <div class="header_content">
            <div class="login">
                <a href="/"><img src="/img/LoginLogo.png"></a>
            </div>
            <div class="userUp">
                <span>登录 </span>
                <span onclick="window.location.href='/qrRegister'">注册</span>
            </div>
        </div>
    </header>
    <div class="content">
        <div class="auro_">
            <form action="{{ route("s_auth_login") }}"  id="form_1"  method="post">
                {{ csrf_field() }}
                <input type="hidden" name="url" value="{{$url}}">
            <div class="from" >
                <div class="wropUser">
                    <ul>
                        <li><a href="javascript:void(0)" class="activeDser DserPhone">手机登录</a></li>
                        <li><a href="javascript:void(0)" class="DserWei">微信登录</a></li>
                    </ul>
                </div>
                <div class="DserPhoneCentent">
                    <div class="userDlu" >
                        <div class="user_messgin">
                            <div>
                                <input autocomplete="off" id="username" name="username" onkeyup="value = this.value.replace(/[^0-9.]/g, '');" maxlength="11" type="text" class="inputstyle" placeholder="输入手机号">
                                <div class="rightTxi" style="display: none;"></div>
                                <span id="userNameT" style="display: none">请输入正确的账号</span>
                            </div>
                            <input id="userPassword" type="password" name="password" class="inputstyle password" placeholder="密码">
                            <span id="userPasswordT" style="display: none">请输入正确的密码</span>
                            @if (count($errors) > 0)

                                <span id="userPasswordT">{{ \Illuminate\Support\Arr::get($errors->all(), 0) }}</span>

                            @endif
                        </div>
                        <div class="bottom">
                            <div class="bottom_right">
                                <input type="checkbox" id="unChecked" style="display:none;">
                                <span class="wesr" style="display:none;">记住密码</span>
                                <span onclick="window.location.href='{{route('s_forgetpassword')}}'">忘记密码</span>
                            </div>
                        </div>
                        <div class="runBottom">
                            {{--onclick="formSubmit();--}}
                            <a href="javascript:;" class="userLogin">立即登录</a>
                        </div>
                    </div>
                    <div class="userWei" style="display: none">
                        <div class="userWeiWrop">
                            <div id="code"></div>
                            <p>请使用微信扫描二维码登录</p>
                        </div>

                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
<footer>
    <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: black;">沪ICP备16017440号</a>　</p>
</footer>
</div>
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
var wxLogin = 1;
    $(".DserPhone").click(function () {
        $(".userDlu").show();
        $(".userWei").hide();
        $(".DserPhone").addClass("activeDser");
        $(".DserWei").removeClass("activeDser");
    });
    $(".DserWei").click(function () {
        $(".userWei").show();
        $(".userDlu").hide();
        $(".DserWei").addClass("activeDser");
        $(".DserPhone").removeClass("activeDser");
        if(wxLogin==1){
            wxLogin = 2;
            aaa();
        }
    });
    //登录用户名
    $("#username").blur(function () {
        var username = document.getElementById('username').value;
        $("#username").removeClass("border_color");
        if(username==""){
            $("#userNameT").hide();
        }
        else{
            if(!(/^1[34578]\d{9}$/.test(username))){
                $("#userNameT").show();
                // return false;
            }
            else {
                $("#userNameT").hide();
            }
        }
    });
    //密码
    $("#userPassword").blur(function () {
        var password = document.getElementById('userPassword').value;
        $("#userPassword").removeClass("border_color");
        if(password==""){
            $("#userPasswordT").css("display","none");
        }
        else {
            // console.log(password);
            if (!(/^[A-Za-z0-9]{8,18}$/.test(password))){
                $("#userPasswordT").css("display","block");
                return false;
            }
            else {
                $("#userPasswordT").css("display","none");
            }
        }
    });

    //登录
    $(".userLogin").click(function () {



        var username = $("#username").val();
        $("#username").removeClass("border_color");
        if(username==""){
            $("#userNameT").show();
        }else{
            $("#userNameT").show();
            $("#userNameT").html("请输入验用户名");

            if(!(/^1[34578]\d{9}$/.test(username))){
                $("#userNameT").show();
                // return false;
            }else {
                $("#userNameT").hide();
                var password = document.getElementById('userPassword').value;
                $("#userPassword").removeClass("border_color");
                if(password==""){
                    $("#userPasswordT").css("display","none");
                }
                else {
                    // console.log(password);
                    formSubmit();
                }

            }
        }
        });
    
    
    

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
        setInterval("ajaxLogin()",3000); //循环计数
    }
/////////////
    $(document).ready(function(){
//            $("input").attr("value","");
        $("#form_1").validate({
            rules: {
                userName: {
                    required: true,
                    f_mobile: true
                },
                userPassword: {
                    required: true,
                    f_passwd: true
                }
            },
            messages: {
                userName: {
                    required: "请输入用户名"
                },
                userPassword: {
                    required: "请输入密码"
                }
            }
        });
    });
    $(".as").click(function () {
        // if(jkdgh)
        // shun();
    });
    function formSubmit() {
        $("#form_1").submit();
    }


    //光标事件线条
    $("#userName").focus(function () {
        $("#userName").addClass("border_color");
    });
    $("#userName").blur(function () {
        $("#userName").removeClass("border_color");
    });
    $(".password").focus(function () {
        $(".password").addClass("border_color");
    });
    $(".password").blur(function () {
        $(".password").removeClass("border_color");
    });

    // var Huialert=document.getElementsByClassName("Huialert");
    // Huialert.cssText="font-size: 14px;color: #ff7241;position: absolute;";
    // var uls=Huialert.getElementsByTagName("ul");
    // alert("uls");
    $(".Huialert ul").addClass("lisr");
    $(".Hui-iconfont").css("display"," none");
    // alert($(".Hui-article"));
</script>
<!-- Google Analytics: change UA-XXXXX-Y to be your site's ID. -->
</body>
</html>
