@extends('Register.layout')


@section("title", "注册-手机验证")


@section("css")
{{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="bindMobile" style="display:none">
    <div class="pgy_reg_user">
        <img src="{{$wxUserInfo['headimgurl'] or ''}}">
        <div class="reg_userName">{{$wxUserInfo['nickname'] or ''}}</div>
        <div class="reg_authority icon_shouquan">微信已授权</div>
    </div>
    <div class="pgy_remindNav">
        <ul>
            <li>1</li>
            <li></li>
            <li>2</li>
            <li></li>
            <li>3</li>
        </ul>
    </div>
    <div class="pgy_remindNavT">
        <ul>
            <li>绑定手机号</li>
            <li></li>
            <li>设置详细信息</li>
            <li></li>
            <li>注册成功</li>
        </ul>
    </div>
    <div class="pgy_sendContent">
        <div class="pgy_1200">
            <div class="pgy_send_codes">
                <div class="pgy_mobile_login" style="margin-top:91px;">
                    <div class="pgy_input_wraps">
                        <i class="pgy_input_mobile"></i>
                        <input class="pgy_input2" type="tel" maxlength="11" v-model="mobile" placeholder="手机号" @keyup="replacenum" @blur="checkmobile">
                    </div>                
                    <div class="pgy_input_error" v-html="errMsg">@{{errMsg}}</div>
                    <div class="pgy_send_code">
                        <input class="pgy_vcodeinput" v-model="code" type="password" maxlength="4" placeholder="输入验证码">
                        <input type="button" class="pgy_vcodeBtn" v-model="validCode" :disabled="disabled" @click="sendMsg">
                    </div>
                    <div class="ggzdl_protocol">
                        <input id="inputCheck" class="ggzdl_inputCheck" type="checkbox" v-model="checked" checked="checked"><label for="inputCheck"></label>剑指网络科技有限公司<a href="{{Route('s_agreement')}}" target="_blank">《注册协议》</a>
                    </div>
                    <input class="ggzdl_btn" type="button" @click="toReg" @keydown.enter="toReg" value="下一步">
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section("script")
    <script src="/js/jquery.cookie.js" type="text/javascript"></script>
    <script src="/js/function.js" type="text/javascript"></script>
    <script src="/js/BigInt.js" type="text/javascript"></script>
    <script src="/js/aes.js" type="text/javascript"></script>
    <script src="/js/pad-zeropadding-min.js"></script>
    
    <script type="text/javascript">
        $(function(){
            clearAES();
            sessionStorage.removeItem("regSuccess");
            var app = new Vue({
                el: '#bindMobile',
                data: {
                    mobile:"",
                    mobileAES:"",
                    errMsg:"",
                    code:"",
                    validCode:"发送验证码",
                    disabled:false,
                    times:60,
                    checked:true,
                    codeFlag:false
                },
                created:function(){
                    var _this=this;
                },
                mounted:function(){
                    var _this=this;
                    this.$nextTick(function() {
                        document.getElementById("bindMobile").style.display = "block";
                    });
                },
                methods:{
                    toReg:function(){
                        var _this=this;
                        if( !(/^1[34578]\d{9}$/.test(_this.mobile)) ){
                            _this.errMsg="<i></i>请输入正确格式的手机号码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                            return false;
                        }
                        if(_this.code.length<4){
                            _this.errMsg="<i></i>请输入正确格式的验证码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                            return false;
                        }
                        if(!_this.checked){
                            _this.errMsg="<i></i>请勾选服务协议";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                            return false;
                        }
                        _this.mobileAES=secretWithString(_this.mobile);
                        $.ajax({
                            url:'{{route('s_validator_register')}}',
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
                                    location.href="{{route('s_regSetPass')}}";
                                }else{
                                    _this.errMsg="<i></i>"+res.message;
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
                        if( (/^1[34578]\d{9}$/.test(this.mobile)) ){
                            _this.mobileAES=secretWithString(_this.mobile);
                            $.ajax({
                                url:'{{route('s_sms_checkMobile')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    mobile:_this.mobileAES
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                beforeSend: function () {
                                    // 禁用按钮防止重复提交
                                    $(".ggzdl_vcodeBtn").attr({ disabled: "disabled" });
                                },
                                success:function(res){
                                    if(res.status==200) {
                                        $.ajax({
                                            url:'{{route('s_sms_register')}}',
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
                                                            _this.times=60;
                                                            _this.validCode = "发送验证码";
                                                            _this.disabled=false;
                                                            $(".ggzdl_vcodeBtn").removeAttr("disabled");
                                                        }
                                                    }, 1000);
                                                }else{
                                                    _this.errMsg="<i></i>"+res.message;
                                                    setTimeout(function(){
                                                        _this.errMsg="";
                                                    }, 3000);
                                                    $(".ggzdl_vcodeBtn").removeAttr("disabled");
                                                }
                                            },
                                            complete:function(){
                                                clearAES();
                                            }
                                        });
                                    }else{
                                        _this.errMsg="<i></i>"+res.msg;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                        $(".ggzdl_vcodeBtn").removeAttr("disabled");
                                    }
                                },
                                complete:function(){
                                    clearAES();
                                }
                            });
                        }else{
                            _this.errMsg="<i></i>请输入正确格式的手机号码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                        }

                    },
                    replacenum:function(){
                        var _this=this;
                        _this.mobile=_this.mobile.replace(/[^0-9.]/g, '');
                    }
                }
            });
        });

    </script>
@endsection
