 @extends('layout')

@section("title", "设置密码")

@section("content")
 @section("css")
<link rel="stylesheet" href="{{ mix('css/set_password.css') }}">
<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
 @endsection
        <p class="title_t" >我要注册</p>
        @include('layouts.errors')
        <form action="{{ route("s_register") }}"  id="form_1" method="post">
            {{ csrf_field() }}
            <div class="content_from">
                <p class="from_span">创建登录密码</p>
                <div class="user_messgin">
                    <div class="mgint_T">
                        <i class="password_long"></i>
                        <input id="passwd" type="password" name="passwd" class="inputstyle" placeholder="输入你的密码" maxlength="20">
                        <span id="userNameT" style="display: none ">密码必须为8-18位，不能有特殊符号</span>
                    </div>
                    <div class="mgint_T">
                        <i class="password_long"></i>
                        <input id="passwd1" type="password" name="passwd_confirmation" class="inputstyle password" placeholder="再次输入密码" maxlength="20">
                        <span id="userPasswordT" style="display: none;">两次输入的密码不一样</span>
                    </div>

                    <p id="userPasswordT" class="user_title">密码必须8位至20位，由英文与数字组成</p>
                </div>
            </div>
        </form>
        <div class="runBottom">
            <a href="javascript:void(0);"  id="run_a">注&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;册</a>
        </div>

    @endsection
    @section("script")

        <script type="text/javascript">
            $("#passwd").focus(function () {
                $("#passwd").addClass("border_color");
            });
            $("#passwd").blur(function () {
                $("#passwd").removeClass("border_color");
                var password = document.getElementById('passwd').value;
                // console.log(password);
                if (!(/^[A-Za-z0-9]{8,18}$/.test(password))){
                    $("#userNameT").css("display","block");
                    return false;
                }
                else {
                    $("#userNameT").css("display","none");//正确
                    // $("#userNameT").html("请输入正确的密码");

                }
            });
            //二次输入密码
            $("#passwd1").focus(function () {
                $("#userPasswordT").addClass("border_color");
            });
            $("#passwd1").blur(function () {
                $("#userPasswordT").removeClass("border_color");
                var password = document.getElementById('passwd').value;
                var password1 = document.getElementById('passwd1').value;
                if (password===password1){
                    $("#userPasswordT").css("display","none");//正确
                }
                else {
                    $("#userPasswordT").css("display","block");//正确
                }
                // console.log(password);
            });

            $("#run_a").click(function () {
                var passwd=$("#passwd").val();
                if(passwd===""){
                    $("#userNameT").show();
                    $("#userNameT").html("请输入密码");
                }
                else {
                    $("#userNameT").hide();
                    var Reg=/^[A-Za-z0-9]{8,20}$/;
                    if (Reg.test(passwd)){
                        // var passwd1=$("#passwd1").val();
                        // if(password1===""){
                        //     $("#userPasswordT").show();
                        //     $("#userPasswordT").html("请输入密码");
                        // }
                        // else {
                            register();
                        // }
                    }
                    else {
                        $("#userNameT").show();
                        $("#userNameT").html("请输入您密码");
                    }

                }
                // var password = document.getElementById('passwd').value;
                // var password1 = document.getElementById('passwd1').value;
                // if (password==""){
                //     return false;
                // }
                // $("#passwd").removeClass("border_color");
                // var password = document.getElementById('passwd').value;
                // var Reg=/^[A-Za-z0-9]{8,18}$/;
                // if (Reg.test(password)){
                //     if(password1===password){
                //
                //     }
                //     else {
                //         $("#userPasswordT").show();
                //
                //     }
                //
                // }
                // else {
                //     $("#userNameT").css("display","block");
                // }
            });


            function register() {
                $("#form_1").submit();
            }

        </script>

    @endsection




<script type="text/javascript">

</script>
