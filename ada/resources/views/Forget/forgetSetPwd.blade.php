<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英 - 微信授权</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <script type="text/javascript" src="/js/echarts.common.min.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/js/vue.js"></script>
</head>
<body class="zmtdl_loginBg">
<div id="forgetSetPwd" class="zmtdl_loginWrap">
    <header class="zmtdl_loginHeader">
        <div class="zmtdl_logo"></div>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
    </header>
    <div class="zmtdl_loginBox">
        <div class="zmtdl_loginBoxTab" style=" text-align:left;">
            <span>设置密码</span>
        </div>
        <div style=" font-size:12px; color:#7E7E7E;">●密码必须8位至18位，由英文与数字组成</div>
        <div class="zmtdl_loginBoxBody">
            <input class="zmtdl_input" type="password" v-model="newpass" max-length=18 placeholder="创建登录密码">
            <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg}}</div>
            <input style="margin: 0;" class="zmtdl_input" v-model="newpass2" type="password" max-length=18 placeholder="请再次输入">
            <input class="zmtdl_btn" style="margin:40px 0 0 0;" @click="toSetPwd" type="button" value="完成">
        </div>
        <div class="zmtdl_toRegister">已有账号?<a href="{{route('s_login')}}">返回登录</a></div>
    </div>
    <footer class="zmtdl_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：kefu@pugongying.com</footer>
</div>
<script type="text/javascript">
$(function () {
    var app = new Vue({
        el: '#forgetSetPwd',
        data: {
            newpass:"",
            newpass2:"",
            errMsg:""
        },
        created:function(){

        },
        mounted:function(){

        },
        methods:{
            toSetPwd:function(){
                var _this=this;
                if( (/^[A-Za-z0-9]{8,18}$/.test(_this.newpass)) && (/^[A-Za-z0-9]{8,18}$/.test(_this.newpass2)) && _this.newpass===_this.newpass2 ){
                    $.ajax({
                        url:'{{route('s_restsetPasswordPost')}}',
                        type:'POST',
                        async:true,
                        data:{
                            newpass:_this.newpass
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                location.href="{{route('s_forgetSetPwdSuccess')}}";
                            }else{
                                _this.errMsg=res.message;
                                setTimeout(function(){
                                    _this.errMsg="";
                                }, 3000);                            
                            }
                        }
                    }); 
                }else{
                    _this.errMsg="请输入正确的密码格式";
                    setTimeout(function(){
                        _this.errMsg="";
                    }, 3000);
                }               
            }
        }
    })
});
</script>
</body>
</html>