@extends('User.layout')


@section("title", "账户设置")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}
    <link href="{{ mix('css/cropper.min.css') }}" rel="stylesheet">

@endsection

@section("content")
<script src="/js/bootstrap.min.js"></script>
<script src="/js/cropper.js" type="text/javascript"></script>
<script src="/js/sitelogo.js" type="text/javascript"></script>
<div id="accountSetting" style="display:none">
    <!-- <h3 class="ggzdl_admin_title">账户设置</h3> -->
    <div class="ggzdl_accountSetupWrap">
        <div class="ggzdl_accountSetupBrief" :class="{on:basicShow}"  @click="tabSwitch('changeBasic')">
            基本资料<i class="ggzdl_accountSetupRight"></i>
        </div>
        <div class="ggzdl_accountBasic" v-show="basicShow">
            <div class="ggzdl_basic_portrait"><img src="{{\Auth::getUser()->getHeadImgurl()}}" alt=""><a href="javascript:;" @click="showPopupDiv('changeHead')">更改头像</a></div>
            <div class="ggzdl_basic_text" v-show="!nickChange">
                <label>昵称：</label>
                <div class="pgy_account_mobile">@{{nickName}}</div>
                <a href="javascript:;" @click="nickChange=true">修改</a>
            </div>
            <div class="ggzdl_basic_text" v-show="nickChange">
                <label>昵称：</label>
                <div class="pgy_account_input">
                    <input type="text" class="pgy_account_nick" placeholder="昵称" v-model="nickName2">
                </div>
                <a href="javascript:;" @click="saveNick">确认</a><a href="javascript:;" @click="nickChange=false;nickName2=nickName;">取消</a>
                <div class="error_msg" v-html="errMsg">@{{errMsg}}</div>
            </div>
            <div class="ggzdl_basic_text" v-show="!mailFlag">
                <label>邮箱：</label>
                <div class="pgy_account_input">
                    <i class="pay_account_mail"></i>
                    <input type="text" class="pgy_input_text" placeholder="邮箱" v-model="email2">
                </div>
                <a href="javascript:;" @click="sendMail">绑定</a>
                <div class="error_msg" v-html="errMail">@{{errMail}}</div>
            </div>
            <div class="ggzdl_basic_text" v-show="mailFlag">
                <label>邮箱：</label>

                <div class="pgy_account_mobile" v-show="!mailChange">@{{email}}</div>
                <a href="javascript:;" @click="mailChange=true" v-show="!mailChange">修改</a>
                
                <div class="pgy_account_input" v-show="mailChange">
                    <i class="pay_account_mail"></i>
                    <input type="text" class="pgy_input_text" placeholder="邮箱" v-model="email2">
                </div>
                <a href="javascript:;" @click="sendMail" v-show="mailChange">验证</a>
                <a href="javascript:;" @click="mailChange=false;email2=email;" v-show="mailChange">取消</a>

                <div class="error_msg" v-html="errMail">@{{errMail}}</div>
            </div>
            <div class="ggzdl_basic_text">
                <label>手机：</label>
                <div class="pgy_account_mobile">@{{mobile|mobileFormat}}</div>
                <a href="javascript:;" @click="showPopupDiv('mobileUnbind')">更换</a>
            </div>
            <div class="ggzdl_basic_text">
                <label>第三方账号：</label>
                <div class="pgy_account_third"><img src="/images/account_wx.png" alt="第三方账号"><br>已绑定</div>
            </div>
        </div>
    </div>

    <div class="ggzdl_accountSetupWrap">
        <div class="ggzdl_accountSetupBrief" :class="{on:renzShow}"  @click="tabSwitch('changeRenz')">
            身份认证<i class="ggzdl_accountSetupRight"></i>
        </div>
        <div  v-show="renzShow" >
            <div class="ggzdl_accountSetupPwd" v-show="!checked">
                <div class="ggzdl_accountSetupPwdWrapbd"  >
                    <div class="pgy_account_text">
                        <label>手机号：</label>
                        <div class="pgy_account_mobile">@{{mobile|mobileFormat}}</div>
                    </div>
                    <div class="pgy_account_text">
                        <label>&nbsp;</label>
                        <div class="pgy_account_code">
                            <input class="zmtdl_accountSetupBank_code" type="text" placeholder="输入验证码" v-model="code" maxlength="4">
                            <input class="pgy_accountSetupBank_codebtn" type="button" @click="sendRenzmsg" value="发送验证码" :disabled="disabled" v-model="validCode">
                        </div>
                        <div class="error_msg" v-html="errMsg">@{{errMsg}}</div>
                    </div>
                    <div class="pgy_account_text">
                        <label for="pgy_account_text1">姓名：</label>
                        <div class="pgy_account_input">
                            <i class="pay_account_name"></i>
                            <input type="text" id="pgy_account_text1"  placeholder="姓名" v-model="bindBankObj.bind_realname" @blur="checkName">
                        </div>
                        <div class="error_msg" v-html="errName">@{{errName}}</div>
                    </div>
                    <div class="pgy_account_text">
                        <label for="pgy_account_text2">身份证：</label>
                        <div class="pgy_account_input">
                            <i class="pay_account_id"></i>
                            <input type="text" id="pgy_account_text2"  placeholder="身份证号码" v-model="bindBankObj.bind_idnumber" @blur="checkIdentity">
                        </div>
                        <div class="error_msg" v-html="errIdentity">@{{errIdentity}}</div>
                    </div>
                    <div class="pgy_account_text">
                        <label for="">&nbsp;</label>
                        <input type="button" @click="renzSubmit" value="提  交" class="pgyti">
                    </div>
                    
                </div>

            </div>
            
            <div class="ggzdl_accountSetupPwd" v-show="checked">
                <div class="ggzdl_accountSetupPwdWrap">
                    <div class="pgy_success_g">
                        <img src="/images/pgy_renz_success2.png" alt="">
                        <p class="pgy_successP1">@{{realname}}**</p>
                        <p class="pgy_successP2">@{{idnumber|idNumFormat}}</p>
                    </div>
                </div>
            </div>
        </div>
        <!--认证成功-->
        
    </div>


    <div class="ggzdl_accountSetupWrap">
        <div class="ggzdl_accountSetupBrief" :class="{on:passwordShow}"  @click="tabSwitch('changePassword')">
            修改密码<i class="ggzdl_accountSetupRight"></i>
        </div>
        <div class="ggzdl_accountSetupPwd" v-show="passwordShow">
            <div class="ggzdl_accountSetupPwdWrapbd">
                <div class="pgy_account_text">
                    <label>&nbsp; </label>
                    <div class="pgy_account_pwd">密码必须8位至18位，由英文与数字组成</div>
                </div>
                <div class="pgy_account_text">
                    <label for="">原密码：</label>
                    <div class="pgy_account_input">
                        <i class="pay_account_pwd"></i>
                        <input type="password" class="pgy_input_text" placeholder="请输入原密码" v-model="testPwd.oldpas" @blur="checkErrPwd" maxlength="18">
                    </div>
                    <div class="error_msg" v-html="testPwd.errPwd">@{{testPwd.errPwd}}</div>
                </div>
                <div class="pgy_account_text">
                    <label for="">新密码：</label>
                    <div class="pgy_account_input">
                        <i class="pay_account_pwd"></i>
                        <input type="password" class="pgy_input_text" placeholder="请输入新密码" v-model="testPwd.newpas" @blur="checkErrNewPwd" maxlength="18">
                    </div>
                    <div class="error_msg" v-html="testPwd.errNewPwd">@{{testPwd.errNewPwd}}</div>
                </div>
                <div class="pgy_account_text">
                    <label for="">确认密码：</label>
                    <div class="pgy_account_input">
                        <i class="pay_account_pwd"></i>
                        <input type="password" class="pgy_input_text" placeholder="请输入确认密码" v-model="testPwd.newpas2" @blur="checkErrNewPwd2" maxlength="18">
                    </div>
                    <div class="error_msg" v-html="testPwd.errConfirmPwd">@{{testPwd.errConfirmPwd}}</div>
                </div>
                <div class="pgy_account_text">
                    <label for="">&nbsp;</label>
                    <input type="button" @click="changePwd" value="提交" class="pgyti">
                </div>
            </div>
        </div>
    </div>
        
    <!-- 弹窗 -->
    <div class="ggzdl_popup_wrap" v-show="popupFlag">
        <div class="ggzdl_popup_box popup_box_mail" v-show="popupDiv==='mailBind'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">邮箱绑定</div> 
            <div class="popup_con">
                <p>邮件验证已发送</p>
                <p>邮件已发送至邮箱@{{email2}}<br>请点击邮件中的链接完成操作</p>
            </div>
            <div class="popup_btn" style="margin-top:15px;" @click="goToMail">立即前往</div>
        </div>
        <div class="ggzdl_popup_box popup_box_mobile" v-show="popupDiv==='mobileUnbind'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">旧手机号验证</div>
            <div class="popup_con">
                <div class="pgy_account_text">
                    <div class="pgy_account_mobile">@{{mobile|mobileFormat}}</div>
                </div>
                <div class="pgy_account_text">
                    <div class="pgy_account_code">
                        <input class="zmtdl_accountSetupBank_code" type="text" placeholder="输入验证码" v-model="code" maxlength="4" style="width:131px;">
                        <input class="pgy_accountSetupBank_codebtn" type="button" @click="sendChangeSms1" value="发送验证码" :disabled="disabled1" v-model="validCode1">
                    </div>
                    <div class="error_msg" v-html="errMsg">@{{errMsg}}</div>
                </div>
            </div>
            <div class="popup_btn" style="margin-top:20px;" @click="mobileBindFirst">下一步</div>
        </div>
        <div class="ggzdl_popup_box popup_box_mobile" v-show="popupDiv==='mobileBind'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">新手机号绑定</div>
            <div class="popup_con">
                <div class="pgy_account_text">     
                    <div class="pgy_account_input">
                        <i class="pay_account_mobile"></i>
                        <input id="pgy_account_text3" type="tel" placeholder="输入手机号" v-model="mobile2" maxlength="11">
                    </div>
                    <div class="error_msg" v-html="errMobile">@{{errMobile}}</div>
                </div>
                <div class="pgy_account_text">
                    <div class="pgy_account_code">
                        <input class="zmtdl_accountSetupBank_code" type="text" placeholder="输入验证码" v-model="code" maxlength="4" style="width:131px;">
                        <input class="pgy_accountSetupBank_codebtn" type="button" @click="sendChangeSms2" value="发送验证码" :disabled="disabled" v-model="validCode">
                    </div>
                    <div class="error_msg" v-html="errMsg">@{{errMsg}}</div>
                </div>
            </div>
            <div class="popup_btn" style="margin-top:10px;" @click="mobileBindNext">下一步</div>
        </div>
        <div class="ggzdl_popup_box" v-show="popupDiv==='mobileBindSuc'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">绑定成功</div> 
            <div class="popup_successed">
                <img src="/image/oksuccess.png" alt="">
                <p>手机号绑定成功</p>
            </div>
            <div class="popup_btn" @click="closePopup">确认</div>
            
        </div>

        <div class="ggzdl_popup_box popup_box_head" id="avatar-modal" v-show="popupDiv==='changeHead'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title_head">更换头像</div> 
            <form class="avatar-form">
                <div class="jc-demo-box avatar-body">
                    <div class="avatar-upload">
                        <input class="avatar-src" name="avatar_src" type="hidden">
                        <input class="avatar-data" name="avatar_data" type="hidden">
                        <span id="avatar-name" hidden></span>
                        <input class="avatar-input hide" id="avatarInput" name="avatar_file" type="file">
                    </div>
                    <div id="uploadImg" onclick="$('input[id=avatarInput]').click();">上传图片</div>
                    <div id="reuploadImg" onclick="$('input[id=avatarInput]').click();" class="hide">重新上传</div>
                    <div class="avatar-wrapper" id="preview"></div>
                    <div id="preview-pane">
                        <p>头像预览</p>
                        <div class="avatar-preview" id="imageHead"></div>
                    </div>
                    <div class="popup_confirm">
                        <span @click="closePopup">取消</span>
                        <span class="on avatar-save">确认</span>
                    </div>
                    <div class="error-msg"></div>
                </div>
            </form>
        </div>

        <div class="ggzdl_popup_box" v-show="popupDiv==='changePwdSuc'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">修改密码</div> 
            <div class="popup_successed">
                <img src="/image/oksuccess.png" alt="">
                <p>您已成功修改密码</p>
            </div>
            <div class="popup_btn" @click="closePopup">确定</div>
        </div>

    </div>
    <!-- 弹窗 -->

</div>

<div class="loading" aria-label="Loading" role="img" tabindex="-1"></div>
@endsection



@section("script")

    <script type="text/javascript">
        Vue.filter("bankFormat",function(value){
            return value.substr(value.length-4);
        });

        Vue.filter("mobileFormat",function(value){
            return value.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2');
        });

        Vue.filter("idNumFormat",function(value){
            return value.substr(0,3)+"***********"+value.substr(value.length-4);
        });

         var app = new Vue({
                el: '#accountSetting',
                data: {
                    errName:"",
                    errIdentity:"",
                    errCard:"",
                    errMobile:"",
                    errMail:"",
                    basicShow:false,
                    renzShow:false,
                    passwordShow:false,
                    bindBankObj:{
                        bind_realname:"",
                        bind_idnumber:"",
                        bind_bankid:"",
                        bind_banknumber:"",
                        bind_mobile:""
                    }, 
                    validCode:"发送验证码",
                    validCode1:"发送验证码",
                    times:60,
                    times1:60,           
                    errMsg:"",
                    code:"",
                    realname:"",
                    idnumber:"",
                    mobile:"",
                    mobile2:"",
                    isBind:"",
                    checked:false,
                    disabled:false,
                    disabled1:false,
                    popupFlag:false,
                    popupDiv:"",
                    nickName:"{{\Auth::getUser()->getUserNickname()}}",
                    nickName2:"{{\Auth::getUser()->getUserNickname()}}",
                    nickChange:false,
                    email:"",
                    email2:"",
                    mailFlag:false,
                    mailChange:false,
                    testPwd:{
                        oldpas:"",
                        newpas:"",
                        newpas2:"",
                        errPwd:"",
                        errNewPwd:"",
                        errConfirmPwd:"",
                        pwdTime:3
                    },
                    countStart:"",
                },
                created:function(){
                    var _this=this;

                    //获取实名状态
                    $.ajax({
                        url:'{{route('s_user_getAuthInfo')}}',
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
                                _this.mobile=res.data.mobile;
                                //console.log(res.data)
                                if(_this.isBind===1){
                                    _this.checked=true;
                                    _this.realname=res.data.realname.substr(0, 1);
                                    _this.idnumber=res.data.id_card;
                                    if(res.data.email){
                                        _this.email=res.data.email;
                                        _this.email2=res.data.email;
                                        _this.mailFlag=true;
                                    }                                   
                                } 
                            }else if(res.status==403){
                                location.reload();
                            }else{
                                _this.checked=false;
                                _this.errMsg='<i></i>'+res.message;                                
                                setTimeout(function(){
                                    _this.errMsg="";
                                }, 3000);
                            }
                        }
                    });
                    
                },
                mounted:function(){
                    var _this=this;
                    this.$nextTick(function() {
                        document.getElementById("accountSetting").style.display = "block";
                        //_this.tabSwitch('changePassword');
                        //_this.showPopupDiv('mobileBindSuc');
                        
                    });  
                },
                methods:{
                    checkName:function(){
                        var _this=this;
                        if ( !(/^[\u4e00-\u9fa5]{2,4}$/i.test(this.bindBankObj.bind_realname)) ) {
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
                        if ( !(/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(this.bindBankObj.bind_idnumber)) ) {
                            this.errIdentity="<i></i>请输入正确格式的身份证号";
                            setTimeout(function(){
                                _this.errIdentity="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    checkMobile:function(){
                        var _this=this;
                        if ( !(/^1[34578]\d{9}$/.test(this.bindBankObj.bind_mobile)) ) {
                            this.errMobile="<i></i>请输入正确格式的手机号";
                            setTimeout(function(){
                                _this.errMobile="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    checkMobile2:function(){
                        var _this=this;
                        if ( !(/^1[34578]\d{9}$/.test(_this.mobile2)) ) {
                            _this.errMobile="<i></i>请输入正确格式的手机号";
                            setTimeout(function(){
                                _this.errMobile="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    checkMobileExist:function(){
                        var _this=this;
                        var eflag=false;
                        $.ajax({
                            url:'{{route('s_user_checkMobileExist')}}',
                            type:'POST',
                            async:false,
                            data:{
                                mobile:_this.mobile2
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200 && res.data.mobileExist==1) {
                                    _this.errMobile="<i></i>手机号已存在";
                                    setTimeout(function(){
                                        _this.errMobile="";
                                    }, 3000);
                                    eflag=false;  
                                }else{
                                    eflag=true;
                                }
                            }
                        }); 
                        return eflag;
                    },
                    checkCode:function(){
                        var _this=this;
                        if ( this.code.length < 4 ) {
                            this.errMsg="<i></i>请输入正确的验证码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    checkMail:function(){
                        var _this=this;
                        if ( !(/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/.test(_this.email2)) ) {
                            _this.errMail="<i></i>请输入正确格式的邮箱";
                            setTimeout(function(){
                                _this.errMail="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }  
                        
                    },
                    checkErrPwd:function(){
                        var _this=this;
                        //^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,18}$
                        if ( !(/^[A-Za-z0-9]{8,18}$/.test(_this.testPwd.oldpas)) ) {
                            _this.testPwd.errPwd="<i></i>请输入正确的密码格式";
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
                        if ( !(/^[A-Za-z0-9]{8,18}$/.test(_this.testPwd.newpas)) ) {
                            _this.testPwd.errNewPwd="<i></i>请输入正确的密码格式";
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
                        if ( this.testPwd.newpas !== _this.testPwd.newpas2 ) {
                            _this.testPwd.errConfirmPwd="<i></i>两次密码不一致";
                            setTimeout(function(){
                                _this.testPwd.errConfirmPwd="";
                            }, 3000);
                            return false;
                        } else {
                            return true;
                        }                        
                    },
                    tabSwitch:function(name){
                        if(name==="changeBasic"){
                            this.basicShow=this.basicShow?false:true;
                            this.renzShow=false;
                            this.passwordShow=false;
                        }else if(name==="changeRenz"){
                            this.renzShow=this.renzShow?false:true;
                            this.basicShow=false;
                            this.passwordShow=false;
                        }else if(name==="changePassword"){
                            this.passwordShow=this.passwordShow?false:true;
                            this.basicShow=false;
                            this.renzShow=false;
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
                                            _this.validCode = "发送验证码";
                                            _this.times=60;
                                            _this.disabled=false;
                                        }
                                    }, 1000);
                                }else{
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
                                                    _this.realname=_this.bindBankObj.bind_realname.substr(0, 1);
                                                    _this.idnumber=_this.bindBankObj.bind_idnumber;
                                                }else{
                                                    _this.errMsg='<i></i>'+res.message;
                                                    setTimeout(function(){
                                                        _this.errMsg="";
                                                    }, 3000);
                                                } 
                                            }
                                        });
                                    }else{
                                        _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                    }
                                }
                            }); 
                        }
                    },
                    sendChangeSms:function(){//更换手机号第一步发送短信
                        var _this=this;
                        _this.disabled=true;
                        $.ajax({
                            url:'{{route('s_user_sendChangeSms')}}',
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
                                    _this.errMsg='<i></i>'+res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);  
                                    _this.disabled=false;
                                }
                            }
                        });  
                                              
                    },
                    sendChangeSms1:function(){//身份认证短信手机号
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_user_sendChangeSms')}}',
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
                                    _this.countStart = setInterval(function () {
                                        _this.validCode1 = _this.times1-- + '秒后重发';
                                        _this.disabled1=true;
                                        if (_this.times1 < 0) {
                                            clearInterval(_this.countStart);
                                            _this.validCode1 = "发送验证码";
                                            _this.times1=60;
                                            _this.disabled1=false;
                                        }
                                    }, 1000);
                                }else{
                                    _this.errMsg='<i></i>'+res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);  
                                }
                            }
                        });  
                                              
                    },
                    sendChangeSms2:function(){//更换手机号第二步发送短信
                        var _this=this; 
                        if(_this.disabled){
                            return false;
                        }
                        if(this.checkMobile2() && this.checkMobileExist() ){ 
                            _this.disabled=true;
                            $.ajax({
                                url:'{{route('s_user_sendChangeNewSms')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    mobile:_this.mobile2
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
                                                clearInterval(_this.countStart);
                                                _this.validCode = "发送验证码";
                                                _this.times=60;
                                                _this.disabled=false;
                                            }
                                        }, 1000);
                                    }else{
                                        _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);  
                                        _this.disabled=false;
                                    }
                                }
                            });    
                        }
                                            
                    },
                    showPopupDiv:function(name){//弹窗显示
                        var _this=this;
                        _this.popupDiv=name;
                        _this.popupFlag=true;
                    },
                    closePopup:function(){//弹窗关闭
                        var _this=this;
                        _this.popupDiv="";
                        _this.popupFlag=false;
                        _this.disabled1=false;
                        _this.times1=60;
                        _this.validCode1 = "发送验证码";
                        clearInterval(_this.countStart);
                    },
                    saveNick:function(){//保存昵称
                        var _this=this;
                        if(this.nickName2==""){
                            _this.errMsg="<i></i>请输入正确的昵称";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                            return false;
                        }
                        $.ajax({
                            url:'{{route('s_user_changeNickname')}}',
                            type:'POST',
                            async:true,
                            data:{
                                nickname:_this.nickName2
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    //alert('成功');
                                    _this.nickName=_this.nickName2;
                                    _this.nickChange=false;
                                    $(".pgy_admin_name a,.ggzdl_adminuser_name").text(_this.nickName2);
                                    
                                }else{
                                    _this.errMsg='<i></i>'+res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000);  
                                }
                            }
                        });   
                    },
                    sendMail:function(){
                        var _this = this;
                        if(_this.checkMail()){
                            $.ajax({
                                url:'{{route('s_user_sendEmail')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    email:_this.email2
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        //alert('成功');
                                        _this.showPopupDiv('mailBind');
                                    }else{
                                        _this.errMail='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMail="";
                                        }, 3000);  
                                    }
                                }
                            });  
                        }
                         
                    },
                    goToMail:function(){
                        var _this = this;
                        var _email = "";
                        var hash = {
                            'qq.com': 'http://mail.qq.com',
                            'gmail.com': 'http://mail.google.com',
                            'sina.com': 'http://mail.sina.com.cn',
                            '163.com': 'http://mail.163.com',
                            '126.com': 'http://mail.126.com',
                            'yeah.net': 'http://www.yeah.net/',
                            'sohu.com': 'http://mail.sohu.com/',
                            'tom.com': 'http://mail.tom.com/',
                            'sogou.com': 'http://mail.sogou.com/',
                            '139.com': 'http://mail.10086.cn/',
                            'hotmail.com': 'http://www.hotmail.com',
                            'live.com': 'http://login.live.com/',
                            'live.cn': 'http://login.live.cn/',
                            'live.com.cn': 'http://login.live.com.cn',
                            '189.com': 'http://webmail16.189.cn/webmail/',
                            'yahoo.com.cn': 'http://mail.cn.yahoo.com/',
                            'yahoo.cn': 'http://mail.cn.yahoo.com/',
                            'eyou.com': 'http://www.eyou.com/',
                            '21cn.com': 'http://mail.21cn.com/',
                            '188.com': 'http://www.188.com/',
                            'foxmail.com': 'http://www.foxmail.com',
                            'outlook.com': 'http://www.outlook.com'
                        }
                        _email = _this.email2.split('@')[1];    //获取邮箱域
                        for (var j in hash){
                            if(j == _email){
                                window.open(hash[_email]);    //替换登陆链接
                                return;
                            }
                        }
                        window.open("http://mail."+_email); //其他公司邮箱
                    },
                    mobileBindFirst:function(){
                        var _this = this;
                        if(_this.checkCode()){
                            $.ajax({
                                url:'{{route('s_user_validatorChangeSMS')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    code:_this.code
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        //alert('成功');
                                        _this.times = 0;
                                        setTimeout(function(){
                                            _this.code = "";
                                            _this.errMsg = "";
                                            _this.showPopupDiv('mobileBind');
                                        },1000);
                                    }else{
                                        _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);  
                                    }
                                }
                            });  
                            
                        }
                    },
                    mobileBindNext:function(){
                        var _this = this;
                        if(_this.checkMobile2() && _this.checkCode()){
                            $.ajax({
                                url:'{{route('s_user_validatorChangeNewSMS')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    code:_this.code
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        //alert('成功');
                                        $.ajax({
                                            url:'{{route('s_user_ChangeNewMobile')}}',
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
                                                    //alert('成功');
                                                    _this.showPopupDiv("mobileBindSuc");
                                                    _this.mobile=_this.mobile2;
                                                    _this.validCode = "发送验证码";
                                                    _this.times=-1;
                                                    _this.disabled=false;
                                                    _this.code="";
                                                    _this.mobile2="";
                                                }else{
                                                    _this.errMsg='<i></i>'+res.message;
                                                    setTimeout(function(){
                                                        _this.errMsg="";
                                                    }, 3000);  
                                                }
                                            }
                                        });  
                                    }else{
                                        _this.errMsg='<i></i>'+res.message;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);  
                                    }
                                }
                            });  
                            
                        }
                    },
                    changePwd:function(){//修改登录密码
                        var _this=this;
                        if ( _this.checkErrPwd() && _this.checkErrNewPwd() && _this.checkErrNewPwd2() ){
                            $.ajax({
                                url:'{{route('s_user_changePassword')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    oldpas:_this.testPwd.oldpas,
                                    newpas:_this.testPwd.newpas,
                                    newpas2:_this.testPwd.newpas2
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        _this.showPopupDiv('changePwdSuc');
                                        _this.testPwd.oldpas="";
                                        _this.testPwd.newpas="";
                                        _this.testPwd.newpas2="";
                                    }else{
                                        _this.testPwd.errPwd="<i></i>"+res.message;
                                        setTimeout(function(){
                                            _this.testPwd.errPwd="";
                                        }, 3000);                                       
                                    }
                                }
                            });
                        }                        
                    }
                }
            });
 
    </script>
    
    <script src="/js/html2canvas.min.js" type="text/javascript"></script>
    
    <script type="text/javascript">
		//做个下简易的验证  大小 格式 
        $('#avatarInput').on('change', function(e) {
            var filemaxsize = 1024 * 5;//5M
            var target = $(e.target);
            var Size = target[0].files[0].size / 1024;
            if(Size > filemaxsize) {
                alert('图片过大，请重新选择!');
                $(".avatar-wrapper").children().remove;
                return false;
            }
            if(!this.files[0].type.match(/image.*/)) {
                alert('请选择正确的图片!')
            } else {
                var filename = document.querySelector("#avatar-name");
                var texts = document.querySelector("#avatarInput").value;
                var teststr = texts; //你这里的路径写错了
                testend = teststr.match(/[^\\]+\.[^\(]+/i); //直接完整文件名的
                filename.innerHTML = testend;
                $("#uploadImg").addClass("hide");
                $("#reuploadImg").removeClass("hide");
            }
        
        });

        $(".avatar-save").on("click", function() {
            var img_lg = document.getElementById('imageHead');
            // 截图小的显示框内的内容
            html2canvas(img_lg, {
                allowTaint: true,
                taintTest: false,
                onrendered: function(canvas) {
                    canvas.id = "mycanvas";
                    //生成base64图片数据
                    var dataUrl = canvas.toDataURL("image/jpeg");
                    var newImg = document.createElement("img");
                    newImg.src = dataUrl;
                    imagesAjax(dataUrl)
                }
            });
        })

        //保存选择头像
        function imagesAjax(src) {
            if($(".avatar-wrapper").html()!=""){
                $.ajax({
                    url:'{{route('s_user_changeAvatar')}}',
                    type:'POST',
                    async:true,
                    data:{
                        avatar:src
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            var imgurl=res.newHeadurl;
                            $(".ggzdl_basic_portrait img,.pgy_admin_top img,.ggzdl_adminuser_avator img").attr("src",imgurl);
                            app.closePopup();
                        }else{
                            //头像更换失败
                            $(".popup_box_head .error-msg").html("上传失败");
                            setTimeout(function(){
                                $(".popup_box_head .error-msg").html("");
                            }, 3000);  
                        }
                    }
                });   
            }
        }
    </script>
@endsection

