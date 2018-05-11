@extends('User.layout')

@section("title", "用户设置")
@section("css")
<link rel="stylesheet" href="{{ mix('css/infocenter.css') }}">
<link rel="stylesheet" href="{{ mix('css/morris.css') }}">
@endsection

@section("content")
    <div class="centent_right_A">
        <div class="centent_right_left Tfont">
            <span class="h2">累计金额</span>
            <p class="margin_top75 ">￥<span class="JEfont">{{$canWithDraw or 0.00}}</span></p>
            <span id="T_money" class="money" style="display: none">金额只能为数字</span>
            <p class="Jfont">未结算佣金：￥<span class="JEfonts">{{$not_settle_money or 0.00}}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;今日订单:<span
                        class="JEfonts">{{$today_nums}}</span>/单</p>
        </div>
        <div class="centent_T">
            <a href="{{route('s_user_accountWithdraw')}}">我要提现</a>
        </div>
    </div>
    <div class="centent_right_B">
        <div class=" centent_YJ">
            累计产生佣金&nbsp;&nbsp;&nbsp;<span class="j_q">￥</span><span class="JEfont">{{$all_commission_profit}}</span>
        </div>
        <div class="centnt_right_TB" style="">
            <div id="myfirstchart" style="height: 440px;"></div>
        </div>
    </div>
@endsection
@section("script")
    <script src="/js/vendor/raphael-min.js" type="text/javascript"></script>
    <script src="/js/vendor/morris.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(function () {

            $(".wothdrawa_o").click(function () {
                $(".wothdrawa_o").addClass("active1");
                $(".wothdrawa_t").removeClass("active2");
                $(".list1").show();
                $(".list2").hide();
            });
            $(".wothdrawa_t").click(function () {
                $(".wothdrawa_o").removeClass("active1");
                $(".wothdrawa_t").addClass("active2");
                $(".list2").show();
                $(".list1").hide();
            });

            $('.centent_left_title').find('ul').find('li').each(function(){
                $(this).removeClass();
                $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
                if($(this).attr('lang') == 'accountInfo'){
                    $(this).addClass('active');
                    $(this).find('a').addClass('activeA1');
                }
            });
        });

        @if(@$list != -1)
        new Morris.Line({
            element: 'myfirstchart',
            data: {!!$list!!},
            xkey: 'new_time',
            ykeys: ['account'],
            labels: ['money'],
            lineColors:['#ff7241'],
            parseTime: false
        });

        @endif
//        new Morris.Line({
//            element: 'myfirstchart',
//            data: [
//                {day: "2016.07.10", value: 120},
//                {day: "2016.07.11", value: 10},
//                {day: "2016.07.12", value: 5},
//                {day: "2016.07.13", value: 50},
//                {day: "2016.07.14", value: 20},
//                {day: "2016.07.15", value: 20},
//                {day: "2016.07.16", value: 20}
//            ],
//            // The name of the data record attribute that contains x-values.
//            xkey: 'day',
//            // A list of names of data record attributes that contain y-values.
//            ykeys: ['value'],
//            // Labels for the ykeys -- will be displayed when you hover over the
//            // chart.
//            labels: ['money'],
//            lineColors:['#ff7241'],
//            parseTime: false　　//注意
//        });

//
//
//        //ajax例子：
//         function zhou() {
//             if ($("#myfirstchart").text() == "") {
//                 $.post("newsCountAjax.aspx", {type: 0}, function (datas) {
//                     var dataObj = eval("(" + datas + ")");//转换为json对象
//                     //1周
//                     new Morris.Line({
//                         element: 'myfirstchart',
//                         data: dataObj,
//                         xkey: 'day',
//                         ykeys: ['value'],
//                         labels: ['money'],
//                         parseTime: false
//                     });
//                 });
//             }
//         }
        // zhou();
    </script>
@endsection
