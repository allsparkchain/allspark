@extends('User.layout')


@section("title", "佣金提现")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}
@endsection

@section("content")
<div id="unsettledCommissionFlow" style="display:none;">
    <div class="zmtdl_admin_withdrawalCondition">
        <div class="zmtdl_admin_dayPickerWrap">
            <div class="zmtdl_admin_dayPicker" style="border-right:1px solid #eaeaea;">
                <input id="timeStart" type="text" readonly v-model="starttime" placeholder="请选择查询日期"><i class="zmtdl_iconCalendar"></i>
            </div>
            <div class="zmtdl_admin_dayPicker">
                <input id="timeEnd" type="text" readonly v-model="endtime" placeholder="请选择查询日期"><i class="zmtdl_iconCalendar"></i>
            </div>
        </div>
        <input class="zmtdl_admin_queryBtn" type="button" value="查 询" @click="getTableData">
        <a href="javascript:window.history.go(-1);">返回</a>
    </div>
    <div class="zmtdl_reportTableWrap">
        <div style=" min-height:600px;">
        <table class="zmtdl_reportTable" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <td style="text-align:left; padding-left:40px;">日期</td>
                <td>成交量</td>
                <td>佣金</td>
                <td>未结算总金额</td>
            </tr>
            </thead>
            <tbody>
            <tr v-if="tableData.length" v-for="t in tableData">
                <td style="text-align:left; width:30%; padding-left:40px;">
                    @{{t.date|timeFormat2}}
                    <!-- <span class="zmtdl_settlementDate">结算日</span> -->
                </td>
                <td>@{{t.count}}</td>
                <td>@{{t.commission|numberFormat}}</td>
                <td>@{{t.total_unset|numberFormat}}</td>
            </tr>
            <tr v-if="!tableData.length">
                <td colspan="4">暂无数据</td>
            </tr>
            </tbody>
        </table>
        </div>

        <div v-if="page_count!=0" class="zmtdl_pageWrap">
            <ul class="zmtdl_page">
                <li v-show="!(page==1)" @click="toPage(1)" class="first">首页</li>
                <li v-show="page>1" @click="toPage(page-1)">上一页</li>
                <li v-for="(value,index) in page_count" @click="toPage(value)" :class="{on:page===index+1}" v-show="page===index+1||page===index||page===index+2">@{{value}}</li>
                <li v-show="page<page_count" @click="toPage(page+1)">下一页</li>
                <li v-show="!(page==page_count)" class="last" @click="toPage(page_count)">末页</li>
            </ul>
        </div>


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

            Vue.filter("timeFormat",function(value){
                var date = new Date(value * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
                Y = date.getFullYear() + '-';
                M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
                D = date.getDate() + ' ';
                h = date.getHours() + ':';
                m = date.getMinutes() + ':';
                s = date.getSeconds() < 10 ? '0'+ date.getSeconds() : date.getSeconds();
                return Y+M+D+h+m+s;
            });

            Vue.filter("timeFormat2",function(value){
                var date = new Date(value * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
                Y = date.getFullYear() + '-';
                M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
                D = date.getDate() + ' ';
                h = date.getHours() + ':';
                m = date.getMinutes() + ':';
                s = date.getSeconds() < 10 ? '0'+ date.getSeconds() : date.getSeconds();
                return Y+M+D;
            });


            var app = new Vue({
                el: '#unsettledCommissionFlow',
                data: {
                    canwithdraw:"",
                    total_unsetted_commission:"",
                    tableData:[],
                    starttime:"",
                    endtime:"",
                    page:1,
                    page_count:""
                },
                created:function(){
                    var _this=this;
                    this.getTableData();
                },
                mounted:function(){
                    var _this=this;
                    this.$nextTick(function() {
                        document.getElementById("unsettledCommissionFlow").style.display = "block";
                    });

                    $('#timeStart').datetimepicker({
                        bootcssVer:3,
                        language:  'zh-CN',
                        format:"yyyy-mm-dd",
                        autoclose: true,
                        todayHighlight: true,
                        maxViewMode: 1,
                        minViewMode:1,
                        startView: 2,
                        minView: 2,
                        forceParse: 0,
                        todayBtn: true
                    }).on('changeDate', function (ev) {  
                        _this.starttime=$("#timeStart").val();
                        $("#timeEnd").datetimepicker('setStartDate',_this.starttime);
                        $("#timeStart").datetimepicker('hide'); 
                    });

                    $('#timeEnd').datetimepicker({
                        bootcssVer:3,
                        language:  'zh-CN',
                        format:"yyyy-mm-dd",
                        autoclose: true,
                        todayHighlight: true,
                        maxViewMode: 1,
                        minViewMode:1,
                        startView: 2,
                        minView: 2,
                        forceParse: 0,
                        todayBtn: true
                    }).on('changeDate', function (ev) {  
                        _this.endtime=$("#timeEnd").val();
                        $("#timeStart").datetimepicker('setEndDate',_this.endtime);
                        $("#timeEnd").datetimepicker('hide'); 
                    });;


                },
                methods:{
                    toPage:function(page){
                        this.page=page;
                        this.getTableData();
                    },
                    getTableData:function(){
                        var _this=this;
                        console.log(_this.starttime);
                        $.ajax({
                            url:'{{route('s_user_getUserUnsettledPage')}}',
                            type:'POST',
                            async:true,
                            data:{
                                page:_this.page,
                                pagesize:10,
                                starttime:_this.starttime,
                                endtime:_this.endtime
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    _this.tableData=res.data.data;
                                    _this.page=res.data.page;
                                    _this.page_count=res.data.page_count;
                                }
                            }
                        });
                    }
                }
            })
        });

    </script>
@endsection

