@extends('User.layout')


@section("title", "商品数据")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="channelShops" style="display:none;">
    <div class="zmtdl_admin_crumbs">
        广告主数据 > <span class="on">商品列表</span>
        <a href="/User/channelList">返回</a>
    </div>
    <div class="zmtdl_addShops"><a :href="'/User/channelAddShops?id='+detail_id">添加商品</a></div>
    <div class="zmtdl_reportWrap">
        <table class="zmtdl_reportTable" cellpadding="0" cellspacing="0">
            <colgroup>
                <col width="20%">
                <col width="50%">
                <col width="15%">
                <col width="15%">
            </colgroup>
            <thead>
                <tr>
                    <td>添加时间</td>
                    <td style="text-align:left;">商品描述</td>
                    <td>物料</td>
                    <td>当前状态</td>
                </tr>
            </thead>
            <tbody>
                <tr v-if="shopsList.length" v-for="t in shopsList">
                    <td>@{{t.add_time|timeFormat}}</td>
                    <td style="text-align:left;">@{{t.attachment_describe}}</td>
                    <td>已上传</td>
                    <td>@{{t.status_show}}</td>
                </tr>
                <tr v-if="!shopsList.length">
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
            el:"#channelShops",
            data:{
                shopsList:[],
                page:1,
                page_count:""
            },
            created:function(){
                var _this=this;
                _this.detail_id=_this.getQueryVariable('id');
                if(_this.detail_id){
                    _this.getDetail();
                }
            },
            mounted:function(){
                var _this=this;
                this.$nextTick(function() {
                    document.getElementById("channelShops").style.display = "block";
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
                    return("");
                },
                toPage:function(page){
                    var _this=this;
                    _this.page=page;
                    _this.getDetail();
                },
                getDetail:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_user_getAdvertRelativeAttachmentsPage')}}',
                        type:'POST',
                        async:true,
                        data:{
                            detail_id:_this.detail_id,
                            page: _this.page,
                            pagesize: 10
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.shopsList=res.data.data;
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

