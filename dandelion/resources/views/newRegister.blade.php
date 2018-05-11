<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>注册-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="site.webmanifest">
    <link rel="apple-touch-icon" href="icon.png">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <!-- Place favicon.ico in the root directory -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/sign_up.css') }}">
    <link rel="stylesheet" href="{{ mix('css/wei_sign.css') }}">
    <!--<link rel="stylesheet" href="css/D_header.css">-->
</head>
<body>
<div class="warp">
    <header>
        <div class="header_content">
            <div class="login">
                <a href="{{route('s_index_index')}}"><img src="/img/LoginLogo.png"></a>
            </div>
            <div class="userUp">
                <span onclick="window.location.href='/login'">登录 </span>
                <span class="activeSe">注册</span>
            </div>
        </div>
    </header>
    <div class="remindNav">
        <ul>
            <li>1</li>
            <li></li>
            <li>2</li>
            <li></li>
            <li>3</li>
        </ul>
    </div>
    <div class="remindNavT">
        <ul>
            <li>手机号验证</li>
            <li></li>
            <li>设置密码</li>
            <li></li>
            <li>注册成功</li>
        </ul>
    </div>
    <form id="register" action="">
    <div class="content">
        @if($code)
        <div class="user_login">
            <div class="imgBgs">
                <img src="{{$wxUserInfo['headimgurl']}}" width="70px" height="70px">
            </div>
            <p style="text-align: center">{{$wxUserInfo['nickname']}}</p>
        </div>
        @endif
        <div class="content_from">
            <div class="user_messgin">
                <input id="mobile" name="mobile" type="text" class="inputstyle" maxlength="11"  placeholder="输入你的手机号">
                <span id="userNameT" style="display: none">请输入正确的账号</span>
                <div>
                    <input id="code" name="code" type="text" class="inputstyle password" placeholder="输入验证码" maxlength="4">
                    <a class="user_runA" href="javascript:;">发送验证码</a>
                </div>
                <span id="userPasswordT" style="display: none">请输入正确验证码</span>


                <div class="y_hied" style="display: none"></div>

            </div>
        </div>
        <div class="content_chckbox">
            <input type="checkbox" id="unChecked"  name="unChecked">
            <span class="record" >剑指网络科技有限公司<a href="{{Route('s_index_useragreement')}}" target="_blank">《注册协议》</a></span>
            <p id="serUser" style="display: none">请同意注册协议</p>
        </div>

        <div class="runBottom">
            <a href="javascript:;" id="run_login">继&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;续</a>
        </div>
    </div>
    </form>
    <footer>
        <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: black;">沪ICP备16017440号</a>　</p>
    </footer>
</div>
<script type="text/javascript" src="/js/vendor/jquery-3.2.1.min.js"></script>
<script src="js/vendor/jquery.validate.min.js" type="text/javascript"></script>
<script src="js/vendor/messages_zh.min.js" type="text/javascript"></script>
<script src="js/vendor/jquery.cookie.js" type="text/javascript"></script>
<script src="js/function.js"></script>
<script src="{{ mix('js/main.js') }}"></script>
<script src="js/function.js"></script>
<script src="js/BigInt.js"></script>
<script src="js/aes.js"></script>
<script src="js/core-min.js"></script>
<script src="js/pad-zeropadding-min.js"></script>


<script>
    clearAES();
    {{--手机验证码--}}
            $("#mobile").blur(function () {
                var userName = document.getElementById('mobile').value;
                if (userName==""){
                }
                else {
                    if(!(/^1[34578]\d{9}$/.test(userName))){
                        $("#userNameT").css("display","block");
                        $("#userNameT").html("请输入正确的手机号");
                    }
                }
            });
            //验证码验证
            $("#code").blur(function () {
                var userName = document.getElementById('code').value;
                if (userName==""){
                    $("#userPasswordT").hide();
                }
                else {
                    if(!(/[0-9]{4}/.test(userName))){
                        $("#userPasswordT").show();
                        $("#userPasswordT").html("请输入正确验证码");
                    }
                    else {
                        $("#userPasswordT").hide();
                    }
                }
            });
            
    
            //点击发送验证码
            $(".user_runA").click(function () {
                // $(".user_runA").disabled=true;
                var userNAmeV= $("#mobile").val();
                if (userNAmeV===""){
                    $("#userNameT").css("display","block");
                    $("#userNameT").html("请输入手机号");
                }
                else {
                    var userName = document.getElementById('mobile').value;
                    if(!(/^1[34578]\d{9}$/.test(userName))){
                        $("#userNameT").css("display","block");
                        $("#userNameT").html("请输入正确的手机号");

                    }
                    else {
                        // var codefrist=$("#code").val();
                        unRegisterSms();

                        $("#userNameT").css("display","none");
                        // var mobileError= $("#mobile-error").class;
                        // console.log(mobileError);
                        // if(mobile-error){
                        //
                        // }
                        //
                        // sendRegisterSms();
                        // sendemail();
                        //
                        // $(".y_hied").show();
                        // setTimeout('$(".y_hied").hide()',60000);
                    }
                }
            });
        // 继续

        $("#run_login").click(function () {
            var userNAmeV= $("#mobile").val();
            if (userNAmeV===""){
                $("#userNameT").css("display","block");
                $("#userNameT").html("请输入手机号");
            }
            else {
                var code= $("#code").val();
                if(code===""){
                    $("#userPasswordT").show();
                    $("#userPasswordT").html("请输入验证码");
                }
                else {
                    // alert("codefris"+codefris);
                    var bischecked = $('#unChecked').is(':checked');
                    if (bischecked==true) {
                        // do something
                        //     alert("选中");
                        //     alert("yzmFirst:"+yzmFirst);
                            var  yzmlast = $("#mobile").val();
                            // alert("yzmFirst"+yzmFirst);
                            // alert("yzm:"+ yzmlast);
                            if(yzmlast===yzmFirst){
                                validatorRegisterSms();
                            }
                            else {
                                $("#userPasswordT").show();
                                $("#userPasswordT").html("手机号码错误或未发送验证码");
                            }
                            $("#serUser").hide();


                    }
                    else {
                        // alert("请选中");
                        $("#serUser").show();
                    }

                }
            }
        });
        // 倒计时
        var countdown=60;
        function sendemail(){
            var obj = $(".user_runA");
            settime(obj);
        }
        function settime(obj) { //发送验证码倒计时
            if (countdown == 0) {
                obj.prop('disabled',false);
                //obj.removeattr("disabled");
                obj.text("重新获取验证码");
                countdown = 60;
                $('.user_runA').css("backgroundColor","#ff7241");
                $('.user_runA').css("cursor","pointer");
                return;
            } else {
                obj.prop('disabled',true);
                obj.text("(" + countdown + "s)重新发送");
                countdown--;
                $('.user_runA').css("backgroundColor","#bebebe");
                $('.user_runA').css("cursor","auto");
            }
            setTimeout(function() {
                    settime(obj) }
                ,1000)
        }
        // function sendRegisterSms() {//发送验证码
        //     $.ajax({
        //         url:'/sendRegisterSms',
        //         type:'POST', //GET
        //         async:true,    //或false,是否异步
        //         data:{
        //             mobile: secretWithString($("#mobile").val())
        //         },
        //         "headers": {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         timeout:5000,    //超时时间
        //         dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
        //         success:function(data,textStatus,jqXHR){
        //             // clearAES();
        //             // yzm = true;
        //             if(data['status']==200){
        //                 console.log(data.message);
        //             }
        //             else {
        //                 console.log(data.msg);
        //             }
        //         }
        //     })
        // }


        function unRegisterSms() {//检测发送验证码
            $.ajax({
                url:'/checkMobile',
                type:'POST', //POST
                async:true,    //或false,是否异步
                data:{
                    mobile:$("#mobile").val(),
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data['status']==200){
                        $.ajax({
                            url:'/sendRegisterSms',
                            type:'POST', //GET
                            async:true,    //或false,是否异步
                            data:{
                                mobile: secretWithString($("#mobile").val())
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            timeout:5000,    //超时时间
                            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                            success:function(data,textStatus,jqXHR){
                                clearAES();
                                // yzm = true;
                                if(data['status']==200){
                                    // var mobilefirst=$("#mobile").val();
                                    // alert("已发送验证码");
                                    sendemail();
                                    // var yzy=return mobilefirst;
                                    yzmFirst = $("#mobile").val();
                                    $(".y_hied").show();
                                    setTimeout('$(".y_hied").hide()',60000);

                                }
                                else {
                                    console.log(data.msg);
                                    // alert("验证码发送失败");
                                }
                            }
                        })
                    }
                    else {
                        // alert(data['message']);
                        // alert("号码已经注册");
                        // alert(data['message']);
                        $("#userNameT").show();
                        $("#userNameT").html(data.msg);
                    }
                }
            })
        }
// alert("yzy"+mobilefirst);
        function validatorRegisterSms() {
            $.ajax({
                url:'/validatorRegisterSms',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    mobile:$("#mobile").val(),
                    code:$("#code").val()
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        window.location = '{{ route("s_setpassword") }}';
                        // var moves= document.getElementById("user_").getElementsByTagName("span");
                        // moves.removeChild();
                    } else {
                        $("#userPasswordT").css("display","block");
                        // alert( "继续"+data['message']);
                    }

                }
            });
        }


        //线条
        $("#mobile").focus(function () {
            $("#mobile").addClass("border_color");
            // console.log("xian");
        });
        $("#mobile").blur(function () {
            $("#mobile").removeClass("border_color");
        });
        $(".password").focus(function () {
            $(".password").addClass("border_color");
        });
        $(".password").blur(function () {
            $(".password").removeClass("border_color");
        });

        /////////////////////////////////
//        var userName = document.getElementById("userName");
//        var userPassword = document.getElementById("userPassword");
//        userName.onfocus = function () {
//            // userName.setAttribute("class", "border_color");
//            userName.classList.add("border_color");
//        }
//        userName.onkeyup = function () {//用户输入文字触发时间
//            // useruserNameT.style.opacity=1;//透明
//        }
//        userName.onblur = function () {
//            // useruserNameT.style.opacity=0;
//            userName.classList.remove("border_color");
//        }
//        userPassword.onfocus = function () {
//            userPassword.classList.add("border_color");
//        }
//        userPassword.onblur = function () {
//            // useruserNameT.style.opacity=0;
//            userPassword.classList.remove("border_color");
//        }
//    }
//    $(function () {
//        $("#userName").focus(function () {
//            $("#userName").addClass("border_color");
//        });
//        $("#userName").blur(function () {
//            $("#userName").removeClass("border_color");
//            var userName = document.getElementById('userName').value;
//            if(!(/^1[34578]\d{9}$/.test(userName))){
//                $("#userNameT").css("display","block");
//                return false;
//            }
//            else {
//                $("#userNameT").css("display","none");
//            }
//        });
//        $(".user_runA").click(function () {
//            // .setTimeout();
//            //     $('.user_runA').removeAttr('href');//去掉a标签中的href属性
//            //     $('.user_runA').removeAttr('onclick');
//            //     $('.user_runA').css("backgroundColor","#bebebe");
//            //     $('.user_runA').text("60S重新发送");
//
//            sendemail();
//        });
//        var countdown=60;
//        function sendemail(){
//            var obj = $(".user_runA");
//            settime(obj);
//
//        }
//        function settime(obj) { //发送验证码倒计时
//            if (countdown == 0) {
//                obj.attr('disabled',false);
//                //obj.removeattr("disabled");
//                obj.text("重新获取验证码");
//                countdown = 60;
//                $('.user_runA').css("backgroundColor","#ff7241");
//                $('.user_runA').css("cursor","pointer");
//                return;
//            } else {
//                obj.attr('disabled',true);
//                obj.text("(" + countdown + "s)重新发送");
//                countdown--;
//                $('.user_runA').css("backgroundColor","#bebebe");
//                $('.user_runA').css("cursor","auto");
//
//
//            }
//            setTimeout(function() {
//                    settime(obj) }
//                ,1000)
//        }
//    });
    $.ajax({
        url:  '/getkeys2',
        type: 'GET',
        async: false,
        data: {"mobile":secretWithString('13818282017')},
        cache: false,
        timeout: 5 * 1000,
        dataType: 'json',
        success: function(data, status, xhr) {
            clearAES();
            alert(data.B);
        },
        error: function() {
            alert(2222)
        }
    });
</script>
</body>
</html>
