<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>可维权商品</title>
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

<div id="orderList" class="centent" style=" display: none;">
   <ul class="after_service">
       <li :class="{onColor:showtype==1}" @click="tabSwitch(1)">可维权商品</li>
       <li :class="{onColor:showtype==2}" @click="tabSwitch(2)">受理中</li>
       <li :class="{onColor:showtype==3}" @click="tabSwitch(3)">已处理</li>
   </ul>
   <div class="list_contents">
       <div class="list_wrong">购买商品30天内如果有质量问题可以申请售后退换货服务</div>

       <div class="list_comm" v-if="o.showTypeNow==1" v-for="o in orderList">
           <a :href="'/Order/applicationAdd?id='+o.id+'&pid='+o.product_order_id">
                <div class="comm_left">
                    <img :src="o.goods.img_path" alt="">
                </div>
                <div class="comm_right">
                    <div class="comm_content">
                        <p class="comm_title">@{{o.ArticleName}}</p>
                        <p class="com_neir">@{{o.summary}}</p>
                        <p class="com_font"><span>@{{o.specifications}}</span></p>
                    </div>
                    <div class="comm_count">
                        <div class="comTwo">x@{{o.number}}</div>
                        <div class="comMoney">￥@{{o.account|numberFormat}}</div>
                    </div>
                </div>
            </a>
       </div>



       <div class="list_comm" v-if="(o.showTypeNow==2 && (o.afterSaleStatus==1 || o.afterSaleStatus==2 || o.afterSaleStatus==3))" v-for="o in orderList">
           <a :href="'/Order/afterSaleChat?oid='+o.id+'&pid='+o.product_order_id">
                <div class="comm_left">
                    <img :src="o.goods.img_path" alt="">
                </div>
                <div class="comm_right">
                    <div class="comm_content">
                        <p class="comm_title">@{{o.ArticleName}}</p>
                        <p class="com_neir">@{{o.summary}}</p>
                        <p class="com_font"><span>@{{o.specifications}}</span></p>
                    </div>
                    <div class="comm_count">
                        <div class="comTwo">x@{{o.number}}</div>
                        <div class="comMoney">￥@{{o.account|numberFormat}}</div>
                    </div>
                </div>
            </a>
       </div>

        <div class="list_comm" v-if="(o.showTypeNow==2 && (o.afterSaleStatus==4 || o.afterSaleStatus==5 || o.afterSaleStatus==6))" v-for="o in orderList">
           <a :href="'/Order/afterSaleRes?oid='+o.id+'&sid='+o.afterSaleId">
                <div class="comm_left">
                    <img :src="o.goods.img_path" alt="">
                </div>
                <div class="comm_right">
                    <div class="comm_content">
                        <p class="comm_title">@{{o.ArticleName}}</p>
                        <p class="com_neir">@{{o.summary}}</p>
                        <p class="com_font"><span>@{{o.specifications}}</span></p>
                    </div>
                    <div class="comm_count">
                        <div class="comTwo">x@{{o.number}}</div>
                        <div class="comMoney">￥@{{o.account|numberFormat}}</div>
                    </div>
                </div>
            </a>
       </div>

       <div class="list_comm" v-if="(o.showTypeNow==3 && (o.afterSaleStatus==7 || o.afterSaleStatus==8 || o.afterSaleStatus==9 ))" v-for="o in orderList">
            <a :href="'/Order/afterSaleSuccessChat?oid='+o.id+'&pid='+o.product_order_id">
                <div class="comm_left">
                    <img :src="o.goods.img_path" alt="">
                </div>
                <div class="comm_right">
                    <div class="comm_content">
                        <p class="comm_title">@{{o.ArticleName}}</p>
                        <p class="com_neir">@{{o.summary}}</p>
                        <p class="com_font"><span>@{{o.specifications}}</span></p>
                    </div>
                    <div class="comm_count">
                        <div class="comTwo">x@{{o.number}}</div>
                        <div class="comMoney">￥@{{o.account|numberFormat}}</div>
                    </div>
                </div>
            </a>
       </div>



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
        el:'#orderList',
        data:{
            showtype:1,
            orderList:[],
            page:1
        },
        created:function(){
            this.getOrder();
        },
        mounted:function(){
            var _this=this;

            this.$nextTick(function() {
                document.getElementById("orderList").style.display = "block";
                $(window).on("scroll", function () {
                    if ($(document).scrollTop() + $(window).height() == $(document).height() ) {
                        _this.page++;
                        console.log(_this.page);
                        _this.getOrder();
                    }
                });
            });
        },
        methods:{
            tabSwitch:function(type){
                this.showtype=type;
                this.page=1;
                window.scrollTo(0,0);
                this.getOrder();
            },
            getOrder:function(){
                var _this=this;
                if(this.page==1){
                    this.orderList=[];
                }
                $.ajax({
                    url:'{{route('s_order_getAfterOrderHistoryList')}}',
                    type:'POST', //GET
                    async:true,    //或false,是否异步
                    data:{
                        page:_this.page,
                        pagesize:10,
                        showtype:_this.showtype
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if (res.status == 200) {
                           _this.orderList=_this.orderList.concat(res.data.data);
                        }
                    }
                });
            }
        }
    })
});
</script>
</html>