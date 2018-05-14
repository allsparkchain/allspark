@extends('layout')

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="/js/jquery.qrcode.min.js"></script>


<div id="code"></div>
<script type="text/javascript">
    $("#code").qrcode({
        render: "canvas", //table方式
        width: 200, //宽度
        height:200, //高度
        text: "http://weixin.pugongying.link/auth/weixin/login/{{ $pc_jzstate }}/1" //任意内容
    });

    $.ajax({
        url:"{{route('s_weixin_ajax_login')}}",
        type:'POST', //GET
        async:true,    //或false,是否异步
        data:{
            code:"{{ $pc_jzstate }}",
        },
        "headers": {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout:50000,    //超时时间
        dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
        success:function(data,textStatus,jqXHR){
            if(data.type==1){
                //登录
                window.location = "{{route('s_user_accountInfo')}}";
            }else if(data.type==2){
                //注册
                window.location = "/register";
            }else if(data.type==3){

            }
        }
    })
</script>

