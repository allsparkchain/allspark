<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>填写维权信息页面</title>
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
    <link rel="stylesheet" type="text/css" href="{{ mix('css/commodity.css') }}">
    <style>
    </style>
</head>
<body>

<div id="applicationAdd" class="centent" style=" display: none;">
   <div class="list_contents">
       <div class="list_comm">
            <div class="comm_left">
                <img :src="proObj.img_path" alt="">
            </div>
            <div class="comm_right">
                    <div class="comm_content">
                        <p class="comm_title">@{{proObj.goods_name}}</p>
                        <p class="com_neir">@{{proObj.synopsis}}</p>
                        <p class="com_font"><span>@{{proObj2.specifications}}</span></p>
                    </div>
                    <div class="comm_count">
                        <div class="comTwo">x@{{proObj2.number}}</div>
                        <div class="comMoney">￥@{{proObj2.account|numberFormat}}</div>
                    </div>
            </div>             
            
       </div>
       <div class="com_select">
            <div class="com_se_left">
                退款原因
            </div>
            <div class="com_se_right" @click="refundShow">
                @{{refundTxt[picked]}}&nbsp;&nbsp;>
            </div>
       </div>
       <div class="com_write">
           <textarea name="" id="" cols="30" rows="10" placeholder="请描述商品问题" v-model="reason"></textarea>
       </div>
       <div class="com_upoto">
           <p>上传照片<span>(最多上传5张图片)</span></p>
           <div style="margin-top:15px;">
               <div class="com_files" @click="chooseImage"></div>
               <div v-if="localImg.length" class="com_tups" v-for="(l,key) in localImg">
                   <img :src="l">
                   <div class="detes" @click="deleteImg(key)"></div>
               </div>
               <div style=" clear: both;"></div>
           </div>
       </div>


   </div>
   <div class="com_tij" @click="add">提交</div>

    <div v-show="refund">
        <div class="com_zhezhaoBg"></div>
        <div class="tuik_bottom" >
            <p>退款原因</p>
            <div class="tui_k">
                <span>退货并退款</span>
                <div class='radio-check'> <input type='radio' id='test1' value="3" v-model="picked"/> <label for='test1' ></label></div>
            </div>
            <div class="tui_k">
                <span>换货</span>
                <div class='radio-check'> <input type='radio' id='test2' value="2" v-model="picked"/> <label for='test2' ></label></div>
            </div>
            <div class="tui_k">
                <span>仅退款</span>
                <div class='radio-check'> <input type='radio' id='test3' value="1" v-model="picked"/> <label for='test3' ></label></div>
            </div>
            <div class="top_com" @click="close">确定</div>
        </div>
    </div>

</div>
<!-- 退款原因 -->
</body>
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/vue.js"></script>
<script src="{{config('params.httpType')}}://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
<script>
$(function(){
    wx.config(<?php echo $app->jssdk->buildConfig(array('chooseImage','uploadImage','downloadImage'), false) ?>);
    Vue.filter("numberFormat",function(s){
        
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

    var app=new Vue({
        el:'#applicationAdd',
        data:{
            proObj:{},
            proObj2:{},
            product_order_id:"",
            status:"",
            reason:"",
            refund:false,
            picked:0,
            refundTxt:["请选择","仅退款","换货","退货并退款"],
            localImg:[],
            serverImg:[]
        },
        created:function(){
            
        },
        mounted:function(){
            var _this=this;
            this.getOrder();
            this.$nextTick(function() {
                document.getElementById("applicationAdd").style.display = "block";
            });

            wx.ready(function(){

            });
        },
        methods:{
            deleteImg:function(i){
                this.localImg.splice(i,1);
                this.serverImg.splice(i,1);
            },
            chooseImage:function(){
                var _this=this;
                if(this.serverImg.length<5){
                    wx.chooseImage({
                        count: 1, // 默认9
                        sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
                        sourceType: ['camera'], // 可以指定来源是相册还是相机，默认二者都有
                        success: function (res) {
                            var localIds = res.localIds;
                            _this.localImg.push(res.localIds); // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                            wx.uploadImage({
                                localId: ''+localIds, // 需要上传的图片的本地ID，由chooseImage接口获得
                                isShowProgressTips: 1, // 默认为1，显示进度提示
                                success: function (res) {
                                    _this.serverImg.push(res.serverId); // 返回图片的服务器端ID
                                }
                            });
                            // for (var i = 0; i<_this.localImg.length; i++) {
                            //     (function(i){
                            //         wx.uploadImage({
                            //             localId: _this.localImg[i].toString(), // 需要上传的图片的本地ID，由chooseImage接口获得
                            //             isShowProgressTips: 1, // 默认为1，显示进度提示
                            //             success: function (res) {
                            //                 _this.serverImg.push(res.serverId); // 返回图片的服务器端ID
                            //                 _this.reason=JSON.stringify(_this.serverImg);
                            //             },
                            //             fail: function(res){
                            //                 alert("上传失败，msg："+JSON.stringify(res));
                            //             }
                            //         });
                            //     })(i);
                            // };
                        }
                    });
                }else{
                    alert("最多上传5张图片");
                }

            },
            refundShow:function(){
                this.refund=true;
                this.picked=3;
            },
            close:function(){
                this.refund=false;
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
            getOrder:function(){
                var _this=this;
                $.ajax({
                    url:'{{route('s_order_getOrderDetail')}}',
                    type:'POST', //GET
                    async:true,    //或false,是否异步
                    data:{
                        oid:_this.getQueryVariable('id'),
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if (res.status == 200) {
                            _this.proObj=res.data.goods;
                            _this.proObj2=res.data;
                            console.log(_this.proObj);
                        }
                    }
                });
            },
            add:function(){
                var _this=this;
                if(this.picked && this.reason){
                    $.ajax({
                        url:'{{route('s_order_applicationAddPost')}}',
                        type:'POST', //GET
                        async:true,    //或false,是否异步
                        data:{
                            product_order_id:_this.proObj2.product_order_id,
                            status:_this.picked,
                            reason:_this.reason,
                            img:_this.serverImg
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if (res.status == 200) {
                                location.href="/Order/applicationSuccess?id="+res.data;
                            }else{
                                alert(res.message);
                            }
                        }
                    });
                }else{
                    alert("请把信息填写完整");
                }
            }
        }
    })
});

</script>
</html>