@extends('User.layout')


@section("title", "广告主数据")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="channelUserDetail" style="display:none;">
    <div class="zmtdl_admin_crumbs">
        广告主数据 > <span class="on">数据</span>
        <a href="javascript:window.history.go(-1);">返回</a>
    </div>
    <div class="zmtdl_admin_condition">
        <ul class="zmtdl_admin_day">
            <li :class="{on:time_filter==='all'}" @click="timeFilter('all')">全部</li>
            <li :class="{on:time_filter==='recent_30'}" @click="timeFilter('recent_30')">最近30天</li>
            <li :class="{on:time_filter==='recent_7'}" @click="timeFilter('recent_7')">最近7天</li>
            <li :class="{on:time_filter==='today'}" @click="timeFilter('today')">今日数据</li>
        </ul>
        <div class="zmtdl_admin_search">
            <i class="zmtdl_iconQuery"></i>
            <input class="zmtdl_zmtsearch" type="text" placeholder="输入文章名称进行搜索" v-model="name" @keyup.enter="getTableData(name)">
        </div>
    </div>
    <div class="zmtdl_userData">
        <div class="zmtdl_userDataBox"><span>商品总数</span><br>@{{number}}</div>
        <div class="zmtdl_userDataBox"><span>成交总数</span><br>@{{mcount}}</div>
        <div class="zmtdl_userDataBox"><span>交易总额</span><br>@{{account|numberFormat}}</div>
        <div class="zmtdl_userDataBox"><span>佣金总额</span><br>@{{commission|numberFormat}}</div>
    </div>
    <div class="zmtdl_reportWrap">
        <div class="zmtdl_reportTableWrap">
            <table class="zmtdl_reportTable" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <td onclick="location='{{route('s_user_channelUserArticleDetail')}}'">商品名称</td>
                    <td style="cursor: pointer;" :class="{on:type==='number'}" @click="typeFilter('number')">成交次数<i class="zmtdl_iconArrow" :class="{down:type==='number' && sortObj.desc,up:type==='number' && sortObj.asc}"></i></td>
                    <td style="cursor: pointer;" :class="{on:type==='account'}" @click="typeFilter('account')">交易额<i class="zmtdl_iconArrow" :class="{down:type==='account' && sortObj.desc,up:type==='account' && sortObj.asc}"></i></td>
                    <td style="cursor: pointer;" :class="{on:type==='commission'}" @click="typeFilter('commission')">佣金<i class="zmtdl_iconArrow" :class="{down:type==='commission' && sortObj.desc,up:type==='commission' && sortObj.asc}"></i></td>
                </tr>
                </thead>
                <tbody>
                <tr v-if="tableData.length" v-for="t in tableData">
                    <td><a :href="'/User/channelUserArticleDetail?product_id='+t.product_id+'&product_name='+t.product_name">@{{t.product_name}}</a></td>
                    <td>@{{t.number}}</td>
                    <td>@{{t.account|numberFormat}}</td>
                    <td>@{{t.commission|numberFormat}}</td>
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
                s=parseFloat(s);
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

            var app = new Vue({
                el: '#channelUserDetail',
                data: {
                    number :"",
                    mcount :"",
                    uid:"",
                    commission :"",
                    account :"",
                    tableData:[],
                    sortObj:{
                        asc:false,
                        desc:false
                    },
                    name:"",
                    channel_name:"",
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
                    this.uid=this.getQueryVariable('invited_uid');
                    this.$nextTick(function() {
                        document.getElementById("channelUserDetail").style.display = "block";
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
                            case 'number':
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
                            url:'{{route('s_user_getUserDetail')}}',
                            type:'POST',
                            async:true,
                            data:{
                                name:_this.name,
                                advert_relative_uid:_this.getQueryVariable('advert_relative_uid'),
                                realname:_this.getQueryVariable('realname'),
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
                                    _this.number=res.data.number?res.data.number:0;
                                    _this.mcount=res.data.mcount?res.data.mcount:0;
                                    _this.commission=res.data.commission;
                                    _this.account=res.data.account;
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

