@extends('User.layout')


@section("title", "好友邀请")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}
    <style>
        .ggzdl_reportTable td{text-align:left!important;padding:10px 40px!important;box-sizing:border-box;}
        .ggzdl_reportTable thead td{border:0;}
    </style>
@endsection

@section("content")
<div id="inviteDate" style="display:none">
    <!-- <h3 class="ggzdl_admin_title">好友邀请</h3> -->
    <div class="ggzdl_admin_zhuans">
        <div class="zhuans_lt fl">
            <div class="zhuans_ewm">
                <p style="margin-bottom:21px;">方式一</p>
                <p>好友通过你分享的二维码注册，获得收益时</p><p>你也会得到相应的收益</p>
                <div class="qrcode" id="inviteCode"><img class="qrlogo" src="{{\Auth::getUser()->getHeadImgurl()}}" alt=""></div>
            </div>
        </div>
        <div class="zhuans_rt fr">
            <p style="margin-bottom:21px;">方式二</p>
            <p>好友通过您的专属链接注册，获得收益时</p><p>你也会得到相应的收益</p>
            <div class="pgy_http_href">
                <span id="shareUrl">{!! route("s_recommend_registration", ['code'=>$code]) !!}</span>
                <span id="shareUrl1" style="display: none">{!! $fxurl !!}</span>
                <i class="pgy_invite_icon">复制链接</i>
            </div>
        </div>
    </div>
    <div class="zmtdl_userData">
        <span>累计邀请人数</span>@{{count}}
        <!-- <div class="zmtdl_userDataBox3"><span>我的收益</span><br>@{{sumTotal|numberFormat}}</div> -->
    </div>

    <div class="ggzdl_reportWrap">
        <div class="ggzdl_reportTableWrap">
            <table class="ggzdl_reportTable" cellpadding="0" cellspacing="0" v-if="tableData.length">
                <colgroup>
                    <col width="51%">
                    <col width="49%">
                </colgroup>
                <tbody>
                    <tr>
                        <td style="color:#373737;font-size:16px;">注册时间</td>
                        <td style="color:#373737;font-size:16px;">用户</td>
                    </tr>
                    <tr v-for="t in tableData">
                        <td>@{{t.add_time|timeFormat}}</td>
                        <td>@{{t.nickname}}</td>
                    </tr>

                </tbody>
            </table>
            <div class="nowithData" v-if="!tableData.length">
                <img src="/image/nullpic.jpg" alt="">
                <p>你还没有相关数据，被邀请人注册后会出现在这里</p>
            </div>
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
    <script type="text/javascript" src="/js/qrcode.min.js"></script>
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
                el: '#inviteDate',
                data: {
                    count:"",
                    sumTotal:"",
                    page:1,
                    page_count:"",
                    tableData:[]

                },
                created:function(){
                    var _this=this;
                    this.getNumber();
                },
                mounted:function(){
                    var _this=this;                    
                    this.$nextTick(function() {
                        document.getElementById("inviteDate").style.display = "block";
                        //生成qr 图片
                        var inviteCode = new QRCode(document.getElementById('inviteCode'), {
                            width: 116,
                            height: 116,
                        });
                        console.log($('#shareUrl1').html());
                        inviteCode.makeCode($('#shareUrl1').html());
                    });
                    var clipboard = new ClipboardJS('.pgy_http_href', {
                        target: function() {
                            return document.getElementById("shareUrl");
                        }
                    });
                    clipboard.on('success', function(e) {
                        //console.log(e);
                    });
                    clipboard.on('error', function(e) {
                        //console.log(e);
                    });
                    
                },
                methods:{
                    getNumber:function(){
                        var _this=this;
                         $.ajax({
                            url:'{{route('s_user_getInviteFriend')}}',
                            type:'POST',
                            async:true,
                            data:{
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
                                    _this.count=res.data.count;
                                    _this.sumTotal=res.data.sumTotal;
                                    _this.tableData=res.data.data;
                                    _this.page=res.data.page;
                                    _this.page_count=res.data.page_count;
                                }else if(res.status==403){
                                    location.reload();
                                }
                            }
                        })

                    }
                }
            })
        });

    </script>
@endsection

