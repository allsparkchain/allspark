<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英 - 忘记密码</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=1400">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
    <script src="/js/vue.js" type="text/javascript"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <style>
        body{
            background: #f2f2f2;
        }
    </style>
</head>
<body>
<div id="forgetVerifyTel">
    <header class="pgy_login2_header">
        <div class="pgy_login2_header_wrap">
            <div class="pgy_logo2"><a href="/"></a></div>
            <div class="pgy_logo2_txt">忘记密码</div>
            <div class="pgy_logo2_txt2" style="display:block">
                没有账号，<a href="{{route('s_register')}}">立即注册</a>
            </div>
        </div>
    </header>

    <div class="pgy_login_wrap2">
        <div class="pgy_login_left2">

            <div class="pgy_mobile_login" v-show="mobileShow">
                <div class="pgy_input_wraps">
                    <i class="pgy_input_mobile"></i>
                    <input id="username" class="pgy_input2" maxlength="11" v-model="mobile" type="tel"  @blur="checkmobile" placeholder="手机号">
                </div>                
                <div class="pgy_input_error" v-html="errMsg">@{{errMsg}}</div>
                <div class="pgy_send_code">
                        <input  class="pgy_vcodeinput" v-model="code" type="password" placeholder="输入验证码">
                        <input type="button" class="pgy_vcodeBtn" maxlength="4" v-model="validCode" :disabled="disabled" @click="sendMsg">
                </div>
                <input style="margin-top:64px;width: 275px;" class="pgy_login_btn" type="button"  @click="toSetPwd" value="继续">
            </div>
            <div class="pgy_loginEmail" v-show="emailShow">
                <div id="pgyLoginemail" class="ggzdl_loginBoxBody" style="width: 280px; margin: 0px auto 0 auto;">
                    <div id="pgyInputEmail"  style="position: relative;">
                        <div class="pgy_input_wraps">
                            <i class="pgy_input_email"></i>
                            <input id="email" class="pgy_input2" type="email" v-model="email" placeholder="邮箱">
                        </div>
                        <div style=" font-size: 12px; color: rgb(255, 114, 65); height: 30px; line-height: 30px; padding: 0px 10px;">@{{errMsg2}}</div>
                    </div>
                    <div>
                        <input style="margin: 0;" class="pgy_vcodeinput" v-model="code" type="password" placeholder="输入验证码">
                        <input type="button" class="pgy_vcodeBtn" maxlength="4" v-model="validCode" :disabled="disabled" @click="sendMsg">
                    </div>
                    <input style="margin-top:64px;width: 275px;" class="pgy_login_btn"  type="button"  value="继续">
                </div>
            </div>

        </div>
        <div class="pgy_login_right2">
            <ul class="pgy_login_right2_menu">
                <li  :class="{on:mobileShow}" @click="tabSwitch('mobile')"><i class="mobileloginIcon"></i>手机验证</li>
                <!-- <li :class="{on:wxShow}" @click="tabSwitch('wxLogin')"><i class="qrscanloginIcon"></i>扫码登录</li> -->
                <li :class="{on:emailShow}" @click="tabSwitch('email')"><i class="emailloginIcon"></i>邮箱验证</li>
            </ul>
        </div>
    </div>

    <div class="pgy_login_footer2">©2018 剑指网络 ALL RIGHTS RESERVED. 沪ICP备07001687号　</div>
</div>
</body>
    <script src="/js/jquery.cookie.js" type="text/javascript"></script>
    <script src="/js/function.js" type="text/javascript"></script>
    <script src="/js/BigInt.js" type="text/javascript"></script>
    <script src="/js/aes.js" type="text/javascript"></script>
    <script src="/js/pad-zeropadding-min.js"></script>
    <script type="text/javascript">
        $(function () {
            clearAES();
            var app = new Vue({
                el: '#forgetVerifyTel',
                data: {
                    mobileShow:true,
                    emailShow:false,
                    mobile:"",
                    mobileAES:"",
                    code:"",
                    errMsg:"",
                    validCode:"发送验证码",
                    disabled:false,
                    times:60,
                    email:"",
                    errMsg2:""
                },
                created:function(){

                },
                mounted:function(){
                    this.$nextTick(function() {
                        document.getElementById("forgetVerifyTel").style.display = "block";
                    });
                },
                methods:{
                    tabSwitch:function(name){
                        if(name==="mobile"){
                            this.mobileShow=true;
                            this.emailShow=false;
                        }else if(name==="email"){
                            this.mobileShow=false;
                            this.emailShow=true;
                        }
                    },
                    toSetPwd:function(){
                        var _this=this;
                        _this.mobileAES=secretWithString(_this.mobile);
                        $.ajax({
                            url:'{{route('s_validatorForgetSms')}}',
                            type:'POST',
                            async:true,
                            data:{
                                mobile:_this.mobileAES,
                                code:_this.code
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    location.href="{{route('s_forgetSetPwd')}}";
                                }else{
                                    _this.errMsg='<i></i>'+res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);
                                }
                            },
                            complete:function(){
                                clearAES();
                            }
                        });
                    },
                    checkmobile:function(){
                        var _this=this;
                        if( (/^1[34578]\d{9}$/.test(this.mobile)) ){

                        }else{
                            _this.errMsg="<i></i>请输入正确格式的手机号码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                        }
                    },
                    sendMsg:function(){
                        var _this=this;
                        _this.mobileAES=secretWithString(_this.mobile);
                        $.ajax({
                            url:'{{route('s_sms_sendForgetSms')}}',
                            type:'POST',
                            async:true,
                            data:{
                                mobile:_this.mobileAES
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    var countStart = setInterval(function () {
                                        _this.validCode = _this.times-- + '秒后重发';
                                        _this.disabled=true;
                                        if (_this.times < 0) {
                                            clearInterval(countStart);
                                            _this.validCode = "发送验证码";
                                            _this.times=60;
                                            _this.disabled=false;
                                        }
                                    }, 1000);
                                }else{
                                    _this.errMsg=res.msg;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);
                                }
                            },
                            complete:function(){
                                clearAES();
                            }
                        });
                    }
                }
            })
        });

    </script>


