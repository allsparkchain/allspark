@extends('Register.layout')


@section("title", "注册-设置详细信息")


@section("css")
{{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="setDetail" style=" display:none;">
<div class="pgy_sendContent">
    <div class="pgy_reg_user">
        <img src="{{$wxUserInfo['headimgurl'] or ''}}">
        <div class="reg_userName">{{$wxUserInfo['nickname'] or ''}}</div>
        <div class="reg_authority"></div>
    </div>
    <div class="pgy_remindNav" >
        <ul>
            <li>1</li>
            <li class="pgy_bgLine"></li>
            <li class="pgy_bgColor">2</li>
            <li></li>
            <li>3</li>
        </ul>
    </div>
    <div class="pgy_remindNavT">
        <ul>
            <li>绑定手机号</li>
            <li></li>
            <li class="pgy_color_orgin">设置详细信息</li>
            <li></li>
            <li>注册成功</li>
        </ul>
    </div>

        <div class="pgy_1200">
            <div class="py_litterDetails" style="margin:112px auto auto auto">
                <div style="height: 93px;position: relative;">
                    <div class="pgy_input_wraps" style="float:right;">
                                <i class="pgy_input_email"></i>
                                <input id="email" class="pgy_input2" type="email" v-model="email" placeholder="邮箱账户" @blur="checkEmail">
                    </div>
                    <span hidden>可通过邮箱找回密码</span>
                    <span style=" top:45px; color:#FF7241;" v-html="emailErr">@{{emailErr}}</span>
                </div>
                <div style="height: 166px;position: relative">
                    <div class="pgy_input_wraps" style="float:right;">
                                <i class="pgy_input_name"></i>
                                <input id="email" class="pgy_input2" type="email" name="realname" id="identityName" v-model="realname" placeholder="姓名">
                    </div>
                    <span class="pgy_color_orgin" v-html="errMsg">@{{errMsg}}</span>
                    <div class="pgy_input_wraps" style="float:right;margin-top:30px;">
                                <i class="pgy_input_sf"></i>
                                <input id="email" class="pgy_input2" type="text" id="identityCode" name="idcard" v-model="idcard" @blur="checkIdentity" placeholder="身份证号码">
                    </div>
                    <span style=" top: 123px;" hidden>实名后可自由创作内容</span>
                    <span style=" top:119px; color:#FF7241;" v-html="idErr">@{{idErr}}</span>
                </div>
                <div style="position: relative;height:146px;">
                    <div class="total_bank" style="height: 146px;">
                        <div class="zmtdl_accountSelectBank">
                            <input style="height:43px;" type="text" placeholder="选择开户银行" v-model="bankName" readonly="readonly" @blur="checkBankName">
                            <div class="zmtdl_support_bank"><span>查看支持银行</span>
                                <ul class="zmtdl_bank_ul">
                                    <li v-for="a in bankNameArr" @click="chooseBank(a.id)">@{{a.name}}</li>
                                </ul>
                            </div>
                        </div> 
                        <div class="pgy_input_wraps" style="float:right;">
                                <i class="pgy_input_snum"></i>
                                <input id="email" class="pgy_input2" type="email" v-model="bankcard" id="cardCode" @blur="luhnCheck" placeholder="银行卡号">
                        </div>               
                                           
                        <span style="font-size: 12px;color: #7E7E7E;" hidden>可将收益提现至该银行卡</span>
                        <span style="position: absolute;top:121px; color:#FF7241;left: 57px;font-size: 12px;line-height:16px;" v-html="bankErr">@{{bankErr}}</span>
                        </div>                    
                    </div>
                <div id="pgy_next_goto">
                    <a href="javascript:;" @click="toReg">下一步</a>
                    <a href="javascript:;" @click="toReg2">跳过</a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section("script")
<script type="text/javascript">

//下一步 获得填写的值一起提交   跳过，，直接提交  Route('s_auth_register')
$(function(){

    var app = new Vue({
        el: '#setDetail',
        data: {
            email:"",
            bankcard:"",
            idcard:"",
            realname:"",
            errMsg:"",
            emailErr:"",
            idErr:"",
            bankErr:"",
            bankNameArr:[],
            bankName:"",
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
                document.getElementById("setDetail").style.display = "block";
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
                            this.errBankCard="<i></i>请选择开户银行";
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
                    this.emailErr="<i></i>请输入正确格式的邮箱";
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
                    this.idErr="<i></i>请输入正确格式的身份证号";
                    setTimeout(function(){
                        _this.idErr="";
                    }, 3000);
                    return false;
                } else {
                    return true;
                }

                // var city={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江 ",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北 ",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏 ",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外 "};
                // var pass= true;
                // var code=this.idcard;

                // if(!code || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(code)){
                //     pass = false;
                // }else if(!city[code.substr(0,2)]){
                //     pass = false;
                // }else{
                //     //18位身份证需要验证最后一位校验位
                //     if(code.length == 18){
                //         code = code.split('');
                //         //∑(ai×Wi)(mod 11)
                //         //加权因子
                //         var factor = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ];
                //         //校验位
                //         var parity = [ 1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2 ];
                //         var sum = 0;
                //         var ai = 0;
                //         var wi = 0;
                //         for (var i = 0; i < 17; i++)
                //         {
                //             ai = code[i];
                //             wi = factor[i];
                //             sum += ai * wi;
                //         }
                //         var last = parity[sum % 11];
                //         if(parity[sum % 11] != code[17]){
                //             pass =false;
                //         }
                //     }
                // }
                // if(!pass){
                //     this.idErr="请输入正确格式的身份证号";
                //     setTimeout(function(){
                //         _this.idErr="";
                //     }, 3000);
                //     return false;
                // }
                // return pass;
                                  
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
                        //console.log("验证通过");
                        return true;
                    } else {
                        this.bankErr="<i></i>请输入正确格式的银行卡号";
                        setTimeout(function(){
                            _this.bankErr="";
                        }, 3000);
                        return false;
                    }
                }else{
                    this.bankErr="<i></i>请输入正确格式的银行卡号";
                    setTimeout(function(){
                        _this.bankErr="";
                    }, 3000);
                    return false;
                }
            },
            toReg:function(){
                var _this=this;
                if ( this.checkEmail() && this.checkIdentity() && this.checkBankName() && this.luhnCheck() ) {
                    $.ajax({
                        url:'{{route('s_auth_register')}}',
                        type:'POST',
                        async:true,
                        data:{
                            email:_this.email,
                            bankcard:_this.bankcard,
                            idcard:_this.idcard,
                            realname:_this.realname,
                            bank_relative:_this.bindBankObj.bind_bankid
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                location.href="{{route('s_regSuccess')}}";
                            }else{
                                _this.errMsg="<i></i>"+res.msg;
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
                if (sessionStorage.getItem("regSuccess")) {//如果有值就直接跳到成功
                    location.href="{{route('s_regSuccess')}}";
                }else{
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
                                sessionStorage.setItem("regSuccess",true);//存
                                location.href="{{route('s_regSuccess')}}";
                            }else{
                                alert(res.message);
                            }
                        }
                    });                    
                }

            }
        }
    });
});

</script>
@endsection


