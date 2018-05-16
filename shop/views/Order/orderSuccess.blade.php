<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>支付成功</title>
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
    <link rel="stylesheet" type="text/css" href="{{ mix('css/header.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/complete_order.css') }}">
</head>
<body>
<div class="centent">
    <div class="centent_bg">
        <!-- <div class="details_complete"></div>
            <div class="details_complete_centen"></div> -->
        <div class="details_complete_centenA">
            <div class="centent_datails">
                <p>
                    <span>{{number_format($detail['selling_price'] * $detail['num'],2)}}</span>
                </p>
            </div>
            <div class="cnetent_font">
                <div class="as_height">
                    <div class="as_height_left"><img src="{{$detail['proinfo']['img_path']}}"></div>
                    <div class="as_height_right">
                        <p>{{$detail['proinfo']['name']}}</p>
                        <span>{{$detail['extra']}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="pgy_orderSuccess">
            <a href="{{route('s_order_orderHistoryList')}}">查看订单</a>
            <img style=" display: block; width: 125px; height: 125px; margin: 20px auto;" src="/images/IMG_3938.jpg">
            <div style=" font-size: 12px; color: #7e7e7e;">长按二维码识别<br>关注蒲公英公众号</div>
        </div>
           
        
    </div>



</div>
<!-- <div class="btn_run"><a href="{{route('s_order_orderHistoryList')}}">查看个人订单</a></div> -->
</body>
</html>
