<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>【欢迎领取VIPKID388学习礼包】</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no, email=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--强制竖屏-->
    <meta name="screen-orientation" content="portrait">
    <!--点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/cpa.css') }}">
</head>
<body>
<div id="cpa">
    <div class="header">
        <div class="fb_headerInfo" style="padding-top:0px" tabindex="1">
            <div class="h_headline" style="text-align:center;font-size:21px;color:#FFFFFF;">
                【欢迎领取VIPKID388学习礼包】
            </div>
        </div>
    </div>
    <div id="page1" v-if="pageShow">

        <div class="body">
            <div class="pic">
                <img src="http://pcdn.mikecrm.com/ugc_6_b/pub/gm/gm07di4lerkndo5wczsp68ws5muwa3iq/form/image/QkAnUs42FXgaufgEM7sceJAKbGPORPuH.jpg">
            </div>
            <div style=" margin: 10px 0 0 0;">
                <span>您的名字</span>
                <span style=" margin-left: 5px; color: #DA2824;">*</span>
            </div>
            <div class="fbc_input">
                <input class="fbi_input aria-content" type="text" placeholder="请填写中文名哦" v-model="name">
            </div>
            <div style=" margin: 10px 0 0 0;">
                <span>手机号码</span>
                <span style=" margin-left: 5px; color: #DA2824;">*</span>
            </div>
            <div class="fbc_input">
                <input class="fbi_input aria-content" type="text" placeholder="请填写手机号哦" v-model="mobile">
            </div>
            <div style=" margin: 10px 0 0 0;">
                <span>您孩子的年龄：</span>
                <span style=" margin-left: 5px; color: #DA2824;">*</span>
            </div>
            <div class="fbc_input">
                <span v-show="!age" style=" position: absolute; height: 43px; line-height: 43px; padding: 0 5px; font-size: 16px; font-weight: normal;">仅适合4-12周岁的小朋友</span>
                <select class="fbc_innerSelect" v-model="age" style="height:43px;line-height:1.4;font-size:16px;font-weight:normal;">
                    <option class="fbc_selectLi" value="201546734">4</option>
                    <option class="fbc_selectLi" value="201546735">5</option>
                    <option class="fbc_selectLi" value="201546736">6</option>
                    <option class="fbc_selectLi" value="201546737">7</option>
                    <option class="fbc_selectLi" value="201546738">8</option>
                    <option class="fbc_selectLi" value="201546739">9</option>
                    <option class="fbc_selectLi" value="201546740">10</option>
                    <option class="fbc_selectLi" value="201546741">11</option>
                    <option class="fbc_selectLi" value="201546742">12</option>
                </select>
                <span class="arrow">▼</span>
            </div>
            <div style=" margin: 10px 0 0 0; line-height: 27px;">
                <span>☆ 本课程为在线1对1英语课程，您家里需要有电脑或ipad，有摄像头和耳机，可以进行视频语音聊天，以下请选择：</span>
                <span style=" margin-left: 5px; color: #DA2824;">*</span>
            </div>
            <div class="choose">
                <div class="txt" @click="choose(0)">
                    <svg class="fbc_optionSvg " :class="{'selected':status==0}" style="padding:1px;padding-top:9px;pointer-events:visible;" xml:space="preserve" viewBox="0 0 30 30" preserveAspectRatio="xMinYMin meet">
                        <circle fill="#FFFFFF" stroke="currentColor" stroke-width="2" cx="15" cy="15" r="14" data-reactid=".0.0.0:$c201789693.1.0.0:$item1.0.0.0"></circle>
                        <circle fill="currentColor" cx="15" cy="15" r="7"></circle>
                    </svg>
                    <span>我有设备，愿意领取礼包并试听课程</span></div>
                <div class="txt" @click="choose(1)">
                    <svg class="fbc_optionSvg" :class="{'selected':status==1}" style="padding:1px;padding-top:9px;pointer-events:visible;" xml:space="preserve" viewBox="0 0 30 30" preserveAspectRatio="xMinYMin meet">
                        <circle fill="#FFFFFF" stroke="currentColor" stroke-width="2" cx="15" cy="15" r="14" data-reactid=".0.0.0:$c201789693.1.0.0:$item1.0.0.0"></circle>
                        <circle fill="currentColor" cx="15" cy="15" r="7"></circle>
                    </svg>
                    <span>我没有以上设备，遗憾放弃领取</span>
                </div>        
            </div>

            <div class="pic">
                <img src="http://pcdn.mikecrm.com/ugc_6_b/pub/gm/gm07di4lerkndo5wczsp68ws5muwa3iq/form/image/KeY630oV4ggp2wZsJh0Od5mwO5aMJaXt.png">
            </div>
            <div class="pic">
                <img src="http://pcdn.mikecrm.com/ugc_6_b/pub/gm/gm07di4lerkndo5wczsp68ws5muwa3iq/form/image/nTxqUVjYczlc5kFNBDzMGVisYrfHza8U.png">
            </div>
            <div class="pic">
                <img src="http://pcdn.mikecrm.com/ugc_6_b/pub/gm/gm07di4lerkndo5wczsp68ws5muwa3iq/form/image/e6eNmQ4kS3UnlMiAsdky5W6zkLLEYE0d.png">
            </div>
            <div class="pic">
                <img src="http://pcdn.mikecrm.com/ugc_6_b/pub/gm/gm07di4lerkndo5wczsp68ws5muwa3iq/form/image/2DkWzBUJadTexyZ6ayQOs9hqyu67Ec0G.png">
            </div>

            <div class="submitWrapper_sticky" @click="save">
                <a class="fb_submitBtn" id="form_submit" style=" display:block; width:100%; height:36px; line-height:36px; text-align:center; font-weight:normal; color:#FFFFFF; background:#E67E22;">
                    点击提交领取VIPKID在线外教课
                </a>
            </div>
            </div>
    </div>        
    <div id="page2" v-else>
        <div class="fb_secondaryStatus">
            <div class="fb_ssItem image">
                <img class="fb_ssImg" src="http://cn.mikecrm.com/ugc_6_b/pub/gm/gm07di4lerkndo5wczsp68ws5muwa3iq/form/image/OmumzIvnC6xudo2Na2iUyxbYghWDScuE.jpg" data-reactid=".3.0.0"></div>
                <div class="fb_ssItem text">
                    <div class="fb_ssTitle" style="color:#000000;">
                        <p style=" margin: 0; padding: 0; text-align: center;">提交成功！请注意接听电话010或021开头的电话，以便及时兑换价值388元外教课！</p>
                    </div>
                    <div class="fb_ssSubTitle" style="color:#666666;"></div>
                </div>
            </div>
    </div>
</div>
<script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="/js/vue.js"></script>
<script>
$(function () {
    var app = new Vue({
        el: '#cpa',
        data: {
            product_id:"",
            article_id:"",
            spread_id:"",
            name:"",
            mobile:"",
            age:"",
            status:0,
            pageShow:true,
            content:["我有设备，愿意领取礼包并试听课程","我没有以上设备，遗憾放弃领取"]
        },
        created:function(){
            this.product_id=this.getQueryVariable('productid');
            this.article_id=this.getQueryVariable('articleid');
            this.spread_id=this.getQueryVariable('spreadid');
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
            choose:function(n){
                this.status=n;
            },
            save:function(){
                var _this=this;
                console.log( _this.name , _this.mobile , _this.age , _this.content );
                if ( _this.name && _this.mobile && _this.age && _this.content ) {
                    $.ajax({
                        url:'{{route('s_index_addCpaOrder')}}',
                        type:'POST',
                        async:true,
                        data:{
                            product_id:_this.product_id,
                            article_id:_this.article_id,
                            spread_id:_this.spread_id,
                            name:_this.name,
                            mobile:_this.mobile,
                            age:_this.age,
                            content:_this.content[_this.status]
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.pageShow=false;
                            }
                        }
                    });                    
                } else {
                    alert("请把信息填写完整");
                }


            }
        }
    });
})
</script>
</body>
</html>
