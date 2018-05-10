@extends('User.layout')

@section("title", "佣金明细")

@section("css")
<link rel="stylesheet" href="{{ mix('css/commission_subsidiary.css') }}">
<script src="/js/laydate/laydate.js"></script>
@endsection

@section("content")
    <div class="centent_top">
        <ul>
            <li><a href="javascript:;" class="activeTitle title_one">今日佣金查询</a></li>
            <li><a href="javascript:;" class="title_two">时间段查询</a> </li>
        </ul>
    </div>
    <div class="wrop_c_one">
        <div class="centent_bottom">
            <table class="centent_table" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td>时间</td>
                    <td>资讯</td>
                    <td>佣金来源</td>
                    <td>分成</td>
                    <td>订单量</td>
                    <td>佣金</td>
                </tr>
            @if(count($flowList) > 0)
                @foreach($flowList as $k=>$v)
                    <tr>
                        <td>{{$v['day']}}</td>
                        <td><div class="user_fonts"><span>{{$v['name']}}</span></div></td>
                        <td>好友</td>
                        <td>{{number_format($v['channelpercent'],2)}}%</td>
                        <td>{{number_format($v['number'],0)}}</td>
                        <td>{{number_format($v['account'],2)}}</td>
                    </tr>
                @endforeach
            @endif
            </table>

            @if(count($flowList) > 0)
                @if($pageList)
                    <div class="runT">
                        @if(isset($pageList['first']) && $pageList['first'])
                            <span class="fsle_one" onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['first']])}}'">首页</span>
                        @endif
                        {{--<li><a href="{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['first']])}}">首页</a></li>--}}
                        <ul>
                            @if($pageList['prev'])
                                <li onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['prev']])}}'">
                                    上一页</li>
                            @endif

                            @foreach($pageList['list'] as $page)

                                <li class="@if($page == $current_page)active @endif " onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page'=>$page])}}'">
                                    {{$page}}</li>
                            @endforeach

                            @if($pageList['dot'])
                                <li onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['dot']])}}'">
                                    ...</li>
                            @endif
                            @if($pageList['last'])
                                <li onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['last']])}}'">
                                    {{$pageList['last']}}</li>
                            @endif
                            @if($pageList['next'])
                                <li onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['next']])}}'">
                                    下一页</li>
                            @endif
                        </ul>
                        @if(isset($pageList['last']) && $pageList['last'])
                            <span class="fsle_two" onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['last']])}}'">末尾</span>
                        @endif

                    </div>
                @endif
            @endif


        </div>
    </div>
    <div class="wrop_c_two">
        <div class="hide_top">
            <div class="centent_wpsa">
                <ul>
                    <li><input type="text" class="inputSe abcd" id="test1" value="@if(strlen($startDay)>0) {{$startDay}} @else {{date('Y-m-d')}} @endif" readonly="readonly" ><i class="tine_icon_frist"></i></li>
                    <li><input  type="text" class="inputSe a"  id="test2" value="@if(strlen($endDay)>0) {{$endDay}} @else {{date('Y-m-d',strtotime('+1 day'))}} @endif" readonly="readonly" ><i class="tine_icon_frist"></i></li>
                </ul>
                <input type="button" value="提交" class="centent_wpas_button">
            </div>
            <div class="centent_wpsa_middle">
                @if(count($flowList2) > 0)
                    <table class="centent_table" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>时间</td>
                            <td>资讯</td>
                            <td>佣金来源</td>
                            <td>分成</td>
                            <td>订单量</td>
                            <td>佣金</td>
                        </tr>
                        @foreach($flowList2 as $k=>$v)
                            <tr>
                                <td>{{$v['day']}}</td>
                                <td><div class="user_fonts"><span>{{$v['name']}}</span></div></td>
                                <td>好友</td>
                                <td>{{number_format($v['channelpercent'],2)}}%</td>
                                <td>{{number_format($v['number'],0)}}</td>
                                <td>{{number_format($v['account'],0)}}</td>
                            </tr>
                        @endforeach

                    </table>
                    @if($pageList2)
                        <div class="runT">
                            @if(isset($pageList2['first']))
                                <span class="fsle_one" onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page2'=>$pageList2['first'],'startDay'=>$startDay,'endDay'=>$endDay])}}'" >首页</span>
                            @endif

                            {{--<li><a href="{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['first']])}}">首页</a></li>--}}
                            <ul>
                                @if($pageList2['prev'])
                                    <li onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page2'=>$pageList2['prev'],'startDay'=>$startDay,'endDay'=>$endDay])}}'">
                                        上一页</li>
                                @endif

                                @foreach($pageList2['list'] as $page)
                                    <li class="@if($page == $current_page2)active @endif " onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page2'=>$page,'startDay'=>$startDay,'endDay'=>$endDay])}}'">
                                        {{$page}}</li>
                                @endforeach

                                @if($pageList2['dot'])
                                    <li onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page2'=>$pageList2['dot'],'startDay'=>$startDay,'endDay'=>$endDay])}}'">
                                        ...</li>
                                @endif
                                @if($pageList2['last'])
                                    <li onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page2'=>$pageList2['last'],'startDay'=>$startDay,'endDay'=>$endDay])}}'">
                                        {{$pageList2['last']}}</li>
                                @endif
                                @if($pageList2['next'])
                                    <li onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page2'=>$pageList2['next'],'startDay'=>$startDay,'endDay'=>$endDay])}}'">
                                        下一页</li>
                                @endif
                            </ul>
                            @if(isset($pageList2['last']) && $pageList2['last'])
                                <span class="fsle_two" onclick="window.location.href='{{route('s_user_accountCommissionSettlementDetail',['page2'=>$pageList2['last'],'startDay'=>$startDay,'endDay'=>$endDay])}}'" >末尾</span>
                            @endif
                            {{--<li><a href="{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['last']])}}">末页</a></li>--}}
                        </div>
                    @endif


                @endif

            </div>
        </div>
    </div>
@endsection
@section("script")
    <script type="text/javascript">
        var begin = '';
        var end = '';
        $(function () {
            $('.wrop_c_two').hide();
            $('.centent_left_title').find('ul').find('li').each(function(){
                $(this).removeClass();
                $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
                if($(this).attr('lang') == 'accountCommissionSettlementDetail'){
                    $(this).addClass('active');
                    $(this).find('a').addClass('activeA3');
                }
            });

            $(".title_one").click(function () {
                $(".wrop_c_one").show();
                $(".title_one").addClass("activeTitle");
                $(".wrop_c_two").hide();
                $(".title_two").removeClass("activeTitle");
            });
            $(".title_two").click(function () {
                $(".wrop_c_two").show();
                $(".title_two").addClass("activeTitle");
                $(".wrop_c_one").hide();
                $(".title_one").removeClass("activeTitle");
            });

            $(".centent_wpas_button").click(function () {
                begin = $('#test1').val();
                end = $('#test2').val();
                //alert(begin+'   '+end);
                location.href='{{route('s_user_accountCommissionSettlementDetail')}}?startDay='+begin+'&endDay='+end
            });
        });
        //执行一个laydate实例
        laydate.render({
            elem: '#test1'
            ,format: 'yyyy-MM-dd'
        });
        laydate.render({
            elem: '#test2'
            ,format: 'yyyy-MM-dd'
        });

        function getDataO() {
            $.ajax({
                url:'{{route('s_user_getAccountCommissionSettlementDetail')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
//                    today:1,//1当天的数据，-1不是当天的数据
                    page:1,//当前页数
                    pagesize:10,//每页条数
//                    startDay:1//2018-1-30 日历控件 左侧
//                    endDay: 1,//2018-1-31 日历控件 右侧

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
                        console.log(data);
                    }
                }
            })
        }

    </script>

@endsection


