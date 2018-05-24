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
    <link rel="stylesheet" href="{{ mix('css/D_header.css') }}">

    <script src="js/vendor/jquery-3.2.1.min.js" type="text/javascript"></script>

    <script src="js/vendor/jquery.validate.min.js" type="text/javascript"></script>
    <script src="js/vendor/messages_zh.min.js" type="text/javascript"></script>

    <script src="{{ mix('js/main.js') }}"></script>
    <!--/meta 作为公共模版分离出去-->

    <title>@yield('title')</title>
</head>
<body>

<div class="warp">
    <header>
        <div class="header_content">
            <div class="login" onclick="window.location='/'">LOGO</div>
        </div>
    </header>

    <div class="content">
        @yield('content')

    </div>
    <footer>
        <p class="footer_p">©2017 典星网络 ALL RIGHTS RESERVED. 沪ICP备07001687号</p>
    </footer>
</div>


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