<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>账户总览-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>

    <link rel="stylesheet" href="{{ mix('css/fonts.css') }}">
    {{--<link rel="stylesheet" href="{{ mix('css/header.css') }}">--}}
    <link rel="stylesheet" href="{{ mix('css/nav.css') }}">
    <link rel="stylesheet" href="{{ mix('css/infocenter.css') }}">
</head>
<body>
<div class="f_content" style="overflow:hidden;">
    {{--<div class="runSign">--}}
        {{--<div class="runSign_mes">--}}
            {{--<div class="block_w">--}}
                {{--<a class="runSign_mes_d" href="/login">登录</a><a class="G_run">/</a><a href="/register">注册</a>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
    {{--<div class="colors">--}}
        {{--<div class="header">--}}
            {{--<div class="login">--}}
                {{--<a href="/">LOGO</a>--}}
            {{--</div>--}}
            {{--<div class="header_nva">--}}
                {{--<ul>--}}
                    {{--<li><a href="/">首页</a></li>--}}
                    {{--<li><a href="{{route('s_article_lists')}}">资讯</a></li>--}}
                    {{--<li><a class="active">个人中心</a></li>--}}
                {{--</ul>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
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
    <!--<div style="clear: both"></div>-->
    <div class="centent">
        <div class="centent_left">
            <div class="centent_left_portrait Tfont">
                <i><img src="{{\Auth::getUser()->getHeadImgurl()}}" width="100%" height="100%"></i>
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
            <div class="centent_right_middle">
                <span class="centent_right_paddingL">每月15日结算上月未结算佣金 </span ><span class="centent_right_paddingR">累计产生佣金总额<span>{{number_format($user_accountInfo['sum_commission_account'],2)}}</span></span>
            </div>
            <div class="centent_right_commission">
                <div class="commission_left_font">
                    <div class="commission_left_fontOne">
                        <p>未结算佣金总额</p>
                        <span>{{number_format($user_accountInfo['unsettled_amount'],2)}}</span>
                    </div>
                    <span class="spanLine">=</span>
                    <div class="commission_left_fontThree">
                        <p>本月未结算佣金</p>
                        <span>{{number_format($user_accountInfo['unsettled_amount_month'],2)}}</span>
                    </div>
                    <span class="spanLine">+</span>

                    <div class="commission_left_fontFive">
                        <p>上月未结算佣金</p>
                        <span>{{number_format( $user_accountInfo['non_settlement_month'],2)}}</span>
                    </div>
                </div>
                <div class="commission_right_font">
                    <p>今日佣金</p>
                    <span>{{number_format($user_accountInfo['day_commission_account'],2)}}</span>
                </div>
            </div>
            {{--<div class="centent_right_commission">--}}
                {{--<div class="commission_left_font">--}}
                    {{--<div class="centent_right_A">--}}
                        {{--<div class="centent_right_left Tfont">--}}
                            {{----}}

                            {{--<p class="margin_top75 "><span class="h2">可提现金额</span><span class="font_side">{{number_format($user_accountInfo['available_amount'],2)}}</span></p>--}}
                            {{--<!--<p class="Jfont">未结算佣金：￥<span class="JEfonts">0.00</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;今日订单:<span class="JEfonts">1,000</span>/单</p>-->--}}
                        {{--</div>--}}
                        {{--<div class="centent_T">--}}
                            {{--<a href="{{route('s_user_accountWithdraw')}}">我要提现</a>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="commission_left_font">--}}
                    {{--<div class="bttons">--}}
                        {{--<div class="">--}}

                            {{--<p class="margin_top75 "><span class="h2">可提现金额</span><span class="font_side">2,174.22</span></p>--}}
                            {{--<!--<p class="Jfont">未结算佣金：￥<span class="JEfonts">0.00</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;今日订单:<span class="JEfonts">1,000</span>/单</p>-->--}}
                        {{--</div>--}}
                        {{--<div class="centent_T">--}}
                            {{--<a href="infocenter_withdrawal.html">我要提现</a>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div class="commission_right_font">--}}
                    {{--<p>今日订单</p>--}}
                    {{--<span>{{number_format($today_nums,0)}}</span>--}}
                {{--</div>--}}
            {{--</div>--}}
            <div class="centent_rig">
                <div class="commiss">
                    <div class="cent_A">
                        <div class="centendFse">

                            <p class="margin_top75 "><span class="h2">可提现金额</span><span class="font_side">{{number_format($user_accountInfo['available_amount'],2)}}</span></p>
                            <!--<p class="Jfont">未结算佣金：￥<span class="JEfonts">0.00</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;今日订单:<span class="JEfonts">1,000</span>/单</p>-->
                        </div>
                        <div class="centent_Taw">
                            <a href="{{route('s_user_accountWithdraw')}}">我要提现</a>

                            <span style="margin-left: 54px; cursor: pointer;color: #999;" onclick="window.location.href='{{route('s_user_accountWithdraw')}}?c=1'" >余额明细 ></span>
                        </div>
                    </div>
                </div>
                <div class="commission_right_font">
                    <p>今日订单</p>
                    <span>{{number_format($user_accountInfo['today_nums'],0)}}</span>
                </div>
            </div>
            <!--<div class="centent_right_A">-->
            <!--<div class="centent_right_left Tfont">-->
            <!--<span class="h2">可提现金额</span>-->
            <!--<p class="margin_top75 ">￥<span class="JEfont">10,000</span></p>-->
            <!--<p class="Jfont">未结算佣金：￥<span class="JEfonts">0.00</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;今日订单:<span class="JEfonts">1,000</span>/单</p>-->
            <!--</div>-->
            <!--<div class="centent_T">-->
            <!--<a href="infocenter_withdrawal.html">我要提现</a>-->
            <!--</div>-->
            <!--</div>-->
            <div class="centent_right_B">
                <!--<div class=" centent_YJ">-->
                <!--累计产生佣金&nbsp;&nbsp;&nbsp;<span class="j_q">￥</span><span class="JEfont">1,000.00</span>-->
                <!--</div>-->
                <div class="centnt_right_TB"><div id="myfirstchart" style="height: 440px;"></div></div>
            </div>
        </div>
    </div>
</div>

<footer>
    <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>
</footer>
<script src="/js/vendor/jquery-3.2.1.min.js"></script>
<script src="/js/vendor/raphael-min.js" type="text/javascript"></script>
<script src="/js/vendor/morris.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {

        $('.centent_left_title').find('ul').find('li').each(function(){
            $(this).removeClass();
            $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
            if($(this).attr('lang') == 'accountInfo'){
                $(this).addClass('active');
                $(this).find('a').addClass('activeA1');
            }
        });

        $(".wothdrawa_o").click(function () {
            $(".wothdrawa_o").addClass("active1");
            $(".wothdrawa_t").removeClass("active2");
            $(".list1").show();
            $(".list2").hide();
        });
        $(".wothdrawa_t").click(function () {
            $(".wothdrawa_o").removeClass("active1");
            $(".wothdrawa_t").addClass("active2");
            $(".list2").show();
            $(".list1").hide();
        });
        @if(@$list != -1)
        new Morris.Line({
            element: 'myfirstchart',
            data: {!!$list!!},
            xkey: 'new_time',
            ykeys: ['account'],
            labels: ['money'],
            lineColors:['#ff7241'],
            parseTime: false
        });

        @endif

    });
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

</script>
</body>
</html>
