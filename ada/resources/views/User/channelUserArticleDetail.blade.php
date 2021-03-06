@extends('User.layout')


@section("title", "渠道数据")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="channelUserArticleDetail" style="display:none;">
    <div class="zmtdl_admin_crumbs">
        渠道数据 > @{{channel_name}} > <span class="on">@{{product_name}}</span>
        <a href="javascript:window.history.go(-1);">返回</a>
    </div>
    <div class="zmtdl_admin_condition">
        <ul class="zmtdl_admin_day">
            <li :class="{on:time_filter==='all'}" @click="timeFilter('all')">全部</li>
            <li :class="{on:time_filter==='recent_30'}" @click="timeFilter('recent_30')">最近30天</li>
            <li :class="{on:time_filter==='recent_7'}" @click="timeFilter('recent_7')">最近7天</li>
            <li :class="{on:time_filter==='today'}" @click="timeFilter('today')">今日数据</li>
        </ul>
    </div>
    <div class="zmtdl_userData">
        <div class="zmtdl_userDataBox2"><span>内容创建篇数</span><br>@{{number}}</div>
        <div class="zmtdl_userDataBox2"><span>成交总数</span><br>@{{cjcount|numberFormat}}</div>
        <div class="zmtdl_userDataBox2"><span>交易总额</span><br>@{{account|numberFormat}}</div>
        <div class="zmtdl_userDataBox2"><span>佣金总额</span><br>@{{commission|numberFormat}}</div>
    </div>
    <div class="zmtdl_reportWrap">
        <table class="zmtdl_reportTable" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <td>内容标题</td>
                    <td style="cursor: pointer;" :class="{on:type==='count'}" @click="typeFilter('count')">成交次数<i class="zmtdl_iconArrow" :class="{down:type==='count' && sortObj.desc,up:type==='count' && sortObj.asc}"></i></td>
                    <td style="cursor: pointer;" :class="{on:type==='account'}" @click="typeFilter('account')">交易额<i class="zmtdl_iconArrow" :class="{down:type==='account' && sortObj.desc,up:type==='account' && sortObj.asc}"></i></td>
                    <td style="cursor: pointer;" :class="{on:type==='commission'}" @click="typeFilter('commission')">佣金<i class="zmtdl_iconArrow" :class="{down:type==='commission' && sortObj.desc,up:type==='commission' && sortObj.asc}"></i></td>
                </tr>
            </thead>
            <tbody>
                <tr v-if="tableData.length" v-for="t in tableData">
                    <td><a :href="'/User/channelUserOrderList?article_id='+t.article_id+'&article_name='+t.name">@{{t.name}}</a></td>
                    <td>@{{t.count}}</td>
                    <td>@{{t.account|numberFormat}}</td>
                    <td>@{{t.commission|numberFormat}}</td>
                </tr>
                <tr v-if="!tableData.length">
                    <td colspan="4">暂无数据</td>
                </tr>
            </tbody>
        </table>

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


            var app = new Vue({
                el: '#channelUserArticleDetail',
                data: {
                    number :"",
                    cjcount :"",
                    account :"",
                    commission:"",
                    tableData:[],
                    sortObj:{
                        asc:false,
                        desc:false
                    },
                    name:"",
                    articlename:"",
                    nickname:"",
                    time_filter:"all",
                    type:"",
                    sort:"",
                    tableData:[],
                    page:1,
                    page_count:""
                },
                created:function(){
                    this.getTableData(this.time_fliter);
                },
                mounted:function(){
                    var _this=this;
                    this.$nextTick(function() {
                        document.getElementById("channelUserArticleDetail").style.display = "block";
                    });
                },
                methods:{
                    getQueryVariable:function(variable){
                        var query = window.location.search.substring(1);
                        var vars = query.split("&");
                        for (var i=0;i<vars.length;i++) {
                                var pair = vars[i].split("=");
                                if(pair[0] == variable){return pair[1];}
                        }
                        return(false);
                    },
                    typeFilter:function(type){
                        switch (type) {
                            case 'count':
                                this.type=type;
                                if(this.sortObj.asc===false){
                                    this.sortObj.asc=true;
                                    this.sortObj.desc=false;
                                    this.sort='asc';
                                }else{
                                    this.sortObj.asc=false;
                                    this.sortObj.desc=true;
                                    this.sort='desc';
                                }
                                this.getTableData();
                                break;
                            case 'account':
                                this.type=type;
                                if(this.sortObj.asc===false){
                                    this.sortObj.asc=true;
                                    this.sortObj.desc=false;
                                    this.sort='asc';
                                }else{
                                    this.sortObj.asc=false;
                                    this.sortObj.desc=true;
                                    this.sort='desc';
                                }
                                this.getTableData();
                                break;
                            case 'commission':
                                this.type=type;
                                if(this.sortObj.asc===false){
                                    this.sortObj.asc=true;
                                    this.sortObj.desc=false;
                                    this.sort='asc';
                                }else{
                                    this.sortObj.asc=false;
                                    this.sortObj.desc=true;
                                    this.sort='desc';
                                }
                                this.getTableData();
                                break;
                            default:
                                break;
                        }
                    },
                    timeFilter:function(time_filter){
                        switch (time_filter) {
                            case 'all':
                                this.time_filter=time_filter;
                                this.getTableData();
                                break;
                            case 'recent_30':
                                this.time_filter=time_filter;
                                this.getTableData();
                                break;
                            case 'recent_7':
                                this.time_filter=time_filter;
                                this.getTableData();
                                break;
                            case 'today':
                                this.time_filter=time_filter;
                                this.getTableData();
                                break;
                            default:
                                break;
                        }
                    },
                    toPage:function(page){
                        this.page=page;
                        this.getTableData();
                    },
                    getTableData:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_getArticleDetail')}}',
                            type:'POST',
                            async:true,
                            data:{
                                name:_this.name,
                                product_id:_this.getQueryVariable('product_id'),
                                product_name:_this.getQueryVariable('product_name'),
                                time_fliter:_this.time_filter,
                                type:_this.type,
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
                                    _this.channel_name=res.data.channel_name;
                                    _this.product_name=res.data.product_name;
                                    _this.number=res.data.number;
                                    _this.cjcount=res.data.cjcount;
                                    _this.account=res.data.account;
                                    _this.commission=res.data.commission;
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

