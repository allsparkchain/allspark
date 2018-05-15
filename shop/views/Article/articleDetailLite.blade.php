<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>内容详情</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no, email=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <!--强制竖屏-->
    <meta name="screen-orientation" content="portrait">
    <!--点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/pgylite.css') }}">
</head>
<body>
    <div class="article-content">
        <div class="article-title">
            {{$info['name']}}
        </div>
        <div class="article-brief">
            夏天悄悄过去&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{date('Y-m-d H:i',$info['add_time'])}}
        </div>
        <div class="article-detail">
            {!! html_entity_decode($info['content']) !!}
        </div>
    </div>
    <footer class="article-footer">
        投诉
        <div class="article-footer-tab">
            <span id="share" class="yellow"><i class="article-tab-share"></i>分享</span>
            <span id="buy" class="red">购买<i class="article-tab-buy"></i></span>
        </div>
    </footer>
<script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
<script src="{{config('params.httpType')}}://res.wx.qq.com/open/js/jweixin-1.3.2.js" type="text/javascript" charset="utf-8"></script>
<script>
$(function(){

    $('#share').on('click',function(s) {
        wx.miniProgram.navigateTo({
            url:'/pages/index_contentShare/index_contentShare',
            success: function(){
                console.log('success')
            },
            fail: function(){
                console.log('fail');
            },
            complete:function(){
                console.log('complete');
            }
        });
    });

    $('#buy').on('click',function(s) {
        wx.miniProgram.navigateTo({
            url:'/pages/fair_goodsBuy/fair_goodsBuy',
            success: function(){
                console.log('success')
            },
            fail: function(){
                console.log('fail');
            },
            complete:function(){
                console.log('complete');
            }
        });
    });

    $("img").attr("style","");
    var deviceWidth=$(document).width();
    $(".article-detail section").each(function(){
        if($(this).width()>deviceWidth){
            $(this).css("width","100%");
        }
    });

    $(".article-detail div").each(function(){
        if($(this).width()>deviceWidth){
            $(this).css("width","100%");
        }
    });

    $(".Powered-by-XIUMI .V5").css("width","auto");

    $(".article-detail img").each(function(){
        var w = $(this)[0].naturalWidth;
        var h = $(this)[0].naturalHeight;
        if(w>deviceWidth){
            $(this).css("width","100%")
        }else{

        }
    });
})





</script>
</body>
</html>
