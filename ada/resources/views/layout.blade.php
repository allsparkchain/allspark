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
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    @yield('css')
    <script type="text/javascript" src="/js/echarts.common.min.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <!--/meta 作为公共模版分离出去-->
    <title>蒲公英 - @yield('title')</title>
</head>
<style>

</style>
            <!-- 注册，忘记密码不做 公共拆分组合 -->
<body class="zmtdl_loginBg">
    <div class="zmtdl_loginWrap">

        <header class="zmtdl_loginHeader">
            <div class="zmtdl_logo"></div>
            &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
        </header>

        <div class="zmtdl_loginBox">
            <div class="zmtdl_loginBoxTab">
                <span>@yield('sub-title')</span>
            </div>
            <div class="zmtdl_loginBoxBody">
                @yield('content')
            </div>

        </div>

        <footer class="zmtdl_adminFooter">
       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link
        </footer>

    </div>





<script type="text/javascript">

</script>
@yield('script')
<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>