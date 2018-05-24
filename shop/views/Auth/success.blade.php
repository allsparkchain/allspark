<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>已确认登录</title>
    <style>
        body{ text-align:center}
        div{ margin:0 auto; width:400px; height:100px; border:0px solid #F00; padding-top: 400px}
        /* css注释：为了观察效果设置宽度 边框 高度等样式 */
    </style>
</head>
<body>
<div><img src="/img/genhuan.png"></div>
<a id="confirm" style=" display: block; width: 300px; height: 100px; line-height: 100px; text-align: center; font-size: 50px; margin: 720px auto 0 auto; color: rgb(67,68,68); text-decoration: none; border: 1px solid #eaeaea; border-radius: 10px;" href="javascript:;">确&nbsp;认</a>
<script type="text/javascript" src="/js/vendor/jquery-3.2.1.min.js"></script>
<script src="{{config('params.httpType')}}://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
<script>

wx.config(<?php echo $app->jssdk->buildConfig(array('onMenuShareTimeline', 'onMenuShareAppMessage'), false) ?>);

wx.ready(function () {
    //wx.closeWindow();
    $(function(){
        $("#confirm").on("click",function(){
            wx.closeWindow();
        })
    })
});





</script>

</body>
</html>