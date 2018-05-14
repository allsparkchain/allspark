<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英 - 微信授权</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/js/vue.js"></script>
</head>
<body class="zmtdl_loginBg">
<div id="forgetVerifyTel" class="zmtdl_loginWrap">
    <header class="zmtdl_loginHeader">
        <div class="zmtdl_logo"></div>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
    </header>
    <div class="zmtdl_loginBox">
        <div class="zmtdl_loginBoxTab">
            <span>设置密码</span>
        </div>
        <div class="zmtdl_loginBoxBody">
            <input class="zmtdl_input" type="tel" v-model="mobile" placeholder="手机号">
            <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg}}</div>
            <input style="margin: 0;" class="zmtdl_vcodeinput" type="password" v-model="code" placeholder="输入验证码">
            <input type="button" class="zmtdl_vcodeBtn" :disabled="disabled" v-model="validCode" @click="sendMsg">
            <input class="zmtdl_btn" style="margin:40px 0 0 0;" @click="toSetPwd" type="button" value="继 续">
        </div>
        <div class="zmtdl_toRegister">已有账号?<a href="{{route('s_login')}}">返回登录</a></div>
    </div>
    <footer class="zmtdl_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：kefu@pugongying.com</footer>
</div>
<script type="text/javascript">
$(function () {
    var app = new Vue({
        el: '#forgetVerifyTel',
        data: {
            mobile:"",
            code:"",
            errMsg:"",
            validCode:"发送验证码",
            disabled:false,
            times:60
        },
        created:function(){

        },
        mounted:function(){

        },
        methods:{
            toSetPwd:function(){
                var _this=this;
                $.ajax({
                    url:'{{route('s_validatorForgetSms')}}',
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
                            location.href="{{route('s_forgetSetPwd')}}";
                        }else{
                            _this.errMsg=res.message;
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);                            
                        }
                    }
                });                
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
                        if(res.status==201) {
                            $.ajax({
                    url:'{{route('s_sms_sendForgetSms')}}',
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
                                _this.disabled=true;
                                if (_this.times < 0) {
                                    clearInterval(countStart);
                                    _this.validCode = "发送验证码";
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
                            setTimeout(function(){
                                _this.errMsg="手机号码不存在";
                            }, 1000);
                        }
                    }
                })
               
            }
        }
    })
});

</script>
</body>
</html>