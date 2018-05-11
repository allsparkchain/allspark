<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>用户设置-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <link rel="stylesheet" href="{{ mix('css/fonts.css') }}">
    {{--<link rel="stylesheet" href="{{ mix('css/header.css') }}">--}}
    <link rel="stylesheet" href="{{ mix('css/account_settings.css') }}">
    <script src="/js/vendor/jquery-3.2.1.min.js"></script>
</head>
<body style="margin: 0 !important;">
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
                <ul style="padding-left: 0 !important;">
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
            <div class="centent_right_bg">
                <div class="tab_switch">
                    <!--<ul>-->
                    <!--<li class=" active1">修改密码</li>-->
                    <!--<li class="wothdrawa_t ">我的银行卡</li>-->
                    <!--</ul>-->
                </div>
                <div class="change_password">
                    <div class="change_password_as wothdrawa_o"><span class="change_password_font">修改密码</span><span class="change_password_icon">></span></div>
                    <div class="list1" style="display: none;">
                        <div class="list1_centent">
                            <input class="password" id="oldpas" type="password" placeholder="请输入原始密码">
                            <span class="colors_font password_as" style="display: none">密码不对</span>
                            <p class="error_t">密码必须8位至20位，由英文与数字组成</p>
                            <input class="password" id="newpas" type="password" placeholder="请输入新密码">
                            <span class="colors_font password_new" style="display: none">密码格式不对</span>
                            <input class="password" id="newpas2" type="password" placeholder="再次输入密码">
                            <span class="colors_font password_newT" style="display: none">两次输入密码不一致</span>
                            <div class="button-wrapper">
                                <a href="javascript:;"  class="texts">确定</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="middle_wrop">
                    <div class="cnetent_middle">
                        <div class="change_password_as wothdrawa_t" style="cursor: pointer;">
                            <span class="change_password_font">我的银行卡</span>
                            <span class="change_password_icon">&gt;</span>
                        </div>
                    </div>
                    <div class="font_wrop" style="display: none">
                        <div class="list2"  style="display: none">
                            <div class="mg_b">
                                <div class="t_prompt">提示：你可以将结算余额提现绑定的这张银行卡。</div>
                            </div>

                            <div class="list2_centent">

                                <!--<p class="list2_ps_title no_m"> 请输入你的真实姓名</p>-->

                                <input class="password"  id="bind_realname"  placeholder="请输入你的真实姓名">
                                <span class="colors_font zhFont" style="display: none">真实姓名只能是中文</span>
                                <!--<p class="list2_ps_title"> 请输入你的身份证</p>-->

                                <input class="password2" id="bind_idnumber" placeholder="请输入身份证号码" maxlength="18">
                                <span class="colors_font idCard" style="display: none">身份证号有误</span>
                                <!--<p class="list2_ps_title"> 请输入银行卡号</p>-->

                                <input class="password3" id="bind_banknumber" placeholder="请输入银行卡号" maxlength="19">
                                <span class="colors_font moneyCard" style="display: none">银行卡有误</span>
                                <!--<p class="list2_ps_title">请输入银行预留手机号</p>-->

                                <input class="password4" id="bind_mobile"  placeholder="请输入银行预留手机号" maxlength="11">
                                <span class="colors_font pmunber" style="display: none">手机号有误</span>
                                <div class="button-wrapper mg_t">

                                    <a href="javascript:void(0);"  class="text">绑&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;定</a>

                                </div>
                                <input type="hidden" id="bid" value="{{$bid or 0}}">
                                <input id="banknumber"  type="hidden" value="{{$banknumber or 0}}">
                                <input id="realname"  type="hidden" value="{{$realname or ''}}">
                            </div>

                        </div>



                        <div class="my_card" style="display: none">
                            <div style="padding-top: 30px;    padding-left: 70px;">
                            <div class="card_im">
                                <div class="W_card" style="">
                                    <span id="showarea" class="card_1" style=""></span>
                                    <span id="cardNum" class="card_2" style=""></span>
                                    <span class="user_id card_3" style=""></span>
                                </div>

                            </div>
                            </div>

                            <div class="card_font">

                                <span id="unbundling">解绑银行卡</span>

                                <span id="replace_card">更换银行卡</span>

                            </div>

                        </div>
                        <input id="mobilenumber"  type="hidden" value="{{$mobile or -1}}">
                        <div class="bind" style="display: none">
                            你的银行卡解绑成功
                        </div>
                        <!--更换银行卡-->
                        <div class="p_code" style="display:none">
                            <!--<div  class="t_shi">提示：需要进行手机短信验证</div>-->
                            <div>
                                <!--<p class="p_password no_m">请输入你的手机号码</p>-->

                                <span class="mobilePhone">手机号<span class="G_pshos"></span></span>
                                <span class="colors_font G_p" style="display: none">请输入手机号</span>
                                <div class="mgint_T">
                                    <i class="yancode"></i>
                                    <input id="change_code" type="text" class="inputstyle " placeholder="输入验证码" maxlength="4">
                                    <a class="user_runA G_runa" href="javascript:void(0);">发送验证码</a>
                                </div>

                                <span class="colors_font G_yans" style="display: none">验证码有误</span>



                                <div class="y_hiedGhu" style="display: none;"></div>
                                <div class="runBottom">

                                    <a id="run_bund" href="javascript:;">继&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;续</a>

                                </div>

                            </div>

                        </div>
                        <!--解绑银行卡-->
                        <div class="p_code_jm " style="">
                            <!--<div  class="t_shi">提示：需要进行手机短信验证</div>-->
                            <div>
                                <!--<p class="p_password no_m">请输入你的手机号码</p>-->

                                <span class="mobilePhone">手机号<span class="G_pshos"></span></span>
                                <span class="colors_font J_pmunber" style="display: none">手机号格式不对</span>
                                <div class="mgint_T">
                                    <i class="yancode"></i>
                                    <input type="text" id="unbind_code" class="inputstyle " placeholder="输入验证码" maxlength="4">
                                    <a class="user_runA user_runAs" href="javascript:;">发送验证码</a>
                                </div>
                                <span class="colors_font J_cosy" style="display: none">验证码有误</span>
                                <div class="y_hiedJba" style="display: none;"></div>
                                <div class="runBottom">
                                    <a id="run_bundj" href="javascript:;">继&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;续</a>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!--<div  class="t_shi">提示：需要进行手机短信验证</div>-->
                </div>
            </div>

        </div>
    </div>
</div>
</div>
{{--<footer>--}}
    {{--<p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>--}}
{{--</footer>--}}
<script type="text/javascript">
    $(".G_pshos").html();
    $(function () {

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


        $('.centent_left_title').find('ul').find('li').each(function(){
            $(this).removeClass();
            $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
            if($(this).attr('lang') == 'accountSetting'){
                $(this).addClass('active');
                $(this).find('a').addClass('activeA6');
            }
        });

        $(".wothdrawa_o").css("cursor","pointer");
        $(".wothdrawa_o").click(function () {
            // $(".wothdrawa_o").addClass("active1");
            // $(".wothdrawa_t").removeClass("active2");
            $(".list1").toggle();
            // $(".list2").hide();
            // $(".bind").hide();
            // $(".my_card").hide();
            // $(".p_code").hide();
            // $(".p_code_jm").hide();
        });
        $(".wothdrawa_t").click(function () {
            // $(".font_wrop").toggle("transforma");
            // $(".font_wrop").toggle(
            //     function(){$(".change_password_icon").class();}
            //     );
            var bid=document.getElementById("bid").value;
            if (bid>0){
                $(".font_wrop").toggle();
                $(".list2").show();
                $(".list1").hide();
                $(".bind").hide();
                $(".my_card").hide();
                $(".p_code").hide();
                $(".p_code_jm").hide();
                $(".p_code_jm").hide();
                $(".list2").hide();
                $(".my_card").show();
            }else {
                $(".font_wrop").toggle();
                $(".list2").show();
                $(".list1").hide();
                $(".bind").hide();
                $(".my_card").hide();
                $(".p_code").hide();
                $(".p_code_jm").hide();
                $(".p_code_jm").hide();
            }
        });
        $(".p_code_jm").hide();
        $("#unbundling").click(function () {
            $(".my_card").hide();
            $(".p_code").show();
            $(".p_code_jm").hide();
            // $(".p_code_jm").hide();
        });
        $("#replace_card").click(function () {
            $(".my_card").hide();
            $(".p_code_jm").show();
            // $(".p_code_jm").hide();
            //
        });


        //密码
        function password(str) {
            Reg=/^[A-Za-z0-9]{8,18}$/;
            return Reg.test(str);
        }
        $("#oldpas").blur(function () {
            var oldpas=$("#oldpas").val();
            if (oldpas===""){
                $(".password_as").css("display","none");
            }
            else {
                if (password(oldpas)===true){
                    $(".password_as").css("display","none");
                }
                else {
                    $(".password_as").css("display","block");
                }
            }

        });
        //新密码（第一次）
        $("#newpas").blur(function () {
            var newpass=$("#newpas").val();
            if (newpass===""){
                $(".password_new").css("display","none");
            }
            else {
                if(password(newpass)){
                    $(".password_new").css("display","none");
                }
                else {
                    $(".password_new").css("display","block");
                }
            }

        });
        //新密码（第二次）

        $("#newpas2").blur(function () {
            var newpass1=$("#newpas").val();
            var newpass=$("#newpas2").val();
            if (newpass===newpass1){
                $(".password_newT").css("display","none");
            }
            else {
                $(".password_newT").css("display","block");
            }
        });
        //继续
        $(".texts").click(function () {
            var oldpas=$("#oldpas").val();
            if(oldpas===""){
                $(".password_as").css("display","block");
            }
            var newpass=$("#newpas").val();
            if(newpass===""){
                $(".password_new").css("display","block");
            }
            var newpass1=$("#newpas").val();
            if(newpass===""){
                $(".password_newT").css("display","block");
            }
            if(password(newpass)){
                changepwd();
            }

        });


        function changepwd() {
            $.ajax({
                url:'{{route('s_user_changePassword')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    oldpas:$("#oldpas").val(),
                    newpas:$("#newpas").val(),
                    newpas2:$("#newpas2").val(),
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        window.location="{{route('s_logout')}}?changepwd=1";
                        {{--window.location="{{route('s_user_accountInfo')}}";--}}

                    }else{
                        // alert(data['message']);
                    }
                }
            })
        }

        //中文
        function user(str) {
            Reg=/^[\u4e00-\u9fa5]{0,}$/;
            return Reg.test(str);
        }
        $("#bind_realname").blur(function () {
            var bind_realname=$("#bind_realname").val();
            if (bind_realname===""){
                $(".zhFont").css("display","none");
            }
            else {
                if(user(bind_realname)){
                    $(".zhFont").css("display","none");
                }
                else {
                    $(".zhFont").css("display","block");
                }
            }

        });


        //身份证
        function idCard(str) {
            Reg=/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/;
            return Reg.test(str);
        }
        $("#bind_idnumber").blur(function () {
            var bind_idnumber=$("#bind_idnumber").val();
            if (bind_idnumber===""){
                $(".idCard").css("display","none");
            }
            else {
                if(idCard(bind_idnumber)){
                    $(".idCard").css("display","none");
                }
                else {
                    $(".idCard").css("display","block");
                }
            }

        });


        //银行卡号
        $("#bind_banknumber").blur(function () {
            var password3=$(".password3").val();
            if (password3===""){
                $(".moneyCard").hide();
            }
            else {
                // alert("输入的文字不对");
                var Reg=/^([1-9]{1})(\d{14}|\d{18})$/;
                var bind_banknumber=parseInt($("#bind_banknumber").val());
                if(Reg.test(bind_banknumber)===true){

                    $(".moneyCard").css("display","none");
                }
                else {
                    $(".moneyCard").css("display","block");
                }
            }
        });

        var Usermobile = {{$mobile}};

        //手机号
        function bankCard(str) {
            var Reg = /^1[34578]\d{9}$/;
            return Reg.test(str);
        }
        $("#bind_mobile").blur(function () {
            var bind_mobile=$("#bind_mobile").val();
            if (bind_mobile===""){
                $(".pmunber").css("display","none");
            }
            else {
                var Reg=/^1[34578]\d{9}$/;
                var bind_mobile=$("#bind_mobile").val();
                if(Reg.test(bind_mobile)===true){
                    $(".pmunber").css("display","none");
                }
                else {
                    $(".pmunber").css("display","block");
                }
            }

        });
        //绑定
        $(".text").click(function () {
            var bind_realname=$("#bind_realname").val();
            if(bind_realname===""){
                $(".zhFont").css("display","block");
            }

            var bind_idnumber=$("#bind_idnumber").val();
            if(bind_idnumber===""){
                $(".idCard").css("display","block");
            }

            var bind_banknumber=$("#bind_banknumber").val();
            if(bind_banknumber===""){
                $(".moneyCard").css("display","block");
            }

            var bind_mobile=$("#bind_mobile").val();
            if(bind_mobile===""){
                $(".pmunber").css("display","block");
            }
            var Reg=/^1[34578]\d{9}$/;
            var bind_mobile=$("#bind_mobile").val();
            if(Reg.test(bind_mobile)===true){
                bind();
            }
        });
        //end

        //绑定
        function bind() {
            $.ajax({
                url:'{{route('s_user_bindBankCard')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    bind_realname:$("#bind_realname").val(),
                    bind_idnumber:$("#bind_idnumber").val(),
                    bind_banknumber:$("#bind_banknumber").val(),
                    bind_mobile:$("#bind_mobile").val(),
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        $('#bid').val(data['data']['ids']);

                        var bids=data['data']['ids'];
                        var banknumbs=data['data']['banknumbs'];

                        $('#banknumber').val(data['data']['banknumbs']);

                        var banknumber=$("#banknumber").val();//银行卡号
                        $("#cardNum").html(banknumbs);
                        var realname= $("#realname").val();//用户名
                        // $(".card_3").html(bids);


                        //获取手机号
                        // var mobilenumber= $("#mobilenumber").val();
                        // $(".G_pshos").html(mobilenumber);
                        //
                        //




                        $(".list2").hide();
                        $(".my_card").show();



                        //  把刚才获得的银行卡 姓名 显示要页面上
                    }else{
                        // alert(data['message']);
                    }
                }
            })
        }
        //解绑
        function unbind() {
            $.ajax({
                url:'{{route('s_user_unbindBankCard')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    bid:$("#bid").val(),
                    code : $('#unbind_code').val(),
                    // mobile:$("#unbind_mobile").val(),
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        //解绑成功
                        $('#bid').val();//删除信息
                        //清空 刚才添加成功的银行卡内容
                        $('#banknumber').val();
                        // alert('解绑成功');
                        $(".list2").show();
                        $(".p_code_jm").hide();
                    }else{
                        $(".J_cosy").show();
                        $(".J_cosy").html(data['message']);
                    }
                }
            })
        }

        //解绑发送手机验证码
        $(".user_runAs").click(function () {
            // var unbind_mobile=$("#unbind_mobile").val();
            // if (unbind_mobile===""){
            //     $(".J_pmunber").css("display","block");
            //     $(".J_pmunber").html("请输入手机号");
            // }
            // if (bankCard(unbind_mobile)){
            sendSms(1);
            var countdown=60;
            function sendemail1(){
                var obj = $(".user_runAs");
                settime(obj);
            }
            function settime(obj) { //发送验证码倒计时
                if (countdown == 0) {
                    obj.prop('disabled',false);
                    //obj.removeattr("disabled");
                    obj.text("重新获取验证码");
                    countdown = 60;
                    $('.user_runAs').css("backgroundColor","#ff7241");
                    $('.user_runAs').css("cursor","pointer");
                    return;
                } else {
                    obj.prop('disabled',true);
                    obj.text("(" + countdown + "s)重新发送");
                    countdown--;
                    $('.user_runAs').css("backgroundColor","#bebebe");
                    $('.user_runAs').css("cursor","auto");
                }
                setTimeout(function() {
                        settime(obj) }
                    ,1000)
            }
            sendemail1();
            $(".y_hiedJba").show();
            setTimeout('$(".y_hiedJba").hide()',60000);
            // }
            // else {
            //     alert("错误");
            // }
        });
        $("#unbind_code").blur(function () {
            var unbind_code=$("#unbind_code").val();
            if (unbind_code===""){
                $(".J_cosy").css("display","none");
            }
            else {
                var Reg=/[0-9]{4}/;
                var unbind_code=$("#unbind_code").val();
                if(Reg.test(unbind_code)===true){
                    $(".J_cosy").css("display","none");
                }
                else {
                    $(".J_cosy").css("display","block");
                }
            }
        });

        $("#run_bundj").click(function () {
            // var unbind_mobile=$("#unbind_mobile").val();
            // if(unbind_mobile===""){
            //     $(".J_pmunber").css("display","block");
            //     $(".J_pmunber").html("请输入手机号");
            // }
            // var Reg=/^1[34578]\d{9}$/;
            // var unbind_mobile=$("#unbind_mobile").val();
            // if(Reg.test(unbind_mobile)===true){
            //     $(".J_pmunber").css("display","none");
            unbind();
            // }
            // else {
            //     $(".J_pmunber").css("display","block");
            // }
            var unbind_code=$("#unbind_code").val();
            if (unbind_code===""){
                $(".J_cosy").show();
                $(".J_cosy").html("请获取验证码");
            }
            else {
                unbind();
            }

        });

        //发送手机验证码
        $(".G_runa").click(function () {
            // var change_mobile=$("#change_mobile").val();
            // if (change_mobile===""){
            //     $(".G_p").css("display","block");
            //     $(".G_p").html("请输入手机号");
            // }
            // else {
            //     var Reg=/^1[34578]\d{9}$/;
            //     var change_mobile=$("#change_mobile").val();
            //
            //     if(Reg.test(change_mobile)===true){
            //         $(".G_p").css("display","none");
            sendSms(2);
            var countdown=60;
            function sendemail2(){
                var obj = $(".G_runa");
                settime(obj);
            }
            function settime(obj) { //发送验证码倒计时
                if (countdown == 0) {
                    obj.prop('disabled',false);
                    //obj.removeattr("disabled");
                    obj.text("重新获取验证码");
                    countdown = 60;
                    $('.G_runa').css("backgroundColor","#ff7241");
                    $('.G_runa').css("cursor","pointer");
                    return;
                } else {
                    obj.prop('disabled',true);
                    obj.text("(" + countdown + "s)重新发送");
                    countdown--;
                    $('.G_runa').css("backgroundColor","#bebebe");
                    $('.G_runa').css("cursor","auto");
                }
                setTimeout(function() {
                        settime(obj) }
                    ,1000)
            }
            sendemail2();
            $(".y_hiedGhu").show();
            setTimeout('$(".y_hiedGhu").hide()',60000);
            // }
            // else {
            //     $(".G_p").css("display","block");
            // }
            // }
        });
        //更换验证码
        $("#change_code").blur(function () {
            var change_code=$("#change_code").val();
            if (change_code===""){
                $(".G_yans").css("display","none");
            }
            else {
                var Reg=/[0-9]{4}/;
                var change_code=$("#change_code").val();
                if(Reg.test(change_code)===true){
                    $(".G_yans").css("display","none");
                }
                else {
                    $(".G_yans").css("display","block");
                }
            }
        });
        //跟换银行卡 跟换手机号继续

        $("#run_bund").click(function () {
            var change_code =$("#change_code").val();
            if(change_code===""){
                $(".G_yans").show();
                $(".G_yans").html("请获取验证码");
            }
            else {
                change();


            }




        });


        //end
        function change() {
            //change_mobile  change_code
            $.ajax({
                url:'{{route('s_user_changeBankCard')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    bid:$("#bid").val(),
                    code : $('#change_code').val(),
                    // mobile:$("#change_mobile").val(),
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        $('#bid').val();//删除信息

                        $(".list2").show();
                        $(".p_code_jm").hide();
                        $(".p_code").hide();
                    }else{
                        // alert(data['message']);
                        $(".G_yans").show();
                        $(".G_yans").html(data['message']);
                    }
                }
            })
        }

        //sendSms
        function sendSms(type) {//
            $.ajax({
                url:'{{route('s_user_sendBankSms')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    // mobile:mobile,
                    type:type
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.status==200){

                    }
                    else {
                        // $(".G_yans").show();
                        // $(".G_yans").html(data);
                    }

                }
            })
        }

    });

    $(function () {
        @if($judge ==1)
            $(".wothdrawa_t").click();
//        alert(1);
        @endif
    });


    // var bid=$("#bid").val();
    //
    //
    // if(bid>0){
    //     $(".centent_T").hide();
    //     $(".centent_Ts").show();
    //     var banknumber=$("#banknumber").val();
    //     var  realname=$("#realname").val();
    //     $("#cardNum").html(bbanknumber);
    //     $(".user_id").html(realname+"你好");
    // }
    // else {
    //     $(".centent_T").show();
    //     $(".centent_Ts").hide();
    // }
    //
    // cardNum




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

    var banknumber=$("#banknumber").val();//银行卡号
    $("#cardNum").html(banknumber);
    var realname= $("#realname").val();//用户名
    $(".card_3").html(realname);
    //获取手机号
    var mobilenumber= $("#mobilenumber").val();
    $(".G_pshos").html(mobilenumber);



</script>
</body>
</html>
