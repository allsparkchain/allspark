<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>佣金结算-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    {{--<link rel="stylesheet" href="{{ mix('css/header.css') }}">--}}
    <link rel="stylesheet" href="{{ mix('css/nav.css') }}">
    <link rel="stylesheet" href="{{ mix('css/commission_settlement.css') }}">
    <link rel="stylesheet" href="{{ mix('css/fonts.css') }}">
    <script src="/js/vendor/jquery-3.2.1.min.js"></script>
</head>
<body>
<div class="f_content" style="overflow:hidden;">
    <div class="header_border">
        <div class="header">
            <div class="login">
                <a href="{{Route('s_index_index')}}"></a>
            </div>
            <div class="header_navWrop">
                <div class="header_nva">
                    <div class="nav_list">
                        <a href="{{Route('s_index_index')}}">首页</a>
                        <a href="{{route('s_article_lists')}}" class="active">资讯</a>
                        <a href="https://www.pugongying.link/jzinter" target="_blank">关于我们</a>
                    </div>
                    @if(strlen(\Auth::getUser()->getUserNickname()) >0)
                        {{--登录状态--}}
                        <div class="user_logged" style="display:block;    width: 198px;height: 70px;float: left;">
                            <div class="userHides" >
                                <div class="" style="float: right;line-height: 70px;width: 138px;margin-left: 60px;    cursor: pointer;">
                                    <div class="usserImg" style="float: left; margin-top: 16px; font-size: 0; width:37px;height:37px;border-radius: 50%;"><img width="100%" height="100%" style="border-radius: 50%" src="{{\Auth::getUser()->getHeadImgurl()}}"></div>
                                    <span style="margin-left: 13px" class="loggedname">{{\Auth::getUser()->getUserNickname()}}</span>
                                </div>
                            </div>
                            <div class="user_hide" style="display: none">
                                <span class="userAs" id="userAs" onclick="window.location.href='{{Route('s_user_accountInfo')}}'">个人中心</span>
                                <span class="userAs" onclick="window.location.href='{{Route('s_logout')}}'">退出</span>
                            </div>
                        </div>
                    @else
                        {{--未登录状态--}}
                        <div class="user_login" style="">
                            <span class="spacing" onclick="window.location.href='/login'">登录</span>
                            <i class="spacing">/</i>
                            <span class="spacing" onclick="window.location.href='/qrRegister'">注册</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!--<div style="clear: both"></div>-->
    <div class="centent">
        <div class="centent_left">
            <div class="centent_left_portrait Tfont">
                <i><img src="{{\Auth::getUser()->getHeadImgurl()}}" width="100%" height="100%"> </i>
                <span class="user_name">{{\Auth::getUser()->getUserNickname()}}</span>
            </div>
            <div class="centent_left_title Tfont">
                <ul>
                    <li lang="accountInfo" ><a href="{{route('s_user_accountInfo')}}">账户总览</a></li>
                    <li lang="accountCommissionSettlement"class=""><a href="{{route('s_user_accountCommissionSettlement')}}">佣金结算</a></li>
                    <li lang="accountCommissionSettlementDetail"><a href="{{route('s_user_accountCommissionSettlementDetail')}}">佣金明细</a></li>
                    <li lang="accountSpreadData" ><a href="{{route('s_user_accountSpreadData')}}">推广数据</a></li>
                    <li lang="accountFreiendInvite" class=""><a href="{{route('s_user_accountFreiendInvite')}}" class="" >好友邀请</a></li>
                    <li lang="accountSetting" ><a href="{{route('s_user_accountSetting')}}">账户设置</a></li>
                </ul>
            </div>
        </div>
        <div class="centent_right">
            <div class="centent_right_A">
                <div class="centent_right_left Tfont">
                    <p class="TA_w TA_wRed">上月未结算佣金</p>
                    <span class="J_string"><span class="J_settlement">{{number_format($unsettled_amount_last_month,2)}}</span></span>
                </div>
                <div class="centent_TA">
                    <p class="TA_w TA_wRed">本月未结算佣金</p>
                    <span class="J_string"><span class="J_settlement">{{number_format($unsettled_amount_month,2)}}</span></span>
                </div>
                <div class="centent_TB">
                    <p class="TA_w TA_wBlue">未结算佣金总额</p>
                    <span class="J_string"><span class="J_settlement">{{number_format($not_settle_money,2)}}</span></span>
                </div>
            </div>
            <div class="centent_right_B">
                <div class="centent_right_q">
                    <ul>
                        <li class="wothdrawal active1">上月未结算佣金</li>
                        <li class="Run_water">本月未结算佣金</li>
                        <li class="Run_sum">已结算佣金</li>
                    </ul>
                </div>
                <div class="cnetent_right_1">
                    <div class="centent_runWater">
                        <ul class="one_ulCentent">
                            <li>
                                <span>时间</span>
                                <span class="last_span">佣金</span>
                            </li>

                        </ul>
                    </div>
                    <div class="runT">
                        <ul class="one_page">

                        </ul>
                    </div>
                </div>
                <div class="cnetent_right_2" style="display: none">
                    <div class="centent_runWater">
                        <ul class="twe_ulCentent">
                            <li>
                                <span>时间</span>
                                <span class="last_span">佣金</span>
                            </li>

                        </ul>
                    </div>
                    <div class="runT">
                        <ul class="twe_page">

                        </ul>
                    </div>
                </div>
                <div class="cnetent_right_3" style="display: none">
                    <div class="centent_runWater">
                        <ul class="three_ulCentent">
                            <li>
                                <span>时间</span>
                                <span class="last_span">佣金</span>
                            </li>

                        </ul>
                    </div>
                    <div class="runT">
                        <ul class="three_page">

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{--<footer>--}}
    {{--<p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>--}}
{{--</footer>--}}
<script>
    $(function () {
        $('.centent_left_title').find('ul').find('li').each(function(){
            $(this).removeClass();
            $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
            if($(this).attr('lang') == 'accountCommissionSettlement'){
                $(this).addClass('active');
                $(this).find('a').addClass('activeA2');
            }
        });

        $(".wothdrawal").click(function () {
            $(".wothdrawal").addClass("active1");
            $(".Run_water").removeClass("active2");
            $(".Run_sum").removeClass("active3");
            $(".cnetent_right_2").hide();
            $(".cnetent_right_1").show();
            $(".cnetent_right_3").hide();
        });
        $(".Run_water").click(function () {
            $(".Run_water").addClass("active2");
            $(".wothdrawal").removeClass("active1");
            $(".Run_sum").removeClass("active3");
            $(".cnetent_right_2").show();
            $(".cnetent_right_1").hide();
            $(".cnetent_right_3").hide()
        });
        $(".Run_sum").click(function () {
            $(".Run_sum").addClass("active3");
            $(".wothdrawal").removeClass("active1");
            $(".Run_water").removeClass("active2");
            $(".cnetent_right_3").show();
            $(".cnetent_right_1").hide();
            $(".cnetent_right_2").hide();
        });
        $(".runT ul li").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".runT ul li").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });

        $(".Run_water").click();
        //点击发送获取数据
        //当前第一页
        function getDataO(index_page) {

            $.ajax({
                url:'{{route('s_user_getCommissionSettlement')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    seetype:1,//1,2,3 tab 当前值
                    page:index_page,//当前页数
                    pagesize:10//每页条数
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.status==200){

                        var datesize=data.data.data.length;
                        console.log(datesize);
                        for(var i=0 ;i<datesize;i++){
                            var s = data.data.data[i].money + '';
                            var str = s.substring(0,s.indexOf('.') + 3);
                            $(".one_ulCentent").append("<li class='o"+i+"'><span>"+data.data.data[i].day+"</span><span>"+str+"</span></li>");
                        }
                        var page=data.data.page_count;
                        // var sumPage=1;
                        if(page){
                            $(".one_page").html('');
                            $(".one_page").append("<li>上一页</li>");
                            for(var j=1 ;j<=page;j++){
                                $(".one_page").append("<li class='"+j+"' >"+j+"</li>");
                            }
                            $(".one_page").append("<li>下一页</li>");
                            $(".1").addClass("active");
                        }

                    }
                    else {

                        console.log(data);
                    }
                }
            })
        }
        getDataO(1);
        //第二页数据(本月的未结算)
        function getDataT(index_page) {
            $.ajax({
                url:'{{route('s_user_getCommissionSettlement')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    seetype:2,//1,2,3 tab 当前值
                    page:index_page,//当前页数
                    pagesize:10//每页条数
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.status==200){
                        var timeS=data.data.page;
                        var datesize=data.data.data.length;
                        for(var i=0 ;i<datesize;i++){
                            var s = data.data.data[i].money + '';
                            var str = s.substring(0,s.indexOf('.') + 3);
                            $(".twe_ulCentent").append("<li class='o"+i+"'><span>"+data.data.data[i].day+"</span><span>"+str+"</span></li>");
                        }
                        var page=data.data.page_count;
                        if(page) {
                            $(".twe_page").append("<li>上一页</li>");
                            for (var j = 1; j <= page; j++) {
                                $(".twe_page").append("<li class='" + j + "'>" + j + "</li>");
                            }
                            $(".twe_page").append("<li>下一页</li>");
                            $(".1").addClass("active");
                        }
                    }
                    else {
                        console.log(data);
                    }
                }
            })
        }
        getDataT(1);
        //第三页数据（历史结算记录）
        function getDataS(index_page) {
            $.ajax({
                url:'{{route('s_user_getCommissionSettlement')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    seetype:3,//1,2,3 tab 当前值
                    page:index_page,//当前页数
                    pagesize:10//每页条数
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.status==200){
                        var timeS=data.data.page;
                        var datesize=data.data.data.length;
                        for(var i=0 ;i<datesize;i++){
                            var s = data.data.data[i].money + '';
                            var str = s.substring(0,s.indexOf('.') + 3);
                            $(".three_ulCentent").append("<li class='o"+i+"'><span>"+data.data.data[i].day+"</span><span>"+str+"</span></li>");
                        }
                        var page=data.data.page_count;
                        if(page) {
                            $(".three_page").append("<li>上一页</li>");
                            for (var j = 1; j <= page; j++) {
                                $(".three_page").append("<li class='" + j + "'>" + j + "</li>");
                            }
                            $(".three_page").append("<li>下一页</li>");
                            $(".1").addClass("active");
                        }
                    }
                    else {
                        console.log(data);
                    }
                }
            })
        }
        getDataS(1);

    });
    //    鼠标移动显示

    $(".userHides").mouseover(function () {
        $(".user_hide").show();
    });
    $(".userHides").mouseout(function () {
        $(".user_hide").hide();
    });

    $(".userAs").mouseover(function () {
        $(".user_hide").show();
    });
    $(".userAs").mouseout(function () {
        $(".user_hide").hide();
    });
</script>
</body>
</html>
