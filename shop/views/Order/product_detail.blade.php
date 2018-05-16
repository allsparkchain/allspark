<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>商品详情</title>
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
        input[type=button],input[type=text],input[type=password]{-webkit-appearance:none;outline:none}
        body{ background: #f2f2f2;}
    </style>
</head>
<body>
<div class="pgy_pro_content">
    <div class="pgy_pro_banner">
        <img src="{{$info['img_path']}}">
    </div>
    <div class="pgy_pro_describe">
        <h3>{{$info['product_name']}}</h3>
        <div class="pgy_pro_price" lang="{{$info['selling_price']}}">¥{{number_format($info['selling_price'],2)}}</div>
    </div>
</div>

<div class="pgy_pro_detail">
    {!! html_entity_decode($info['contents']) !!}
</div>
<footer class="pgy_footer">
    @if($preview == 1)
        <a class="wechat" href="#"></a>
        <div style=" width: auto; margin: 0 0 0 65px; overflow: hidden;">
            <div class="pgy_footer_wrap">
                <a class="history" href="#" style="margin: 0 5px 0 0;">
                    <i class="iconClock"></i>
                    历史订单
                </a>
            </div>
            <div class="pgy_footer_wrap">
                <a class="buy" href="javascript:;" style="margin: 0 0 0 5px;">
                    <i class="iconCart"></i>
                    我要购买
                </a>
            </div>
        </div>
    @else
        <a class="wechat" href="{{route('s_user_redirectWeixin',['name'=>$info['product_name'],'img'=>$info['img_path'],'price'=>number_format($info['selling_price'],2),'url'=>$url,'product_id'=>$info['product_id']])}}"></a>

        <div style=" width: auto; margin: 0 0 0 65px; overflow: hidden;">
            <div class="pgy_footer_wrap">
                <a class="history" href="{{route('s_order_orderHistoryList')}}" style="margin: 0 5px 0 0;">
                    <i class="iconClock"></i>
                    历史订单
                </a>
            </div>
            <div class="pgy_footer_wrap">
                <a class="buy" href="javascript:;" style="margin: 0 0 0 5px;">
                    <i class="iconCart"></i>
                    我要购买
                </a>
            </div>
        </div>

    @endif

</footer>

<div class="pgy_layer" style=" display:none;"></div>
<div class="pgy_pro_menu" style=" display:none;">
    <div class="pgy_pro_sku">
        <h3>{{$info['product_name']}}</h3>
        @if(count( $info['specifications'])>0 )
            @foreach( $info['specifications'] as $key=> $detail)
                @if(!is_null($detail['key']))
                    <div class="pgy_pro_skuWrap">
                        <span>{{$detail['key']}}</span>
                        <ul class="pgy_pro_skuList">
                            @foreach( $detail['value'] as $k=> $v)
                                @if(!is_null($v))
                                    <li key="{{$detail['key']}}-{{$v}}" class="@if($k == 0) '' @endif">{{$v}}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

            @endforeach
        @endif
    </div>
    <div class="pgy_pro_skuFoot">
        <div class="pgy_pro_num">
            数量
            <div class="pgy_pro_chooseNum">
                <div class="pgy_pro_numMinus">-</div>
                <div class="pgy_pro_totalNum">1</div>
                <div class="pgy_pro_numAdd">+</div>
            </div>
        </div>
        <div class="pgy_pro_totalPrice">价格<span>¥</span><span id="price">--</span></div>
        <input id="salePrice" type="hidden" value="--">
    </div>
    <input class="pgy_pro_buy" type="button" value="购买">
</div>



<script src="/js/jquery-3.2.1.min.js"></script>
<script>
$(function(){
    // $(".pgy_pro_detail img").each(function(){
    //     if($(this).width()>$(window).width()){
    //         $(this).css("width","100%");
    //     }
    // });

    $(".pgy_pro_detail img").each(function(){
        $(this).attr({'style':'','width':'','height':''});
    });

    $(".pgy_pro_detail table").attr({'style':'','width':''})
    
    var num=$(".pgy_pro_totalNum").text(); //数量
    var price=$("#price").text(); //总价
    var unitPrice=$("#salePrice").val(); //单价
    var skuId="";
    
    $(".buy").on("click",function(){
        $(".pgy_layer").show();
        $(".pgy_pro_menu").show();
    });

    $(".pgy_layer").on("click",function(){
        $(".pgy_layer").hide();
        $(".pgy_pro_menu").hide();        
    });


    var sku_list = [{!! $str !!}];//sku组合
    /* var sku_list = [
        {'sku_key': {"颜色":"白","大小":"16G","系统":"安卓"}, 'num': 4, 'account': '298.00'},
        {'sku_key': {"颜色":"黑","大小":"16G","系统":"安卓"}, 'num': 2, 'account': '299.00'},
        {'sku_key': {"颜色":"黑","大小":"32G","系统":"安卓"}, 'num': 3, 'account': '488.00'},
    ]; */
    console.log(sku_list);

    calAccount();

    //sku js
    function checkList() {
        var selectArray = {};
        var allArray = {};
        var selectArrayCount = 0;
        $(".pgy_pro_skuWrap li").each(function(k, v){
            var selectAttr= $(this).parents(".pgy_pro_skuWrap").find("span").text() ;
            var selectVaule = $(this).text();
            if ($(this).hasClass('on')) { // 选中的值
                selectArray[selectArrayCount] = {"key":selectAttr, "value":selectVaule};
                selectArrayCount++;
            }
            allArray[k] = {"key":selectAttr, "value":selectVaule};
        });
        for (var k1 in allArray) {
            if($('.pgy_pro_skuWrap li[key="'+allArray[k1]['key']+'-'+allArray[k1]['value']+'"]').hasClass('on')){
                continue;
            }else{
                var selected = {};
                var selectedCount = 0;
                for (var k3 in selectArray) {
                    if ( selectArray[k3]["key"] != allArray[k1]["key"]) {
                        selected[selectedCount] = selectArray[k3];
                        selectedCount++;
                    }
                }
                selected[selectedCount] = allArray[k1];
                var boolen = false;
                $(sku_list).each(function(k, v){
                    var flag = 0;
                    if (!boolen) {
                        //console.log(JSON.stringify(v['specificationArr']))
                        for (var k4 in v['specificationArr']){
                            for (var k2 in selected) {
                                if (selected[k2]['key'] == k4 && v['specificationArr'][k4] == selected[k2]['value']) {
                                    flag++;
                                }
                            }
                            if (selectedCount +1  == flag) {
                                boolen = true;
                            }
                        }
                    }
                });
                $('.pgy_pro_skuWrap li[key="'+allArray[k1]['key']+'-'+allArray[k1]['value']+'"]').addClass('b');
                if (boolen) {
                    $('.pgy_pro_skuWrap li[key="'+allArray[k1]['key']+'-'+allArray[k1]['value']+'"]').removeClass('b');
                    
                }
            }
        }
    }

    //根据sku选项计算出金额并显示
    function calAccount(){
        var endArry="{";
        var count=0;
        $(".pgy_pro_skuList li.on").each(function(k,v){
            if(count == 0){
                endArry +='"'+$(v).attr('key').split('-')[0]+'":"'+$(v).attr('key').split('-')[1]+'"';
            }else{
                endArry +=',"'+$(v).attr('key').split('-')[0]+'":"'+$(v).attr('key').split('-')[1]+'"';
            }
            count++;
        });
        endArry +='}';

        $(sku_list).each(function(k, v){
            if (endArry == JSON.stringify(v['specificationArr'])){//console.log(11111)
                unitPrice=v['selling_price'];
                skuId = v['id'];
                $('#price').text(parseFloat(unitPrice*num).toFixed(2));
                return false;
            }else{//console.log(endArry+"+"+JSON.stringify(v['specificationArr']))
                unitPrice="--";
                skuId = "";
                $('#price').text(unitPrice);
            }
            
        });
    }

    //选择规格
    $(".pgy_pro_skuList li").on("click",function(){
        var obj=$(this);
        if (obj.hasClass('b')) {
            return;//不能选择
        }
        if(obj.hasClass('on')){
            obj.removeClass('on').siblings().removeClass('on');
        }else{ 
            obj.addClass('on').siblings().removeClass('on');
        }
        checkList();
        calAccount();        
    });

    //数量-
    $(".pgy_pro_numMinus").on("click",function(){
        if(num<2){
            price=(num*unitPrice).toFixed(2);
            $("#price").text(price);
            return false;
        }else{
            num--;
            price=parseFloat(num*unitPrice).toFixed(2);
            $("#price").text(price);
            $(".pgy_pro_totalNum").text(num);
        }
    });

    //数量+
    $(".pgy_pro_numAdd").on("click",function(){
        num++;
        if(unitPrice!=="--"){
            price=parseFloat(num*unitPrice).toFixed(2);
            $("#price").text(price);
        }
        $(".pgy_pro_totalNum").text(num);
        
    });

    //弹出层购买
    $(".pgy_pro_buy").on("click",function(){
        @if($preview == 1)
            //预览 不可购买
            alert('预览');
            return false;
        @endif

        var extra="";
        $(".pgy_pro_skuList li.on").each(function(k,v){
            extra+=$(v).attr('key').split('-')[0]+"："+$(v).attr('key').split('-')[1]+" ";
        });
        
        if(skuId!=""){//console.log(extra+"+"+skuId);
            $.ajax({
                url:'{{route('s_user_prepare')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    num:num,
                    extra:extra,
                    specificationId:skuId
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                beforeSend:function(){
                    $(".pgy_pro_buy").attr("disabled","disabled");
                },
                success:function(res){
                    if (res.status == 200) {
                        $(".pgy_pro_buy").removeAttr("disabled");
                        $(".pgy_layer").hide();
                        $(".pgy_pro_menu").hide();  
                        window.location="{{route('s_user_confirmOrder')}}/";
                    }else{
                        alert(res.message);
                        $(".pgy_pro_buy").removeAttr("disabled");
                    }
                }
            });
        }
    })

})
</script>
</body>
</html>
