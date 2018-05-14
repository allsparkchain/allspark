<!--_meta 作为公共模版分离出去-->
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=1400">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="/js/vue.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>

    @yield('css')
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">

    <!--/meta 作为公共模版分离出去-->
    <title>蒲公英 - @yield('title')</title>
</head>
<style>

</style>
<body>
    <header class="pgy_headerLogin">
        <div class="pgy_headerContent">
            <div class="pgy_login">
                <a href="/"><img src="image/pgy_loginLOGO.png" alt=""></a>
            </div>

        </div>
    </header>

    <div class="pgy_contentLogin">

        <div class="pgy_1200">
            <div class="pgy_forgetSetcontent">
                @yield('content')
            </div>
        </div>
    </div>



    <div class="pgy_login_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</div>
    @if(config('params.official_site'))
        <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1273382724'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s13.cnzz.com/z_stat.php%3Fid%3D1273382724%26show%3Dpic1' type='text/javascript'%3E%3C/script%3E"));</script>
    @endif
<script type="text/javascript"></script>
@yield('script')    
<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>