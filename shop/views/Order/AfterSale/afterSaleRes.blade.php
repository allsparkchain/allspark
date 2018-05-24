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
<div id="afterSaleRes" style=" display: none;">
<div class="centent">
   <div class="list_success">
       <div class="show_contentf">
           <p>请将商品寄回以下地址  (拒收到付件)</p>    
           <p>收到货后3个工作日内将原路退款商品购买费用</p>
       </div>
       <div class="list_comm">
            <div class="comm_left">
                <img src="" alt="">
            </div>
            <div class="comm_right">
                    <div class="comm_content">
                        <p class="comm_title">@{{goods.goods_name}}</p>
                        <p class="com_neir">@{{goods.synopsis}}</p>
                        <p class="com_font">@{{commodity.specifications}}</p>
                    </div>
                    <div class="comm_count">
                        <div class="comTwo">x@{{commodity.number}}</div>
                        <div class="comMoney">￥@{{commodity.account|numberFormat}}</div>
                    </div>
            </div>             
            
       </div>
       <div class="com_select" >
            <div class="com_se_left">
                退款地址
            </div>
            <div class="com_se_right" style="color:#373737">
            上海市普陀区江宁路1158号友力国际大厦2301室
            </div>
       </div>
       <div class="com_select" style="margin-top:0px">
            <div class="com_se_left">
                收件人
            </div>
            <div class="com_se_right" style="color:#373737">
               运营中心收
            </div>
       </div>
       <div class="com_select" style="margin-top:0px">
            <div class="com_se_left">
                联系电话
            </div>
            <div class="com_se_right" style="color:#373737">
            021-31836300
            </div>
       </div>
       <div class="com_select" >
            <div class="com_se_left">
                物流公司
            </div>
            <div class="com_se_right qinaColor" style="color:#373737">
                <input type="text" placeholder="请填写物流公司名称" v-model="express_name">
            
            </div>
       </div>
       <div class="com_select" style="margin-top:0px">
            <div class="com_se_left">
                快递单号
            </div>
            <div class="com_se_right qinaColor" style="color:#373737">
            <input type="text" placeholder="请填写快递单号" v-model="express_no">
            </div>
       </div>
             

   </div>
   <div class="com_tij" @click="resubmit()">提交</div>
</div>
</div>
</body>
<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/vue.js"></script>
<script>
    $(function(){
        Vue.filter("numberFormat",function(value){
            return Number(Number(value).toFixed(2)).toLocaleString('en-US');
        });
        var app=new Vue({
        el:'#afterSaleRes',
        data:{
            commodity:{},
            goods:{},
            number:"",
            messages:[],
            express_name:"",
            express_no:"",
            logistics:{},
            logistics2:{}
        },
        created:function(){
            var _this=this;
            _this.getCharts();
            // _this.getComd();
            _this.getShow();
        },
        mounted:function(){
            var _this=this;
            this.$nextTick(function() {
                document.getElementById("afterSaleRes").style.display = "block";
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
            getShow:function(){
                var _this=this;
                $.ajax({
                  url:'{{route('s_order_getOrderDetail')}}',
                  type:'POST',
                  async:true,
                   data:{
                        oid:_this.getQueryVariable("oid"),
                   },
                   "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   dataType:'json',
                   success:function(res){
                       if(res.status==200){
                        _this.commodity=res.data;
                         _this.goods=res.data.goods;
                         
                       }
                   
                   }
                });
            },
            getCharts:function(){
                var _this=this;

                $.ajax({ 

                  url:'{{route('s_order_getAfterChangePrepear')}}',
                  type:'POST',
                  async:true,
                   data:{
                       sales_id:_this.getQueryVariable("sid")
                   },
                   "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   dataType:'json',
                   success:function(res){
                       _this.logistics=res.data;
                       _this.logistics2=res.data.adv_info;
                   }
                });
            },
            resubmit:function(){
                var _this=this;
                $.ajax({
                  url:'{{route('s_order_submitChangeInfo')}}',
                  type:'POST',
                  async:true,
                   data:{
                    sales_id:_this.getQueryVariable('sid'),
                    express_name:_this.express_name,
                    express_no:_this.express_no
                   },
                   "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   dataType:'json',
                   success:function(res){
                       if(res.status==200){
                           location.href="/Order/afterOrderHistoryList";
                       }
                   }
                });
            }

           
            
        }
    })
        
    })
           
</script>
</html>