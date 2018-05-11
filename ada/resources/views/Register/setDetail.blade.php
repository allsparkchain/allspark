<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>蒲公英 - 详细信息</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/style.css') }}">
    <script type="text/javascript" src="/js/echarts.common.min.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/js/vue.js" type="text/javascript"></script>
</head>
<body class="zmtdl_loginBg">
<div id="setPass" class="zmtdl_loginWrap">
    <header class="zmtdl_loginHeader">
        <div class="zmtdl_logo"></div>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;广告主代理后台
    </header>
    <div class="zmtdl_loginBox" style="width:411px;height:708px;margin:-318px 0 0 -205px;z-index:1">
        <div class="py_litterDetails" >
                <div style="position: relative;">
                    <div >绑定邮箱</div>
                    <input class="pgy_bdemail" type="email" name="email" id="bdemail" v-model="email" @blur="checkEmail" placeholder="邮箱账户">
                    <span>可通过邮箱找回密码</span>
                    <span style=" top:103px; color:#FF7241;">@{{emailErr}}</span>
                </div>
                <div style="height: 195px;position: relative">
                    <div >实名认证</div>
                    <input class="pgy_identityName" type="email" name="realname" id="identityName" v-model="realname"  placeholder="姓名">
                    <span class="pgy_color_orgin">@{{errMsg}}</span>
                    <input class="pgy_identityCode" type="email" id="identityCode" name="idcard" v-model="idcard" @blur="checkIdentity" placeholder="身份证号码">
                    <span style=" top: 161px;">实名后可自由创作内容</span>
                    <span style=" top:177px; color:#FF7241;">@{{idErr}}</span>
                </div>
                <!-- <div style="position: relative;height:221px">
                    <div for="cardCode">提现卡号</div>
                    <div class="total_bank" >
                        <div class="zmtdl_accountSelectBanks">
                            <input type="text" placeholder="选择开户银行" v-model="bankName" readonly="readonly" @blur="checkBankName" >
                            <a href="javascript:;" class="zmtdl_support_bank">查看支持银行
                                <ul class="zmtdl_bank_ul">
                                    <li v-for="a in bankNameArr" @click="chooseBank(a.id)">@{{a.name}}</li>
                                </ul>
                            </a>
                        </div>  
                        <input class="pgy_cardCode" name="bankcard" type="email" v-model="bankcard" id="cardCode" @blur="luhnCheck" placeholder="银行卡号">
                        <span style="font-size: 12px;color: #7E7E7E;">可将佣金提现至该银行卡</span>
                        <span style="position: absolute;top:181px;color:#FF7241;left:2px;font-size:12px;">@{{bankErr}}</span>
                    </div>
                </div> -->
                <div id="pgy_next_goto" style="margin-top:225px;">
                    <a href="javascript:;" @click="toReg">下一步</a>
                    <a href="javascript:;" @click="toReg2">跳过</a>
                </div>
                <div class="zmtdl_toRegister_more">已有账号?<a href="{{route('s_login')}}">返回登录</a></div>
        </div>
    </div>
    <footer class="zmtdl_footer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</footer>
</div>
<script type="text/javascript">
   //下一步 获得填写的值一起提交   跳过，，直接提交  Route('s_auth_register')
   $(function(){

var app = new Vue({
    el: '#setPass',
    data: {
        email:"",
        bankcard:"",
        idcard:"",
        realname:"",
        errMsg:"",
        emailErr:"",
        idErr:"",
        bankErr:"",
        bankName:"",
        bankNameArr:[],
        bindBankObj:{
                bind_bankid:"",
                },
    },
    created:function(){
        var _this=this;
        _this.getUserBank();
    },
    mounted:function(){
        var _this=this;
        this.$nextTick(function() {
            document.getElementById("setPass").style.display = "block";
        });
    },
    methods:{
        chooseBank:function(id){
                            this.bindBankObj.bind_bankid=id;
                            this.bankName=this.bankNameArr[id-1].name;
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
        checkEmail:function(){
            var _this=this;
            if( !(/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/.test(this.email)) ){
                this.emailErr="请输入正确格式的邮箱";
                setTimeout(function(){
                    _this.emailErr="";
                }, 3000);
                return false;
            }else{
                return true;
            }
        },
        checkIdentity:function(){
            var _this=this;
            if ( !(/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(this.idcard)) ) {
                this.idErr="请输入正确格式的身份证号";
                setTimeout(function(){
                    _this.idErr="";
                }, 3000);
                return false;
            } else {
                return true;
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
        luhnCheck:function() {
            var _this=this;
            if(this.bankcard){
                var lastNum = this.bankcard.substr(this.bankcard.length - 1, 1);// 取出最后一位（与luhn进行比较）
                var first15Num = this.bankcard.substr(0, this.bankcard.length - 1);// 前15或18位
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
                    this.bankErr="请输入正确格式的银行卡号";
                    setTimeout(function(){
                        _this.bankErr="";
                    }, 3000);
                    return false;
                }
            }else{
                this.bankErr="请输入正确格式的银行卡号";
                setTimeout(function(){
                    _this.bankErr="";
                }, 3000);
                return false;
            }
        },
        toReg:function(){
            // && this.checkBankName() && this.luhnCheck() 
            var _this=this;
            if ( this.checkEmail() && this.checkIdentity() ) {
                $.ajax({
                    url:'{{route('s_auth_register')}}',
                    type:'POST',
                    async:true,
                    data:{
                        email:_this.email,
                        // bankcard:_this.bankcard,
                        idcard:_this.idcard,
                        realname:_this.realname,
                        // bank_relative:_this.bindBankObj.bind_bankid
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            location.href="{{route('s_regSuccess')}}";
                        }else{
                            _this.errMsg=res.msg;
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                        }
                    }
                });
            } else {
                  
            }
        },
        toReg2:function(){
            var _this=this;
            $.ajax({
                url:'{{route('s_auth_register')}}',
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
                       location.href="{{route('s_regSuccess')}}";
                    }
                }
            });
        }
    }
});
});




</script>

</script>
</body>
</html>