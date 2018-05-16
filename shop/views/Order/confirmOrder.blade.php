<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>确认订单</title>
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
    <link rel="stylesheet" type="text/css" href="{{ mix('css/weui.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/order_details.css') }}">
</head>
<body>
<div class="cneten">
    <div class="personal_information">
        <input id="realname" placeholder="请输入您的姓名" value="{{$address['realname'] or ''}}" maxlength="20">
        <input id="mobile" placeholder="输入您的手机号码" value="{{$address['mobile'] or ''}}" maxlength="11">
        <input id="address" placeholder="收货地址" value="{{$address['address'] or ''}}">
        <textarea id="remark" style="width: 100%; height: 55px; box-sizing: border-box; padding: 10px; border: none; outline: none; display: block; resize: none;" placeholder="备注">{{$address['remark'] or ''}}</textarea>
        <!-- <input id="remark" placeholder="备注" value="{{$address['remark'] or ''}}"> -->
        <input type="hidden" id="addressid" value="{{$address['id'] or -1}} ">
    </div>  
    <div class="invoice">
        <div class="choise_invoice">
            <div class='radio-check'><input type='checkbox' id='test1' value="0" name="pp"/> <label for='test1' ></label><span>需要发票</span></div>
            <div style="display:none" class="font_invoice">
                <div class='radio-check'><input type='radio' id='test2' value="2" v-model="picked1" name="picked1"/> <label for='test2' ></label><span>个人发票</span></div>                
                <div class='radio-check'><input type='radio' id='test3' value="1" v-model="picked1" name="picked1"/> <label for='test3' ></label><span>公司发票</span></div>
            </div>
        </div>
        <div class="invoice_company" style="display:none">
            <input type="text" id="companyName" placeholder="请输入公司名字" value="{{$address['companyName'] or ''}}">
            <input type="text" id="numberC" placeholder="请输入税号" value="{{$address['numberC'] or ''}}">
        </div>
       
    </div>
    <p class="Invoice_prompt">您本次购买的是电子凭证，不需要收货地址，请核对您的购买信息</p>
    <div class="details_information">
        <div class="information_one"><img width="100%" height="100%" src="{{$proinfo['img_path']}}"></div>
        <div class="information_two">
            <p>{{$proinfo['name']}}</p>
            <p style=" color: #7E7E7E; margin: 5px 0; height: 40px;">{{$proinfo['summary']}}</p>
            <p style=" color: #7E7E7E; font-size: 10px;">{{$extra}}</p>
        </div>
        <div class="information_three">
            <p style="opacity: 0">¥{{number_format($proinfo['selling_price'] or 0,2)}}</p>
            <span>X{{$num}}</span>
        </div>
    </div>
    <div class="sum_money">
        <span>应付总额<span class="sum_money_color">¥{{number_format($selling_price * $num,2)}}</span></span>
    </div>
    <div class="WeChat_channels">
        <span class="WeChat_channels_fonts">微信支付</span>
        <i></i>
    </div>
    <div class="bottom_default">
        <a href="javascript:void(0);"  class="bottom_sure">确认购买</a>
    </div>
</div>
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/weui.min.js"></script>
<script>

    var json = {};
    function onBridgeReady(){
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest',  json ,
            function(res){
                if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                    location.href="{{route('s_user_orderSuccess')}}";
                }
            }
        );
    }


    (function () {
        function changeRootFont() {
            var designWidth = 750, rem2px = 100;
            document.documentElement.style.fontsize =
                ((window.innerWidth / designWidth) * rem2px) + 'px';
            //iphone6: (375 / 750) * 100 + 'px';
        }

        changeRootFont();
        window.addEventListener('resize', changeRootFont, false);


            // addAddress();

    })();
    
    $('#test1').change(function(e){  
        if($("#test1").prop('checked')){
            console.log('aaaa');
            $('.font_invoice').show();
        }else{
            $('.font_invoice').hide();
            $('.invoice_company').hide();
            $("#test2").prop('checked',false);
            $("#test3").prop('checked',false);
        }

    })
    $('#test3').change(function(e){
        $('.invoice_company').show();
        if($("#test3").prop('checked')){
          $("#test2").prop('checked',false);
        }
    })
    $('#test2').change(function(e){
        $('.invoice_company').hide();
        if($("#test2").prop('checked')){
          $("#test3").prop('checked',false);
        }
       // console.log($("#test3").prop('checked')?'1':$("#test2").prop('checked')?'2':'0');
    })
   
    //点击保存 刚刚输入的 姓名，手机号，地址，返回正确才保存成功
    function addAddress() {
        $.ajax({
            url:'{{route('s_user_addAddress')}}',
            type:'POST', //GET
            async:true,    //或false,是否异步
            data:{
                realname:$("#realname").val(),
                mobile:$("#mobile").val(),
                address:$("#address").val(),
                remark:$("#remark").val(),
                picked1:$("#test3").prop('checked')?'1':$("#test2").prop('checked')?'2':'0',
                companyName:$("#companyName").val(),
                numberC:$("#numberC").val()

            },
            "headers": {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            timeout:5000,    //超时时间
            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
            success:function(data,textStatus,jqXHR){
                if (data['status'] == 200) {
                    //data['data']  address id 获得
                    $('#addressid').val(data['data']);
                    // alert( "address"+data.data);
                }else{
                    $("#userNameT1").css("display","block");
                    $("#userNameT1").html(data['message']);
                }
            }
        })
    }
    //点击购买
    function buy() {
        $.ajax({
            url:'{{route('s_user_postbuy')}}',
            type:'POST', //GET
            async:true,    //或false,是否异步
            data:{
                realname:$("#realname").val(),
                mobile:$("#mobile").val(),
                address:$("#addressid").val(),
                remark:$("#remark").val()
            },
            "headers": {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            timeout:5000,    //超时时间
            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
            success:function(data,textStatus,jqXHR){
                if (data['status'] == 200) {

                    json =  $.parseJSON( data['json'] );
                    if (typeof WeixinJSBridge == "undefined"){
                        if( document.addEventListener ){
                            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                        }else if (document.attachEvent){
                            document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                        }
                    }else{
                        onBridgeReady();
                    }


                    //购买成功

                    /*window.location="";*/
                }else{
                    // $("#userNameT1").css("display","block");
                    alert(data['message']);
                }
            }
        })
    }
    var realname_o=$("#realname").val();
    var  mobile_o=$("#mobile").val();
    var addresdsd_o=$("#address").val();
    var sumValue_o=realname_o+mobile_o+addresdsd_o;
    var sumValue_so=sumValue_o.replace(/\ +/g,"");
    // 去空格

    $(".bottom_sure").click(function () {
        var realname=$("#realname").val();
        var  mobile=$("#mobile").val();
        var addresdsd=$("#address").val();
        if(realname===""){
            weui.alert('姓名不能为空', {
                title: '蒲公英',
                buttons: [{
                    label: '确定',
                    type: 'parimary',
                    onClick: function(){
			        	
                    }
                }]
            });
        }
        else {
            if(mobile===""){
                weui.alert('手机号不能为空', {
                    title: '蒲公英',
                    buttons: [{
                        label: '确定',
                        type: 'parimary',
                        onClick: function(){
                            
                        }
                    }]
                });
            }
            else {
                if (addresdsd===""){
                    weui.alert('地址不能为空', {
                        title: '蒲公英',
                        buttons: [{
                            label: '确定',
                            type: 'parimary',
                            onClick: function(){
                                
                            }
                        }]
                    });
                }
                else {
                    var valValue=realname+mobile+addresdsd;
                    var addressid=$("#addressid").val();//隐藏域的值
                    var sumValue=valValue.replace(/\ +/g,"");
                    if(false){//sumValue_so==sumValue
                        if(addressid>0){

                            //有默认地址且，没有修改  直接购买
                            $.ajax({
                                url:'{{route('s_user_postbuy')}}',
                                type:'POST', //GET
                                async:true,    //或false,是否异步
                                data:{
                                    realname:$("#realname").val(),
                                    mobile:$("#mobile").val(),
                                    address:$("#addressid").val(),
                                    remark:$("#remark").val()
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                timeout:5000,    //超时时间
                                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                                success:function(data,textStatus,jqXHR){
                                    if (data['status'] == 200) {
                                        json =  $.parseJSON( data['json'] );
                                        if (typeof WeixinJSBridge == "undefined"){
                                            if( document.addEventListener ){
                                                document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                                            }else if (document.attachEvent){
                                                document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                                                document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                                            }
                                        }else{
                                            onBridgeReady();
                                        }


                                        //购买成功

                                        /*window.location="";*/
                                    }else{
                                        // $("#userNameT1").css("display","block");
                                        alert(data['message']);
                                    }
                                }
                            });
                        } else {
                            weui.alert('地址有误，刷新或者重写。', {
                                title: '蒲公英',
                                buttons: [{
                                    label: '确定',
                                    type: 'parimary',
                                    onClick: function(){
                                        
                                    }
                                }]
                            });
                        }
                    }
                    else {
                        ////////////填写的地址不同，需要先增加成功后提交购买
                        $.ajax({
                            url:'{{route('s_user_addAddress')}}',
                            type:'POST', //GET
                            async:true,    //或false,是否异步
                            data:{
                                realname:$("#realname").val(),
                                mobile:$("#mobile").val(),
                                address:$("#address").val(),
                                remark:$("#remark").val(),
                                picked1:$("#test3").prop('checked')?'1':$("#test2").prop('checked')?'2':'0',
                                companyName:$("#companyName").val(),
                                numberC:$("#numberC").val()
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            timeout:5000,    //超时时间
                            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                            success:function(data,textStatus,jqXHR){
                                if (data['status'] == 200) {
                                    //data['data']  address id 获得
                                    $('#addressid').val(data['data']);
                                    //成功添加地址，可以购买
                                    $.ajax({
                                        url:'{{route('s_user_postbuy')}}',
                                        type:'POST', //GET
                                        async:true,    //或false,是否异步
                                        data:{
                                            realname:$("#realname").val(),
                                            mobile:$("#mobile").val(),
                                            address:$("#addressid").val(),
                                        },
                                        "headers": {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        timeout:5000,    //超时时间
                                        dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                                        success:function(data,textStatus,jqXHR){
                                            if (data['status'] == 200) {
                                                json =  $.parseJSON( data['json'] );
                                                if (typeof WeixinJSBridge == "undefined"){
                                                    if( document.addEventListener ){
                                                        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                                                    }else if (document.attachEvent){
                                                        document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                                                        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                                                    }
                                                }else{
                                                    onBridgeReady();
                                                }


                                                //购买成功

                                                /*window.location="";*/
                                            }else{
                                                // $("#userNameT1").css("display","block");
                                                alert(data['message']);
                                            }
                                        }
                                    });

                                }else{
                                    $("#userNameT1").css("display","block");
                                    $("#userNameT1").html(data['message']);
                                    return false;
                                }
                            }
                        })





                        ///////////addAddress,buy 方法同步运行 缺少关联  不符合目前逻辑
//                        addAddress();
//                        if(addressid>0){
//                            buy();
//                            alert("两次输入信息不一样");
//                        }
//                        else {
//                            alert("地址有误，刷新或者重写");
//                        }
//                        //alert("两次输入的值不一样");
                    }
                }
            }
        }




    });

</script>
</body>
</html>
