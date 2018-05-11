@extends('Register.layout')


@section("title", "注册-设置密码")


@section("css")
{{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="setPass">
    <div class="pgy_reg_user">
        <img src="{{$wxUserInfo['headimgurl'] or ''}}">
        <div class="reg_userName">{{$wxUserInfo['nickname'] or ''}}</div>
        <div class="reg_authority"></div>
    </div>
    <div class="pgy_remindNav">
        <ul>
            <li>1</li>
            <li class="pgy_bgLine"></li>
            <li class="pgy_bgColor">2</li>
            <li></li>
            <li>3</li>
        </ul>
    </div>
    <div class="pgy_remindNavT">
        <ul>
            <li>绑定手机号</li>
            <li></li>
            <li class="pgy_color_orgin">设置详细信息</li>
            <li></li>
            <li>注册成功</li>
        </ul>
    </div>
    <div class="pgy_sendContent">
        <div class="pgy_1200">
            <div class="pgy_send_codes">
                <div class="pgy_mobile_login" style="position: relative;margin-top:112px;">
                    <div style="position: absolute;top:-27px;font-size: 12px; height: 36px; cursor: pointer; color: #7E7E7E;">●密码必须8位至18位，由英文与数字组成</div>
                        <div class="pgy_input_wraps">
                            <i class="pgy_input_password"></i>
                            <input class="pgy_input2" type="password" maxlength="18" v-model="passwd"  placeholder="创建登录密码">
                        </div>               
                        <div class="pgy_input_error" v-html="errMsg">@{{errMsg}}</div>
                        <div class="pgy_input_wraps">
                            <i class="pgy_input_password"></i>
                            <input class="pgy_input2" v-model="passwd_confirmation" type="password" maxlength="18" placeholder="再次输入密码">
                        </div>
                        <input class="ggzdl_btn" style="margin:40px 0 0 0;" @click="toReg" type="button" value="下一步">
                    </div>


                <!-- <div class="ggzdl_loginBoxBody">
                    <li style="position: absolute;top:-20px;">密码必须8位至18位，由英文与数字组成</li>
                    <input class="ggzdl_input" type="password" maxlength="18" v-model="passwd"  placeholder="创建登录密码">
                    <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg}}</div>
                    <input style="margin: 0;" class="ggzdl_input" v-model="passwd_confirmation" type="password" maxlength="18" placeholder="请再次输入">
                    <input class="ggzdl_btn" style="margin:40px 0 0 0;" @click="toReg" type="button" value="完成">
                </div> -->

            </div>

        </div>
    </div>
</div>
@endsection

@section("script")

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
                                                    sessionStorage.setItem("regSuccess",true);//存
                                                    location.href="{{route('s_regSuccess')}}";
                                                }else{
                                                    _this.errMsg="<i></i>"+res.msg;
                                                    setTimeout(function(){
                                                        _this.errMsg="";
                                                    }, 3000);
                                                }
                                            }
                                        });    
                                     
                                    }else{
                                        _this.errMsg="<i></i>"+res.msg;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                    }
                                }
                            });
                        } else if(_this.passwd!==_this.passwd_confirmation){
                            _this.errMsg="<i></i>两次密码不一致";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000); 
                        } else {
                            _this.errMsg="<i></i>请输入正确的密码格式";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);                        
                        }
    
                    }
                }
            });

        });
    </script>
@endsection
