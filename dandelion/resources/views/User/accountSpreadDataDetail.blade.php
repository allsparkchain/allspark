@extends('User.layout')
@section("title", "推广数据详情")
@section("css")
<link rel="stylesheet" href="{{ mix('css/data_promote_details.css') }}">
@endsection
@section("content")

            <div class="centent_right_top">
                <div class="centent_right_topFlex">
                    <p>{{$spreadList['name']}}&nbsp &nbsp &nbsp</p>
                    <span onclick="returnUp()">返回</span>
                </div>
                <div class="flexTu">
                    <div class="flexTu_J">
                        <p>可提金额</p>
                        <span class="flexTu_Jy">￥<span class="flexTu_JBig">{{number_format($spreadList['sum_account'],2)}}</span></span>
                    </div>
                    <div class="flexTu_B">
                        <p>{{number_format($spreadList['channelpercent'],0)}}%</p>
                        <span>返点率</span>
                    </div>
                    <div class="flexTu_sum">
                        <p>{{number_format($spreadList['sum_count'],0)}}</p>
                        <span>累计成单</span>
                    </div>
                </div>
            </div>
            <div class="colors_b">
                <div class="cnetent_fot">
                    <ul class="obtain">
                        <li>
                            <span>时间</span>
                            <span>成单量</span>
                            <span>当日佣金</span>
                        </li>
                        @if(count($spreadList['data']) > 0)
                            @foreach($spreadList['data'] as $value)
                            <li>
                                <span>{{date('Y.m.d',strtotime($value['add_time']))}}</span>
                                <span>{{($value['number'])}}</span>
                                <span><span class="obtain_r">+{{number_format($value['account'],2)}}</span></span>
                            </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
                @if(count($spreadList['data']) > 0)
                    @if($pageList)
                        <div class="runT">
                            <ul>
                                @if($pageList['prev'])
                                    <li onclick="window.location.href='{{route('s_user_accountSpreadDataDetail',['page'=>$pageList['prev']])}}'">
                                        上一页</li>
                                @endif
                                @foreach($pageList['list'] as $page)
                                    <li class="@if($page == $current_page)active @endif " onclick="window.location.href='{{route('s_user_accountSpreadDataDetail',['page'=>$page])}}'">
                                        {{$page}}</li>
                                @endforeach
                                @if($pageList['dot'])
                                    <li onclick="window.location.href='{{route('s_user_accountSpreadDataDetail',['page'=>$pageList['dot']])}}'">...</li>
                                @endif
                                @if($pageList['last'])
                                    <li onclick="window.location.href='{{route('s_user_accountSpreadDataDetail',['page'=>$pageList['last']])}}'">
                                        {{$pageList['last']}}</li>
                                @endif
                                @if($pageList['next'])
                                    <li onclick="window.location.href='{{route('s_user_accountSpreadDataDetail',['page'=>$pageList['next']])}}'">下一页</li>
                                @endif
                            </ul>
                        </div>
                    @endif
                @endif
            </div>
@endsection
@section("script")
<script>
    $(".runT ul li").mouseover(function () {
        $(this).addClass("on_Mousemove");
    });
    $(".runT ul li").mouseout(function () {
        $(this).removeClass("on_Mousemove");
    });



    function returnUp(){
        window.location.href="{{route('s_user_accountSpreadData')}}";
    }
    $(function(){
        $('.centent_left_title').find('ul').find('li').each(function(){
            $(this).removeClass();
            $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
            if($(this).attr('lang') == 'accountSpreadData'){
                $(this).addClass('active');
                $(this).find('a').addClass('activeA4');
            }
        });
    })

</script>
@endsection
