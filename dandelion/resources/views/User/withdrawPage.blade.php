@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}
    {{--<link rel="stylesheet" href="/laydate/theme/default/laydate.css">--}}
@endsection

@section("content")
<div id="withdrawPage" style=" display:none;">
    <!-- 弹框 -->
    <div class="ggzdl_popup_wrap" v-show="popupFlag">
        <div class="ggzdl_popup_box" v-show="popupDiv==='withdraw'">
            <span class="popup_close" @click="closePopup2"></span>
            <div class="popup_title">提现申请</div> 
            <div class="popup_successed">
                <img src="/image/oksuccess.png" alt="">
                <p>提现申请提交成功</p>
                <p>预计到帐时间0-1个工作日</p>
            </div>
            <div class="popup_btn" @click="closePopup2">完成</div>
        </div>
        <div class="ggzdl_popup_box" v-show="popupDiv==='untieBank'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">解绑银行卡</div> 
            <div class="popup_successed" style="width:240px;">
                <img src="/image/errorbink.png" alt="">
                <p>解绑银行卡后将无法使用提现业务</p>
                <p>您确认要解绑吗?</p>
            </div>
            <div class="popup_confirm">
                <span @click="closePopup">取消</span><span @click='showPopupDiv("unBanksendcode")'>确认</span>
            </div>
        </div>
        <div class="ggzdl_popup_box" v-show="popupDiv==='unBanksendcode'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">解绑银行卡</div> 
            <div class="popup_sendcode">
                <p>为确认是您本人操作，请先认证手机号码</p>
                <p>手机号码&nbsp;&nbsp;&nbsp; @{{mobile|mobileFormat}}</p>
                <div style="width:100%;position:relative;">
                    <input class="popup_accountSetupBank_code" type="text" placeholder="输入验证码" v-model="code" style="float:left">
                    <input class="popup_accountSetupBank_codebtn" type="button" :disabled="disabled2"  style="right" v-model="validCode2" @click="sendMsgs(3)" style="float:right;">
                    <span class="error_msg" v-html="errMsg">@{{errMsg}}</span>
                </div>
                <div class="popup_nexts" @click="unBindBank">下一步</div>
            </div>
           
        </div>
        <div class="ggzdl_popup_box" v-show="popupDiv==='unBanksuccess'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">解绑银行卡</div> 
            <div class="popup_successed">
                <img src="/image/oksuccess.png" alt="">
                <p>解绑成功</p>
            </div>
            <div class="popup_btn" onclick="location.href='{{route('s_user_withdrawPage')}}'">完成</div>
           
        </div>
        <div class="ggzdl_popup_box" v-show="popupDiv==='Banksuccess'">
            <span class="popup_close" @click="closePopup2"></span>
            <div class="popup_title">绑定结果</div> 
            <div class="popup_successed">
                <img src="/image/oksuccess.png" alt="">
                <p>恭喜您绑定成功</p>
            </div>
            <div class="popup_btn" @click="closePopup2">确定</div>
        </div>

        <div class="ggzdl_popup_box popup_box_renz" v-show="popupDiv==='goToRenzheng'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">实名认证</div> 
            <div class="popup_con">
                <p class="renz_msg">实名认证后才可绑定银行卡哦<br>请先实名认证</p>
                <div class="rez_text">@{{mobile|mobileFormat}}</div>
                <div class="renz_code">
                    <input class="renz_code_input" type="text" placeholder="输入验证码" v-model="code" maxlength="4" @blur="checkCode">
                    <input class="renz_code_btn" type="button" value="发送验证码" :disabled="disabled" v-model="validCode" @click="sendRenzmsg">
                    <span class="error_msg" v-html="errMsg">@{{errMsg}}</span>
                </div>
                <div class="renz_input">
                    <i class="pgy_input_name"></i>
                    <input type="text" placeholder="姓名" v-model="realname" @blur="checkName">
                    <span class="error_msg" v-html="errName">@{{errName}}</span>
                </div>
                <div class="renz_input">
                    <i class="pgy_input_sf"></i>
                    <input type="text" placeholder="身份证号码" v-model="idCard" @blur="checkIdentity">
                    <span class="error_msg" v-html="errIdentity">@{{errIdentity}}</span>
                </div>
                <div class="popup_btn" @click="renzSubmit">认证</div>
            </div>
        </div>
        <div class="ggzdl_popup_box" v-show="popupDiv==='renZSuccess'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">实名认证</div> 
            <div class="popup_renresult">
                <img src="/images/renz_suc.png" alt="">
                <h3>恭喜您认证成功</h3>
            </div>
            <div class="popup_btn" @click="tobandcards">绑定银行卡 （@{{renzTime}}S）</div>
        </div>
        <div class="ggzdl_popup_box" v-show="popupDiv==='renZFail'" style="height:380px;margin-top:-190px;">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">实名认证</div> 
            <div class="popup_renresult">
                <img src="/images/renz_fail.png" alt="">
                <h3>身份信息匹配失败</h3>
                <p>若确认身份无误，可以联系客服邮箱</p>
                <p><a href="mailto:hi@pugongying.link">hi@pugongying.link</a>工作人员核实后帮您处理</p>
            </div>
            <div class="popup_btn" style="margin-top:30px;" @click="popupDiv='goToRenzheng'">重新输入</div>
        </div>

    </div>

    <div  class="withgetMoney" v-show="withgetMoney">
    
        <div class="ggzdl_reportWrap" style="overflow: hidden" >
            <div class="bread_top">
                <div>
                <a href="{{route('s_user_accountInfo')}}">账户总览</a>&nbsp;&gt;&nbsp;<span class="on" >我要提现</span>
                <a href="javascript:window.history.go(-1);" style="float:right;">返回</a>
                </div>
            </div>
            <div class="withdrawPage_right" >
                <div class="widthOper_left">
                    <div class="withdrawAccount">
                        <span>可提现金额</span><i style="padding-left:33px;">@{{available_amount|accountFormat}}</i>
                    </div>
                    <div class="widthdrwaPage_bank">
                        <span>银行卡</span>
                        <div style="padding-left:33px;" v-if="isBind===1">
                            <div class="ggzdl_accountSetupBankBind" :class="bankbg">
                                <span class="ggzdl_accountSetupBankName">@{{bankname}}</span>
                                <span class="ggzdl_accountSetupBankNum">****&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;****&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;****&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;@{{banknumber|bankFormat}}</span>
                                <span class="ggzdl_accountSetupBankUser">持卡人：@{{realname|nameFormat}}</span>
                            </div>
                            <div class="zmtdl_accountSetupBankLinkNew"><span @click="unbindBank">解绑银行卡</span> <span @click="changeBank">更换银行卡</span></div>
                        </div>
                        <div class="nowidthcards" v-if="isBind===0">
                            <a href="javascript:void(0)" @click="tobandcards" v-if="isBind2===1"><img src="/image/nowidthcard1.jpg" alt=""></a>
                            <a href="javascript:void(0)" @click="showPopupDiv('goToRenzheng')" v-if="isBind2===0"><img src="/image/nowidthcard1.jpg" alt=""></a>
                        </div>
                        
                    </div>
                    <div class="widthdraw_money">
                        <span>提现金额</span>
                        <div class="widthdraw_tixians">
                            <div style="position:relative">
                            <input class="widthdraw_accountSetupBank_code" type="number" min="0"   placeholder="￥" @input="accountChange" v-model="account">                           
                                <span class="accountErrons " style="display:none">提现金额小于100</span> 
                                <span class="accountErronx " style="display:none">超出可提现余额</span>
                                <span class="ggzdl_accountSetupBank_tishi" @click="all" style="cursor:pointer;">全部提现</span>
                            </div>
                            <div class="widthdraw_tocost">手续费：3.00元&nbsp;&nbsp;&nbsp;到账：@{{suremoney|numberFormat1}}元</div>
                        </div>
                    </div>
                    <div class="widthdrawMobile">
                        <span>手机号</span>
                        <div style="width: 340px;float: right;">@{{mobile|mobileFormat}}</div>
                    </div>
                    <div class="widthdrawcodes">
                        <span>手机验证码</span>
                        <div class="widthdraw_fencodes">
                                <input class="width_accountSetupBank_code" type="text" placeholder="输入验证码" v-model="smsCode">
                                <input v-if="isBind===1" class="zmtdl_accountSetupBank_codebtn" type="button" @click="sendMsg" value="发送验证码" :disabled="disabled" v-model="validCode" style="float:right">
                                <input v-if="isBind===0" class="zmtdl_accountSetupBank_codebtn" type="button" value="发送验证码"   style="float:right;background:#7E7E7E;outline:none" @click="showBankError">
                                <span class="error_msg" v-html="errCode">@{{errCode}}</span>
                        </div>
                    </div>  
                </div>
                <div class="widthOper_right">
                    <div class="widthOper_titles">收益提现说明</div>
                    <div>
                        <p>1、提现要求：提现金额需大于100元</p><p> 2、提现到账：16:00之前申请提现，当天到账，16:00之后申请提现，次日到账，如遇节假日顺延</p>
                        <p>3、提现手续费：每笔3元</p>
                    </div>
                </div>
                <div class="widthOper_next" @click="toWithdrawal">
                    下一步
                </div>
            </div>
            
        </div>
    </div>
    <div class="widthbankcards" v-show="!withgetMoney">
    <!-- 绑定银行卡 -->
        <div class="ggzdl_reportWrap" style="overflow: hidden" >
            <div class="bread_top">
                <div>
                <a href="{{route('s_user_accountInfo')}}">账户总览</a>&nbsp;&gt;&nbsp;<a href="" >我要提现</a>&nbsp;&gt;&nbsp;<span class="on" >绑定银行卡</span>
                <a href="" style="float:right;" @click="abandLists">返回</a>
                </div>
            </div>
            <div class="withdraw_banks">
                <div style="overflow:hidden">
                    <label for="">姓名</label>
                    <div class="withWrited">@{{realname|nameFormat}}</div>
                </div>
                <div  style="overflow:hidden">
                    <label for="">身份证号</label>
                    <div class="withWrited">@{{idCard|idNumFormat}}</div>
                </div>
                <div >
                    <label for="">银行卡号</label>
                    <div class="withWrited">
                        <div class="input_withwraps" style="position:relative;">
                                <i class="width_input_snum"></i>
                                <input id="text" class="width_pgy_input2" v-model="bindBankObj.bind_banknumber"  @blur="luhnCheck" placeholder="银行卡号">
                                <span class="error_msg" v-html="errCard">@{{errCard}}</span>
                            </div>
                    </div>
                    
                </div>

                <div>
                    <label for="">开户行</label>
                    <div class="withWriteds">
                        <div class="with_accountSelectBank">
                            <input  type="text" placeholder="选择开户银行" v-model="bankSelect.bank_name" readonly="readonly">
                            <div class="bans_support_bank"><span></span>
                                <ul class="bans_bank_ul">
                                    <li v-for="(a,k) in bankNameArr" @click="chooseBank(k+1,a.sub_branch_id,a.name)">@{{a.name}}</li>
                                </ul>
                            </div>
                            <span class="error_msg" v-html="errBank">@{{errBank}}</span>
                        </div> 
                    </div>
                    
                </div>
                <div>
                    <label for="">开户行支行</label> 
                    <div class="bankArea">
                        <div class="bankCity" @mouseenter="provinceFlag=true" @mouseleave="provinceFlag=false">
                            <input style="height:43px;" type="text" placeholder="省" readonly="readonly" v-model="bankSelect.province_name">
                            <div class="city_support_bank"><span></span>
                            <ul class="city_bank_ul" v-show="provinceFlag">
                                <li v-for="(v,k) in provinceArr" @click="chooseProvince(k,v)">@{{v}}</li>
                            </ul>   
                            </div>
                        </div>
                        <div class="bankCountry" @mouseenter="cityFlag=true" @mouseleave="cityFlag=false">
                            <input style="height:43px;" type="text" placeholder="市"  readonly="readonly" v-model="bankSelect.city_name">
                            <div class="area_support_bank"><span></span>
                            <ul class="area_bank_ul" v-show="cityFlag">
                                <li v-for="(v,k) in cityArr" @click="chooseCity(k,v)">@{{v}}</li>
                            </ul>
                            </div>
                        </div>
                        <div class="bankContent" @mouseenter="subBankFlag=true" @mouseleave="subBankFlag=false">
                            <input style="height:43px;" type="text" placeholder="支行" v-model="bankSelect.sub_name" @keyup="getBankSelect(3)">
                            <div class="area_support_bank"><span></span>
                            <ul class="sub_bank_ul" v-show="subBankFlag">
                                <li v-for="(v,k) in subBankArr" @click="chooseSubBank(k,v)">@{{v}}</li>
                            </ul>
                            </div>
                        </div>
                        <span class="error_msg" v-html="errSubBank">@{{errSubBank}}</span>
                    </div>
                </div>
                <div>
                    <label for="">手机号码</label>                    
                    <div class="withWrited"> @{{mobile|mobileFormat}}</div>
                </div>
                <div>
                    <label for="">短信验证码</label> 
                    <div class="withWrited">
                    <div class="withdraw_send_code">
                        <input type="tel" maxlength="4" placeholder="输入验证码" class="with_vcodeinput" v-model="code"> 
                        <input type="button" class="widthdraws_vcodeBtn" value="发送验证码"  @click="sendMsgs(9)" :disabled="disabled2" v-model="validCode2"></div>
                        <span class="error_msg" v-html="errMsg">@{{errMsg}}</span>
                    </div>
                </div>
                <input type="button" value="绑定" class="widthdraw_send" @click="bindBankName">
            </div>
        </div>
    </div>
</div>


@endsection

@section("script")
    <script type="text/javascript" src="/js/validateCardInfo.js"></script>
    <script type="text/javascript">
        $(function(){
            Vue.filter("numberFormat",function(value){
                return Number(value.substr(0,value.indexOf(".")+3)).toLocaleString('en-US');//截取2位小数，千分位
            });
            Vue.filter("mobileFormat",function(value){
                if(value){
                    return value.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2');
                }else{
                    return value;
                }
            });
            Vue.filter("timeFormat",function(value){
                var date = new Date(value * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
                Y = date.getFullYear() + '-';
                M = (date.getMonth()+1 < 10 ? '0' + (date.getMonth()+1) : date.getMonth()+1) + '-';
                D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
                h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
                m = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':';
                s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
                return Y+M+D+h+m+s;
            });
            Vue.filter("idNumFormat",function(value){
                if(value){
                    return value.substr(0,3)+"***********"+value.substr(value.length-4);
                }else{
                    return value;
                }
            });
            Vue.filter("nameFormat",function(value){
                if(value){
                    return value.substr(0,1)+"**";
                }else{
                    return value;
                }
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

            Vue.filter("bankFormat",function(value){
                return value.substr(value.length-4);
            });
            Vue.filter("accountFormat",function(value){
                return value.substr(0,value.length-2);
            });
            var app=new Vue({
                el:'#withdrawPage',
                data:{
                    account:"",
                    smsCode:"",
                    validCode:"发送验证码",
                    disabled:false,
                    validCode2:"发送验证码",
                    disabled2:false,
                    banknumber:"",
                    realname:"",
                    times:60,
                    times2:60,
                    errMsg:"",
                    errCode:"",
                    errName:"",
                    errIdentity:"",
                    bankname:"",
                    bank_id:"",
                    isBind:0,//银行卡是否绑定
                    isBind2:0,//是否实名
                    available_amount:"",
                    isClick: 0,
                    bankbg:"",
                    isReadOnly:true,
                    mobile:"",
                    idCard:"",
                    code:"",
                    accountBank:{
                        bankStatus:"bindBank",
                        bankStatusName:"绑定银行卡"
                    },
                    suremoney:"",
                    popupFlag:false,
                    popupDiv:"",
                    bid:"",
                    bankcard:"",
                    bankName:"",
                    bankNameArr:[],//银行卡列表
                    provinceArr:[],//省列表
                    cityArr:[],//市列表
                    subBankArr:[],//支行列表
                    bindBankObj:{
                        bind_banknumber:""
                    },
                    errCard:"",
                    errMobile:"",
                    errBank:"",
                    errSubBank:"",
                    withgetMoney:true,
                    bankSelect:{
                        id:0,
                        bank_id:0,
                        bank_name:'',
                        city_id:0,
                        city_name:"",
                        province_id:0,
                        province_name:"",
                        sub_id:0,
                        sub_name:""                  
                    },
                    provinceFlag:false,
                    cityFlag:false,
                    subBankFlag:false,
                    getMoneyFlag:false,
                    renzTime:3,
                    countStart:""
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
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.isBind=res.data.isBind;
                                _this.mobile=res.data.sendMobile;
                                if (_this.isBind===1) {
                                    _this.accountBank.bankStatus="bindBank";
                                    _this.bank_id=res.data.id;
                                    _this.bid=res.data.id;
                                    _this.banknumber=res.data.banknumber;
                                    _this.bankname=res.data.BankName;
                                    // _this.banknumber=_this.banknumber.substr(_this.banknumber.length-4);
                                    switch (_this.bankname) {
                                        case "工商银行":
                                            _this.bankbg="gsyh";
                                            break;
                                        case "招商银行":
                                            _this.bankbg="zsyh";
                                            break;
                                        case "光大银行":
                                            _this.bankbg="gdyh";
                                            break;
                                        case "交通银行":
                                            _this.bankbg="jtyh";
                                            break;
                                        case "中信银行":
                                            _this.bankbg="zxyh";
                                            break;
                                        case "农业银行":
                                            _this.bankbg="nyyh";
                                            break;    
                                        case "建设银行":
                                            _this.bankbg="jsyh";
                                            break;
                                        case "兴业银行":
                                            _this.bankbg="xyyh";
                                            break;
                                        case "中国银行":
                                            _this.bankbg="zgyh";
                                            break;
                                        case "邮政储蓄银行":
                                            _this.bankbg="yzcxyh";
                                            break;                 
                                        default:
                                            break;
                                    }


                                }else {
                                    _this.accountBank.bankStatus="noBindBank";
                                }
                            }else if(res.status==403){
                                location.reload();
                            }
                        }
                        
                    });
                    //  this.all();
                    $.ajax({
                            url:'{{route('s_user_getBalacnce')}}',
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
                                    _this.available_amount=res.data.available_amount;
                                }
                            }
                        });
                    
                    _this.getUserBank();
                    // _this.tobandcards();
                    _this.getAuthInfo();
                },
                mounted:function(){
                    var _this=this;
                    this.$nextTick(function() {
                        document.getElementById("withdrawPage").style.display = "block";
                        //_this.showPopupDiv("renZFail");
                        //_this.getBankSelect();
                        _this.getBankSelect(1);
                    });
                },
                methods:{
                    chooseBank:function(k,id,name){
                        var _this=this;
                        _this.bankSelect.id=k;
                        _this.bankSelect.bank_id=id;
                        _this.bankSelect.bank_name=name;
                        if(_this.bankSelect.city_id!=0){
                            _this.bankSelect.sub_name="";
                            _this.getBankSelect(3);
                        }
                    },
                    chooseProvince:function(id,name){
                        var _this=this;
                        _this.bankSelect.province_id=id;
                        _this.bankSelect.province_name=name;
                        _this.bankSelect.sub_name="";
                        _this.bankSelect.city_id=0;
                        _this.bankSelect.city_name="";
                        _this.getBankSelect(2);
                        
                    },
                    chooseCity:function(id,name){
                        var _this=this;
                        _this.bankSelect.city_id=id;
                        _this.bankSelect.city_name=name;
                        if(_this.bankSelect.bank_id!=0){
                            _this.bankSelect.sub_name="";
                            _this.getBankSelect(3);
                        }
                    },
                    chooseSubBank:function(id,name){
                        var _this=this;
                        _this.bankSelect.sub_id=id;
                        _this.bankSelect.sub_name=name;
                    },
                    checkBankName:function(){
                        var _this=this;
                        if ( !_this.bankSelect.bank_name ) {
                            this.errBank="<i></i>请选择开户银行";
                            setTimeout(function(){
                                _this.errBank="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    checkSubBank:function(){
                        var _this=this;
                        if ( !this.bankSelect.sub_name ) {
                            this.errSubBank="<i></i>请选择开户行支行";
                            setTimeout(function(){
                                _this.errSubBank="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }   
                    }, 
                    checkCode:function(){
                        var _this=this;
                        if ( this.code.length<4 ) {
                            this.errMsg="<i></i>请输入正确的验证码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }   
                    },
                    checkName:function(){
                        var _this=this;
                        if ( !(/^[\u4e00-\u9fa5]{2,4}$/i.test(this.realname)) ) {
                            this.errName="<i></i>请输入正确格式的姓名";
                            setTimeout(function(){
                                _this.errName="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }
                    },
                    checkIdentity:function(){
                        var _this=this;
                        if ( !(/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(this.idCard)) ) {
                            this.errIdentity="<i></i>请输入正确格式的身份证号";
                            setTimeout(function(){
                                _this.errIdentity="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    bandLists:function(){
                        var _this=this;
                        _this.withgetMoney=true;
                       _this.widthbankcards=false;
                    },
                    abandLists:function(){
                        var _this=this;
                        _this.withgetMoney=true;
                       _this.widthbankcards=false;
                    },
                    accountChange:function(){
                        var _this=this;
                        var _account=Number(_this.account);
                        var _available_amount=Number(_this.available_amount);
                        if(_account < 100){
                            $('.accountErrons').show();
                            $('.accountErronx').hide();
                        }else if(_account > _available_amount){
                            $('.accountErronx').show();
                            $('.accountErrons').hide();
                        }else{
                            $('.accountErrons').hide();
                            $('.accountErronx').hide();
                        }
                        _this.suremoney=_this.account-3;
                    },
                    all:function(){
                        var _this=this;
                        _this.account=_this.available_amount.substr(0,_this.available_amount.length-2);
                        if(_this.account<100){                           
                            $('.accountErrons').show();                            
                        }else{
                            $('.accountErrons').hide();
                        }
                        this.suremoney=this.account-3;
                        $.ajax({
                            url:'{{route('s_user_getBalacnce')}}',
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
                                    _this.available_amount=res.data.available_amount;
                                }
                            }
                        });
                         
                        
                    },
                    Close_renz:function(){
                        //  isClick=0;
                        location.reload();

                    },
                    // 点击绑定银行卡
                    tobandcards:function(){
                        var _this=this;
                        _this.withgetMoney=false;
                        _this.closePopup();
                    },
                    sendMsgs:function(type){//发送银行卡验证码
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_sendBankSms')}}',
                            type:'POST',
                            async:true,
                            data:{
                                type:type
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            beforeSend: function () {
                                // 禁用按钮防止重复提交
                                _this.disabled2=true;
                            },
                            success:function(res){
                                if(res.status==200) {
                                    _this.countStart = setInterval(function () {
                                        _this.validCode2 = _this.times2-- + '秒后重发';
                                        _this.disabled2=true;
                                        if (_this.times2 < 0) {
                                            clearInterval(_this.countStart);
                                            _this.validCode2 = "发送验证码";
                                            _this.times2=60;
                                            _this.disabled2=false;
                                        }
                                    }, 1000);
                                }else{
                                    _this.disabled2=false;
                                    _this.errMsg='<i></i>'+res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);
                                }
                            }
                        });                        
                    },
                    sendMsg:function(){
                        var _this=this;
                        var _account=Number(_this.account);
                        var _available_amount=Number(_this.available_amount);
                        if(_account < 100){
                            $('.accountErrons').show();
                            $('.accountErronx').hide();
                            return false;
                        }else if(_account > _available_amount){
                            $('.accountErronx').show();
                            $('.accountErrons').hide();
                            return false;
                        }else{
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
                                beforeSend: function () {
                                    // 禁用按钮防止重复提交
                                    _this.disabled=true;
                                },
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
                                        _this.disabled=false;
                                        _this.errCode='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errCode="";
                                        }, 3000);  
                                    }
                                }
                            });
                        }
                       
                    },
                    getUserBank:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_index_getBankRelative')}}',
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
                                    _this.bankNameArr=res.data;
                                }
                            }
                        });
                    },
                    getAuthInfo:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_getAuthInfo')}}',
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
                                    //console.log(res.data)
                                    _this.isBind2=res.data.isBind;
                                    _this.realname=res.data.realname;
                                    _this.idCard=res.data.id_card;
                                }
                            }
                        });
                    },
                    getBankSelect:function(n){
                        var _this=this;
                        _this.bankSelect.sub_id="";
                        $.ajax({
                            url:'{{route('s_user_bankSelect')}}',
                            type:'POST',
                            async:true,
                            data:{
                                'bank_id':_this.bankSelect.bank_id,
                                'province_id':_this.bankSelect.province_id,
                                'city_id':_this.bankSelect.city_id,
                                'bank_name':_this.bankSelect.sub_name
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    //console.log(n)
                                    //console.log(res.data)
                                    if(n==1){
                                        _this.provinceArr=res.data;
                                    }else if(n==2){
                                        _this.cityArr=res.data;
                                    }else if(n==3){
                                        _this.subBankArr=res.data;
                                    }
                                }
                            }
                        });
                    },
                    luhnCheck:function() {
                        var _this=this;
                        var bankno=_this.bindBankObj.bind_banknumber;
                        //console.log(bankno);
                        var lastNum = bankno.substr(bankno.length - 1, 1); //取出最后一位（与luhn进行比较）
                        var first15Num = bankno.substr(0, bankno.length - 1); //前15或18位
                        var newArr = new Array();
                        for (var i = first15Num.length - 1; i > -1; i--) { //前15或18位倒序存进数组
                            newArr.push(first15Num.substr(i, 1));
                        }
                        var arrJiShu = new Array(); //奇数位*2的积 <9
                        var arrJiShu2 = new Array(); //奇数位*2的积 >9
                        var arrOuShu = new Array(); //偶数位数组
                        for (var j = 0; j < newArr.length; j++) {
                            if ((j + 1) % 2 == 1) { //奇数位
                                if (parseInt(newArr[j]) * 2 < 9) arrJiShu.push(parseInt(newArr[j]) * 2);
                                else arrJiShu2.push(parseInt(newArr[j]) * 2);
                            } else //偶数位
                            arrOuShu.push(newArr[j]);
                        }

                        var jishu_child1 = new Array(); //奇数位*2 >9 的分割之后的数组个位数
                        var jishu_child2 = new Array(); //奇数位*2 >9 的分割之后的数组十位数
                        for (var h = 0; h < arrJiShu2.length; h++) {
                            jishu_child1.push(parseInt(arrJiShu2[h]) % 10);
                            jishu_child2.push(parseInt(arrJiShu2[h]) / 10);
                        }

                        var sumJiShu = 0; //奇数位*2 < 9 的数组之和
                        var sumOuShu = 0; //偶数位数组之和
                        var sumJiShuChild1 = 0; //奇数位*2 >9 的分割之后的数组个位数之和
                        var sumJiShuChild2 = 0; //奇数位*2 >9 的分割之后的数组十位数之和
                        var sumTotal = 0;
                        for (var m = 0; m < arrJiShu.length; m++) {
                            sumJiShu = sumJiShu + parseInt(arrJiShu[m]);
                        }

                        for (var n = 0; n < arrOuShu.length; n++) {
                            sumOuShu = sumOuShu + parseInt(arrOuShu[n]);
                        }

                        for (var p = 0; p < jishu_child1.length; p++) {
                            sumJiShuChild1 = sumJiShuChild1 + parseInt(jishu_child1[p]);
                            sumJiShuChild2 = sumJiShuChild2 + parseInt(jishu_child2[p]);
                        }
                        //计算总和
                        sumTotal = parseInt(sumJiShu) + parseInt(sumOuShu) + parseInt(sumJiShuChild1) + parseInt(sumJiShuChild2);

                        //计算luhn值
                        var k = parseInt(sumTotal) % 10 == 0 ? 10 : parseInt(sumTotal) % 10;
                        var luhn = 10 - k;

                        if ( bankno && lastNum == luhn ) {
                            //console.log("验证通过");
                            var bname=bankCardAttribution(bankno).bankName;
                            if(bankCardAttribution(bankno).bankName){
                                _this.bankNameArr.forEach(function(v,i,arr){
                                    if(v.name==bname){
                                        _this.bankSelect.bank_name=v.name;
                                        _this.bankSelect.id=v.id;
                                        _this.bankSelect.bank_id=v.sub_branch_id;
                                    }
                                });
                            }
                            return true;
                        } else {
                            _this.errCard="<i></i>请输入正确格式的银行卡号";
                            _this.bankSelect.bank_name="";
                            setTimeout(function(){
                                _this.errCard="";
                            }, 3000);
                            return false;
                        }
                    },
                    luhnCheck2:function() {
                        var _this=this;
                        var bankno=_this.bindBankObj.bind_banknumber;
                        //console.log(bankno);
                        var lastNum = bankno.substr(bankno.length - 1, 1); //取出最后一位（与luhn进行比较）
                        var first15Num = bankno.substr(0, bankno.length - 1); //前15或18位
                        var newArr = new Array();
                        for (var i = first15Num.length - 1; i > -1; i--) { //前15或18位倒序存进数组
                            newArr.push(first15Num.substr(i, 1));
                        }
                        var arrJiShu = new Array(); //奇数位*2的积 <9
                        var arrJiShu2 = new Array(); //奇数位*2的积 >9
                        var arrOuShu = new Array(); //偶数位数组
                        for (var j = 0; j < newArr.length; j++) {
                            if ((j + 1) % 2 == 1) { //奇数位
                                if (parseInt(newArr[j]) * 2 < 9) arrJiShu.push(parseInt(newArr[j]) * 2);
                                else arrJiShu2.push(parseInt(newArr[j]) * 2);
                            } else //偶数位
                            arrOuShu.push(newArr[j]);
                        }

                        var jishu_child1 = new Array(); //奇数位*2 >9 的分割之后的数组个位数
                        var jishu_child2 = new Array(); //奇数位*2 >9 的分割之后的数组十位数
                        for (var h = 0; h < arrJiShu2.length; h++) {
                            jishu_child1.push(parseInt(arrJiShu2[h]) % 10);
                            jishu_child2.push(parseInt(arrJiShu2[h]) / 10);
                        }

                        var sumJiShu = 0; //奇数位*2 < 9 的数组之和
                        var sumOuShu = 0; //偶数位数组之和
                        var sumJiShuChild1 = 0; //奇数位*2 >9 的分割之后的数组个位数之和
                        var sumJiShuChild2 = 0; //奇数位*2 >9 的分割之后的数组十位数之和
                        var sumTotal = 0;
                        for (var m = 0; m < arrJiShu.length; m++) {
                            sumJiShu = sumJiShu + parseInt(arrJiShu[m]);
                        }

                        for (var n = 0; n < arrOuShu.length; n++) {
                            sumOuShu = sumOuShu + parseInt(arrOuShu[n]);
                        }

                        for (var p = 0; p < jishu_child1.length; p++) {
                            sumJiShuChild1 = sumJiShuChild1 + parseInt(jishu_child1[p]);
                            sumJiShuChild2 = sumJiShuChild2 + parseInt(jishu_child2[p]);
                        }
                        //计算总和
                        sumTotal = parseInt(sumJiShu) + parseInt(sumOuShu) + parseInt(sumJiShuChild1) + parseInt(sumJiShuChild2);

                        //计算luhn值
                        var k = parseInt(sumTotal) % 10 == 0 ? 10 : parseInt(sumTotal) % 10;
                        var luhn = 10 - k;

                        if ( bankno && lastNum == luhn ) {
                            //console.log("验证通过");
                            return true;
                        } else {
                            return false;
                        }
                    },
                    toWithdrawal:function(){
                        var _this=this;
                        var _account=Number(_this.account);
                        var _available_amount=Number(_this.available_amount);
                        
                        if(_account>=100 && _account<=_available_amount && _this.smsCode>=4){
                            $.ajax({
                                url:'{{route('s_sms_validatorDrawSms')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    code:_this.smsCode,
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
                                                    _this.showPopupDiv("withdraw");
                                                    //_this.errCode='<i></i>'+res.message;     
                                                        // setTimeout(function(){                                                     
                                                        //     _this.errMsg="";
                                                        //     location.reload();
                                                        // }, 3000);
                                                
                                                }else{                                                
                                                    _this.errCode='<i></i>'+res.message;
                                                    setTimeout(function(){
                                                        _this.errCode="";
                                                    }, 3000); 
                                                                                                
                                                }
                                            }
                                        });
                                    }else{
                                        _this.errCode='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errCode="";
                                        }, 3000);  
                                    }
                                }
                            });
                        }else if(_account<100){
                            $('.accountErrons').show();
                            $('.accountErronx').hide();
                            return false;
                        }else if(_account>_available_amount){
                            $('.accountErronx').show();
                            $('.accountErrons').hide();
                            return false;
                        }else{
                            _this.errCode='<i></i>请输入正确的验证码';
                            setTimeout(function(){
                                _this.errCode="";
                            }, 3000); 
                        }
                      

                    },
                    showPopupDiv:function(name){
                        var _this=this;
                        _this.popupDiv=name;
                        _this.popupFlag=true;
                    },
                    closePopup:function(){
                        var _this=this;
                        _this.popupDiv="";
                        _this.popupFlag=false;
                        _this.disabled2=false;
                        _this.times2=60;
                        _this.validCode2= "发送验证码"; 
                        clearInterval(_this.countStart);
                    },
                    closePopup2:function(){
                        var _this=this;
                        location.reload();
                    },
                    toChangeBank:function(){//切换银行卡
                        var _this=this;
                        if(_this.code>=4){
                            $.ajax({
                                url:'{{route('s_user_unbindBankCard')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    code:_this.code,
                                    bid:_this.bid
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        location.reload();
                                    }else{
                                        _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                    }
                                }
                            });
                        }else{
                            _this.errMsg='<i></i>请输入正确的验证码';
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                        }
                    },
                    unBindBank:function(){//解绑银行卡
                        var _this=this;
                        if(_this.code>=4){
                            $.ajax({
                                url:'{{route('s_user_unbindBankCard')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    code:_this.code,
                                    bid:_this.bid
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        _this.showPopupDiv("unBanksuccess");
                                    }else{
                                        _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                    }
                                }
                            }); 
                        }else{
                            _this.errMsg='<i></i>请输入正确的验证码';
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                        }
                    },
                    bindBankName:function(){//绑定银行卡
                        var _this=this;
                        if( this.luhnCheck2() && this.checkBankName() && this.checkSubBank() && this.checkCode() ){
                            $.ajax({
                                url:'{{route('s_user_bindBankCard')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    bind_realname:_this.realname, //真实姓名 
                                    bind_idnumber:_this.idCard, //身份证号码
                                    bind_banknumber:_this.bindBankObj.bind_banknumber, //银行卡号
                                    bind_mobile:_this.mobile, //手机号码
                                    bank_relative:_this.bankSelect.id, //银行key
                                    bank_id:_this.bankSelect.bank_id, //银行id
                                    province_id:_this.bankSelect.province_id, //支行省份
                                    city_id:_this.bankSelect.city_id, //支行城市
                                    bank_name:_this.bankSelect.sub_name, //支行名称
                                    sub_branch_id:_this.bankSelect.sub_id, //支行编号
                                    code:_this.code //验证码
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        _this.showPopupDiv("Banksuccess");
                                    }else{
                                        _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                    }
                                }
                            });
                        }else{
                            
                        }

                    },
                    unbindBank:function(){
                        var _this=this;
                        _this.showPopupDiv("untieBank");
                        this.accountBank.bankStatus="unbindBank";
                        this.accountBank.bankStatusName="解绑银行卡";
                    },
                    changeBank:function(){
                        var _this=this;
                        _this.withgetMoney=false;
                        this.accountBank.bankStatus="changeBank";
                        this.accountBank.bankStatusName="更换银行卡";    

                    },
                    showBankError:function(){
                        var _this=this;
                        _this.errCode='<i></i>请先绑定银行卡';
                        setTimeout(function(){
                            _this.errCode="";
                        }, 3000);
                    },
                    sendRenzmsg:function(){//发送实名验证短信
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_sms_sendAuthSms')}}',
                            type:'POST',
                            async:true,
                            data:{
                                type:1
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            beforeSend: function () {
                                // 禁用按钮防止重复提交
                                _this.disabled=true;
                            },
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
                                    _this.disabled=false;
                                    _this.errMsg='<i></i>'+res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);  
                                }
                            }
                        });                        
                    },
                    renzSubmit:function(){//实名认证
                        var _this=this;
                        if( this.checkCode() && this.checkName() && this.checkIdentity() ){
                            $.ajax({
                                url:'{{route('s_sms_validatorAuthSms')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    code:_this.code,
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {  
                                        $.ajax({
                                            url:'{{route('s_auth_authVerify')}}',
                                            type:'POST',
                                            async:true,
                                            data:{
                                                idno:_this.idCard,
                                                realname:_this.realname
                                            },
                                            "headers": {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                            },
                                            dataType:'json',
                                            success:function(res){
                                                if(res.status==200) {
                                                    _this.popupDiv='renZSuccess';
                                                    _this.isBind2=1;
                                                    var _timer = setInterval(function() {
                                                        if(_this.renzTime<=0) {
                                                            window.clearInterval(_timer);  
                                                            _this.code="";
                                                            _this.closePopup();
                                                            _this.tobandcards();
                                                        }
                                                        _this.renzTime--;
                                                        
                                                    }, 1000);
                                                }else{
                                                    _this.code="";
                                                    _this.idCard="";
                                                    _this.realname="";
                                                    _this.popupDiv='renZFail';
                                                    /* _this.errMsg='<i></i>'+res.message;
                                                    setTimeout(function(){
                                                        _this.errMsg="";
                                                    }, 3000); */
                                                } 
                                            }
                                        });
                                    }else{
                                        _this.code="";
                                        _this.idCard="";
                                        _this.realname="";
                                        _this.popupDiv='renZFail';
                                        /* _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000); */
                                    }
                                }
                            }); 
                        }
                    },

                }
            })
        })
    </script>
@endsection

