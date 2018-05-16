<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>提交成功</title>
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

<div id="applicationSuccess" class="centent">
   <div class="list_success">
       <div class="show_contents">
           <div><i class="applicationIcon"></i>提交成功</div>
           <p>给您带来的不便尽请谅解</p>
           <p>蒲公英运营中心已受理，我们将尽快进行回复</p>
       </div>
       <div class="list_comm">
            <div class="comm_left">
                <img :src="proInfoObj.img_path" alt="">
            </div>
            <div class="comm_right">
                    <div class="comm_content">
                        <p class="comm_title">@{{proInfoObj.goods_name}}</p>
                        <p class="com_neir">@{{proInfoObj.synopsis}}</p>
                        <p class="com_font"><span>@{{proInfoObj.specifications}}</span></p>
                    </div>
                    <div class="comm_count">
                        <div class="comTwo">x@{{proObj.number}}</div>
                        <div class="comMoney">￥@{{proObj.account|numberFormat}}</div>
                    </div>
            </div>             
            
       </div>
       <div class="com_select">
            <div class="com_se_left">
                退款原因
            </div>
            <div v-if="proObj.status==1" class="com_se_right" style="color:#373737">仅退款</div>
            <div v-if="proObj.status==2" class="com_se_right" style="color:#373737">换货</div>
            <div v-if="proObj.status==3" class="com_se_right" style="color:#373737">退货并退款</div>
       </div>
       <div class="com_write">
           <p style="margin-bottom:12px;font-size:12px;">商品问题描述</p>
           <div class="bewrited" style=" margin: 0 0 25px 0;">@{{proObj.reason}}</div>
           <div class="com_img_s">
               <img v-for="p in proObjImg" :src="p.image_path" alt="">
           </div>
       </div>
      

    </div>
    <div class="com_tij">
       <a style=" color: #fff; text-decoration: none;" href="/Order/afterOrderHistoryList">返回售后服务</a>
    </div>
</div>

</body>
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/vue.js"></script>
<script>
$(function(){
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
        el:'#applicationSuccess',
        data:{
            proObj:{},
            proObjImg:{},
            proInfoObj:{}
        },
        created:function(){
            this.getOrder();
        },
        mounted:function(){
            var _this=this;
            this.$nextTick(function() {
                document.getElementById("applicationSuccess").style.display = "block";
            });
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
            getOrder:function(){
                var _this=this;
                $.ajax({
                    url:'{{route('s_order_getApplicationSuccessInfo')}}',
                    type:'POST', //GET
                    async:true,    //或false,是否异步
                    data:{
                        sales_id:_this.getQueryVariable('id')
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if (res.status == 200) {
                            _this.proObj=res.data;
                            _this.proObjImg=res.data.img;
                            _this.proInfoObj=res.data.product_info;
                        }
                    }
                });
            }
        }
    })
});

</script>
</html>