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
    <header class="pgy_login2_header">
        <div class="pgy_login2_header_wrap">
            <div class="pgy_logo2"><a href="/"></a></div>
            <div class="pgy_logo2_txt">欢迎注册</div>
            <div class="pgy_logo2_txt2">
                已有账号，<a href="{{Route('s_login')}}">立即登录</a>
            </div>
        </div>
    </header> 

    @yield('content')


    <div class="pgy_login_footer2">&copy;2018 剑指网络 ALL RIGHTS RESERVED. 沪ICP备07001687号　</div>
    <div style=" display: none;">
        @if(config('params.official_site'))
            <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1273382724'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s13.cnzz.com/z_stat.php%3Fid%3D1273382724%26show%3Dpic1' type='text/javascript'%3E%3C/script%3E"));</script>
        @endif
    </div>
<script type="text/javascript"></script>
@yield('script')    
<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>