@extends('User.layout')

@section("title", "用户设置")
<link rel="stylesheet" href="{{ mix('css/infocenter.css') }}">
<link rel="stylesheet" href="{{ mix('css/morris.css') }}">
@section("content")
    <div class="centent_right_A">
        <div class="centent_right_left Tfont">
            <span class="h2">可提现金额</span>
            <p class="margin_top75 ">￥<span class="JEfont">{{$canWithDraw or 0.00}}</span></p>
            <span id="T_money" class="money" style="display: none">金额只能为数字</span>
            <p class="Jfont">未结算佣金：￥<span class="JEfonts">{{$not_settle_money or 0.00}}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;今日订单:<span
                        class="JEfonts">{{$today_nums}}</span>/单</p>
        </div>
        <div class="centent_T">
            <a href="#">我要提现</a>
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
                if($(this).attr('lang') == 'accountInfo'){
                    $(this).removeClass().addClass('activeLi');
                    $(this).find('a').removeClass('activeA2').addClass('activeA2');
                }else{
                    $(this).removeClass();
                    $(this).find('a').removeClass('activeA2');
                }

            })

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


        $(function() {
            /**
             * 通用
             * 购物车加一减一
             * data-min为最小值，data-max为最大值，data-step为步长（默认为1，或不设置，步长即为每操作一下“加”或“减”的数值）
             * 不加data-min和data-max则无限制
             * HTML：
             *      <p class="cart-number-box">
             *            <input type="text" value="1" name="number" data-min="1" data-max="5" data-step="2">
             *            <i class="up input-num-up">+</i>
             *            <i class="down input-num-down">-</i>
             *        </p>
             * CSS：
             *      .cart-number-box { position: relative; }
             *        .cart-number-box input { width: 60px; height: 27px; margin-left: 26px; text-align: center; }
             *        .cart-number-box input,
             *        .cart-number-box .up,
             *        .cart-number-box .down { border: 1px solid #aaa; }
             *        .cart-number-box .up,
             *        .cart-number-box .down { position: absolute; display: block; width: 27px; height: 27px; top: 0; text-align: center; line-height: 23px; font-style: normal; cursor: pointer; }
             *        .cart-number-box .up { left: 85px; }
             *      .disabled { cursor: not-allowed; filter: alpha(opacity=65); -webkit-box-shadow: none; box-shadow: none; opacity: .65 }
             * DATE:2015.8.3
             */
            $('.input-num-up').click(function(){
                upDownOperation( $(this) );
            });
            $('.input-num-down').click(function(){
                upDownOperation( $(this) );
            });
            function upDownOperation(element)
            {
                var _input = element.parent().find('input'),
                    _value = _input.val(),
                    _step = _input.attr('data-step') || 1;
                //检测当前操作的元素是否有disabled，有则去除
                element.hasClass('disabled') && element.removeClass('disabled');
                //检测当前操作的元素是否是操作的添加按钮（.input-num-up）‘是’ 则为加操作，‘否’ 则为减操作
                if ( element.hasClass('input-num-up') )
                {
                    var _new_value = parseInt( parseFloat(_value) + parseFloat(_step) ),
                        _max = _input.attr('data-max') || false,
                        _down = element.parent().find('.input-num-down');

                    //若执行‘加’操作且‘减’按钮存在class='disabled'的话，则移除‘减’操作按钮的class 'disabled'
                    _down.hasClass('disabled') && _down.removeClass('disabled');
                    if (_max && _new_value >= _max) {
                        _new_value = _max;
                        element.addClass('disabled');
                    }
                } else {
                    var _new_value = parseInt( parseFloat(_value) - parseFloat(_step) ),
                        _min = _input.attr('data-min') || false,
                        _up = element.parent().find('.input-num-up');
                    //若执行‘减’操作且‘加’按钮存在class='disabled'的话，则移除‘加’操作按钮的class 'disabled'
                    _up.hasClass('disabled') && _up.removeClass('disabled');
                    if (_min && _new_value <= _min) {
                        _new_value = _min;
                        element.addClass('disabled');
                    }
                }
                _input.val( _new_value );
            }
        });
    </script>
@endsection
