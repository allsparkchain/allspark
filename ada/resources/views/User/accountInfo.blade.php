@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
    <div id="accountInfo" style="display:none;">
    <ul class="zmtdl_accountDetail">
        <li><span>历史累计成交</span><br>@{{sumAccount|numberFormat}}</li>
        <li><span>累计获得佣金</span><br>@{{sumComission|numberFormat}}</li>
        <li><span>今日订单</span><br>@{{dayNumber}}</li>
        <li><span>本月成交额</span><br>@{{monthAccount|numberFormat}}</li>
        <li><span>本月已获得佣金</span><br>@{{monthcommission|numberFormat}}</li>
        <li><span>今日成交额</span><br>@{{dayAccount|numberFormat}}</li>
        <li><span>已接入广告主</span><br>@{{peoplenum}}</li>
        <li><span>本月最佳广告主</span><br>@{{monthBest}}</li>
    </ul>
    <div class="zmtdl_accountChart">
        <div class="zmtdl_chartTitle">
            <h6>近日数据视图</h6><span :class="{on:showBy===1}" @click="getChart(1)">按日</span> / <span :class="{on:showBy===2}" @click="getChart(2)">按周</span> / <span :class="{on:showBy===3}" @click="getChart(3)">按月</span>
        </div>
        <div id="zmtdl_chart" style="width: 880px;height:400px; margin: 0 auto 0 auto;"></div>
    </div>
        
    </div>
@endsection



@section("script")
    <script type="text/javascript">
        $(function () {
            Vue.filter("numberFormat",function(s){
                s=s.toString();
                if(/[^0-9\.]/.test(s)) return "invalid value";
                    s=s.replace(/^(\d*)$/,"$1.");
                    s=(s+"00").replace(/(\d*\.\d\d)\d*/,"$1");
                    s=s.replace(".",",");
                    var re=/(\d)(\d{3},)/;
                    while(re.test(s))
                        s=s.replace(re,"$1,$2");
                    s=s.replace(/,(\d\d)$/,".$1");
                    return s.replace(/^\./,"0.");
            });

            var app = new Vue({
                el: '#accountInfo',
                data: {
                    sumAccount:"",
                    sumComission :"",
                    dayNumber :"",
                    monthAccount :"",
                    monthcommission :"",
                    dayAccount :"",
                    peoplenum :"",
                    monthBest:"",
                    views :"",
                    time:[],
                    account:[],
                    options:{},
                    myChart:null,
                    showBy:1
                },
                created:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_user_getAccountInfo')}}',
                        type:'POST',
                        async:true,
                        data:{
                            type:1
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.sumAccount=res.data.sumAccount;
                                _this.sumComission=res.data.sumComission;
                                _this.dayNumber=res.data.dayNumber;
                                _this.monthAccount=res.data.monthAccount;
                                _this.monthcommission=res.data.monthcommission;
                                _this.dayAccount=res.data.dayAccount;
                                _this.peoplenum=res.data.peoplenum;
                                _this.monthBest=res.data.monthBest;
                            }
                        }
                    });
                    this.getChart(1);
                },
                mounted:function(){
                    var _this=this;
                    // 基于准备好的dom，初始化echarts实例
                    this.myChart = echarts.init(document.getElementById('zmtdl_chart'));
                    this.$nextTick(function() {
                        document.getElementById("accountInfo").style.display = "block";
                    });
                },
                methods:{
                    getChart:function(showBy){
                        var _this=this;
                        _this.time=[];
                        _this.account=[];
                        _this.showBy=showBy;
                        $.ajax({
                            url:'{{route('s_user_getAccountInfo')}}',
                            type:'POST',
                            async:true,
                            data:{
                                type:2,
                                showBy:showBy
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    _this.views=res.data;
                                    res.data.forEach(function(v,i,a){
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
                                                    fontSize:'13'
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
                                                    fontSize:'13'
                                                },
                                            }
                                        },
                                        series: [{
                                            data: _this.account,
                                            type: 'line',
                                            smooth: true,
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
        });

    </script>
@endsection

