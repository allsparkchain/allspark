@extends('User.layout')


@section("title", "结算明细")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="commissionInfo" style="display:none;">
    <div class="zmtdl_admin_withdrawalwrap">
        <div class="zmtdl_admin_withdrawalcontent">
            <span>未结算佣金</span><br>
           @{{total_unsetted_commission|numberFormat}}<br>
            <a href="{{route('s_user_unsettledCommissionFlow')}}">查询明细</a>
        </div>
        <div class="zmtdl_admin_withdrawalcontent">
            <span>可提现金额</span><br>
            @{{canwithdraw|numberFormat}}<br>
            <a href="{{route('s_user_balanceFlow')}}">我要提现</a>
        </div>
    </div>
    <div class="zmtdl_reportWrap">
        <div class="zmtdl_reportTableWrap">
            <table class="zmtdl_reportTable" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <td colspan="4" style="text-align:left; padding-left:40px;">近期提现记录</td>
                </tr>
                </thead>
                <tbody>
                <tr v-if="tableData.length" v-for="t in tableData">
                    <td style="text-align:left; width:30%; padding-left:40px;">@{{t.add_time|timeFormat}}</td>
                    <td>@{{t.commission_name}}</td>
                    <td>@{{t.account|numberFormat}}</td>
                    <td>@{{t.status_show}}</td>
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
                m = date.getMinutes() < 10 ? '0'+ date.getMinutes()+':' : date.getMinutes() +':';
                s = date.getSeconds() < 10 ? '0'+ date.getSeconds() : date.getSeconds();
                return Y+M+D+h+m+s;
            });


            var app = new Vue({
                el: '#commissionInfo',
                data: {
                    canwithdraw:"",
                    total_unsetted_commission:"",
                    tableData:[],
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
                        document.getElementById("commissionInfo").style.display = "block";
                    });
                },
                methods:{
                    toPage:function(page){
                        this.page=page;
                        this.getTableData();
                    },
                    getTableData:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_getUserWithdrawPage')}}',
                            type:'POST',
                            async:true,
                            data:{
                                sort:_this.sort,
                                page:_this.page,
                                pagesize:10
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    _this.canwithdraw=res.data.canwithdraw;
                                    _this.total_unsetted_commission=res.data.total_unsetted_commission;
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

