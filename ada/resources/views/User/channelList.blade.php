@extends('User.layout')


@section("title", "广告主数据")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="channelList" style="display:none;">
    <div class="zmtdl_admin_condition">
        <ul class="zmtdl_admin_day">
            <li :class="{on:filter===''}" @click="typeFilter('')">所有</li>
            <li :class="{on:filter==='3'}" @click="typeFilter('3')">审核成功</li>
            <li :class="{on:filter==='4'}" @click="typeFilter('4')">审核失败</li>
            <li :class="{on:filter==='1'}" @click="typeFilter('1')">审核中</li>
            <li :class="{on:filter==='2'}" @click="typeFilter('2')">需补充资料</li>
        </ul>
    </div>
    <div class="zmtdl_reportWrap">
        <table class="zmtdl_reportTable" cellpadding="0" cellspacing="0">
            <colgroup>
                <col width="20%">
                <col width="40%">
                <col width="20%">
                <col width="20%">
            </colgroup>
            <thead>
                <tr>
                    <td>申请时间</td>
                    <td style="text-align:left;">广告主名称</td>
                    <td>审核状态</td>
                    <td>操作</td>
                </tr>
            </thead>
            <tbody>
                <tr v-if="tableData.length" v-for="t in tableData">
                    <td>@{{t.add_time|timeFormat}}</td>
                    <td style="text-align:left;">
                        <a :href="'/User/channelUserDetail?advert_relative_uid='+t.advert_relative_uid+'&realname='+t.realname">@{{t.realname}}</a>
                    </td>
                    <td>@{{t.review_status_show}}</td>
                    <td v-if="t.review_status==='2' || t.review_status==='4'">
                        <a :href="'/User/channelEditUser?id='+t.detail_id">编辑</a>
                    </td>
                    <td v-else-if="t.review_status==='3'">
                        <a :href="'/User/channelShops?id='+t.detail_id">添加商品</a> 
                        <a :href="'/User/channelUserDetail?advert_relative_uid='+t.advert_relative_uid+'&realname='+t.realname">数据</a>
                        <a :href="'/User/channeladdUser?id='+t.detail_id">信息</a>
                    </td>
                    <td v-else>
                        &nbsp;
                    </td>
                </tr>
                <tr v-if="!tableData.length">
                    <td colspan="4">暂无数据</td>
                </tr>
            </tbody>
        </table>

        <div v-if="page_count>1" class="zmtdl_pageWrap">
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

        Vue.filter("timeFormat",function(value){
            var date = new Date(value * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
            Y = date.getFullYear() + '-';
            M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
            D = date.getDate() < 10 ? '0'+ date.getDate() + ' ' : date.getDate() + ' ';
            h = date.getHours() < 10 ? '0'+ date.getHours() + ':' : date.getHours() + ':';
            m = date.getMinutes() < 10 ? '0'+ date.getMinutes() + ':' : date.getMinutes() + ':';
            s = date.getSeconds() < 10 ? '0'+ date.getSeconds() : date.getSeconds();
            return Y+M+D+h+m+s;
        });

        var app=new Vue({
            el:"#channelList",
            data:{
                name:"",
                filter:"",
                tableData:[],
                page:1,
                page_count:""
            },
            created:function(){
                this.getTableData(this.fliter);
            },
            mounted:function(){
                var _this=this;
                this.$nextTick(function() {
                    document.getElementById("channelList").style.display = "block";
                });
            },
            methods:{
                toPage:function(page){
                    var _this=this;
                    _this.page=page;
                    _this.getTableData();
                },
                typeFilter:function(filter){
                    var _this=this;
                    _this.page=1;
                    _this.filter=filter;
                    _this.getTableData();
                },
                getTableData:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_user_getMyAgentAdvertRelative')}}',
                        type:'POST',
                        async:true,
                        data:{
                            name:_this.name,
                            review_status:_this.filter,
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
                }

            }
        })
    })
    </script>
@endsection

