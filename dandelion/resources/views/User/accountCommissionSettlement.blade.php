@extends('User.layout')

@section("title", "佣金结算")

@section("css")
<link rel="stylesheet" href="{{ mix('css/commission_settlement.css') }}">
@endsection

@section("content")
    <!--<div style="clear: both"></div>-->
            <div class="centent_right_A">
                <div class="centent_right_left Tfont">
                    <p class="Y_w">未结算</p>
                    <p class="Y_sum">佣金总额</p>
                    <span class="Z_c">￥<span class="Q_b">{{$not_settle_money or 0.00}}</span></span>
                    <p class="J_month">每月15号结算</p>
                </div>
                <div class="centent_TA">
                    <p class="TA_w TA_wRed">上月未结算佣金</p>
                    <span class="J_string">￥<span class="J_settlement">{{$unsettled_amount_last_month or 0.00}}</span></span>
                </div>
                <div class="centent_TB">
                    <p class="TA_w TA_wBlue">本月未结算佣金</p>
                    <span class="J_string">￥<span class="J_settlement">{{$unsettled_amount_month or 0.00}}</span></span>
                </div>
            </div>
            <div class="centent_right_B">
                <div class="centent_right_q">
                    <ul>
                        <li class="  wothdrawal active1">上月的未结算</li>
                        <li class=" Run_water">本月的未结算</li>
                        <li class=" Run_sum">历史结算</li>
                    </ul>
                </div>
                <div class="cnetent_right_1">
                    <div class="conetent_oneW">
                        <div class="bg_wh"></div>
                        <div class="th_cent">
                            <span>时间</span>
                            <span>佣金</span>
                        </div>
                        <ul class="one_ulCentent">
                        </ul>
                    </div>

                    <div class="runT">
                        <span>上一页</span>
                        <ul class="one_page">
                        </ul>
                        <span>下一页</span>
                    </div>

                </div>
                <div class="cnetent_right_2" style="display: none">
                    <div class="conetent_oneT">
                        <div class="bg_wh"></div>
                        <div class="th_cent">
                            <span>时间</span>
                            <span>佣金</span>
                        </div>
                        <ul class="twe_ulCentent">
                        </ul>
                    </div>
                    <div class="run_twe">
                        <span>上一页</span>
                        <ul class="twe_page">

                        </ul>
                        <span>下一页</span>
                    </div>
                </div>
                <div class="cnetent_right_3" style="display: none">
                    <div class="conetent_oneS">
                        <div class="bg_wh"></div>
                        <div class="th_cent">
                            <span>时间</span>
                            <span>佣金</span>
                        </div>
                        <ul class="three_ulCentent">
                        </ul>
                    </div>
                    <div class="run_three">
                        <span>上一页</span>
                        <ul class="three_page">

                        </ul>
                        <span>下一页</span>
                    </div>
                </div>
            </div>
@endsection
@section("script")
    <script type="text/javascript">
        $(function () {
            $('.centent_left_title').find('ul').find('li').each(function(){
                $(this).removeClass();
                $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
                if($(this).attr('lang') == 'accountCommissionSettlement'){
                    $(this).addClass('active');
                    $(this).find('a').addClass('activeA2');
                }
            });

            $(".wothdrawal").click(function () {
                $(".wothdrawal").addClass("active1");
                $(".Run_water").removeClass("active2");
                $(".Run_sum").removeClass("active3");
                $(".cnetent_right_2").hide();
                $(".cnetent_right_1").show();
                $(".cnetent_right_3").hide();
            });
            $(".Run_water").click(function () {
                $(".Run_water").addClass("active2");
                $(".wothdrawal").removeClass("active1");
                $(".Run_sum").removeClass("active3");
                $(".cnetent_right_2").show();
                $(".cnetent_right_1").hide();
                $(".cnetent_right_3").hide()
            });
            $(".Run_sum").click(function () {
                $(".Run_sum").addClass("active3");
                $(".wothdrawal").removeClass("active1");
                $(".Run_water").removeClass("active2");
                $(".cnetent_right_3").show();
                $(".cnetent_right_1").hide();
                $(".cnetent_right_2").hide();
            });
            $(".runT ul li").mouseover(function () {
                $(this).addClass("on_Mousemove");
            });
            $(".runT ul li").mouseout(function () {
                $(this).removeClass("on_Mousemove");
            });
            // getData();

        //点击发送获取数据
        //当前第一页
        function getDataO() {
            $.ajax({
                url:'{{route('s_user_getCommissionSettlement')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    seetype:1,//1,2,3 tab 当前值
                    page:1,//当前页数
                    pagesize:10//每页条数
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.status==200){
                        var datesize=data.data.data.length;
                        console.log(datesize);
                        for(var i=0 ;i<datesize;i++){
                            $(".one_ulCentent").append("<li class='o"+i+"'><span>"+data.data.data[i].day+"</span><span>"+data.data.data[0].money+"</span></li>");
                        }
                        var page=data.data.page;
                        // var sumPage=1;
                        for(var j=1 ;j<=page;j++){
                            $(".one_page").append("<li class='"+j+"'>"+j+"</li>");
                        }
                        $(".1").addClass("active");
                    }
                    else {
                        console.log(data);
                    }
                }
            })
        }
        getDataO();
        //第二页数据(本月的未结算)
        function getDataT() {
            $.ajax({
                url:'{{route('s_user_getCommissionSettlement')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    seetype:2,//1,2,3 tab 当前值
                    page:1,//当前页数
                    pagesize:10//每页条数
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.status==200){
                        var timeS=data.data.page;
                        var datesize=data.data.data.length;
                        for(var i=0 ;i<datesize;i++){
                            $(".twe_ulCentent").append("<li class='o"+i+"'><span>"+data.data.data[i].day+"</span><span>"+data.data.data[0].money+"</span></li>");
                        }
                        var page=data.data.page;
                        // var sumPage=1;
                        for(var j=1 ;j<=page;j++){
                            $(".twe_page").append("<li class='"+j+"'>"+j+"</li>");
                        }
                        $(".1").addClass("active");
                    }
                    else {
                        console.log(data);
                    }
                }
            })
        }
        getDataT();
        //第三页数据（历史结算记录）
        function getDataS() {
            $.ajax({
                url:'{{route('s_user_getCommissionSettlement')}}',
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    seetype:3,//1,2,3 tab 当前值
                    page:1,//当前页数
                    pagesize:10//每页条数
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:5000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.status==200){
                        var timeS=data.data.page;
                        var datesize=data.data.data.length;
                        for(var i=0 ;i<datesize;i++){
                            $(".three_ulCentent").append("<li class='o"+i+"'><span>"+data.data.data[i].day+"</span><span>"+data.data.data[0].money+"</span></li>");
                        }
                        var page=data.data.page;
                        var page=1;
                        for(var j=1 ;j<=page;j++){
                            $(".three_page").append("<li class='"+j+"'>"+j+"</li>");
                        }
                        $(".1").addClass("active");
                    }
                    else {
                        console.log(data);
                    }
                }
            })
        }
        getDataS();
        });
        $(".runT span").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".runT span").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });
        $(".runT ul li").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".runT ul li").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });

        $(".run_twe span").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".run_twe span").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });
        $(".run_twe ul li").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".run_twe ul li").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });
        $(".run_three span").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".run_three span").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });
        $(".run_three ul li").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".run_three ul li").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });
    </script>

@endsection


