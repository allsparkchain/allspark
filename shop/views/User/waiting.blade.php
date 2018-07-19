<html lang="zh-cmn-Hans">
<head>
    <meta charset="utf-8">
    <title>等待页面</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=1400">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}"> 
    <script type="text/javascript" src="/js/vue.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
</head>
<body id="waitings">
    <div id="pgy_waitings">
    <header class="zmtdl_adminHeader_tops">
        <div class="zmtdl_adminHeader_wraps">
            <div class="zmtdl_adminLogos"></div>
        </div>
    </header>
    <div class="pgy_waiting">
        <div class="watintRotate" v-show="pgyTrotate">
            <div>
                <img src="/image/se_03.png" alt="" >                
            </div>
            <div>
                <img src="/image/fe_06.png" alt="" >
            </div>
           
        </div>
        <p>@{{copying}}</p>
    </div>
    <div class="pgy_login_footer" style="position:fixed;bottom:0px;color:#ffffff;background:none;">2018 蒲公英 ALL RIGHTS RESERVED. 沪ICP备07001687号　&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mail：hi@pugongying.link</div>
    <div style="clear:both;"></div>
    </div>
</body>
<script type="text/javascript">
        // var s = ;
         $(function () {
            var app=new Vue({
                el:'#pgy_waitings',
                data:{
                   s:"{{$rand}}",
                   pgyTrotate:true,
                   copying:'文章复制中...'
                },
                created:function(){
                    var _this=this;
                    this.getpush();
                   
                },
                methods:{
                    getpush:function(){
                        var _this=this;
                        setTimeout(function(){
                        $.ajax({
                            url:'{{route('s_weixin_checks')}}',
                            type:'POST', //GET
                            async:true,    //或false,是否异步
                            data:{
                                rand:_this.s
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            
                            dataType:'json',    
                            success:function(data){
                                if(data.status==200){
                                    _this.pgyTrotate=false;
                                    _this.copying='复制成功';
                                }else{
                                    _this.getpush();
                                }

                            }
                        })
                                     
                }, 1000); 
                    }
                }
               
            })
        })
    </script>
</html>
