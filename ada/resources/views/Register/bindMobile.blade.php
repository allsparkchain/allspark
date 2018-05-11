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
    <script src="/js/vue.js" type="text/javascript"></script>
</head>
<body class="zmtdl_loginBg">
<div id="bindMobile" class="zmtdl_loginWrap">
    <header class="zmtdl_loginHeader">
        <div class="zmtdl_logo"></div>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
    </header>
    <div class="zmtdl_loginBox">
        <div class="zmtdl_loginBoxTab">
            <span style="width:93px;">绑定手机号</span>
        </div>
        <div class="zmtdl_loginBoxBody">
            <input id="mobile" class="zmtdl_input" type="tel" placeholder="手机号" v-model="mobile" @blur="checkmobile">
            <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg}}</div>
            <input style="margin:0; width: 210px;" id="code" class="zmtdl_vcodeinput" type="password" v-model="code" placeholder="输入验证码">
            <input type="button" class="zmtdl_vcodeBtn" @click="sendMsg" :disabled="disabled" v-model="validCode">
            <div class="zmtdl_protocol">
            <input id="inputCheck" class="ggzdl_inputCheck" type="checkbox" checked="checked"><label for="inputCheck"></label>我已同意<a href="{{Route('s_agreement')}}" target="_blank">《蒲公英平台注册协议》</a>
            </div>
            <input class="zmtdl_btn" type="button" @click="toReg" value="下 一 步">
        </div>
        <div class="zmtdl_toRegister">已有账号?<a href="{{route('s_login')}}">返回登录</a></div>
    </div>
    <footer class="zmtdl_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</footer>
</div>
<script type="text/javascript">
    $(function(){
        var app = new Vue({
            el: '#bindMobile',
            data: {
                mobile:"",
                errMsg:"",
                code:"",
                validCode:"发送验证码",
                disabled:false,
                times:60
            },
            created:function(){
                var _this=this;
            },
            mounted:function(){
                var _this=this;
            },
            methods:{
                toReg:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_validator_register')}}',
                        type:'POST',
                        async:true,
                        data:{
                            mobile:_this.mobile,
                            code:_this.code
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                location.href="{{route('s_regSetPass')}}";
                            }else{
                                _this.errMsg=res.msg;
                                setTimeout(function(){
                                    _this.errMsg="";
                                }, 3000);
                            }
                        }
                    });                    
                },
                checkmobile:function(){
                        var _this=this;
                        if( (/^1[34578]\d{9}$/.test(this.mobile)) ){

                        }else{
                            _this.errMsg="请输入正确格式的手机号码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                        }
                    },
                sendMsg:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_sms_checkMobile')}}',
                        type:'POST',
                        async:true,
                        data:{
                            mobile:_this.mobile
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                $.ajax({
                                    url:'{{route('s_sms_register')}}',
                                    type:'POST',
                                    async:true,
                                    data:{
                                        mobile:_this.mobile
                                    },
                                    "headers": {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    dataType:'json',
                                    success:function(res){
                                        if(res.status==200) {
                                            var countStart = setInterval(function () {
                                                _this.validCode = _this.times-- + '秒后重发';
                                                if (_this.times < 0) {
                                                    clearInterval(countStart);
                                                    _this.validCode = "发送验证码";
                                                    _this.times=60,
                                                    _this.disabled=false;
                                                }
                                            }, 1000);
                                        }else{
                                            _this.errMsg=res.msg;
                                            setTimeout(function(){
                                                _this.errMsg="";
                                            }, 3000);
                                        }
                                    }
                                });
                            }else{
                                _this.errMsg=res.msg;
                                setTimeout(function(){
                                    _this.errMsg="";
                                }, 3000);
                            }
                        }
                    });
                }
            }
        });
    });
</script>
</body>
</html>