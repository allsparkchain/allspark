@extends('Register.layout')


@section("title", "注册成功")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
    <div class="pgy_sendContent">
        <div class="pgy_1200">
            <div class="pgy_content_from">
                <div class="pgy_successfully">
                    <div class="pgy_successfullyFdr">
                        <p>恭喜您注册成功</p>
                    </div>

                </div>
            </div>
            <div class="pgy_runBottom">
                <a class="pgy_sevew" href="/">返回首页</a>
                <a href="{{Route('s_user_accountInfo')}}" style="margin-left: 42px">前往个人中心</a>
            </div>

        </div>
    </div>
@endsection

@section("script")

    <script type="text/javascript">


    </script>
@endsection


