@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}
    <style>
        .ggzdl_reportTable tr td{text-align:right;}
        .ggzdl_reportTable tr td:nth-child(1){text-align:left;padding-left:40px;}
        .ggzdl_reportTable tr td:nth-child(4){padding-right:40px;}
    </style>
@endsection

@section("content")
<div id="spreadData" style="display:none">
    <div class="ggzdl_reportWrap">
        <div class="spreadstate">
            <div>此处数据为直接分享到第三方平台的数据，用户同步或复制到公微的数据暂时无法获取</div>
        </div>
        <div class="ggzdl_reportTableWrap">
            <table class="ggzdl_reportTable" cellpadding="0" cellspacing="0" v-if="tableData.length">
                <colgroup>
                    <col width="33%">
                    <col width="12%">
                    <col width="25%">
                    <col width="30%">
                </colgroup>
                <thead>
                <tr >
                    <td onclick="location.href='{{route('s_user_spreadDataDetail')}}'">标题</td>
                    <td>用户浏览</td>
                    <td>订单量</td>
                    <td>收益金额</td>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(t,index) in tableData" :class="{firstTd:index==0}">
                    <td>
                    <p style="text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;"> @{{t.name|nameString}}</p>
                    </td>
                    <td>@{{t.showNums|numberFormat1}}</td>
                    <td>@{{t.number}}</td>
                    <td>@{{t.commission_account|numberFormat}}</td>
                    <!-- <td class="ppp" style="width: 13%;text-align:center;position:relative;padding-left: 25px;" >@{{t.commission_account|numberFormat}}
                        <img class="image_sp" src="{{('/image/spreads.png')}}">
                        <div class="tableHover">该商品需广告主确认后统一结算 结算时间为：2018.05.01</div>
                    </td> -->
                </tr>
                </tbody>
            </table>
            <div class="nowithData"  v-if="!tableData.length">
                <img src="/image/nullpic.jpg" alt="">
                <p>你还没有相关数据，<a style="color:#005dd1;" href="{{route('s_goods_lists')}}">写作</a>或<a style="color:#005dd1;" href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
            </div>
        </div>

        <div v-if="page_count!=0" class="zmtdl_pageWrap">
            <ul class="zmtdl_page">
                <li class="first" disabled="true" :class="{'disabled':page==1}" @click="toPage(page==1 ? 1 : page-1)">
                  上一页
                </li>
                <li v-for="(value,index) in showPages" :key="index" :class="{on:value===page}">
                  <a v-if="value" @click="toPage(value)">@{{value}}</a>
                  <span v-else>...</span>
                </li>
                <li class="last" :class="{'disabled':page==page_count}" @click="toPage(page==page_count ? page_count : page+1)">下一页</li>
            </ul>
        </div>
        
    </div>
</div>

@endsection



@section("script")
<script type="text/javascript">
        $(function(){
            Vue.filter("nameString",function(s){
                if (s.length>18) {
                    return s.substr(0,18)+'...';
                } else {
                    return s;
                }
                
            });
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
            Vue.filter("numberFormat1",function(s){
                var result = '', counter = 0;
                s = (s || 0).toString();
                for (var i = s.length - 1; i >= 0; i--) {
                    counter++;
                    result = s.charAt(i) + result;
                    if (!(counter % 3) && i != 0) { result = ',' + result; }
                }
                return result;

        });
            var app=new Vue({
                el:"#spreadData",
                data:{
                   tableData:[],
                   page:1,
                   page_count:""
                },
                created:function(){
                        var _this=this;
                        this.getTableData();
                },
                computed:{
                    showPages:function(){
                        let pageNumber = this.page_count;
                        let index = this.page;
                        let arr = [];
                        if( pageNumber <=5 ){
                            for ( let i=1; i <= pageNumber; i++ ) {
                            arr.push(i);
                            }
                            console.log(arr);
                            return arr;
                        }
                        if (index <= 2){
                            return [1,2,3,0,pageNumber];
                        }

                        if (index >= pageNumber - 1) {
                            return [1,0,pageNumber-2,pageNumber-1,pageNumber];
                        }

                        if (index >= pageNumber -2) {
                            return [1,0,pageNumber-3,pageNumber-2,pageNumber-1,pageNumber]
                        }

                        if (index == 3){
                            return [1,2,3,4,0,pageNumber];
                        }

                        return [1,0,index-1,index,index+1,0,pageNumber];
                    }
                },
                mounted:function(){
                    var _this=this;
                    this.uid=this.getQueryVariable('spreadid');
                    this.$nextTick(function() {
                        document.getElementById("spreadData").style.display = "block";
                    });

                },
                methods:{
                    toPage:function(page){
                        this.page=page;
                        this.getTableData();
                    },
                    getQueryVariable:function(variable){
                        var query = window.location.search.substring(1);
                        var vars = query.split("&");
                        for (var i=0;i<vars.length;i++) {
                                var pair = vars[i].split("=");
                                if(pair[0] == variable){return pair[1];}
                        }
                        return(false);
                    },
                    getTableData:function(){
                        var _this=this;
                        $.ajax({
                        url:'{{route('s_user_getSpreadData')}}',
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
                                _this.tableData=res.data.data;
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

