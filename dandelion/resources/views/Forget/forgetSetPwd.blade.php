<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英 - 忘记密码验证</title>
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
<div id="forgetSetPwd">
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

            <div class="pgy_mobile_login" style="position: relative;padding-top: 25px; box-sizing: border-box;">
            <div style="position: absolute;top:0px;font-size: 12px; height: 13px; cursor: pointer; color: #7E7E7E;">●&nbsp;密码必须8位至18位，由英文与数字组成</div>
                <div class="pgy_input_wraps">
                    <i class="pgy_input_password"></i>
                    <input class="pgy_input2" maxlength="18" v-model="newpass" type="password" placeholder="创建登录密码">
                </div>               
                <div class="pgy_input_error"  v-html="errMsg">@{{errMsg}}</div>
                <div class="pgy_input_wraps">
                    <i class="pgy_input_password"></i>
                    <input  class="pgy_input2" maxlength="18" v-model="newpass2" type="password" placeholder="再次输入密码" >
                </div>
                <input style="margin-top:49px;width: 275px;" class="pgy_login_btn" type="button"  @click="toSetPwd" value="重新登录">
            </div>
            
        </div>
        <div class="pgy_login_right2">
            <ul class="pgy_login_right2_menu">
                <li style="cursor:default;" class="on" ><i class="mobileforgetIcon"></i>重建密码</li>
            </ul>
        </div>
    </div>

    <div class="pgy_login_footer2">©2018 剑指网络 ALL RIGHTS RESERVED. 沪ICP备07001687号　</div>
</div>
</body>
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
                                        location.href="{{route('s_login')}}";
                                    }else{
                                        _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                    }
                                }
                            });
                        } else if(_this.newpass!==_this.newpass2){
                            _this.errMsg="<i></i>两次密码不一致";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000); 
                        } else {
                            _this.errMsg="<i></i>密码格式错误";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);                        
                        }    
                    }
                }
            })
        });
    </script>

