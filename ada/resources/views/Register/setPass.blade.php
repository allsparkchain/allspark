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
    <script type="text/javascript" src="/js/echarts.common.min.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/js/vue.js" type="text/javascript"></script>
</head>
<body class="zmtdl_loginBg">
<div id="setPass" class="zmtdl_loginWrap">
    <header class="zmtdl_loginHeader">
        <div class="zmtdl_logo"></div>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
    </header>
    <div class="zmtdl_loginBox">
        <div class="zmtdl_loginBoxTab" style=" text-align:left;">
            <span style="width:73px;">设置密码</span>
        </div>
        <div style=" font-size:12px; color:#7E7E7E;">●密码必须8位至18位，由英文与数字组成</div>
        <div class="zmtdl_loginBoxBody">
            <input class="zmtdl_input" type="password" v-model="passwd" placeholder="创建登录密码">
            <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg}}</div>
            <input id="passwd1" type="password" v-model="passwd_confirmation" style="margin: 0;" class="zmtdl_input" type="password" placeholder="请再次输入">
            <input class="zmtdl_btn" style="margin:40px 0 0 0;" @click="toReg" type="button" value="完成">
        </div>
        <div class="zmtdl_toRegister">已有账号?<a href="{{route('s_login')}}">返回登录</a></div>
    </div>
    <footer class="zmtdl_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</footer>
</div>
<script type="text/javascript">
    $(function(){
        var app = new Vue({
            el: '#setPass',
            data: {
                passwd:"",
                passwd_confirmation:"",
                errMsg:""
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
                    if ( (/^[A-Za-z0-9]{8,18}$/.test(_this.passwd)) && (/^[A-Za-z0-9]{8,18}$/.test(_this.passwd_confirmation)) && _this.passwd===_this.passwd_confirmation ) {
                        $.ajax({
                            url:'{{route('s_reg_setRegPwd')}}',
                            type:'POST',
                            async:true,
                            data:{
                                passwd:_this.passwd,
                                passwd_confirmation:_this.passwd_confirmation
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    $.ajax({
                                        url:'{{route('s_auth_register')}}',
                                        type:'POST',
                                        async:true,
                                        data:{

                                        },
                                        "headers": {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        dataType:'json',
                                        success:function(res){
                                            if(res.status==200) {
                                            location.href="{{route('s_regSuccess')}}";
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
                    } else {
                        _this.errMsg="请输入正确的密码格式";
                        setTimeout(function(){
                            _this.errMsg="";
                        }, 3000);                        
                    }
                }
            }
        });

    });

</script>
</body>
</html>