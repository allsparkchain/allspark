<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>订单详情</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no, email=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--强制竖屏-->
    <meta name="screen-orientation" content="portrait">
    <!--点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/pgy_style.css') }}">
    <style>
        body{
            background: #f2f2f2;
        }
    </style>
</head>
<body>
<div class="pgy_order_content">
    <div class="pgy_order_wrap">
        订单时间<span>{{date('Y.m.d H:i:s',$detail['add_time'])}}</span>
    </div>
    <div class="pgy_order_wrap">
        订单编号<span>{{$detail['order_number']}}</span>
    </div>
    @if(strlen($detail['showStatus'])>0)
        <div class="pgy_order_wrap">
            <span class="on">{{$detail['showStatus']}}</span>
        </div>
    @endif

</div>

<div class="pgy_order_content">
    <div class="pgy_order_wrap">
        订单商品<br>
        <div class="pgy_order_details">
            {{$detail['goods']['goods_name']}}<br>
            <span>{{$detail['specifications']}}</span>
        </div>
        <div style=" float: right; margin-top: -15px; font-size: 12px; color: #7e7e7e;">X{{$detail['number']}}</div>
    </div>
    <div class="pgy_order_wrap">
        订单总额<span class="on">¥{{number_format($detail['goods']['selling_price'],2)}}</span>
    </div>
    
</div>

<div class="pgy_order_content">
    <div class="pgy_order_wrap">
        姓名<span>{{$detail['userAddress']['realname']}}</span>
    </div>
    <div class="pgy_order_wrap">
        手机号<span>{{$detail['userAddress']['mobile']}}</span>
    </div>
    <div class="pgy_order_wrap">
        收货地址<span>{{$detail['userAddress']['address']}}</span>
    </div>
    <div class="pgy_order_wrap">
        备注<span>{{$detail['userAddress']['address']}}</span>
    </div>
</div>

@if(count($detail['userAddress']['invoice'])>0)
    <div class="pgy_invoice">
        <div>
            公司发票
        </div>
        <div>
            公司名称<span>{{$detail['userAddress']['invoice']['invoice_title']}}</span>
        </div>
        <div>
            公司税号<span>{{$detail['userAddress']['invoice']['invoice_no']}}</span>
        </div>
    </div>
@endif

</body>
</html>