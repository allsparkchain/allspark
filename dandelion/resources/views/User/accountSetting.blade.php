
@extends('User.layout')

@section("title", "用户设置")
@section("css")
<link rel="stylesheet" href="{{ mix('css/account_settings.css') }}">
@endsection

@section("content")
    <div class="centent_right_bg">
        <div class="tab_switch">
            <ul>
                <li class="wothdrawa_o active1">修改密码</li>
                <li class="wothdrawa_t ">我的银行卡</li>
            </ul>
        </div>
        <div class="list1" >
            <div class="list1_centent">
                <p class="list1_ps_title"> 请输入原始密码</p>
                <div class="user_margin" style="margin-top: 12px">
                    <i class="password_longs"></i>
                    <input class="password" id="oldpas" type="password" placeholder="请输入原始密码">
                </div>

                <span class="colors_font password_as" style="display: none">密码不对</span>
                <p class="new_title"> 请重新输入密码</p>
                <div class="user_margin">
                    <i class="password_longs"></i>
                    <input class="password" id="newpas" type="password" placeholder="请输入新密码">
                </div>
                <span class="colors_font password_new" style="display: none">密码格式不对</span>
                <div class="user_margin">
                    <i class="password_longs"></i>
                    <input class="password" id="newpas2" type="password" placeholder="再次输入密码">
                </div>

                <span class="colors_font password_newT" style="display: none">两次输入密码不一致</span>
                <p class="password_G">密码必须8位至16位，由英文与数字组成</p>
                <div class="button-wrapper">
                    <a href="javascript:;"  class="texts">修改密码</a>
                </div>

            </div>
        </div>
        <div class="list2"  style="display: none">
            <div class="mg_b">
                <!--<div class="t_prompt">提示：你可以将结算余额提现绑定的这张银行卡。</div>-->
            </div>
            <!--绑定银行卡-->
            <div class="list2_centent">
                <p class="list2_ps_title no_m"> 请输入你的真实姓名</p>
                <div class="user_margin">
                    <i class="login_nas"></i>
                    <input class="password"  id="bind_realname" placeholder="姓名">
                </div>
                <span class="colors_font zhFont" style="display: none">密码只能是中文</span>
                <p class="list2_ps_title"> 请输入你的身份证</p>
                <div class="user_margin">
                    <input class="password2" id="bind_idnumber"  placeholder="身份证号码" maxlength="18">
                </div>

                <span class="colors_font idCard" style="display: none">省份证号有误</span>

                <p class="list2_ps_title"> 请输入银行卡号</p>
                <div class="user_margin">
                    <input class="password3" id="bind_banknumber"  placeholder="身银行卡号" maxlength="19">
                </div>
                <span class="colors_font moneyCard" style="display: none">银行卡有误</span>
                <p class="list2_ps_title">请输入银行预留手机号</p>
                <div class="user_margin">
                    <input class="password4"  id="bind_mobile" placeholder="手机号" maxlength="11">
                </div>

                <span class="colors_font pmunber" style="display: none">手机号有误</span>
                <div class="button-wrapper mg_t">
                    <a href="javascript:void(0);"  class="text">绑&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;定</a>
                </div>
                <input type="hidden" id="bid" value="{{$bid or 0}}">
                <input id="banknumber"  type="hidden" value="{{$banknumber or 0}}">
            </div>

        </div>


        <!--绑定成功，已有绑定的银行卡-->
        <div class="my_card" style="display: none">

            <div class="card_im">
                <div class="W_card" style="">
                    <span class="card_1" style="">工商银行卡</span>
                    <span class="card_2" style="">4444444444444444444</span>
                    <span class="user_id card_3" style="">持卡人：<span>夏*</span></span>
                </div>

            </div>

            <div class="card_font">

                <span id="unbundling">解绑银行卡</span>

                <span id="replace_card">更换银行卡</span>

            </div>

        </div>
        <input id="mobilenumber"  type="hidden" value="{{$mobile or -1}}">
        <!---解绑成功提示 show-->
        <div class="bind" style="display: none">
            你的银行卡解绑成功
        </div>

        <!---点击更换银行卡 输入验证码show-->
        <div class="p_code" style="display: none">
            <!--<div  class="t_shi">提示：需要进行手机短信验证</div>-->
            <div>
                <p class="p_password no_m">请输入你的手机号码</p>

                <span class="G_pshos" style="display: block;width: 100%;margin-top: 22px;" ></span>
                <span class="colors_font G_p" style="display: none">请输入手机号</span>
                <div class="mgint_T">
                    <i class="yancode"></i>
                    <input id="change_code" type="text" class="inputstyle " placeholder="输入验证码" maxlength="4">
                    <a class="user_runA G_runa" href="javascript:void(0);"  >发送验证码</a>
                </div>

                <span class="colors_font G_yans" style="display: none">验证码有误</span>



                <div class="y_hiedGhu" style="display: none;"></div>
                <div class="runBottom">

                    <a id="run_bund" href="javascript:;"  >继&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;续</a>

                </div>

            </div>

        </div>


        <!---点击解绑 发送验证码show-->
        <div class="p_code_jm " style="display: none">
            <!--<div  class="t_shi">提示：需要进行手机短信验证</div>-->
            <div>
                <p class="p_password no_m">请输入你的手机号码</p>
                {{--<input class="pt_h" id="unbind_mobile" placeholder="输入手机号">--}}
                <span class="J_pshos" style="display: block;width: 100%;margin-top: 22px;" ></span>
                <span class="colors_font J_pmunber" style="display: none">手机号格式不对</span>
                <div class="mgint_T">
                    <i class="yancode"></i>
                    <input type="text" id="unbind_code" class="inputstyle " placeholder="输入验证码" maxlength="4">
                    <a class="user_runA user_runAs" href="javascript:;"  >发送验证码</a>
                </div>
                <span class="colors_font J_cosy" style="display: none">验证码有误</span>
                <div class="y_hiedJba" style="display: none;"></div>
                <div class="runBottom">
                    <a id="run_bundj" href="javascript:;" >继&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;续</a>
                </div>
            </div>
        </div>
    </div>


@endsection

@section("script")
    <script type="text/javascript">
        var judge={{$judge}};
        if( judge>0){
            $(".list1").hide();
            $(".list2").show();
            $(".wothdrawa_o").removeClass("active1");
            $(".wothdrawa_t ").addClass("active1");
        }
        else {
            $(".list2").hide();
            $(".list1").show();
            $(".wothdrawa_t ").removeClass("active1");
            $(".wothdrawa_o").addClass("active1");
        }


        {{--更换手机号--}}
                $(".G_pshos").html($(".runSign_mes_d").html());
        //解绑银行卡
        $(".J_pshos").html($(".runSign_mes_d").html());

        $(".user_id").html($(".runSign_mes_d").html());


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
            var bind_banknumber=parseInt($("#bind_banknumber").val());
            if (bind_banknumber===""){
                $(".idCard").css("display","none");
            }
            else {
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


        //解绑银行卡
        //手机验证
        $("#unbind_mobile").blur(function () {
            var unbind_mobile=$("#unbind_mobile").val();
            if (unbind_mobile===""){
                $(".J_pmunber").css("display","none");
            }
            else {
                var Reg=/^1[34578]\d{9}$/;
                var unbind_mobile=$("#unbind_mobile").val();
                if(Reg.test(unbind_mobile)===true){
                    $(".J_pmunber").css("display","none");
                }
                else {
                    $(".J_pmunber").css("display","block");
                }
            }
        });
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
        //解绑银行卡完成

        //更换银行卡
        //手机验证
        $("#change_mobile").blur(function () {
            var change_mobile=$("#change_mobile").val();
            if (change_mobile===""){
                $(".G_p").css("display","none");
            }
            else {
                var Reg=/^1[34578]\d{9}$/;
                var change_mobile=$("#change_mobile").val();

                if(Reg.test(change_mobile)===true){
                    $(".G_p").css("display","none");
                }
                else {
                    $(".G_p").css("display","block");
                }
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





        //手机倒计时








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

                        $('#banknumber').val(data['data']['banknumbs']);

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
        //change
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

        //更换验证码点击发送
        function changeFsend() {
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
            var mobile;
            // if(type == 1){
            //     mobile = $("#unbind_mobile").val()
            // }
            // if(type == 2){
            //     mobile = $("#change_mobile").val()
            // }
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

            $(".wothdrawa_o").click(function () {
                $(".wothdrawa_o").addClass("active1");
                $(".wothdrawa_t").removeClass("active2");
                $(".wothdrawa_t").removeClass("active1");
                $(".list1").show();
                $(".list2").hide();
                $(".bind").hide();
                $(".my_card").hide();
                $(".p_code").hide();
                $(".p_code_jm").hide();
            });

            $(".wothdrawa_t").click(function () {
                var bid=document.getElementById("bid").value;
                if (bid>0){
                    $(".my_card").show();
                    $(".list2").hide();
                    $(".wothdrawa_o").removeClass("active1");
                    $(".wothdrawa_t").addClass("active2");
                    // $(".list2").show();
                    $(".list1").hide();
                    $(".bind").hide();
                    // $(".my_card").hide();
                    $(".p_code").hide();
                    $(".p_code_jm").hide();
                }
                else {
                    $(".list2").show();
                    $(".wothdrawa_o").removeClass("active1");
                    $(".wothdrawa_t").addClass("active2");
                    // $(".list2").show();
                    $(".list1").hide();
                    $(".bind").hide();
                    $(".my_card").hide();
                    $(".p_code").hide();

                    $(".p_code_jm").hide();
                    $(".p_code").hide();
                }

            });

            //绑定成功/显示绑定的卡
            $(".text").click(function () {
//                $(".list2").hide();
//                $(".my_card").show();
                $(".p_code").hide();
            });
            $("#unbundling").click(function () {
                $(".my_card").hide();
                $(".p_code_jm").show();
                $(".p_code").hide();
            });
            $("#replace_card").click(function () {
                $(".my_card").hide();
                $(".p_code").show();
                // $(".p_code").hide();
            });

        $(function(){
            $('.centent_left_title').find('ul').find('li').each(function(){
                $(this).removeClass();
                $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
                if($(this).attr('lang') == 'accountSetting'){
                    $(this).addClass('active');
                    $(this).find('a').addClass('activeA6');
                }
            });
        });
    </script>

@endsection

