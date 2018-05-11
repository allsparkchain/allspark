@extends('User.layout')


@section("title", "账户设置")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="accountSetting" style="display:none;">
    <div class="zmtdl_accountSetupWrap">
        <div class="zmtdl_accountSetupBrief" :class="{on:bankShow}" @click="tabSwitch('bindBank')">
            @{{accountBank.bankStatusName}}<i class="zmtdl_accountSetupRight"></i>
        </div>
        <div class="zmtdl_accountSetupBank" v-show="bankShow">

            <div v-if="accountBank.bankStatus==='noBindBank'" class="zmtdl_accountSetupBankWrap" style="margin-top:50px;">
                <input type="text" placeholder="请输入对公账户的公司名称" v-model="bindBankObj.bind_realname" @blur="checkName">
                <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errName}}</div>
                <!-- <input type="text" placeholder="请输入身份证号码" v-model="bindBankObj.bind_idnumber" @blur="checkIdentity"> -->
                <!-- <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errIdentity}}</div> -->
                <div class="zmtdl_accountSelectBank" style="position: relative;">
                    <input type="text" placeholder="选择开户银行" v-model="bankName" readonly="readonly" @blur="checkBankName">
                    <div class="zmtdl_support_bank" style=" cursor: pointer;">查看支持银行
                        <ul class="zmtdl_bank_ul">
                            <li v-for="a in bankNameArr" @click="chooseBank(a.id)">@{{a.name}}</li>
                        </ul>
                    </div>
                </div>
                <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errBankCard}}</div>
                <input type="text" placeholder="请输入银行卡号" v-model="bindBankObj.bind_banknumber">
                <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errCard}}</div>
                <!-- <input type="text" placeholder="请输入银行预留手机号" v-model="bindBankObj.bind_mobile" @blur="checkMobile"> -->
                <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMobile}}</div>
                <input @click="bindBankName" type="button" value="绑  定">
            </div>

            <div v-if="accountBank.bankStatus==='bindBank'" class="zmtdl_accountSetupBankInfoWrap">
                <div class="zmtdl_accountSetupBankBind">
                    <div class="zmtdl_bankCard" :class="bankbg"></div>
                    <span class="zmtdl_accountSetupBankName">@{{bankname}}</span>
                    <span class="zmtdl_accountSetupBankNum">XXXX&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;XXXX&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;XXXX&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;@{{banknumber|bankFormat}}</span>
                    <span class="zmtdl_accountSetupBankUser">公司名称：@{{realname}}XX</span>
                </div>
                <div class="zmtdl_accountSetupBankLink">
                    <span @click="unbindBank">解绑银行卡</span>
                    <span @click="changeBank">更换银行卡</span>
                </div>
            </div>

            <div v-if="accountBank.bankStatus==='changeBank'" class="zmtdl_accountSetupBankRelieveChangeWrap">
                <div class="zmtdl_accountSetupBank_mobile">
                    <div><span>手机号</span>&nbsp;&nbsp;&nbsp;@{{mobile|mobileFormat}}</div>
                    <div style="margin: 40px 0 0 0;">
                        <input class="zmtdl_accountSetupBank_code" type="text" placeholder="请输入验证码" v-model="code">
                        <input class="zmtdl_accountSetupBank_codebtn" @click="sendMsg" type="button" v-model="validCode" :disabled="disabled">
                    </div>
                    <div style=" font-size:12px; color:#FF7241; height:40px;">@{{errMsg}}</div>
                    <div>
                        <input class="zmtdl_accountSetupBank_confirm" type="button" @click="toChangeBank" value="确 认">
                        <a class="zmtdl_accountSetupBank_back" href="javascript:location.reload();">返回</a>
                    </div>
                </div>
            </div>

            <div v-if="accountBank.bankStatus==='unbindBank'" class="zmtdl_accountSetupBankRelieveChangeWrap">
                <div class="zmtdl_accountSetupBank_mobile">
                    <div><span>手机号</span>&nbsp;&nbsp;&nbsp;@{{mobile|mobileFormat}}</div>
                    <div style="margin: 40px 0 0 0;">
                        <input class="zmtdl_accountSetupBank_code" type="text" placeholder="请输入验证码" v-model="code">
                        <input class="zmtdl_accountSetupBank_codebtn" @click="sendMsg" type="button" v-model="validCode" :disabled="disabled">
                    </div>
                    <div style=" font-size:12px; color:#FF7241; height:40px;">@{{errMsg}}</div>
                    <div>
                        <input class="zmtdl_accountSetupBank_confirm" type="button" @click="unBindBank" value="确 认">
                        <a class="zmtdl_accountSetupBank_back" href="javascript:location.reload();">返回</a>
                    </div>
                </div>
            </div>

            <div v-if="accountBank.bankStatus==='unbindBankSuccess'" class="zmtdl_bankRelieveSuccessWrap">
                <div class="zmtdl_bankRelieveSuccess">
                    <p>你的银行卡解绑成功</p>
                </div>
            </div>

        </div>
    </div>
    <div class="zmtdl_accountSetupWrap">
        <div class="zmtdl_accountSetupBrief" :class="{on:passwordShow}"  @click="tabSwitch('changePassword')">
            修改登录密码<i class="zmtdl_accountSetupRight"></i>
        </div>
        <div class="zmtdl_accountSetupPwd" v-show="passwordShow">
            <div class="zmtdl_accountSetupPwdWrap">
            <div style=" font-size:14px; color:#7e7e7e; line-height:20px; margin-bottom:15px;">●密码必须8位至18位，由英文与数字组成</div>
                <input type="password" style=" margin:0;" placeholder="请输入原始密码" v-model="oldpas" @blur="checkErrPwd">
                <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{testPwd.errPwd}}</div>
                <input type="password" style=" margin:0;" placeholder="请输入新密码" v-model="newpas" @blur="checkErrNewPwd">
                <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{testPwd.errNewPwd}}</div>
                <input type="password" style=" margin:0;" placeholder="再次输入密码" v-model="newpas2" @blur="checkErrNewPwd2">
                <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{testPwd.errConfirmPwd}}</div>
                <input type="button" @click="changePwd" value="确  定">
            </div>
        </div>
    </div>
    <div class="zmtdl_accountSetupWrap">
        <div class="zmtdl_accountSetupBrief" :class="{on:renzShow}"  @click="tabSwitch('changeRenz')">
            实名认证<i class="zmtdl_accountSetupRight"></i>
        </div>
        <div class="zmtdl_accountSetupPwd "  v-show="renzShow" >
            <div class="zmtdl_accountSetupPwdWrap" id="ggzdl_accountSetupPwdWrap" style="position:relative;" v-show="!checked">
                <input type="text" id="pgy_account_text1"  placeholder="请输入你的真实姓名" v-model="bindBankObj.bind_realname" @blur="checkName">
                <span style="color:#FF7241;position:absolute;top:41px;left:11px">@{{errName}}</span>
                <input type="text" id="pgy_account_text2"  placeholder="请输入身份证号码" v-model="bindBankObj.bind_idnumber" @blur="checkIdentity">
                <span style="color:#FF7241;position:absolute;top:109px;left:11px">@{{errIdentity}}</span>
                <input class="zmtdl_accountSetupBank_code" type="text" placeholder="请输入验证码" v-model="code">
                <input class="pgy_accountSetupBank_codebtn" type="button" @click="sendRenzmsg" value="发送验证码" v-model="validCode" :disabled="disabled">
                <span style="font-size:12px; color:#FF7241;position:absolute;top:183px;left:11px">@{{errMsg}}</span>
                <input type="button" @click="renzSubmit" value="提  交" style="margin-top: 30px">
            </div>


            <div class="ggzdl_accountSetupPwd" v-show="checked">
                <div class="ggzdl_accountSetupPwdWrap">
                    <div class="pgy_success_g">
                        <img src="/images/pgy_renz_success.jpg" alt="">
                        <p class="pgy_successP1">认证成功</p>
                        <p class="pgy_successP2">恭喜您完成了身份认证</p>
                    </div>
                </div>
            </div>
        </div>
        <!--认证成功-->
        
    </div>
</div>
@endsection



@section("script")
    <script type="text/javascript">
        $(function () {
            //var mobile = {{$mobile}};
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

            Vue.filter("bankFormat",function(value){
                return value.substr(value.length-4);
            });

            Vue.filter("mobileFormat",function(value){
                return value.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2');
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
                el: '#accountSetting',
                data: {
                    errName:"",
                    errIdentity:"",
                    errCard:"",
                    errBankCard:"",
                    errMobile:"",
                    renzShow:false,
                    bankShow:false,
                    passwordShow:false,
                    testPwd:{
                        errPwd:"",
                        errNewPwd:"",
                        errConfirmPwd:""
                    },
                    accountBank:{
                        bankStatus:"bindBank",
                        bankStatusName:"绑定银行卡"
                    },
                    bindBankObj:{
                        bind_realname:"",
                        bind_idnumber:"",
                        bind_bankid:"",
                        bind_banknumber:"",
                        bind_mobile:""
                    },
                    bankName:"",
                    bankNameArr:[],
                    validCode:"发送验证码",
                    times:60,
                    errMsg:"",
                    errMsg2:"",
                    code:"",
                    realname:"",
                    banknumber:"",
                    bankname:"",
                    bankbg:"",
                    mobile:"",
                    bid:"",
                    isBind:"",
                    oldpas:"",
                    newpas:"",
                    newpas2:"",
                    checked:false,
                    disabled:false
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
                                    _this.accountBank.bankStatus="bindBank";
                                    _this.banknumber=res.data.banknumber;
                                    _this.bankname=res.data.BankName;
                                    _this.realname=res.data.realname.substr(0, 1);
                                    _this.bid=res.data.id;
                                    _this.mobile=res.data.sendMobile;
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
                                } else {
                                    _this.accountBank.bankStatus="noBindBank";
                                }
                            }
                        }
                    });
                    this.getUserBank();
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
                               _this.isBind=res.data.isBind;
                             
                              if(_this.isBind===1){
                                _this.checked=true;
                              } 
                             }else{
                                _this.checked=false;
                                _this.errMsg=res.message;                                
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);
                             }
                        }
                    });
                },
                mounted:function(){
                    var _this=this;
                    this.bankShow=this.getQueryVariable("bank");
                    this.$nextTick(function() {
                        document.getElementById("accountSetting").style.display = "block";
                    });
                },
                methods:{
                    chooseBank:function(id){
                        this.bindBankObj.bind_bankid=id;
                        this.bankName=this.bankNameArr[id-1].name;
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
                    checkName:function(){
                        var _this=this;
                        if ( !(this.bindBankObj.bind_realname) ) {
                            this.errName="请输入对公账户的公司名称";
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
                        if ( !(/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(this.bindBankObj.bind_idnumber)) ) {
                            this.errIdentity="请输入正确格式的身份证号";
                            setTimeout(function(){
                                _this.errIdentity="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    checkBankName:function(){
                        var _this=this;
                        if ( !this.bindBankObj.bind_bankid ) {
                            this.errBankCard="请选择开户银行";
                            setTimeout(function(){
                                _this.errBankCard="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    luhnCheck:function() {
                        var _this=this;
                        var lastNum = this.bindBankObj.bind_banknumber.substr(this.bindBankObj.bind_banknumber.length - 1, 1);// 取出最后一位（与luhn进行比较）
                        var first15Num = this.bindBankObj.bind_banknumber.substr(0, this.bindBankObj.bind_banknumber.length - 1);// 前15或18位
                        var newArr = new Array();
                        for (var i = first15Num.length - 1; i > -1; i--) { // 前15或18位倒序存进数组
                            newArr.push(first15Num.substr(i, 1));
                        }
                        var arrJiShu = new Array(); // 奇数位*2的积 <9
                        var arrJiShu2 = new Array(); // 奇数位*2的积 >9
                        var arrOuShu = new Array(); // 偶数位数组
                        for (var j = 0; j < newArr.length; j++) {
                            if ((j + 1) % 2 == 1) {// 奇数位
                                if (parseInt(newArr[j]) * 2 < 9) {
                                    arrJiShu.push(parseInt(newArr[j]) * 2);
                                } else {
                                    arrJiShu2.push(parseInt(newArr[j]) * 2);
                                }
                            } else {
                                arrOuShu.push(newArr[j]);// 偶数位
                            }
                        }
                    
                        var jishu_child1 = new Array();// 奇数位*2 >9 的分割之后的数组个位数
                        var jishu_child2 = new Array();// 奇数位*2 >9 的分割之后的数组十位数
                        for (var h = 0; h < arrJiShu2.length; h++) {
                            jishu_child1.push(parseInt(arrJiShu2[h]) % 10);
                            jishu_child2.push(parseInt(arrJiShu2[h]) / 10);
                        }
                        var sumJiShu = 0; // 奇数位*2 < 9 的数组之和
                        var sumOuShu = 0; // 偶数位数组之和
                        var sumJiShuChild1 = 0; // 奇数位*2 >9 的分割之后的数组个位数之和
                        var sumJiShuChild2 = 0; // 奇数位*2 >9 的分割之后的数组十位数之和
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
                        // 计算总和
                        sumTotal = parseInt(sumJiShu) + parseInt(sumOuShu) + parseInt(sumJiShuChild1) + parseInt(sumJiShuChild2);
                        // 计算luhn值
                        var k = parseInt(sumTotal) % 10 == 0 ? 10 : parseInt(sumTotal) % 10;
                        var luhn = 10 - k;
                        if (lastNum == luhn) {
                            console.log("验证通过");
                            return true;
                        } else {
                            this.errCard="请输入正确格式的银行卡号";
                            setTimeout(function(){
                                _this.errCard="";
                            }, 3000);
                            return false;
                        }
                    },
                    checkMobile:function(){
                        var _this=this;
                        if ( !(/^1[34578]\d{9}$/.test(this.bindBankObj.bind_mobile)) ) {
                            this.errMobile="请输入正确格式的手机号";
                            setTimeout(function(){
                                _this.errMobile="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    tabSwitch:function(name){
                        if(name==="bindBank"){
                            this.bankShow=this.bankShow?false:true;
                            this.passwordShow=false;
                            this.renzShow=false;
                        }else if(name==="changePassword"){
                            this.passwordShow=this.passwordShow?false:true;
                            this.bankShow=false;                            
                            this.renzShow=false;
                        }else if(name==="changeRenz"){
                            this.renzShow=this.renzShow?false:true;
                            this.bankShow=false;
                            this.passwordShow=false;
                        }
                    },
                    checkErrPwd:function(){
                        var _this=this;
                        if ( !(/^[A-Za-z0-9]{8,18}$/.test(this.oldpas)) ) {
                            this.testPwd.errPwd="请输入正确的密码格式";
                            setTimeout(function(){
                                _this.testPwd.errPwd="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    checkErrNewPwd:function(){
                        var _this=this;
                        if ( !(/^[A-Za-z0-9]{8,18}$/.test(this.newpas)) ) {
                            this.testPwd.errNewPwd="请输入正确的密码格式";
                            setTimeout(function(){
                                _this.testPwd.errNewPwd="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    checkErrNewPwd2:function(){
                        var _this=this;
                        if ( this.newpas !== this.newpas2 ) {
                            this.testPwd.errConfirmPwd="两次密码不一致";
                            setTimeout(function(){
                                _this.testPwd.errConfirmPwd="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    getUserBank:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_getUserBankRelative')}}',
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
                    changePwd:function(){
                        var _this=this;
                        if ( (/^[A-Za-z0-9]{8,18}$/.test(_this.oldpas)) && (/^[A-Za-z0-9]{8,18}$/.test(_this.newpas)) && (/^[A-Za-z0-9]{8,18}$/.test(_this.newpas2))){
                            $.ajax({
                                url:'{{route('s_user_changePassword')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    oldpas:_this.oldpas,
                                    newpas:_this.newpas,
                                    newpas2:_this.newpas2
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        _this.testPwd.errPwd=res.data.message;
                                        setTimeout(function(){
                                            location.reload();
                                        }, 3000);
                                    }else{
                                        _this.testPwd.errPwd="密码错误";
                                        setTimeout(function(){
                                            _this.testPwd.errPwd="";
                                        }, 3000);                                       
                                    }
                                }
                            });
                        }
                        else {
                            _this.errMsg2="请输入正确的密码格式";
                            setTimeout(function(){
                                _this.errMsg2="";
                            }, 3000);
                        }  
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
                            success:function(res){
                                if(res.status==200) {
                                    var countStart = setInterval(function () {
                                        _this.validCode = _this.times-- + '秒后重发';
                                        _this.disabled=true;
                                        if (_this.times < 0) {
                                            clearInterval(countStart);
                                            _this.times=60;
                                            _this.validCode = "发送验证码";
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
                    },
                    renzSubmit:function(){//实名认证
                        var _this=this;
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
                                                idno:_this.bindBankObj.bind_idnumber,
                                                realname:_this.bindBankObj.bind_realname
                                     
                                            },
                                            "headers": {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                            },
                                            dataType:'json',
                                            success:function(res){
                                               if(res.status==200) {
                                                   _this.checked=true;
                                                 }else{
                                                //   _this.errMsg=res.message;
                                                //   setTimeout(function(){
                                                //         _this.errMsg="";
                                                // }, 3000);
                                                } 
                                            
                                            
                                            }
                                        });
                                }else{
                                    setTimeout(function(){
                                             _this.errMsg="";
                                     }, 3000);
                                }
                            }
                        }); 
                    },
                    sendMsg:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_sendBankSms')}}',
                            type:'POST',
                            async:true,
                            data:{
                                type:1
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    var countStart = setInterval(function () {
                                        _this.validCode = _this.times-- + '秒后重发';
                                        if (_this.times < 0) {
                                            clearInterval(countStart);
                                            _this.validCode = "发送验证码";
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
                    },
                    toChangeBank:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_changeBankCard')}}',
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
                                    _this.errMsg=res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);
                                }
                            }
                        }); 
                    },
                    unBindBank:function(){
                        var _this=this;
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
                                    _this.accountBank.bankStatus="unbindBankSuccess";
                                }else{
                                    _this.errMsg=res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);
                                }
                            }
                        }); 
                    },
                    bindBankName:function(){
                        // && this.checkIdentity()
                        var _this=this;
                        if ( this.checkName()  && this.checkBankName() ) {
                            $.ajax({
                                url:'{{route('s_user_bindBankCard')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    bind_realname:_this.bindBankObj.bind_realname,
                                    // bind_idnumber:_this.bindBankObj.bind_idnumber,
                                    bind_banknumber:_this.bindBankObj.bind_banknumber,
                                    // bind_mobile:_this.bindBankObj.bind_mobile,
                                    bank_relative:_this.bindBankObj.bind_bankid
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        location.reload();
                                    }else{
                                        _this.errMobile=res.message;
                                        setTimeout(function(){
                                            _this.errMobile="";
                                        }, 3000);
                                    }
                                }
                            });
                        } else {
                          
                        }
                    },
                    unbindBank:function(){
                        this.accountBank.bankStatus="unbindBank";
                        this.accountBank.bankStatusName="解绑银行卡";
                    },
                    changeBank:function(){
                        this.accountBank.bankStatus="changeBank";
                        this.accountBank.bankStatusName="更换银行卡";                        
                    }
                }
            })
        });
    </script>
@endsection
