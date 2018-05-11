@extends('layout')

@section('title')
    {{$res['product_name']}}
@endsection


@section("css")
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
    <link rel="stylesheet" href="{{ mix('css/swiper.min.css') }}">
@endsection

@section("content")
    <div id="detail">
        <!--未认证身份登录注册-->
        <div class="login_into" style=" display:none;">
            <div class="login_intoBgcolor"></div>


            <div class="ggzdl_loginBox">
                <a href="javascript:;" class="pgy_closeLogin"></a>
                <div class="ggzdl_loginBoxTab">
                    <span :class="{on:wxShow}" @click="tabSwitch('wx')" style="float:left;">微信登录</span>
                    <span :class="{on:mobileShow}" @click="tabSwitch('mobile')" style="float:left;margin-left: 60px">手机登录</span>
                    <span :class="{on:mailShow}" @click="tabSwitch('mail')" style="float:right;">邮箱登录</span>
                </div>

                <!--微信登录-->
                <div v-show="wxShow" id="qrcode" class="ggzdl_loginBoxBody">
                    <div class="qrcode" id="loginCode"></div>
                </div>

                <!--手机登录-->
                <div v-show="mobileShow" id="ggzdlLogin" class="ggzdl_loginBoxBodys">
                    <div id="ggzdlInputTel" style="position: relative;">
                        <input id="username" class="ggzdl_inputs" maxlength="11" type="tel" v-model="username" placeholder="手机号">
                        <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg}}</div>
                        <!-- <span class="pgy_yz">输入正确的手机号码</span> -->
                    </div>
                    <div id="ggzdlInputPwd" style="position: relative;">
                        <input id="userpassword" class="ggzdl_inputs" maxlength="18" style="margin: 0;" v-model="password" type="password" placeholder="输入密码">
                    </div>
                    <a class="ggzdl_forgetpwd" href="{{Route('s_forgetVerifyTel')}}">忘记密码</a>
                    <input class="ggzdl_btns" type="button" @click="toLogin" value="立 即 登 录">
                </div>

                <!--邮箱登录-->
                <div v-show="mailShow" class="ggzdl_loginBoxBodys">
                    <div  style="position: relative;">
                        <input  class="ggzdl_inputs" type="text" v-model="email" placeholder="邮箱">
                        <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg2}}</div>
                    </div>
                    <div  style="position: relative;">
                        <input  class="ggzdl_inputs" maxlength="18" style="margin: 0;" v-model="passwd" type="password" placeholder="输入密码">
                    </div>
                    <a class="ggzdl_forgetpwd" href="{{Route('s_forgetVerifyTel')}}">忘记密码</a>
                    <input class="ggzdl_btns" type="button" @click="toLogin2" value="立 即 登 录">
                </div>
                <div class="ggzdl_toRegisters">还没有账号?<a href="{{route('s_register')}}">立即注册</a></div>
            </div>
        </div>


        <!--内容区域-->
        <div class="pgy_commodity_detail" style="height: auto;margin-bottom:300px">
            <div class="pgy_1200">
                <div style="overflow:hidden;height: 403px;">
                    <div class="pgy_commodity_leftImg">

                        <img onerror="javascript:this.src='{{$res['img_path']}}';" src="{{$res['new_path325']}}" alt="">
                    </div>
                    <div class="pgy_commodity_rightContent">
                        <div class="pgy_commodity_title">
                            <p>{{$res['product_name']}}</p>
                        <!-- <span>{{$res['category_name']}}</span> -->
                        </div>
                        <div class="pgy_commodity_jouery">
                            <p>{{$res['synopsis']}}</p>
                            <div class="pgy_comodity_area">
                                <div class="com_price">
                                    <span>商品价格</span>
                                    <span class="pgy_color">¥ {{number_format($res['selling_price'],2)}}</span>
                                </div>
                                <div>
                                    @if($res['region'])
                                        <span>目的地</span>
                                        <span>{{$res['region']}}</span>&nbsp;
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="pgy_commodity_glt">
                            <div class="com_price">
                                <span >商品成交获得收益</span>
                                <span class="pgy_color" >¥{{number_format(bcdiv(bcmul($res['selling_price'],$res['percent_commission'],2),100,2),2)}}</span>
                            </div>
                        </div>

                        {{--<a href="{{route('s_goods_edit',['id'=>$res['id']])}}"></a>--}}

                        @if($res['product_type'] == 3)

                            <p class="commodity_hrefs">产品链接：{{$res['landing_page']}}</p>

                        @endif

                        <div class="pgy_startMake" style=" cursor: pointer;">
                            <span>开始创作</span>
                        </div>

                    </div>
                </div>
                <div class="goods_detail" >

                    {!! html_entity_decode($res['contents']) !!}
                </div>

            </div>
        </div>
    </div>
@endsection
@section("script")
    <script type="text/javascript">
        $(function () {
            var app=new Vue({
                el:'#detail',
                data:{
                    titleName:"",
                    wxShow:true,
                    mobileShow:false,
                    mailShow:false,
                    username:"",
                    password:"",
                    email:"",
                    passwd:"",
                    errMsg:"",
                    errMsg2:""
                },
                created:function(){
                    var _this=this;

                },
                mounted:function(){
                    var _this=this;
                    $(".pgy_toTop").click(function() {
                        $("html,body").animate({scrollTop:0}, 500);
                    });

                    $(window).scroll(function() {
                        if($(this).scrollTop()>500){
                            $(".pgy_toTop").show();
                        }else{
                            $(".pgy_toTop").hide();
                        }

                    });

                },
                methods:{
                    toLogin:function(){
                        var _this=this;
                        if ( (/^1[34578]\d{9}$/.test(this.username)) && (/^[A-Za-z0-9]{8,18}$/.test(this.password)) ){
                            $.ajax({
                                url:'{{route('s_auth_login')}}',
                                type:'POST', //GET
                                async:true,    //或false,是否异步
                                data:{
                                    username:_this.username,
                                    password:_this.password,
                                    fastlogin:'fastlogin'
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200){
                                        window.location.href="{{route('s_goods_edit',['id'=>$res['id']])}}";
                                        //window.location.reload();
                                    }else if(res.status==403){
                                        location.reload();
                                    }else {
                                        _this.errMsg=res.msg;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                    }
                                }
                            });
                        } else {
                            _this.errMsg="请输入正确格式的手机号或密码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                        }
                    },
                    toLogin2:function(){
                        var _this=this;
                        if ( (/^[a-zA-Z0-9_-]+@([a-zA-Z0-9]+\.)+(com|cn|net|org)$/.test(this.email)) && (/^[A-Za-z0-9]{8,18}$/.test(this.passwd))){
                            $.ajax({
                                url:"{{ route('s_login_emailLogin') }}",
                                type:'POST',
                                async:true,
                                data:{
                                    email:_this.email,
                                    passwd:_this.passwd
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        window.location.href="{{route('s_goods_edit',['id'=>$res['id']])}}";
                                        //window.location.reload();
                                    }else{
                                        _this.errMsg2=res.message;
                                        setTimeout(function(){
                                            _this.errMsg2="";
                                        }, 3000);
                                    }
                                }
                            });
                        }
                        else {
                            _this.errMsg2="请输入正确格式的邮箱或密码";
                            setTimeout(function(){
                                _this.errMsg2="";
                            }, 3000);
                        }
                    },
                    tabSwitch:function(type){
                        switch (type) {
                            case "wx":
                                this.wxShow=true;
                                this.mobileShow=false;
                                this.mailShow=false;
                                break;
                            case "mobile":
                                this.wxShow=false;
                                this.mobileShow=true;
                                this.mailShow=false;
                                break;
                            case "mail":
                                this.wxShow=false;
                                this.mobileShow=false;
                                this.mailShow=true;
                                break;
                            default:
                                break;
                        }
                    }
                }
            });


            //二维码登陆
            var loginCode = new QRCode(document.getElementById('loginCode'), {
                width: 191,
                height: 191,
            });
            loginCode.makeCode("{{ $wxHost }}auth/weixin/login/{{ $pc_jzstate }}/1");


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
                            window.location.href="{{route('s_goods_edit',['id'=>$res['id']])}}";
                            // window.location.reload();
                        }else if(data.type==2){
                            //注册
                            window.location = "/regBindMobile";
                        }else if(data.type==3){

                        }
                    }
                })
            }

            function aaa(){
                setInterval(function () { ajaxLogin() },5000); //循环计数
            }

            $(".pgy_closeLogin").on("click",function(){
                $('.login_into').hide();
            });

            var user = "{{$user}}";
            $('#pgy1').show();
            $('#pgy2').hide();
            /* if(user2){
                 $('#pgy1').hide();
                 $('#pgy2').show();
             }else{
                 $('#pgy1').show();
                 $('#pgy2').hide();
             }*/



            $(".pgy_startMake").click(function () {
                //  $('.show2').hide();
                var user = "{{$user}}";
                if(!user){
                    $('.login_into').show();
                    aaa();
                    return false;
                }else{
                    location.href="{{route('s_goods_edit',['id'=>$res['id']])}}";
                }
            });

        });
    </script>

@endsection
