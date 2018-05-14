@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
    <div id="accountInfo" >
        <div class="zmtdl_contactus">
            <div class="zmtdl_contactus_wrap">
            <h3>联系我们启动业务</h3>
            TEL:021-31836300<br><br>
            Mail：hi@pugongying.link
            </div>
        </div>
    </div>
@endsection



@section("script")
    <script type="text/javascript">
        $(function () {});

    </script>
@endsection

