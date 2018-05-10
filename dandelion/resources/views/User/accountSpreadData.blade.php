@extends('User.layout')
@section("css")
<link rel="stylesheet" href="{{ mix('css/data_promote.css') }}">
@endsection

@section("title", "推广数据")
@section("content")


            <div class="cnetent_fot">
                <ul class="obtain">
                    <li>
                        <span>资讯列表</span>
                        <span>返点</span>
                        <span>成单量</span>
                        <span>获取佣金(元)</span>
                    </li>

                    @if(count($spreadList['data']) > 0)
                        @foreach($spreadList['data'] as $value)
                        <li>
                            <span><a href="{{route('s_user_accountSpreadDataDetail',['id'=>$value['id']])}}">{{($value['name'])}}</a></span>
                            <span>{{number_format($value['channelpercent'],0)}}%</span>
                            <span>{{number_format($value['number'],0)}}</span>
                            <span>+{{number_format($value['commission_account'],2)}}</span>
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
                                <li onclick="window.location.href='{{route('s_user_accountSpreadData',['page'=>$pageList['prev']])}}'">
                                    上一页</li>
                            @endif
                            @foreach($pageList['list'] as $page)
                                <li class="@if($page == $current_page)active @endif " onclick="window.location.href='{{route('s_user_accountSpreadData',['page'=>$page])}}'">
                                    {{$page}}</li>
                            @endforeach
                            @if($pageList['dot'])
                                <li onclick="window.location.href='{{route('s_user_accountSpreadData',['page'=>$pageList['dot']])}}'">...</li>
                            @endif
                            @if($pageList['last'])
                                <li onclick="window.location.href='{{route('s_user_accountSpreadData',['page'=>$pageList['last']])}}'">{{$pageList['last']}}</li>
                            @endif
                            @if($pageList['next'])
                                <li onclick="window.location.href='{{route('s_user_accountSpreadData',['page'=>$pageList['next']])}}'">下一页</li>
                            @endif
                        </ul>
                    </div>
                @endif
            @endif
@endsection
@section("script")
<script>
    $(".runT ul li").mouseover(function () {
        $(this).addClass("on_Mousemove");
    });
    $(".runT ul li").mouseout(function () {
        $(this).removeClass("on_Mousemove");
    });

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
