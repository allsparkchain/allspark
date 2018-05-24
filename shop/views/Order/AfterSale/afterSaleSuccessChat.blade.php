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
<div id="afterSaleChat" style="display: none;">
    <div class="centent">
        <div class="list_chat">      
            <div class="list_gives">
                <div class="comm_left">
                    <img :src="proInfoObj.img_path" alt="">
                </div>
                <div class="comm_rGive">
                    <div class="comm_content">
                        <p class="comm_title">@{{proInfoObj.goods_name}}</p>
                        <p class="com_neir">@{{proInfoObj.synopsis}}</p>
                        <p class="com_font">@{{proObj.specifications}}</p>
                        <p class="com_price">共<span>@{{proObj.number}}</span>件商品，合计￥@{{proObj.account|numberFormat}}</p>
                    </div>
                </div>
            </div>
            
            <div class="chatWith">
                <p>蒲公英售后服务</p>
                <ul class="chatUl">
                    <li v-for="t in messages" >
                        <div v-if="t.type==2">
                            <div class="times_tis">@{{t.update_time}}</div>
                            <div class="chatAvator"></div>
                            <div class="chatContent">
                                <div class="content">@{{t.message}}</div>
                            </div>
                        </div>
                        
                    </li>
                </ul>
            </div>
        </div>
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
        el:'#afterSaleChat',
        data:{
            proObj:{},
            proInfoObj:{},
            sendMessage:"",
            messages:[],
            headurl:""
        },
        created:function(){
            var _this=this;
             _this.getCharts();
             _this.getDataaction();
        },
        mounted:function(){
            var _this=this;
            this.$nextTick(function(){
                document.getElementById("afterSaleChat").style.display = "block";
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
            getCharts:function(){
                var _this=this;
                _this.time=[];
                _this.account=[];

                $.ajax({
                  url:'{{route('s_order_getOrderDetail')}}',
                  type:'POST',
                  async:true,
                   data:{
                        oid:_this.getQueryVariable('oid'),
                   },
                   "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   dataType:'json',
                   success:function(res){
                       if(res.status==200){
                            _this.proObj=res.data;
                            _this.proInfoObj=res.data.goods;
                       }
                   }
                });
            },
            getDataaction:function(){  
                var _this=this; 
                $.ajax({
                  url:'{{route('s_order_getAfterMessageList')}}',
                  type:'POST',
                  async:true,
                   data:{
                       product_order_id:_this.getQueryVariable('pid'),
                   },
                   "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   dataType:'json',
                   success:function(res){
                       if(res.status==200){
                         _this.messages=res.data.data;
                         _this.messages.reverse();
                         _this.headurl=res.data.headurl;
                       }
                   
                   }
                });

            }
        }
    })
        
    })
           
</script>
</html>