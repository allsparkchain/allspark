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
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    {{--<link rel="stylesheet" href="{{ mix('css/header.css') }}">--}}
    <link rel="stylesheet" href="{{ mix('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ mix('css/nav.css') }}">
    <link rel="stylesheet" href="{{ mix('css/mian_index.css') }}">


    @yield('css')

    <script src="/js/vendor/jquery-3.2.1.min.js" type="text/javascript"></script>
    <!--/meta 作为公共模版分离出去-->

    <title>@yield('title')-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
</head>
<body>
<div class="f_content" style="overflow:hidden;">
    <div class="header_border">
        <div class="header">
            <div class="login">
                <a href="{{Route('s_index_index')}}"></a>
            </div>
            <div class="header_navWrop">
                <div class="header_nva">
                    <div class="nav_list">
                        <a href="{{Route('s_index_index')}}">首页</a>
                        <a href="{{route('s_article_lists')}}" class="active">资讯</a>
                        <a href="https://www.pugongying.link/jzinter" target="_blank">关于我们</a>
                    </div>
                    @if(strlen(\Auth::getUser()->getUserNickname()) >0)
                        {{--登录状态--}}
                        <div class="user_logged" style="display:block;    width: 198px;height: 70px;float: left;">
                            <div class="userHides" >
                                <div class="" style="float: right;line-height: 70px;width: 138px;margin-left: 60px;    cursor: pointer;">
                                    <div class="usserImg" style="float: left; margin-top: 16px; font-size: 0; width:37px;height:37px;border-radius: 50%;"><img width="100%" height="100%" style="border-radius: 50%" src="{{\Auth::getUser()->getHeadImgurl()}}"></div>
                                    <span style="margin-left: 13px" class="loggedname">{{\Auth::getUser()->getUserNickname()}}</span>
                                </div>
                            </div>
                            <div class="user_hide" style="display: none">
                                <span class="userAs" id="userAs" onclick="window.location.href='{{Route('s_user_accountInfo')}}'">个人中心</span>
                                <span class="userAs" onclick="window.location.href='{{Route('s_logout')}}'">退出</span>
                            </div>
                        </div>
                    @else
                        {{--未登录状态--}}
                        <div class="user_login" style="">
                            <span class="spacing" onclick="window.location.href='/login'">登录</span>
                            <i class="spacing">/</i>
                            <span class="spacing" onclick="window.location.href='/qrRegister'">注册</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="centent">
        <div class="centent_left">
            <div class="centent_left_portrait Tfont">
                <i><img src="{{\Auth::getUser()->getHeadImgurl()}}" width="100%" height="100%">  </i>
                <span class="user_name">{{\Auth::getUser()->getUserNickname()}}</span>
            </div>
            <div class="centent_left_title Tfont">
                <ul>
                    <li lang="accountInfo" ><a href="{{route('s_user_accountInfo')}}">账户总览</a></li>
                    <li lang="accountCommissionSettlement"class=""><a href="{{route('s_user_accountCommissionSettlement')}}">佣金结算</a></li>
                    <li lang="accountCommissionSettlementDetail"><a href="{{route('s_user_accountCommissionSettlementDetail')}}">佣金明细</a></li>
                    <li lang="accountSpreadData" ><a href="{{route('s_user_accountSpreadData')}}">推广数据</a></li>
                    <li lang="accountFreiendInvite" class=""><a href="{{route('s_user_accountFreiendInvite')}}" class="" >好友邀请</a></li>
                    <li lang="accountSetting" ><a href="{{route('s_user_accountSetting')}}">账户设置</a></li>

                </ul>
            </div>
        </div>
        <div class="centent_right">
            @yield('content')
        </div>
    </div>


</div>
{{--<footer>--}}
    {{--<p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>--}}
{{--</footer>--}}

<!--_footer 作为公共模版分离出去-->
{{--<script type="text/javascript" src="/lib/jquery/1.9.1/jquery.min.js"></script>--}}
{{--<script type="text/javascript" src="/lib/layer/2.4/layer.js"></script>--}}
{{--<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script>--}}
{{--<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script> <!--/_footer 作为公共模版分离出去-->--}}

{{--<!--请在下方写此页面业务相关的脚本-->--}}
{{--<script type="text/javascript" src="/lib/My97DatePicker/4.8/WdatePicker.js"></script>--}}
{{--<script type="text/javascript" src="/lib/jquery.validation/1.14.0/jquery.validate.js"></script>--}}
{{--<script type="text/javascript" src="/lib/jquery.validation/1.14.0/validate-methods.js"></script>--}}
{{--<script type="text/javascript" src="/lib/jquery.validation/1.14.0/messages_zh.js"></script>--}}


{{--<script type="text/javascript" src="/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>--}}
{{--<script type="text/javascript" src="/lib/laypage/1.2/laypage.js"></script>--}}


{{--<script type="text/javascript" src="/lib/webuploader/0.1.5/webuploader.min.js"></script>--}}
{{--<script type="text/javascript" src="/lib/ueditor/1.4.3/ueditor.config.js"></script>--}}
{{--<script type="text/javascript" src="/lib/ueditor/1.4.3/ueditor.all.min.js"> </script>--}}
{{--<script type="text/javascript" src="/lib/ueditor/1.4.3/lang/zh-cn/zh-cn.js"></script>--}}



<script type="text/javascript">
    //    鼠标移动显示

    $(".userHides").mouseover(function () {
        $(".user_hide").show();
    });
    $(".userHides").mouseout(function () {
        $(".user_hide").hide();
    });

    $(".userAs").mouseover(function () {
        $(".user_hide").show();
    });
    $(".userAs").mouseout(function () {
        $(".user_hide").hide();
    });


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