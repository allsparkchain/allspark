
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>招募写手</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no, email=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/flexible.js"></script>
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/recruit.css') }}">
</head>
<body style="">
<div id="shareIndex" class="recruitSuc">
    <img class="sucImg" src="../images/recruit04.png" alt="">
    <div class="sucMsg">审核通过后我们将在5个工作日与您取得联系</div>
    <div class="sucGo"><a href="https://www.pugongying.link/">前往官网了解更多 ></a></div>
</div>
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/vue.js"></script>
    <script>
      $(function () {  
        var app=new Vue({ 
            el:'#shareIndex',
            data:{
                
            },  
            created:function(){
                var _this=this;
                
            },
            mounted:function(){
                var _this=this;
                this.$nextTick(function() {
                    document.getElementById("shareIndex").style.display = "block";
                });
            },
            methods:{
                
            }
        })
     })
    </script>
</body>
</html>
