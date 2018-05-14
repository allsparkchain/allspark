@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="spreadDataDetail" style="display:none">
    <div class="pgy_commdDetail">
        <div class="ggzdl_admin_crumbs">
            资讯列表&nbsp;&gt;&nbsp;<span class="on">@{{title}}</span>
            <a href="javascript:window.history.go(-1);">返回</a>
        </div>
        <ul class="pgy_commd_li">
            <li>
                <span>累计产生收益</span><br>@{{sum_account|numberFormat}}
            </li>
            <li>
                <span>返点率</span><br>@{{channelpercent|numberFormat}}
            </li>
            <li>
                <span>累计成单</span><br>@{{sum_count|numberFormat}}
            </li>
        </ul>
    </div>
    <div class="ggzdl_reportWrap">
        <div class="ggzdl_reportTableWrap">
            <table class="ggzdl_reportTable" cellpadding="0" cellspacing="0">
                <thead>
                <tr >
                    <td style="text-align:left; padding-left:40px;" >时间</td>
                    <td>成单量</td>
                    <td>当前收益</td>
                </tr>
                </thead>
                <tbody>
                <tr  v-if="tableData.length" v-for="t in tableData">
                    <td style="text-align:left; width:30%; padding-left:40px;">
                   @{{t.add_time}}</a>
                    </td>
                    </td>
                    <td>@{{t.number|numberFormat}}</td>
                    <td>@{{t.account|numberFormat}}</td>
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
            Vue.filter("numberFormat",function(value){
                return Number(Number(value).toFixed(2)).toLocaleString('en-US');
            });
            Vue.filter("decodeFormat",function(value){
                return decode(value);
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
                el:"#spreadDataDetail",
                data:{
                    title:"",
                    name:"",
                    uid:"",
                    sum_account:"",
                    channelpercent:"",
                    sum_count:"",
                    tableData:[],
                    page:1,
                    page_count:"",

                },
                created:function(){
                var _this=this;               
                this.getTableData();
            },
            mounted:function(){
                    var _this=this;
                    // this.uid=this.getQueryVariable('spreadid');
                    this.title=decodeURI(this.getQueryVariable("title"));
                    this.$nextTick(function() {
                        document.getElementById("spreadDataDetail").style.display = "block";
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
                    toPage:function(page){
                        this.page=page;
                        this.getTableData();
                    },
                    getTableData:function(){
                        var _this=this;
                        $.ajax({
                        url:'{{route('s_user_getSpreadDataDetail')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            spreadid:_this.getQueryVariable('spreadid'),
                            page:_this.page,
                            pagesize:10
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.name=res.data.name;
                                _this.tableData=res.data.data;
                                _this.sum_account=res.data.sum_account;
                                _this.channelpercent=res.data.channelpercent;
                                _this.sum_count=res.data.sum_count;
                                _this.page=res.data.page;
                                _this.page_count=res.data.page_count;
                            }else if(res.status==403){
                                location.reload();
                            }
                        }
                    });
                    }
                }
                
            })
        })
    </script>
@endsection

