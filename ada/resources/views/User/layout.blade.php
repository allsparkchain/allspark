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
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    @yield('css')
    <script type="text/javascript" src="/js/vue.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/js/echarts.common.min.js"></script>
    <script type="text/javascript" src="/js/clipboard.min.js"></script>
    <script src="/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/js/bootstrap-datetimepicker.min.js" type="text/javascript" charset="UTF-8"></script>
    <script src="/js/bootstrap-datetimepicker.zh-CN.js" type="text/javascript" charset="UTF-8"></script>
    <!--/meta 作为公共模版分离出去-->
    <title>蒲公英 - @yield('title')</title>
</head>
<style>

</style>
<body>

    <header class="zmtdl_adminHeader">
        <div class="zmtdl_adminHeader_wrap">
            <div class="zmtdl_adminLogo"></div>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
            <div class="zmtdl_adminUser">
                {{\Auth::getUser()->getUserNickname()}}&nbsp;&nbsp;&nbsp;<a class="zmtdlQuit" href="{{ route('s_logout') }}">退出</a>
            </div>
        </div>
    </header>

    <div class="zmtdl_adminWrap">
        <div class="zmtdl_admin_wrapLeft">
            <div class="zmtdl_adminuser_wrap">
                <div class="zmtdl_adminuser_avator"><img src="{{\Auth::getUser()->getHeadImgurl()}}"></div>
                <div class="zmtdl_adminuser_name">{{\Auth::getUser()->getUserNickname()}}</div>
            </div>
            <ul class="zmtdl_admin_navUl">
                <li class="@if(Request::route()->getName() == 's_user_accountInfo') on @endif ">
                    <a href="{{route('s_user_accountInfo')}}"><i class="zmtdl_iconUser"></i>账户总览<i class="zmtdl_rightArrow"></i></a>
                </li>
                <li class="@if(Request::route()->getName() == 's_user_addUserInfo') on @endif ">
                    <a href="{{route('s_user_addUserInfo')}}"><i class="zmtdl_iconaddUser"></i>添加广告主<i class="zmtdl_rightArrow"></i></a>
                </li>
                <li class="@if(Request::route()->getName() == 's_user_channelData' || Request::route()->getName() == 's_user_channelUserDetail'
                        || Request::route()->getName() == 's_user_channelUserArticleDetail' || Request::route()->getName() == 's_user_channeladdUser'
                        || Request::route()->getName() == 's_user_channelUserOrderList') on @endif ">
                    <a href="{{route('s_user_channelList')}}"><i class="zmtdl_iconChannel"></i>广告主数据<i class="zmtdl_rightArrow"></i></a>
                </li>
                <li class="@if(Request::route()->getName() == 's_user_commissionInfo'|| Request::route()->getName() == 's_user_unsettledCommissionFlow'
                        || Request::route()->getName() == 's_user_balanceFlow' ||  Request::route()->getName() =='s_user_getOrderList') on @endif ">
                    <a href="{{route('s_user_commissionInfo')}}"><i class="zmtdl_iconCommission"></i>结算明细<i class="zmtdl_rightArrow"></i></a>
                </li>
                <li class="@if(Request::route()->getName() == 's_user_accountSetting') on @endif ">
                    <a href="{{route('s_user_accountSetting')}}"><i class="zmtdl_iconAccount"></i>账户设置<i class="zmtdl_rightArrow"></i></a>
                </li>
            </ul>
        </div>

        <div class="zmtdl_admin_wrapRight">
            @yield('content')
        </div>


    </div>


    <footer class="zmtdl_adminFooter">
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link
    </footer>


<script type="text/javascript"></script>
@yield('script')    
<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>