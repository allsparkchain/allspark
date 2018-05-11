@extends('User.layout')


@section("title", "收益结算")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="commissionSettlement" style="display:none">
    <div class="zmtdl_userData">
        <div class="zmtdl_userDataBox2"><span>上月未结算收益</span><br>@{{last_month_total|numberFormat}}</div>
        <div class="zmtdl_userDataBox2"><span>本月未结算收益</span><br>@{{unsettled_amount_month|numberFormat}}</div>
        <div class="zmtdl_userDataBox2"><span>未结算收益总额</span><br>@{{unsettled_amount|numberFormat}}</div>
    </div>
    <div class="ggzdl_admin_condition">
        <ul class="ggzdl_admin_day">
            <li v-for="(item,index) in tabs"  :class="{on:num == index}"  @click="tab(item,index)" v-text="item.text"></li>
        </ul>
    </div>
    <div class="ggzdl_reportWrap">
        <div class="ggzdl_reportTableWrap">
            <table class="ggzdl_reportTable" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <td style="text-align:left; padding-left:138px;">时间</td>
                    <td>收益</td>

                </tr>
                </thead>
                <tbody>
                <tr v-if="tableData.length" v-for="t in tableData">
                    <td style="text-align:left; width:50%; padding-left:138px;">@{{t.day}}</td>
                    <td>@{{t.money|numberFormat}}</td>
                </tr>
                <tr v-if="!tableData.length">
                    <td colspan="2">暂无数据</td>
                </tr>
                
                </tbody>
            </table>
        </div>
        <div v-if="page_count>1" class="ggzdl_pageWrap">
            <ul class="ggzdl_page">
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
            var app=new Vue({
                el:"#commissionSettlement",
                data:{
                    seetype:null,
                    tabs:[{seetype:1,text:"上月未结算收益"},{seetype:2, text:"本月未结算收益"},{seetype:3, text:"未结算收益总额"}],
                    num:0,
                    unsettled_amount:"",
                    unsettled_amount_month:"",
                    last_month_total:"",
                    tableData:[],
                    page:1,
                    page_count:"",
                    seetype:1

                },
                created:function(){
                var _this=this;
                $.ajax({
                    url:'{{route('s_user_getAccountInfo')}}',
                    type:'POST',
                    async:true,
                    data:{
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            _this.unsettled_amount=res.data.unsettled_amount;
                            _this.unsettled_amount_month=res.data.unsettled_amount_month;
                            _this.last_month_total=res.data.last_month_total;
                        }else if(res.status==403){
                            location.reload();
                        }
                    }
                });
                this.getTableData();
            },
            mounted:function(){
                this.$nextTick(function() {
                        document.getElementById("commissionSettlement").style.display = "block";
                    });
            },
                methods:{
                    tab(item,index) {
                        this.seetype=item.seetype;
                        this.num = index;
                        this.getTableData();
                    },
                    toPage:function(page){
                        this.page=page;
                        this.getTableData();
                    },
                    getTableData:function(){
                        var _this=this;
                        $.ajax({
                        url:'{{route('s_user_getCommissionSettlement')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            seetype:_this.seetype,
                            page:_this.page,
                            pagesize:10
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
        })
    </script>
@endsection

