@extends('User.layout')


@section("title", "佣金提现")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="balanceFlow" style=" display:none;">
    <div class="zmtdl_admin_condition">
        <ul class="zmtdl_admin_day">
            <li :class="{on:tabStatus}" @click="tabSwitch('withdraw')">提现</li>
            <li :class="{on:!tabStatus}" @click="tabSwitch('balance')">余额记录</li>
        </ul>
    </div>
    <div class="zmtdl_towithdrawWrap">
        <div class="zmtdl_balanceandbank">
            <div class="zmtdl_balanceandbank_box" style=" border-right: 1px solid #eaeaea;">
                <span class="zmtdl_balanceandbank_ye">余额</span>&nbsp;&nbsp;&nbsp;<span class="zmtdl_balanceandbank_num">@{{canwithdraw|numberFormat}}</span>
            </div>
            <div class="zmtdl_balanceandbank_box">
                <span v-if="isBind" class="zmtdl_balanceandbank_bank">@{{bankname}}（@{{banknumber}}）</span>
                <span class="zmtdl_balanceandbank_setup"><a style="font-size: 16px; color: #373737; text-decoration: none;" href="{{route('s_user_accountSetting')}}?bank=show">设置</a></span>
            </div>
        </div>

        <div v-show="tabStatus" class="zmtdl_towithdraw_form">
            <div v-if="isBind" style="position:relative">
                <div style=" overflow:hidden;">
                    <input class="zmtdl_towithdraw_input" type="number" min="0" placeholder="提现金额" @input="accountChange" v-model="account">
                    <span class="zmtdl_towithdraw_full" @click="all" style="cursor:pointer;">全部</span>
                </div>
                <div class="zmtdl_towithdraw_cost">实际到账：@{{suremoney|numberFormat1}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;手续费：3.00</div>
                <i class="canwithI" style="display:none">提现金额小于100</i>
                <i class="accountErronx" style="display:none">超出可提现余额</i>
                <div style="margin: 10px 0px;">
                    <input type="text" placeholder="请输入验证码" class="zmtdl_accountSetupBank_code" v-model="smsCode">
                    <input type="button" v-model="validCode" @click="sendMsg" class="zmtdl_accountSetupBank_codebtn" :disabled="disabled">
                </div>
                <!--<div class="zmtdl_towithdraw_radio" style="margin: 10px 0 0 0;"><input type="radio" name="time" checked="checked"><label>T+0到账 （手续费2元，当天24:00前到账）</label></div>
                <div class="zmtdl_towithdraw_radio"><input type="radio" name="time"><label>T+1到账 （手续费5元，下个工作日24:00到账）</label></div>-->
                <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;" v-html="errMsg">@{{errMsg}}</div>                
                <input class="zmtdl_towithdraw_btn" @click="toWithdrawal" type="button" value="确 定">
            </div>
            <div v-else style="line-height:100px; text-align:center; font-size:30px;">
                暂未绑定银行卡
            </div>
        </div>

        <div v-show="!tabStatus" class="zmtdl_reportWrap">
            <div class="zmtdl_reportTableWrap">
                <table class="zmtdl_reportTable" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <td style="text-align:left; padding-left:40px;">日期</td>
                        <td>订单号</td>
                        <td>类型</td>
                        <td>状态</td>
                        <td>金额</td>
                        <td>余额</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="t in tableData">
                        <td style="text-align:left; padding-left:40px;">@{{t.add_time|timeFormat}}</td>
                        <td>@{{t.order_number}}</td>
                        <td>@{{t.commission_name}}</td>
                        <td>@{{t.status_show}}</td>
                        <td>@{{t.account|numberFormat}}</td>
                        <td>@{{t.available_amount|numberFormat}}</td>
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
            Vue.filter("numberFormat1",function(s){
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
                m = date.getMinutes() < 10 ? '0'+ date.getMinutes()+':' : date.getMinutes() +':';
                s = date.getSeconds() < 10 ? '0'+ date.getSeconds() : date.getSeconds();
                return Y+M+D+h+m+s;
            });

            var app = new Vue({
                el: '#balanceFlow',
                data: {
                    tabStatus:true,
                    canwithdraw:"",
                    total_unsetted_commission:"",
                    tableData:[],
                    smsCode:"",
                    banknumber:"",
                    bankname:"",
                    bank_id:"",
                    account:"",
                    validCode:"发送验证码",
                    times:60,
                    isBind:0,
                    page:1,
                    page_count:"",
                    errMsg:"",
                    disabled:false,
                    suremoney:""
                },
                created:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_user_getAccountSetting')}}',
                        type:'POST',
                        async:true,
                        data:{

                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.isBind=res.data.isBind;
                                if (_this.isBind===1) {
                                    _this.bank_id=res.data.id;
                                    _this.banknumber=res.data.banknumber;
                                    _this.bankname=res.data.BankName;
                                    _this.banknumber=_this.banknumber.substr(_this.banknumber.length-4);
                                }
                            }
                        }
                    });
                    this.getTableData();
                },
                mounted:function(){
                    var _this=this;
                    this.$nextTick(function() {
                        document.getElementById("balanceFlow").style.display = "block";
                    });
                },
                methods:{
                    accountChange:function(){
                        if(this.account<0){
                            this.account=0;
                        }
                        if(Number(this.account)>Number(this.canwithdraw)){
                            $('.accountErronx').show();
                        }else{
                            $('.accountErronx').hide();
                        }
                        if(this.account<100){
                            $('.canwithI').show();
                        }else{
                            $('.canwithI').hide();
                        }
                        this.suremoney=this.account-3;
                    },
                    all:function(){
                        this.account=this.canwithdraw.substr(0,this.canwithdraw.length-2);
                        this.suremoney=this.account-3;
                    },
                    toPage:function(page){
                        this.page=page;
                        this.getTableData();
                    },
                    getTableData:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_getUserAccFLowPage')}}',
                            type:'POST',
                            async:true,
                            data:{
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
                    },
                    tabSwitch:function(status){
                        status==="withdraw" ? this.tabStatus=true : this.tabStatus=false;
                    },
                    sendMsg:function(){
                        var _this=this;
                        if(_this.account>=100 && _this.account<=_this.canwithdraw){
                            $.ajax({
                            url:'{{route('s_sms_sendDrawSms')}}',
                            type:'POST',
                            async:true,
                            data:{
                                type:4
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    var countStart = setInterval(function () {
                                        _this.validCode = _this.times-- + '秒后重发';
                                        _this.disabled=true;
                                        if (_this.times < 0) {
                                            clearInterval(countStart);
                                            _this.validCode = "发送验证码";
                                            _this.times=60;
                                            _this.disabled=false;
                                            
                                        }
                                    }, 1000);
                                }else{
                                    _this.errMsg=res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);  
                                }
                            }
                        });
                          
                        }else{
                           
                        }
                       
                    },
                    toWithdrawal:function(){
                        var _this=this;
                        if(_this.account>=100 && _this.account<=_this.canwithdraw){
                            $.ajax({
                            url:'{{route('s_sms_validatorDrawSms')}}',
                            type:'POST',
                            async:true,
                            data:{
                                code:_this.smsCode
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    $.ajax({
                                        url:'{{route('s_draw_withdrawalApplication')}}',
                                        type:'POST',
                                        async:true,
                                        data:{
                                            account:_this.account,
                                            bank_id:_this.bank_id
                                        },
                                        "headers": {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        dataType:'json',
                                        success:function(res){
                                            if(res.status==200) {                                                
                                                _this.errMsg="提现申请成功！<br/>预计到账时间0-1个工作日";
                                                setTimeout(function(){
                                                    _this.errMsg="";
                                                    location.reload();
                                                }, 3000);
                                            }else{
                                                _this.errMsg=res.message;
                                                setTimeout(function(){
                                                    _this.errMsg="";
                                                }, 3000);                                                
                                            }
                                        }
                                    });
                                }else{
                                    _this.errMsg=res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);  
                                }
                            }
                        });
                           
                        }else{
                          
                        }
                       

                    }                    
                }
            })
        });

    </script>
@endsection

