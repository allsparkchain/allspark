@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="accountInfo" style="display:none">
    <!-- <div class="pgy_wrapTop">
        <span>每笔收益45天后将结算至余额</span>
        <p>累计产生收益总额&nbsp;&nbsp;<span>@{{all_commission_profit|numberFormat}}</span></p>
    </div> -->
    <div class="accountNotal">
        <div >
            <span>余额</span><br>@{{available_amount|numberFormat}}
            
        </div>
        <div ><span>未结算收益</span><br>@{{unsettled_amount|numberFormat}}</div>
        <!-- <div><span>累计佣金</span><br>@{{all_commission_profit|numberFormat}}</div> -->
        <div style="width:240px;border-left:1px solid #EAEAEA;height:65px">
        <i class="pgy_need_tix"><a href="{{route('s_user_withdrawPage')}}">我要提现</a></i>
        </div>
    </div>

    <div class="ggzdl_accountChart" >
        <div class="ggzdl_chartTitle">
            <span :class="{on:showBy===1}" @click="getCharts(1)">近7日收益数据</span><span :class="{on:showBy===2}"  style="padding-left:39px;" @click="getCharts(2)">月度数据</span>
        </div>
        <div id="ggzdl_chart" style="width: 890px;height:400px; margin: 0 auto 0 auto;"></div>
       
    </div>
    <div class="nowithData" style="display:none">
        <img src="/image/nullpic.jpg" alt="">
        <p>你还没有相关数据，<a href="{{route('s_goods_lists')}}">写作</a>或<a href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
    </div>
</div>
@endsection




@section("script")
    <script type="text/javascript">
    $(function(){
        Vue.filter("numberFormat",function(s){
            s=s.toString();
            if(/[^0-9\.]/.test(s)) return "0.00";
                s=s.replace(/^(\d*)$/,"$1.");
                s=(s+"00").replace(/(\d*\.\d\d)\d*/,"$1");
                s=s.replace(".",",");
                var re=/(\d)(\d{3},)/;
                while(re.test(s))
                    s=s.replace(re,"$1,$2");
                s=s.replace(/,(\d\d)$/,".$1");
                return s.replace(/^\./,"0.");
        });  
        var app=new Vue({
            el:'#accountInfo',
            data:{
                all_commission_profit:"",
                unsettled_amount:"",
                unsettled_amount_month:"",
                last_month_total:"",
                day_commission_account:"",
                today_nums:"",
                available_amount:"",
                time:[],
                views :"",
                account:[],
                options:{},
                myChart:null,
                showBy:1,
            },
            created:function(){
                var _this=this;
                $.ajax({
                    url:'{{route('s_user_getAccountInfo')}}',
                    type:'POST',
                    async:true,
                    data:{
                        // type:1
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            _this.all_commission_profit=res.data.all_commission_profit;
                                _this.unsettled_amount=res.data.unsettled_amount;
                                _this.unsettled_amount_month=res.data.unsettled_amount_month;
                                _this.last_month_total=res.data.last_month_total;
                                _this.day_commission_account=res.data.day_commission_account;
                                _this.today_nums=res.data.today_nums;
                                _this.available_amount=res.data.available_amount;

                                if(_this.available_amount==0 && _this.unsettled_amount==0){
                                    $('.nowithData').show();
                                    $('.ggzdl_accountChart').hide();
                                }else if(res.status==403){
                                    location.reload();
                                }else{
                                    $('.nowithData').hide();
                                    $('.ggzdl_accountChart').show();
                                } 
                        }
                    }
                });
                this.getCharts(1)
            },
            mounted:function(){
                var _this=this;
                this.myChart=echarts.init(document.getElementById("ggzdl_chart"));
                this.$nextTick(function() {
                    document.getElementById("accountInfo").style.display = "block";
                });
            },
            methods:{
                  getCharts:function(showBy){
                    var _this=this;
                    _this.time=[];
                    _this.account=[];
                    _this.showBy=showBy;
                    $.ajax({
                        url:'{{route('s_user_getAccountInfoFlow')}}',
                        type:'POST',
                        async:true,
                        data:{
                            seetype:_this.showBy
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.views=res.data;
                                res.data.forEach(function(v,i){
                                    _this.time.push(v.time);

                                  _this.account.push(Number(v.account).toFixed(2).toLocaleString('en-US'));
                                  
                                    
                                });
                                // 使用刚指定的配置项和数据显示图表。
                                _this.myChart.setOption({
                                    tooltip: {
                                        formatter: function(params) {  
                                            var res = params.name+':'+'￥'+params.value;
                                            return res;  
                                        }
                                    },
                                    xAxis: {
                                        type: 'category',
                                        data: _this.time,
                                        axisLine:{
                                            lineStyle:{
                                                color:'#FFF'
                                            }
                                        },
                                        axisLabel: {
                                            textStyle: {
                                                color: '#595959',
                                                fontSize:'14'
                                            },
                                        }
                                    },
                                    yAxis: {
                                        axisLine:{
                                            lineStyle:{
                                                color:'#FFF'
                                            }
                                        },
                                        axisLabel: {
                                            textStyle: {
                                                color: '#595959',
                                                fontSize:'14'
                                            },
                                        }
                                    },
                                    series: [{
                                        data: _this.account,
                                        type: 'line',
                                        symbolSize:10,
                                        itemStyle:{
                                            normal:{
                                                lineStyle:{
                                                    color:'#ff7241',
                                                    width:3
                                                }
                                            }
                                        }
                                    }]
                                });
                            }
                        }
                });
            
                }

              }
        })
    })


    </script>
@endsection

