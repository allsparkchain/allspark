<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>历史订单</title>
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
<ul class="pgy_order_list">
@if(count($list)>0 && $list['data']['count'] >0)
    @foreach($list['data']['data'] as $key=> $order)
    <li onclick="window.location.href='{{route('s_order_orderHistoryDetail')}}?oid={{$order['id']}}';">
        <img src="{{$order['goods']['img_path']}}">
        <div class="pgy_order_list_content">
            <h3>{{$order['ArticleName']}}</h3>
            <p style=" margin-right: 55px;">{{$order['summary']}}<span>X{{$order['number']}}</span></p>
            <p style=" font-size: 10px;">{{$order['specifications']}}</p>
            <p style=" margin: 0; color: #ff7241; text-align: right;">¥{{number_format($order['goods']['selling_price']*$order['number'],2)}}</p>
        </div>
    </li>
    @endforeach
@endif    
</ul>
</body>
</html>