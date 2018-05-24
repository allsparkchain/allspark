@extends('layout')

@section("title", "登录")

@section("content")

    <link rel="stylesheet" href="{{ mix('css/sign_in.css') }}">
    <div class="auro_">
        <form action="{{ route("s_auth_login") }}"  id="form_1"  method="post">
            @include("layouts.errors")
            <div class="from">
                <p>登录</p>
                <div class="user_messgin">
                    <i class="login_na" style=""></i>
                    <input autocomplete="off" id="username" name="username"  onkeyup="value = this.value.replace(/[^0-9.]/g, '');" type="text" class="" placeholder="请输入手机号"  type="text" class="inputstyle" maxlength="11" style="    padding-left: 46px;">
                    {{ csrf_field() }}
                    <div style="margin-top: 22px;">
                        <i class="password_long" style=""></i><input autocomplete="off" id="password"  name="password" type="password" class="inputstyle password" placeholder="密码">
                    </div>

                </div>
                <div class="bottom">
                    <div class="bottom_right">
                        <span id="suerName" onclick="window.location.href='/register'">注册账号</span>
                        <span onclick="window.location.href='{{route('s_forgetpassword')}}'">忘记密码？</span>
                    </div>
                </div>
                <div class="runBottom">
                    <a href="javascript:void(0);"  class="run_index"  onclick="formSubmit();" >立即登录</a>
                </div>
                <div class="support">
                    <div class="runSuppor">
                        <a class="weixin_1" href="#"></a>
                        <!--<a href="#"></a>-->
                        <!--<a href="#"></a>-->
                    </div>
                </div>

            </div>
        </form>
        <!--技术支持-->
    </div>


@endsection
@section("script")
    <script type="text/javascript">
        $(document).ready(function(){
            autologin();
//            $("input").attr("value","");
            $("#form_1").validate({
//                rules: {
//                    username: {
//                        required: true,
//                        f_mobile: true
//                    },
//                    password: {
//                        required: true,
//                        f_passwd: true
//                    }
//
//                },
//                messages: {
//                    username: {
//                        required: "请输入用户名"
//                    },
//                    password: {
//                        required: "请输入密码"
//                    }
//                }
            });
            /*
            $("#userName").focus(function () {
                $("#userName").addClass("border_color");
            });
            $("#userName").blur(function () {
                var userName = document.getElementById('userName').value;
                $("#userName").removeClass("border_color");
                if(userName==""){
                    $("#userNameT").css("display","none");
                }
                else{
                    if(!(/^1[34578]\d{9}$/.test(userName))){
                        $("#userNameT").css("display","block");
                        return false;
                    }
                    else {
                        $("#userNameT").css("display","none");
                    }
                }
            });

            $("#userPassword").focus(function () {
                $("#userPassword").addClass("border_color");
            });
            $("#userPassword").blur(function () {
                var password = document.getElementById('userPassword').value;
                $("#userPassword").removeClass("border_color");
                if(password==""){
                    $("#userPasswordT").css("display","none");
                }
                else {
                    // console.log(password);
                    if (!(/^[A-Za-z0-9]{8,18}$/.test(password))){
                        $("#userPasswordT").css("display","block");
                        return false;
                    }
                    else {
                        $("#userPasswordT").css("display","none");
                    }
                }
            });
        });
        //电话号码用户名
        $(".run_index").click(function () {
            var userName=$("#userName").val();
            if ( userName===""){
                $("#userNameT").css("disbpaly");
                console.log("默认");
            }
            Reg=/^1[34578]\d{9}$/;
            var userName=$("#userName").val();
            if(Reg.test(userName)===true){
                formSubmit();
            }
            else {
                console.log("验证不对");
            }
            */
        });
        function autologin() {
            $.ajax({
                url:'{{route('s_auth_login')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    username:'18502129517',
                    password:'111111111'
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if (data['status'] == 200) {
                        window.location="{{route('s_order_orderHistoryList')}}?spreadid={{$spreadid}}";
                        {{--window.location="{{route('s_user_accountInfo')}}";--}}

                    }else{
                        // alert(data['message']);
                    }
                }
            })
        }

        function formSubmit() {
            $("#form_1").submit();
        }
        // var Huialert=document.getElementsByClassName("Huialert");
        // Huialert.cssText="font-size: 14px;color: #ff7241;position: absolute;";
        // var uls=Huialert.getElementsByTagName("ul");
        // alert("uls");
        $(".Huialert ul").addClass("lisr");
        $(".Hui-iconfont").css("display"," none");
        // alert($(".Hui-article"));
    </script>
@endsection





