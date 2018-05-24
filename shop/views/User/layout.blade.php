<!--_meta 作为公共模版分离出去-->
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="site.webmanifest">
    <link rel="apple-touch-icon" href="icon.png">



    <link rel="stylesheet" href="{{ mix('css/header.css') }}">
    <link rel="stylesheet" href="{{ mix('css/fonts.css') }}">
    <script src="/js/vendor/jquery-3.2.1.min.js" type="text/javascript"></script>


    <!--/meta 作为公共模版分离出去-->

    <title>@yield('title')</title>
</head>
<body>
<div class="f_content" style="overflow:hidden;">
    <div class="runSign">
        <div class="runSign_mes">
            <div class="block_w">
                @if(\Auth::user())
                    <a class="runSign_mes_d" href="{{route('s_user_accountInfo')}}">{{substr_replace(\Auth::user()->getUserMobile(),'****',3,4)}}</a>
                    <a class="G_run" href="{{route('s_logout')}}">退出</a>
                @else
                    <a class="runSign_mes_d" href="{{route('s_login')}}">登录</a><a class="G_run">/</a><a href="/register">注册</a>
                @endif


            </div>
        </div>
    </div>
    <div class="colors">
        <div class="header">
            <div class="login">
                <a href="{{route('s_index_index')}}">LOGO</a>
            </div>
            <div class="header_nva">
                <ul>

                    <li><a href="{{route('s_index_index')}}">首页</a></li>
                    <li><a href="#">资讯</a></li>
                    <li><a class="active" href="{{route('s_user_accountInfo')}}">个人中心</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="centent">
        <div class="centent_left">
            <div class="centent_left_portrait Tfont">
                <i></i>
                <span>{{substr_replace(\Auth::user()->getUserMobile(),'****',3,4)}}</span>
            </div>
            <div class="centent_left_title Tfont">
                <ul>
                    <li lang="accountInfo" ><a href="{{route('s_user_accountInfo')}}">账户总览</a></li>
                    <li lang="accountCommissionSettlement"class="activeLi"><a class="activeA2" href="#">佣金结算</a></li>
                    <li lang="accountSpreadData" ><a href="#">推广数据</a></li>
                    <li lang="accountSetting" ><a href="#">账户设置</a></li>
                </ul>
            </div>
        </div>
        <div class="centent_right">
            @yield('content')
        </div>
    </div>


</div>
<footer>
    <p> ©2017 典星网络 ALL RIGHTS RESERVED. 沪ICP备07001687号　</p>
</footer>

<

<script type="text/javascript">

    Date.prototype.format = function(format) {
        var date = {
            "M+": this.getMonth() + 1,
            "d+": this.getDate(),
            "h+": this.getHours(),
            "m+": this.getMinutes(),
            "s+": this.getSeconds(),
            "q+": Math.floor((this.getMonth() + 3) / 3),
            "S+": this.getMilliseconds()
        };
        if (/(y+)/i.test(format)) {
            format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
        }
        for (var k in date) {
            if (new RegExp("(" + k + ")").test(format)) {
                format = format.replace(RegExp.$1, RegExp.$1.length == 1
                    ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
            }
        }
        return format;
    }
</script>
    @yield('script')
<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>