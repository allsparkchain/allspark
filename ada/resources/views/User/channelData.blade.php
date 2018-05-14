@extends('User.layout')


@section("title", "广告主数据")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="channelData" style="display:none;">
    <div class="zmtdl_admin_condition">
        <ul class="zmtdl_admin_day">
            <li :class="{on:time_filter==='all'}" @click="timeFilter('all')">全部</li>
            <li :class="{on:time_filter==='recent_30'}" @click="timeFilter('recent_30')">最近30天</li>
            <li :class="{on:time_filter==='recent_7'}" @click="timeFilter('recent_7')">最近7天</li>
            <li :class="{on:time_filter==='today'}" @click="timeFilter('today')">今日数据</li>
        </ul>
        <div class="zmtdl_admin_search">
            <i class="zmtdl_iconQuery"></i>
            <input class="zmtdl_zmtsearch" type="text" placeholder="输入广告主名称进行查询" v-model="name" @keyup.enter="getTableData(name)">
        </div>
    </div>
    <div class="zmtdl_reportWrap">
        <table class="zmtdl_reportTable" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <td>广告主</td>
                <td style="cursor: pointer;" :class="{on:type==='number'}" @click="typeFilter('number')">商品数量<i class="zmtdl_iconArrow" :class="{down:type==='number' && sortObj.desc,up:type==='number' && sortObj.asc}"></i></td>
                <td style="cursor: pointer;" :class="{on:type==='count'}" @click="typeFilter('count')">成交次数<i class="zmtdl_iconArrow" :class="{down:type==='count' && sortObj.desc,up:type==='count' && sortObj.asc}"></i></td>
                <td style="cursor: pointer;" :class="{on:type==='account'}" @click="typeFilter('account')">交易总额<i class="zmtdl_iconArrow" :class="{down:type==='account' && sortObj.desc,up:type==='account' && sortObj.asc}"></i></td>
                <td style="cursor: pointer;" :class="{on:type==='commissions'}" @click="typeFilter('commissions')">佣金<i class="zmtdl_iconArrow" :class="{down:type==='commissions' && sortObj.desc,up:type==='commissions' && sortObj.asc}"></i></td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <tr v-if="tableData.length" v-for="t in tableData">
                <td><a :href="'/User/channelUserDetail?advert_relative_uid='+t.advert_relative_uid+'&realname='+t.realname">@{{t.realname}}</a></td>
                <td>@{{t.number}}</td>
                <td>@{{t.count}}</td>
                <td>@{{t.account|numberFormat}}</td>
                <td>@{{t.commissions|numberFormat}}</td>
                <td><a :href="'/User/channeladdUser?id='+t.detail_id">详情</a></td>
            </tr>
            <tr v-if="!tableData.length">
                <td colspan="5">暂无数据</td>
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
    $(function(){
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

        var app=new Vue({
            el:"#channelData",
            data:{
                sortObj:{
                    asc:false,
                    desc:false
                },
                name:"",
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
                    document.getElementById("channelData").style.display = "block";
                });
            },
            methods:{
                downloadExcel:function(){
                    var _this=this;
                    var url = "{{route('s_user_DownLoadCommissionSettlement')}}"+'?download=1'+'&name='+_this.name+'&time_fliter='+_this.time_filter+'&type='+_this.type+'&sort='+_this.sort+'&pagesize='+10+'&page='+_this.page;
                    window.open(url);
                },

                typeFilter:function(type){
                    switch (type) {
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
                        case 'commissions':
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
                toPage:function(page){
                    this.page=page;
                    this.getTableData();
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
                getTableData:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_user_getCommissionSettlement')}}',
                        type:'POST',
                        async:true,
                        data:{
                            name:_this.name,
                            time_fliter:_this.time_filter,
                            type:_this.type,
                            sort:_this.sort,
                            pagesize:10,
                            page:_this.page
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
                },


            }
        })
    })
    </script>
@endsection

