@extends('layout')

@section("title")
    {{$article['name']}}
@endsection


@section("css")
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
    <link rel="stylesheet" href="{{ mix('css/swiper.min.css') }}">
@endsection

@section("content")
    <div id="detail">

        <!--未认证身份登录注册-->
        <div class="login_into" style=" display:none;" >
            <div class="login_intoBgcolor"></div>
            <div class="ggzdl_loginBox">
                <a href="javascript:;" class="pgy_closeLogin"></a>
                <div class="ggzdl_loginBoxTab">
                    <span :class="{on:wxShow}" @click="tabSwitch('wx')" style="float:left;">微信登录</span>
                    <span :class="{on:mobileShow}" @click="tabSwitch('mobile')" style="float:left;margin-left: 60px">手机登录</span>
                    <span :class="{on:mailShow}" @click="tabSwitch('mail')" style="float:right;">邮箱登录</span>
                </div>

                <!--微信登录-->
                <div v-show="wxShow" id="qrcode" class="ggzdl_loginBoxBody">
                    <div class="qrcode" id="loginCode"></div>
                </div>

                <!--手机登录-->
                <div v-show="mobileShow" id="ggzdlLogin" class="ggzdl_loginBoxBodys">
                    <div id="ggzdlInputTel" style="position: relative;">
                        <input id="username" class="ggzdl_inputs" maxlength="11" type="tel" v-model="username" placeholder="手机号">
                        <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg}}</div>
                        <!-- <span class="pgy_yz">输入正确的手机号码</span> -->
                    </div>
                    <div id="ggzdlInputPwd" style="position: relative;">
                        <input id="userpassword" class="ggzdl_inputs" maxlength="18" style="margin: 0;" v-model="password" type="password" placeholder="输入密码">
                    </div>
                    <a class="ggzdl_forgetpwd" href="{{Route('s_forgetVerifyTel')}}">忘记密码</a>
                    <input class="ggzdl_btns" type="button" @click="toLogin" value="立 即 登 录">
                </div>

                <!--邮箱登录-->
                <div v-show="mailShow" class="ggzdl_loginBoxBodys">
                    <div  style="position: relative;">
                        <input  class="ggzdl_inputs" type="text" placeholder="邮箱">
                        <div style=" font-size:12px; color:#FF7241; height:30px; line-height:30px; padding:0 10px;">@{{errMsg2}}</div>
                    </div>
                    <div  style="position: relative;">
                        <input  class="ggzdl_inputs" maxlength="18" style="margin: 0;" type="password" placeholder="输入密码">
                    </div>
                    <a class="ggzdl_forgetpwd" href="{{Route('s_forgetVerifyTel')}}">忘记密码</a>
                    <input class="ggzdl_btns" type="button"  value="立 即 登 录">
                </div>
                <div class="ggzdl_toRegisters">还没有账号?<a href="{{route('s_register')}}">立即注册</a></div>
            </div>
        </div>

        <!-- 一键赋值 -->
        <div class="pgy_copying" style="display:none">
            <div class="pgy_copyingBg"></div>
            <div class="pgy_copyingContent">
                <i class="pgy_copyClose"></i>
                <div class="pgy_bigCopy">
                    <div class="pgy_wechat">
                        <div class="pgy_rotateY">
                            <div class="watintRotates" v-show="pgyTrotate">
                                <div>
                                    <img src="/image/rotatey.png" alt="" class="paused1" id="paused1">
                                </div>
                                <div>
                                    <img src="/image/rotatex.png" alt="" class="paused2" id="paused2">
                                </div>
                            </div>
                            <p >@{{copying}}</p>
                        </div>
                        <div class="pgy_static">
                            <div>
                                <img src="/image/xw.png" alt="">
                                <p>微信公共平台</p>
                            </div>
                            <div>
                                <img src="/image/xt.png" alt="">
                            </div>
                            <div>
                                <img src="/image/xh.png" alt="">
                                <p>
                                    <span>消息管理</span><br/>
                                    <span>用户管理</span><br/>
                                    <span style="color:#FF7241">素材管理</span><br/>
                                </p>
                            </div>
                            <div>
                                <img src="/image/xt.png" alt="">
                            </div>
                            <div>
                                <p>图文消息</p>
                                <img src="/image/xq.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pgyknow" v-show="secondShow">我知道了</div>
            </div>
        </div>

        <!-- 媒体类型 -->
        <!-- <div v-if=" meidatypeflag>0">
            <div class="pgy_mediaType" style="display:none">
                <div class="pgy_mediaBg" @click="close_bgAll"></div>
                <div class="pgy_mediaContent">
                    <i class="pgy_mediaClose" @click="closeMedias()"></i>
                    <div class="pgy_fenleis">
                        <div class="fenleis_title" id="fenleis_title">
                            <div class="chose_medias">请选择您的媒体类型</div>
                            <ul class="choseUl">
                                <li>综合资讯类
                                    <ul class="fenl_contents" style="margin-top:55px;" >
                                        <li :class="{activeT:nums1==t.index}"  @click="chosedTypes(t.index,t.message)" v-for="(t,index) in mediaTypeData1">@{{t.message}}</li>
                                    </ul>
                                </li>
                                <li>美食旅游类
                                    <ul class="fenl_contents" style="margin-top:55px;">
                                        <li  :class="{activeT:nums2==t.index}" @click="chosedTypes(t.index,t.message)" v-for="(t,index) in mediaTypeData2">@{{t.message}}</li>
                                    </ul>
                                </li>
                                <li>游戏动漫类
                                    <ul class="fenl_contents" style="margin-top:55px;">
                                        <li  :class="{activeT:nums3==t.index}" @click="chosedTypes(t.index,t.message)" v-for="(t,index) in mediaTypeData3">@{{t.message}}</li>

                                    </ul>
                                </li>
                                <li>生活教育类
                                    <ul class="fenl_contents" style="margin-top:55px;">
                                        <li  :class="{activeT:nums4==t.index}" @click="chosedTypes(t.index,t.message)" v-for="(t,index) in mediaTypeData4">@{{t.message}}</li>
                                    </ul>
                                </li>
                                <li>数码科技类
                                    <ul class="fenl_contents" style="margin-top:55px;">
                                        <li  :class="{activeT:nums5==t.index}" @click="chosedTypes(t.index,t.message)" v-for="(t,index) in mediaTypeData5">@{{t.message}}</li>
                                    </ul>
                                </li>
                            </ul>
                            <div id="backgroundW" style="padding-bottom:32px;">
                            </div>
                            <div class="type_confirm">
                                <span @click="sureOk()">确认</span>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div> -->

        <!--头部-->

        <div class="pgy_infoDetail" style=" padding: 0;margin-top:81px">
            <div style=" width: 1200px; margin: 0 auto;">

            </div>
            <div class="pgy_1200">
                <div class="pgy_detail_left">
                    <div class="pgy_y_copy" style=" margin-bottom: 20px;">
                        <div class="pgy_y_time" style=" width: 180px;">{{date('Y-m-d H:i',$article['add_time'])}}</div>
                    <!-- <a class="pgy_y_webcat" href="javascript:;">
                    <div class="pgy_weixin" id="shareCode"></div>
                </a>
                <div class="pay_yCopy">
                    <a href="{{$openUrl}}" target="_blank">一键复制</a>
                </div> -->
                    </div>
                    <h1>{{$article['name']}}</h1>
                    <input type = 'hidden' id="aid" value="{{$article['id'] or 0}}">
                    <input type = 'hidden' id="aprs" value="{{$article['article_product_relateId'] or 0}}">
                    <div class="pgy_article">
                        {!! $article['content'] !!}
                    </div>
                    <div class="pgy_infoDetail_hot" style="margin-top:60px;margin-bottom: 100px">
                        <div class="pgy_infoHot_title">
                            <span class="pgy_infoHot_span">热点内容</span>
                        </div>
                        @if(count($lastweekhotrank) > 0)
                            @foreach($lastweekhotrank as $lasthot)
                                <a href="{{route('s_aricle_detail',['id'=>$lasthot['tarticleid']])}}" target="_blank">
                                    <div class="pgy_info_list_article">
                                        <div class="pay_info_href_article">
                                            <div class="pgy_info_pic_article">

                                                <img src="{{ $lasthot['imgs']['newpath285'] or ''}}" alt="">

                                            </div>
                                            <div class="pgy_info_neir_article">
                                                <p class="pgy_info_p_article">{{$lasthot['name']}}</p>
                                                <p class="pgy_info_span_article"><span>{{time_tranx($lasthot['add_time'])}}</span><span style="padding-left: 18px"></span></p>
                                            </div>
                                        </div>
                                        <div class="pgy_hot_marks_article">
                                            <span>分成 {{$lasthot['percentKey']}}</span>
                                            <div class="sanjiao"></div>
                                        </div>
                                    </div></a>
                            @endforeach
                        @endif


                    </div>
                </div>

                @if($goods['product_type'] !=3)

                    <div class="pgy_detail_right" >
                        <div class="pgy_show_commodity">
                            <div class="pgy_comodity_t">
                                <img onerror="javascript:this.src='{{$goods['img_path']}}';" src="{{$goods['new_path325'] }}" alt="" width="244px" height="244px">


                            </div>
                            <div class="pgy_comodity_c">
                                <p class="pgy_comodity_p" style="margin-top: 0;">{{$article['product_name']}}</p>
                                <p>
                                    {{--goods['min_price']goods['max_price']可能两者相同--}}
                                    <span class="pgy_comodity_money"><i>¥</i>{{number_format($goods['selling_price'],2)}}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    @if($goods['orderCount']>0)
                                        <span style=" float: right;">{{$goods['orderCount']}}人购买</span>
                                    @else
                                        <span style=" color: #cdcdcd; float: right;">暂无购买</span>
                                    @endif
                                </p>

                                <hr style="height:1px;border:none;border-top:1px dashed #7e7e7e;" />
                            <!--<p class="pgy_comodity_p" style="margin-bottom: 0;">剩余库存 @if($goods['num'] == '-1') 不限量 @else <span>{{$goods['surplus_num']}}</span>件 @endif</p>-->
                                <p class="pgy_comodity_p" style=" margin-top: 5px;">剩余时间 {{$goods['syts']}}</p>
                            </div>

                            <div id="pgy1" class="pgy_comodity_commond" style="height: 320px;">

                                <div class="pgy_commond_title" style="font-size:24px;width:285px;">推广分成<span>&nbsp;
                                        @if($article['channleway'] == 'account')
                                            {{number_format($article['channlepercent'],2)}}
                                        @else

                                            {{number_format($article['channlepercent'],2)}}%
                                        @endif
                        </span></div>
                                <div class="pgy_commond_pout" @click="commondS"><span>我要推广</span></div>
                                <div class="pgy_commond_code"><a href="">点击获取推广二维码</a></div>
                            </div>

                            <div id="pgy2">
                                <div class="pgy_generalize_href" >
                                    <h1>推广后，完成订单即可获得分成</h1>
                                    <h2>你可以通过以下方式推广</h2>
                                    <div class="pgy_step1"><span>方式1</span></div>
                                    <div id="goodsqrcode" class="er_d" style=" width: 180px; margin: 0 auto;"></div>
                                    <h2>微信扫二维码进行转发</h2>                                   
                                    <div class="pgy_step1"><span>方式2</span></div>
                                    <a class="pgy_generalize_link" @click="pgy_generalize_link" style="cursor:pointer">一键转至公微</a>
                                    <h2>授权后可快速转载到你的公微</h2>
                                    <div class="pgy_step1"><span>方式3</span></div>
                                    <p id="urlcode" style="width: 250px; margin: 0 auto; font-size: 14px; color: #7e7e7e; word-break: break-word;text-align:center;"></p>
                                    <p style="width: 250px; margin: 20px auto 40px auto; font-size: 13px; line-height: 20px; text-align: center; color: #7e7e7e; word-break: break-word;">手动复制原文并附带商品购买链接<br>也可以将此链接复制到公微的阅读原文内</p>
                                    <div class="pgy_finish_order">
                                        <span class="first">完成订单即可获得</span><br>
                                        @if($article['channleway'] == 'account')
                                            <span class="second">{{number_format($article['channlepercent'],2)}}</span>
                                        @else
                                            <span class="second">{{number_format($article['afterchannlepercent'],2)}}</span>
                                        @endif
                                    </div>
                                </div>
                                <div style=" font-size: 14px; color: #7e7e7e; text-align: center; margin: 20px 0 0 0;">Tips：收益收益可在用户中心进行查看</div>
                            </div>

                        </div>
                    </div>

                @else

                    <div class="pgy_detail_right" > <!-- cpa显示的右边 -->
                        <div class="pgy_show_commodity">
                            <div class="pgy_comodity_t"><img src="{{$goods['img_path'] }}" alt="" width="244px" height="244px"></div>
                            <div class="pgy_comodity_c" style="height:78px;">
                                <p class="pgy_comodity_p" style="margin-top: 0;">{{$article['product_name']}}</p>
                            </div>


                            <div id="pgy1" class="pgy_comodity_commond" style="height: 320px;">

                                <div class="pgy_commond_title" style="font-size:24px;width:285px;">推广分成<span>&nbsp;
                                        @if($article['channleway'] == 'account')
                                            {{number_format($article['channlepercent'],2)}}
                                        @else

                                            {{number_format($article['channlepercent'],2)}}%
                                        @endif
                        </span></div>
                                <div class="pgy_commond_pout" @click="commondS"><span>我要推广</span></div>
                                <div class="pgy_commond_code"><a href="">点击获取推广二维码</a></div>
                            </div>

                            <div id="pgy2">
                                <div class="pgy_generalize_href" >
                                    <h1>推广后，完成订单即可获得分成</h1>
                                    <h2>你可以通过以下方式推广</h2>
                                    <div class="pgy_step1"><span>方式1</span></div>
                                    <div id="goodsqrcode" class="er_d" style=" width: 180px; margin: 0 auto;"></div>
                                    <h2>微信扫二维码进行转发</h2>                                    
                                    <div class="pgy_step1"><span>方式2</span></div>
                                    <a class="pgy_generalize_link" @click="pgy_generalize_link" style="cursor:pointer">一键转至公微</a>
                                    <h2>授权后可快速转载到你的公微</h2>
                                    <div class="pgy_step1"><span>方式3</span></div>
                                    <p id="urlcode" style="width: 250px; margin: 0 auto; font-size: 14px; color: #7e7e7e; word-break: break-word;text-align:center;"></p>
                                    <p style="width: 250px; margin: 20px auto 40px auto; font-size: 13px; line-height: 20px; text-align: center; color: #7e7e7e; word-break: break-word;">产品链接</p>
                                    <div class="pgy_finish_order">
                                        <span class="first">核验通过即可获得</span><br>
                                        <span class="second">{{number_format($goods['selling_price']*$article['channlepercent']/100,2)}}</span>
                                    </div>
                                </div>
                                <div style=" font-size: 14px; color: #7e7e7e; text-align: center; margin: 20px 0 0 0;">Tips：收益收益可在用户中心进行查看</div>
                            </div>

                        </div>
                    </div>
                @endif

            </div>
        </div>

        <div class="pgy_toTop"></div>
    </div>
@endsection
@section("script")
    <script src="{{config('params.httpType')}}://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">

        $(".pgy_article section").css("width","auto");
        $(function () {

            var app=new Vue({
                el:'#detail',
                data:{
                    s:"{{$rand}}",
                    titleName:"",
                    wxShow:true,
                    mobileShow:false,
                    mailShow:false,
                    username:"",
                    password:"",
                    errMsg:"",
                    secondShow:false,
                    copying:'文章复制中',
                    pgyTrotate:true,
                    email:"",
                    passwd:"",
                    errMsg:"",
                    errMsg2:"",
                    clicked:false,
                    Category_ids:-1,
                    // isShow:{{count($article_category)}},
                    isShow:0,
                    fenShow:1,//标记：1推广,2一键
                    checkeds:false,
                    spreadid:"",
                    mediaTypeData1:[
                        {index:0, message: '地方号' },{index:1, message: '段子' },{index:2, message: '时事热点' },{index:3, message: '娱乐' },{index:4, message: '时尚' },
                        {index:5, message: '视频' },{index:6, message: '金融财经' },{index:7, message: '体育' },{index:8, message: '综合资讯' }
                    ],
                    mediaTypeData2:[
                        {index:9, message: '美食' },{index:10, message: '购物' },{index:11, message: '酒店' }, {index:12, message: '旅游' }, {index:13, message: '视频' }
                    ],
                    mediaTypeData3:[
                        {index:14, message: '游戏' },{index:15, message: '动漫' },{index:16, message: '二次元' },{index:17, message: '电影' },{index:18, message: '音乐' }
                    ],
                    mediaTypeData4:[
                        {index:19, message: '生活' },{index:20, message: '情感星座' },{index:21, message: '两性' },{index:22, message: '亲子' },{index:23, message: '健康' },
                        { message: '教育' }
                    ],
                    mediaTypeData5:[
                        {index:24, message: '数码' },{index:25, message: '家居' },{index:26, message: '汽车' },{index:27, message: '房产' },{index:28, message: '科技时尚' }
                    ],
                    nums1:0,
                    nums2:0,
                    nums3:0,
                    nums4:0,
                    nums5:0,
                    
                },
                created:function(){
                    var _this=this;
                    this.getpush();
                },
                mounted:function(){
                    var _this=this;
                    $(".pgy_toTop").click(function() {
                        $("html,body").animate({scrollTop:0}, 500);
                    });

                    $(window).scroll(function() {
                        if($(this).scrollTop()>500){
                            $(".pgy_toTop").show();
                        }else{
                            $(".pgy_toTop").hide();
                        }

                    });

                },
                methods:{
                    getpush:function(){
                        var _this=this;
                        if(_this.s == ''){
                            return false;
                        }
                        setTimeout(function(){
                            $.ajax({
                                url:'{{route('s_weixin_checks')}}',
                                type:'POST', //GET
                                async:true,    //或false,是否异步
                                data:{
                                    rand:_this.s
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                dataType:'json',
                                success:function(data){
                                    if(data.status==200){
                                        _this.secondShow=true;
                                        _this.copying='复制成功';
                                        $('#paused1').removeClass('paused1').addClass('paused');
                                        $('#paused2').removeClass('paused2').addClass('paused');
                                    }else if(res.status==403){
                                        location.reload();
                                    }else{
                                        _this.getpush();
                                        $('#paused1').removeClass('paused');
                                        $('#paused2').removeClass('paused');
                                    }

                                }
                            })

                        }, 1000);
                    },
                    commondS:function(){//推广
                        var _this=this;
                        _this.fenShow=1;
                        //console.log(_this.meidatypeflag);
                        // if(_this.isShow>0){  //点击推广没选内容类型
                        if(_this.meidatypeflag>0){
                            $('.pgy_mediaType').show();
                            //console.log("推广"+_this.meidatypeflag);
                        }else{
                            tuiguang();
                        }

                    },
                    pgy_generalize_link:function(){//一键
                        var _this=this;
                        _this.fenShow=2;
                        // if(_this.isShow>0){ //点击一键没选内容类型
                        if(_this.meidatypeflag>0){
                            $('.pgy_mediaType').show();
                            //console.log("一键"+_this.meidatypeflag);
                        }else{
                            pgy_generaliz();
                        }
                    },
                    toLogin:function(){
                        var _this=this;
                        if ( (/^1[34578]\d{9}$/.test(this.username)) && (/^[A-Za-z0-9]{8,18}$/.test(this.password)) ){
                            $.ajax({
                                url:'{{route('s_auth_login')}}',
                                type:'POST', //GET
                                async:true,    //或false,是否异步
                                data:{
                                    username:_this.username,
                                    password:_this.password,
                                    fastlogin:'fastlogin'
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200){
                                        window.location.reload();
                                    }
                                    else {
                                        _this.errMsg=res.msg;
                                        setTimeout(function(){
                                            _this.errMsg="";
                                        }, 3000);
                                    }
                                }
                            });
                        } else {
                            _this.errMsg="请输入正确格式的手机号或密码";
                            setTimeout(function(){
                                _this.errMsg="";
                            }, 3000);
                        }
                    },
                    toLogin2:function(){
                        var _this=this;
                        if ( (/^[a-zA-Z0-9_-]+@([a-zA-Z0-9]+\.)+(com|cn|net|org)$/.test(this.email)) && (/^[A-Za-z0-9]{8,18}$/.test(this.passwd))){
                            $.ajax({
                                url:"{{ route('s_login_emailLogin') }}",
                                type:'POST',
                                async:true,
                                data:{
                                    email:_this.email,
                                    passwd:_this.passwd
                                },
                                "headers": {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(res){
                                    if(res.status==200) {
                                        location.href="{{route('s_user_accountInfo')}}";
                                    }else{
                                        _this.errMsg2=res.message;
                                        setTimeout(function(){
                                            _this.errMsg2="";
                                        }, 3000);
                                    }
                                }
                            });
                        }
                        else {
                            _this.errMsg2="请输入正确格式的邮箱或密码";
                            setTimeout(function(){
                                _this.errMsg2="";
                            }, 3000);
                        }
                    },
                    tabSwitch:function(type){
                        switch (type) {
                            case "wx":
                                this.wxShow=true;
                                this.mobileShow=false;
                                this.mailShow=false;
                                break;
                            case "mobile":
                                this.wxShow=false;
                                this.mobileShow=true;
                                this.mailShow=false;
                                break;
                            case "mail":
                                this.wxShow=false;
                                this.mobileShow=false;
                                this.mailShow=true;
                                break;
                            default:
                                break;
                        }
                    },
                    // chosedTypes:function(Category_id,name){
                    chosedTypes:function(index,name){
                        var _this=this;
                        _this.clicked=true;
                        _this.checkeds=true;
                        _this.nums1=index;
                        _this.nums2=index;
                        _this.nums3=index;
                        _this.nums4=index;
                        _this.nums5=index;
                        _this.names=name;
                    },
                    // sureOk:function(Category_ids,names){
                    sureOk:function(names){
                        var _this=this;
                        $('.pgy_mediaType').hide();

                        $.ajax({
                            url:'{{route('s_aricle_postCategoryChose')}}',
                            type:'POST', //GET
                            async:true,    //或false,是否异步
                            data:{
                                // industry_id:_this.Category_ids
                                category_name:_this.names
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },

                            dataType:'json',
                            success:function(data){
                                if(data.status==200){
                                    $('.pgy_mediaType').hide();
                                    if(_this.fenShow==2){//如果标记为2就是一键
                                        pgy_generaliz();
                                    }else{//否则不为2为1就是推广
                                        tuiguang();
                                    }
                                    //判断走推广还是走一键

                                }

                            }
                        })

                    },
                    closeMedias:function(){
                        var _this=this;
                        $('.pgy_mediaType').hide();
                    },
                    close_bgAll:function(){
                        $('.pgy_mediaType').hide();
                    }
                }
            });

            //二维码登陆
            var loginCode = new QRCode(document.getElementById('loginCode'), {
                width: 191,
                height: 191,
            });
            loginCode.makeCode("{{ $wxHost }}auth/weixin/login/{{ $pc_jzstate }}/1");

            function ajaxLogin() {
                $.ajax({
                    url:"{{route('s_weixin_ajax_login')}}?_date="+new Date(),
                    type:'POST', //GET
                    async:true,    //或false,是否异步
                    data:{
                        code:"{{ $pc_jzstate }}",
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    timeout:600000,    //超时时间
                    dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                    success:function(data,textStatus,jqXHR){
                        if(data.type==1){
                            //登录
                            window.location.reload();
                        }else if(data.type==2){
                            //注册
                            window.location = "/regBindMobile";
                        }else if(data.type==3){

                        }
                    }
                })
            }

            function aaa(){
                setInterval(function () { ajaxLogin() },5000); //循环计数
            }

            $(".pgy_closeLogin").on("click",function(){
                $('.login_into').hide();
            });
            $(".pgy_copyClose").on("click",function(){
                $('.pgy_copying').hide();
            });
            $(".pgyknow").on("click",function(){
                $('.pgy_copying').hide();
            });

            var user2 = "{{$user}}";
            $('#pgy1').show();
            $('#pgy2').hide();
            /* if(user2){
                 $('#pgy1').hide();
                 $('#pgy2').show();
             }else{
                 $('#pgy1').show();
                 $('#pgy2').hide();
             }*/

            <!--商品二维码-->
            function tuiguang(){
                var _this=this;
                var user = "{{$user}}";
                if(!user){
                    $('.login_into').show();
                    aaa();
                    return false;
                }else{

                }
//                console.log(111);
                $.ajax({

                    url:'{{route('s_aricle_createSpreadQRcode')}}',
                    type:'POST', //GET
                    async:true,    //或false,是否异步
                    data:{
                        aid:$('#aid').val(),
                        aprs:$('#aprs').val()
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    timeout:5000,    //超时时间
                    dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                    success:function(data,textStatus,jqXHR){
                        if(data.status==200){
                            _this.spreadid=data.data.id;
                            var qRcode = '{{$qRcode}}';
                            if(qRcode!=1) {
                                window.location.reload();
                                return false;
                            }
                            $('#urlcode').html(data.data.url);
                            $("#goodsqrcode").html('');

                            function makeCodeL () {
                                var goodsqrcode = new QRCode(document.getElementById('goodsqrcode'), {
                                    width: 180,
                                    height: 180,
                                });
                                goodsqrcode.makeCode('{!! $url !!}');

                            }

                            makeCodeL();

                            $('#pgy1').hide();
                            $('#pgy2').show();

                        }
                        else {
                            //console.log(data);
                        }
                    }
                });


            }
            function pgy_generaliz(){
                var _this=this;
                //console.log(_this.spreadid);
                $.ajax({
                    url:"{{route('s_article_addZhuanQuantity')}}",
                    type:'POST', //GET
                    async:true,    //或false,是否异步
                    data:{
                        spread_id:_this.spreadid,
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    dataType:'json',    //返回的数据格式：json/xmlml/script/jsonp/text
                    success:function(res){
                        // if(data.status==200){
                        // }
                    }
                })
                window.open('{!! $openUrl !!}');
            }

            var qRcode = '{{$qRcode}}';

            if(qRcode==1){
                // $(".pgy_commond_pout").click();
                tuiguang();
            }
            var rand = '{{$rand}}';
            if(rand){
                $('.pgy_copying').show();
            }
        });

    </script>
@endsection
