<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>提现-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" href="icon.png">
    <!-- Place favicon.ico in the root directory -->
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    {{--<link rel="stylesheet" href="{{ mix('css/header.css') }}">--}}
    <link rel="stylesheet" href="{{ mix('css/nav.css') }}">
    <link rel="stylesheet" href="{{ mix('css/infocenter_withdrawal.css') }}">
    <link rel="stylesheet" href="{{ mix('css/fonts.css') }}">
    <script src="/js/vendor/jquery-3.2.1.min.js" type="text/javascript"></script>
</head>
<body style="">
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
                <ul >
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
            <div class="centent_right_q">
                <ul>
                    <li class=" active1 wothdrawal">我要提现</li>
                    <li class=" Run_water">资金流水</li>
                </ul>
            </div>

            <input id="bank_id"  type="hidden" value="{{$bid or 0}}">
            <input id="bank_number"  type="hidden" value="{{$banknumber or 0}}">
            <input id="realname"  type="hidden" value="{{$realname or ''}}">

            <div class="cnetent_right_1">
                <div class="centent_right_A">
                    <div class="centent_right_left Tfont">
                        <p class="title_h2">可提现金额 </p>
                        <p class="sfder"><span class="margin_top75 j_y"><span class="Jfont">￥</span>
                                <span id="money_sum" class="JEfont">{{number_format($account,2)}}</span></span></p>
                        <!--<p class="font_size12">预计1-2个工作日到账</p>-->
                        <div class="input_font1">
                            <span>￥</span>
                            <input placeholder="如数提现金额" class="account" id="account">
                            <span id="T_money" class="money" style="display: none">金额只能是数字</span>
                            <span id="takeall" style="cursor: pointer;line-height: 45px;padding-left: 15px;">全部提现</span>
                        </div>
                        <div class="input_font2">
                            <input placeholder="发送验证码" class="verification_code" id="code" maxlength="4">
                            <a class="user_runA" href="javascript:;">发送验证码</a>
                        </div>
                    </div>
                    <div class="centent_T" >
                        <a href="{{route('s_user_accountSetting',['judge'=>1])}}">
                            <div class="colo"></div>
                            <span class="card_font">绑定银行卡</span></a>
                    </div>
                    <div class="centent_Ts card_im" >
                        <div class="W_card" style="">
                            <span id="showarea" class="card_1" style=""></span>
                            <span id="cardNum" class="card_2" style=""></span>
                            <span class="user_id card_3" style=""></span>
                        </div>
                        {{--<a href="{{route('s_user_accountSetting')}}">--}}
                        {{--<span class="card_font">绑定银行卡</span></a>--}}
                    </div>
                </div>
                <div class="run_money">
                    <a href="javascript:;" id="drawClick">我要提现</a>
                </div>
            </div>
            <div class="cnetent_right_2" style="display: none">
                <div class="centent_runWater">
                    <ul>
                        <li>
                            <span>时间</span>
                            <span>流水订单号</span>
                            <span>变动类型</span>
                            <span>支付状态</span>
                            <span>变动金额</span>
                            <span>当时余额</span>
                        </li>
                        @if(count($flowingList) > 0)
                            @foreach($flowingList as $value)
                                <li>
                                    <span>{{date('Y.m.d H:i:s',$value['add_time'])}}</span>
                                    <span>{{$value['order_number']}}</span>
                                    <span>{{$value['commission_name']}}</span>
                                    <span>{{getOrderStatus($value['status'])}}</span>
                                    <span>{{getRecordSymbol($value['type'])}} {{number_format($value['account'],2)}}</span>
                                    <span> {{number_format($value['available_amount'],2)}}</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
                @if($pageList)
                    <div class="runT">
                        <ul>
                            @if($pageList['prev'])
                                <li><a href="{{route('s_user_accountWithdraw',['page'=>$pageList['prev'],'c'=>1])}}">上一页</a></li>
                            @else
                                <li>上一页</li>
                            @endif
                            @foreach($pageList['list'] as $page)
                                <li ><a class="@if($page == $current_page)active @endif " href="{{route('s_user_accountWithdraw',['page'=>$page,'c'=>1])}}">{{$page}}</a></li>
                            @endforeach
                            @if($pageList['dot'])
                                <li><a href="{{route('s_user_accountWithdraw',['page'=>$pageList['dot'],'c'=>1])}}">...</a></li>
                            @endif
                            @if($pageList['last'])
                                <li><a href="{{route('s_user_accountWithdraw',['page'=>$pageList['last'],'c'=>1])}}">{{$pageList['last']}}</a></li>
                            @endif
                            @if($pageList['next'])
                                <li><a href="{{route('s_user_accountWithdraw',['page'=>$pageList['next'],'c'=>1])}}">下一页</a></li>
                            @else
                                <li>下一页</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<footer>
    <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>
</footer>
<script src="js/vendor/jquery-3.3.1.min.js"></script>
<!-- Google Analytics: change UA-XXXXX-Y to be your site's ID. -->
<script type="text/javascript">

    //    鼠标移动显示

    var bank_id=$("#bank_id").val();


    if(bank_id>0){
        $(".centent_T").hide();
        $(".centent_Ts").show();
        var bank_number=$("#bank_number").val();
        var  realname=$("#realname").val();
        $("#cardNum").html(bank_number);
        $(".user_id").html(realname);
    }
    else {
        $(".centent_T").show();
        $(".centent_Ts").hide();
    }



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

    $(function () {
        $('.centent_left_title').find('ul').find('li').each(function(){
            $(this).removeClass();
            $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
            if($(this).attr('lang') == 'accountInfo'){
                $(this).addClass('active');
                $(this).find('a').addClass('activeA1');
            }
        });

        $(".wothdrawal").click(function () {
            $(".wothdrawal").addClass("active1");
            $(".Run_water").removeClass("active2");
            $(".cnetent_right_2").hide();
            $(".cnetent_right_1").show();
        });
        $(".Run_water").click(function () {
            $(".Run_water").addClass("active2");
            $(".wothdrawal").removeClass("active1");
            $(".cnetent_right_1").hide();
            $(".cnetent_right_2").show();
        });

        var c = '{{$c}}';
        if(c==1){
            $(".Run_water").addClass("active2");
            $(".wothdrawal").removeClass("active1");
            $(".cnetent_right_1").hide();
            $(".cnetent_right_2").show();
        }

        //可提金额
        $('#takeall').click(function(){
            $('#account').val({{$account or 0.00}});
        });

        //校验金额
        function  T_phone() {
            var Reg=/^[0-9]+(.[0-9]{1,2})?$/;
            var str=$(".account").val();
            if(str===""){
                console.log("默认行为");

            }
            else {
                if (!Reg.test(str)){
                    $(".money").css("display","block");
                    $(".money").html("只能输入数字");
                }
                else {
                    $(".money").css("display","none");
                }
            }
            var account=parseInt($(".account").val());
//            var money_su=100;
            var money_su= $(".JEfont").html();
             var money_sum=document.getElementById("money_sum").value;
            // console.log(Jt);
            console.log(account);
            if (money_su<account){
                // var JEfont=$(".JEfont").val();
                // console.log("正确");
                $(".money").css("display","block");
                $(".money").html("提现金额不能大于可提现金额");
            }
            // else {
            //     console.log("错误");
            //
            // }
        }
        $(".account").blur(function () {
            T_phone();
        });

        //点击发送验证码
        $(".user_runA").click(function () {
            var Reg=/^[0-9]+(.[0-9]{1,2})?$/;
            var str=$(".account").val();
            var bank_id=$("#bank_id").val();
            if(bank_id==="0"){
                $(".money").css("display","block");
                $(".money").html("请先绑定银行卡");
                return false;
            }
            if(str===""){
                $(".money").css("display","block");
                $(".money").html("金额只能是数字");
                return false;
            }
            else {
                if(Reg.test(str)){
                    var account=parseInt($(".account").val());
                    var money_su= $(".JEfont").html();
                    console.log( typeof  money_su);
                    // var money_sum=document.getElementById("money_sum").value;
                    // console.log(Jt);
                    console.log(account);
                    if (money_su<account){
                        // var JEfont=$(".JEfont").val();
                    }
                    else {
                        // $(".money").css("display","block");
                        // $(".money").html("提现金额不能大于可提现金额");
                        sendDrawSms();
                    }

                }
            }
        });

        //发送短信
        function sendDrawSms() {
            $.ajax({
                url:"{{route('s_sms_sendDrawSms')}}",
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    mobile:$("#userName").val(),
                    type:'4'
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.status==200){
                        // $(".Send_verification_code").css("display","block");
                        // $(".Send_verification_code").html("验证码发送成功");

                        // function untime() {
                        //
                        // }

                        sendemail();
                        $(".y_sin").show();
                        setTimeout('$(".y_sin").hide()',60000);

                    }
                    else {
                        $(".Send_verification_code").show();
                        $(".Send_verification_code").html(data.message);
                    }
                }
            })
        }
        //手机倒计时
        var countdown=60;
        function sendemail(){
            var obj = $(".user_runA");
            settime(obj);
        }
        function settime(obj) { //发送验证码倒计时
            if (countdown == 0) {
                obj.prop('disabled',false);
                //obj.removeattr("disabled");
                obj.text("重新获取验证码");
                countdown = 60;
                $('.user_runA').css("backgroundColor","#ff7241");
                $('.user_runA').css("cursor","pointer");
                return;
            } else {
                obj.prop('disabled',true);
                obj.text("(" + countdown + "s)重新发送");
                countdown--;
                $('.user_runA').css("backgroundColor","#bebebe");
                $('.user_runA').css("cursor","auto");
            }
            setTimeout(function() {
                    settime(obj) }
                ,1000)
        }
        //校验手机验证码验证码
        function T_verificationcode() {
            if(str===""){
                // console.log("默认行为");
            }
            else {
                var Reg=/[0-9]{4}/;
                var str=$(".verification_code").val();
                if(!Reg.test(str)){
                    $(".Send_verification_code").css("display","block");
                }
                else {
                    $(".Send_verification_code").css("display","none");
                }
            }
        }
        //验证码（6位数字）封装
        function T_verification_code(str)  {
            var Reg=/[0-9]{4}/;
            return Reg.test(str);
        }


        $(".verification_code").blur(function () {
            var verification_codeStr=$(".verification_code").val();
            if(verification_codeStr===""){
                console.log("默认行为");
            }
            else {
                var Reg=/^\d{4}$/;
                var verification_code=$(".verification_code").val();
                if(Reg.test(verification_code)){
                    // $("#Send_verification_code").css("display","block");
                    $(".Send_verification_code").css("display","none");
                    // $("#Send_verification_code").html("验证码有误");
                    //
                }
                else {
                    $(".Send_verification_code").css("display","block");
                    $("#Send_verification_code").html("验证码有误");
                }
            }
        });



        //我要提现
        $("#drawClick").click(function () {
            var account=$(".account").val();
            var bank_id=$("#bank_id").val()
            var verification=$(".verification_code").val();
            if(account===""){
                $(".money").css("display","block");
                $(".money").html("请输入金额");
            }
            if(bank_id==="0"){
                $(".money").css("display","block");
                $(".money").html("请先绑定银行卡");
            }
            if(verification===""){
                $(".Send_verification_code").css("display","block");
                $(".Send_verification_code").html("请获取验证码");

            }
            var vas=$(".verification_code").val();
            var str=T_verification_code(vas);
            if(str===true){
                validatorSms();
            }


        });


        function validatorSms(){
            $.ajax({
                url:"{{route('s_sms_validatorDrawSms')}}",
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    mobile:$("#userName").val(),
                    code:$("#code").val()
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        //提现
                        $.ajax({
                            url:"{{route('s_draw_withdrawalApplication')}}",
                            type:'POST', //GET
                            async:true,    //或false,是否异步
                            data:{
                                account:$("#account").val(),
                                bank_id:$("#bank_id").val()
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            timeout:5000,    //超时时间
                            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                            success:function(data,textStatus,jqXHR){
                                if(data.status==200){
                                    // alert("成功");//发送验证码成功
                                    $(".mask").show();
                                    $("body").css("overflow","hidden");
                                    var esdaas=$("#account").val();

                                    $(".hasBeen").html(esdaas);
                                    window.location.href = "{{route('s_user_accountWithdraw')}}?c=1"


                                }else{
                                    // alert("失败");
                                    $("#Send_verification_code").show();
                                    $("#Send_verification_code").html(data['message']);

                                }
                            }
                        })
                    }
                    else {
                        $(".Send_verification_code").show();
                        $(".Send_verification_code").html(data.message);
                    }
                }
            })
        }
        $(".bottom_ss_a").click(function () {
            $(".mask").hide();
            $("body").css("overflow","auto");
        });
        $(".ast_icon").click(function () {
            $(".mask").hide();
            $("body").css("overflow","auto");
        });


    });

    //     上传



    (function ($) {
        $.fn.cncard = function(options){
            var container = $(this);
            options = $.extend({
                display:''
            },options);

            var display=options.display;

            function cardFormBank(num){

                var cards = [
                    {
                        bankName:'中国建设银行',
                        pattern:/^(436742|436745|622280|622700)/g
                    },
                    {
                        bankName:'交通银行',
                        pattern:/^(458123|521899|622260|622250|622251|622258|622259)/g
                    },
                    {
                        bankName:'上海银行',
                        pattern:/^(402674|622892)/g
                    },
                    {
                        bankName:'中国邮政储蓄银行',
                        pattern:/^(622188|622150|622151|622199|955100)/g
                    },
                    {
                        bankName:'北京银行',
                        pattern:/^(602969)/g
                    },
                    {
                        bankName:'中国银行',
                        pattern:/^(622760|622751|622752|622753|622754|622755|622756|622757|622758|622759|622760|622761|622762|622763|409666|438088|601382)/g
                    },
                    {
                        bankName:'中国工商银行',
                        pattern:/^(427010|427018|427019|427020|427028|427029|427030|427038|427039|427062|427064|438125|438126|402791|530990|622230|622235|622210|622215|622200|622202|622203|622208|955880|370246|370247)/g
                    },
                    {
                        bankName:'广发银行',
                        pattern:/^(622568|520152|520382|911121|548844)/g
                    },
                    {
                        bankName:'宁波银行',
                        pattern:/^(512431|520194|622318|622778|622282)/g
                    },
                    {
                        bankName:'中国民生银行',
                        pattern:/^(512466|415599|421870|407405|517636|528948 |552288|556610|622600|622601|622602|622603|421869|421871|628258)/g
                    },
                    {
                        bankName:'浦发银行',
                        pattern:/^(418152|456418|622521|404738|404739|498451|622517|622518|515672|517650|525998|356850|356851|356852)/g
                    },
                    {
                        bankName:'深圳发展银行',
                        pattern:/^(435744|622526|435745|998801|998802|622525|622538)/g
                    },
                    {
                        bankName:'中国光大银行',
                        pattern:/^(406254|622655|622650|622658|356839|486497|481699|543159|425862|406252|356837|356838|356840|622161|628201|628202)/g
                    },
                    {
                        bankName:'平安银行',
                        pattern:/^(622155|622156|528020|526855)/g
                    },
                    {
                        bankName:'华夏银行',
                        pattern:/^(539867|528709|523959|622637|622636|528708|539868)/g
                    },
                    {
                        bankName:'招商银行',
                        pattern:/^(518710|518718|622588|622575|545947|521302|439229|552534|622577|622579|439227|479229|356890|356889|356885|439188|545948|545623|552580|552581|552582|552583|552584|552585|552586|552588|552589|645621|545619|356886|622578|622576|622581|439228|439225|439226|628262|628362)/g
                    },
                    {
                        bankName:'中信银行',
                        pattern:/^(376968|376966|622918|622916|518212|622690|520108|376969|622919|556617|622680|403391|558916|514906|400360|433669|433667|433666|404173|404172|404159|404158|403393|403392|622689|622688|433668|404157|404171|404174|628209|628208|628206)/g
                    },
                    {
                        bankName:'中国农业银行',
                        pattern:/^(552599|404119|404121|519412|403361|558730|520083|520082|519413|404120|622922|404118|404117|622836|622837|622848)/g
                    },
                    {
                        bankName:'兴业银行',
                        pattern:/^(451289|622902|622901|527414|524070|486493|486494|451290|523036|486861|622922 )/g
                    }
                ];

                //循环查询银行，存在则返回该银行的数组
                for(var _i = 0,_len = cards.length; _i<_len;_i++)

                {
                    var card = cards[_i];
                    if(card.pattern.test(num))
                    {
                        return card;
                    };
                };

                //循环结束，无相关数据，则返回false
                if(_i=_len)
                {
                    return false;
                };
            };

            function checkCardNum(e){
                //输入的卡号变为字符串同时截取前六位
                var cardNum = e.toString().substring(0,6);
                //变为数字
                cardNum = parseInt(cardNum);
                //进入cardFormBank()函数进行查询
                var a = cardFormBank(cardNum);
                //若无相关数据返回false，显示其他银行，否则返回银行名称
                if(a == false)
                {
                    return "其他银行" ;
                }
                else
                {
                    return a.bankName;
                }
            };

            //获取输入框的银行卡号
            var num = $(container).html();
            //检测是否为数字且大于16位小于19位，否则显示输入有误
            if(!isNaN(num) && num.length>=16 && num.length<=19)
            {
                var result=checkCardNum(num);
                $(display).html(result);
            }
            else
            {
                $(display).html("输入有误");
            }

        };

    })(jQuery);
    $(document).ready(function() {
        $('#cardNum').cncard({display:'#showarea'});
    });

</script>
</body>
</html>
