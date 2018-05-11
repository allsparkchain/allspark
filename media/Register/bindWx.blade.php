@extends('Register.layout')


@section("title", "注册-微信授权")


@section("css")
{{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}
<style>
    body{
        background: #f2f2f2;
    }
    .pgy_logo2_txt2{
        display: block!important;
    }
</style>

@endsection

@section("content")
<div class="pgyRegister_s">
    <div class="pgyBangding">绑定微信账号进行注册</div>
    <div class="pgy_weixinB">
        <div class="qrcode" id="qrcode"></div>
    </div>
    <div class="pgy_shouquanW">微信授权</div>
</div>
@endsection

@section("script")
<script type="text/javascript" src="https://res.wx.qq.com/connect/zh_CN/htmledition/js/wxLogin.js"></script>
<script type="text/javascript">
    $(function(){

        var obj = new WxLogin({
            id: "qrcode",
            appid: "{{$appId}}",
            scope: "snsapi_login",
            redirect_uri: "{!! $backurl !!}",
            state: Math.ceil(Math.random()*1000),
            style: "black",
            href: "{!! $zmt_host_public.mix('css/wxlogin.css') !!} "
        });


        $(".pgy_shouquanW").on("click", function(){
            if(is_weixin()){
                window.location="{{Route('s_wechat_authLogin')}}";
            }else{
                window.location="{{route('s_pc_qrpage')}}";
            }
        });
        function is_weixin() {
            var ua = window.navigator.userAgent.toLowerCase();
            if (ua.match(/MicroMessenger/i) == 'micromessenger') {
                return true;
            } else {
                return false;
            }
        }


    });

</script>
@endsection
