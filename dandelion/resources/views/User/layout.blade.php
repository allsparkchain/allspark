<!--_meta 作为公共模版分离出去-->
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-datetimepicker.css">
    <link rel="stylesheet" href="{{ mix('css/center.css') }}">


    @yield('css')
    <script type="text/javascript" src="/js/vue.js"></script>
    <script src="/js/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/js/echarts.common.min.js"></script>
    <script type="text/javascript" src="/js/clipboard.min.js"></script>
    {{--<script src="/js/bootstrap.min.js" type="text/javascript"></script>--}}
    <script src="/js/bootstrap-datetimepicker.min.js" type="text/javascript" charset="UTF-8"></script>
    <script src="/js/bootstrap-datetimepicker.zh-CN.js" type="text/javascript" charset="UTF-8"></script>
    <!--/meta 作为公共模版分离出去-->
    <title>蒲公英 - @yield('title')</title>
</head>
<style>

</style>
<body>
    <header class="pgy_adminHeader">
        <div class="pgy_adminHeader_wrap" >
        <a href="/"><div class="pgy_adminLogo_top"></div></a>
            <ul class="pgy_navbar">
                <li ><a href="/">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;首页</a></li>
                <li><a href="{{route('s_goods_lists')}}">我要创作</a></li>
                <li><a href="{{route('s_aricle_lists')}}">我要推广</a></li>
            </ul>
            <div class="pgy_adminUser_top">
                <div class="pgy_admin_top"><img src="{{\Auth::getUser()->getHeadImgurl()}}" alt=""></div>
                <div class="pgy_admin_infos">

                </div>
                <!-- <div class="pgy_admin_name"><a href="{{route('s_user_accountInfo')}}">{{\Auth::getUser()->getUserNickname()}}</a></div> -->
                <!-- <a href="{{ route('s_logout') }}">退出</a> -->
                <ul class="pgy_admin_downs" >
                    <li><a href="{{route('s_user_accountInfo')}}"><i class="pgy_iconUser"></i>账户总览</a></li>
                    <li ><a href="{{route('s_user_commissionSettlemtentDetail')}}"><i class="pgy_iconCommission"></i>账户明细</a></li>
                    <li ><a href="{{route('s_user_articleList')}}"><i class="pgy_iconZixun"></i>我的内容</a></li>
                    <li ><a href="{{route('s_user_spreadData')}}"><i class="pgy_iconCount"></i>推广数据</a></li>
                    <li ><a href="{{route('s_user_inviteData')}}"><i class="pgy_iconYaoqing"></i>好友邀请</a></li>
                    <li ><a href="{{route('s_user_accountSetting')}}"><i class="pgy_iconAccount"></i>账户设置</a></li>
                    <li ><a href="{{ route('s_logout') }}"><i class="pgy_iconOut"></i>退出登录</a></li>
                </ul>
            </div>

        </div>
    </header>


    <div class="ggzdl_adminWrap clearfloat">
        <div class="ggzdl_admin_wrapLeft">
            <div class="ggzdl_adminuser_wrap">
                <div class="ggzdl_adminuser_avator"><img src="{{\Auth::getUser()->getHeadImgurl()}}"></div>
                <div class="ggzdl_adminuser_name">{{\Auth::getUser()->getUserNickname()}}</div>
            </div>
            <ul class="ggzdl_admin_navUl">
                <li class="@if(Request::route()->getName() == 's_user_accountInfo' || Request::route()->getName() == 's_user_withdrawPage') on @endif ">
                    <a href="{{route('s_user_accountInfo')}}"><i class="ggzdl_iconUser1"></i>账户总览<i class="ggzdl_rightArrow"></i></a></li>

                
                <!-- <li class="@if(Request::route()->getName() == 's_user_commissionSettlemtent') on @endif ">
                    <a href="{{route('s_user_commissionSettlemtent')}}"><i class="ggzdl_iconChannel"></i>收益结算<i class="ggzdl_rightArrow"></i></a></li> -->

                <li class="@if(Request::route()->getName() == 's_user_commissionSettlemtentDetail') on @endif ">
                    <a href="{{route('s_user_commissionSettlemtentDetail')}}"><i class="ggzdl_iconCommission"></i>账户明细<i class="ggzdl_rightArrow"></i></a></li>
                    <li class="@if(Request::route()->getName() == 's_user_articleList') on @endif ">
                    <a href="{{route('s_user_articleList')}}">
                        <i class="ggzdl_iconZixun1"></i>我的内容
                        <i class="ggzdl_rightArrow"></i>
                    </a>
                </li>   
                    <li class="@if(Request::route()->getName() == 's_user_spreadData' || Request::route()->getName() == 's_user_spreadDataDetail') on @endif ">
                    <a href="{{route('s_user_spreadData')}}">
                        <i class="ggzdl_iconCommondata"></i>推广数据
                        <i class="ggzdl_rightArrow"></i>
                    </a>
                </li>
                <li class="@if(Request::route()->getName() == 's_user_inviteData') on @endif ">
                    <a href="{{route('s_user_inviteData')}}">
                        <i class="ggzdl_iconFriend1"></i>好友邀请
                        <i class="ggzdl_rightArrow"></i>
                    </a>
                </li>
                <li class="@if(Request::route()->getName() == 's_user_accountSetting') on @endif ">
                    <a href="{{route('s_user_accountSetting')}}">
                        <i class="ggzdl_iconAccount"></i>账户设置
                        <i class="ggzdl_rightArrow"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="ggzdl_admin_wrapRight">
            @yield('content')
        </div>


    </div>



    <!--底部-->
    <div class="pgy_admin_footer" style="overflow: hidden;">
    ©2018 蒲公英 ALL RIGHTS RESERVED. <a href="http://www.miitbeian.gov.cn" target="_blank">沪ICP备18004037号-1</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.jzinter.com/">about us</a></div>
    <div style=" display: none;">
        @if(config('params.official_site'))
            <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1273382724'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s13.cnzz.com/z_stat.php%3Fid%3D1273382724%26show%3Dpic1' type='text/javascript'%3E%3C/script%3E"));</script>
        @endif
    </div>

<script type="text/javascript">
$(function(){
      
        $(".pgy_adminUser_top").mouseover(function(){
            $('.pgy_admin_downs').show();
 
        });
        $(".pgy_adminUser_top").mouseout(function(){
            $('.pgy_admin_downs').hide();
 
        });
})
</script>
@yield('script')    
<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>