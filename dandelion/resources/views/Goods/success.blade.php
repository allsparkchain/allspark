@extends('layout')

@section("title", "首页")


@section("css")
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
    <link rel="stylesheet" href="{{ mix('css/swiper.min.css') }}">
@endsection

@section("content")
<!--内容区域-->
<div class="pgy_commodity_detail" style="height: 836px;">
    <div class="pgy_1200">
        <div class="pgy_commodity_success">
            <img src="/image/pgy_fa_success.png" alt="">
            <p class="pgySuccess">发布成功</p>
            <p class="pgyLook">您可前往个人中心查看<a href="/User/articleList?status_id=22">我的内容</a>跟踪审核进度</p>
        </div>


    </div>
</div>
@endsection
@section("script")

@endsection
