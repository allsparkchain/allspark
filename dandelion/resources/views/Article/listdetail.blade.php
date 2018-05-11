<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>资讯详情-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <link rel="stylesheet" href="{{ mix('css/header.css') }}">
    <link rel="stylesheet" href="{{ mix('css/information_details.css') }}">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
</head>
<body>
<div class="wrop">
    <!--头部导航-->
    <div class="runSign">
        <div class="runSign_mes">
            <div class="block_w">
                @if(\Auth::user())
                    <a class="runSign_mes_d" href="{{route('s_user_accountInfo')}}">{{substr_replace(\Auth::user()->getUserMobile(),'****',3,4)}}</a>
                    <a class="G_run" href="{{route('s_logout')}}">退出</a>
                @else

                    <a class="runSign_mes_d" href="{{route('s_login')}}">登录</a><a class="G_run">/</a>
                    <a class="Z_run" href="/register">注册</a>

                @endif


            </div>
        </div>
    </div>
    <div class="local_wrap">
        <div class="header">
            <div class="login">
                <a href="/">LOGO</a>
            </div>
            <div class="header_nva">
                <ul>
                    <li><a href="/">首页</a></li>
                    <li><a   href="{{route('s_article_lists')}}">资讯</a></li>
                    <li><a href="{{route('s_user_accountInfo')}}">个人中心</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!--头部导航end-->
    <div class="centent">
        <div class="centent_left">
            <div class="centent_lTitle border_bottomColor">
                <i></i>
                <div class="centent_left_font">
                    <p class="headline big_b">{{$article['name']}}</p>
                    <span class="time_fontColor">{{date('Y-m-d H:i',$article['add_time'])}}</span>
                </div>
            </div>
            <div class="centent_fontText">
                {!! $article['content'] !!}
            </div>
        </div>
        <!--左边内容完-->
        <div class="centent_right">
            <div class="centent_right_t">推广分成比例 &nbsp;<span>{{$article['channlepercent']}}</span></div>
            <input type = 'hidden' id="aid" value="{{$article['id'] or 0}}">
            <input type = 'hidden' id="aprs" value="{{$article['article_product_relateId'] or 0}}">

            <div class="centent_right_er">
                <div class="right_er_w">
                    <div id="" class="er_d">
                        <div id="qrcode" style="text-align:center"></div>
                    </div>
                    <p class="show_font" style="display: none;">您可以通过以下方式获得分成</p>
                    <a href="javascript:void(0)" class="run_er">点击获取推广二维码</a>
                </div>
            </div>
            <div class="centent_right_t  s_margin">如何获得分成</div>
            <div class="tui_centent">
                <p class=" tui_title">从推广开始</p>
                <ul  class="font tui_mes">
                    <li>复制左侧资讯内容</li>
                    <li>粘贴至你的微信公众号后台内</li>
                    <li>将二维码复制到文章内（建议末尾）</li>
                    <li>发布它</li>
                </ul>
                <p class=" tui_title">获得成分</p>
                <ul  class="font fen_mes">
                    <li>粉丝阅读这篇文章</li>
                    <li>通过生成的二维码前往商品购买页</li>
                    <li>购买成功后，你就完成了1份订单</li>
                    <li>前往个人中心，看看你的收益吧</li>
                </ul>
            </div>
        </div>
    </div>
    <footer>
        <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>
    </footer>
</div>
<script type="text/javascript" src="/js/vendor/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="http://cdn.bootcss.com/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="http://static.runoob.com/assets/qrcode/qrcode.min.js"></script>
<script type="text/javascript">
    $(function () {
        function getQRurl() {
            $.ajax({
                url:'{{route('s_user_createSpreadQRcode')}}',
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
                         alert(data.data.id);

                    }
                    else {
                        console.log(data);
                    }
                }
            })
        }



        $(".run_er").click(function () {

            var user = "{{$user}}";
            if(!user){
                window.location="{{route('s_login')}}"+'?url={{route('s_article_listdetail')}}'+"?id={{$article['id']}}";return false;
            }

            $.ajax({
                url:'{{route('s_user_createSpreadQRcode')}}',
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

                        var qrcode = new QRCode(document.getElementById("qrcode"), {
                            width : 215,
                            height : 215
                        });
                        function makeCode () {
                            qrcode.makeCode(data.data.url);  //value需要转换成二维码的路径
                        }
                        makeCode();
                        $(".er_d").fadeIn(100,makeCode());
                        $(".run_er").remove();
                        $(".show_font").show();

                    }
                    else {
                        console.log(data);
                    }
                }
            });


        });

    });
</script>
<script>
    //判断浏览器类型
    function myBrowser() {
        var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
        var isOpera = userAgent.indexOf("Opera") > -1;
        if (isOpera) {
            return "Opera"
        }
        ; //判断是否Opera浏览器
        if (userAgent.indexOf("Firefox") > -1) {
            return "FF";
        } //判断是否Firefox浏览器
        if (userAgent.indexOf("Chrome") > -1) {
            return "Chrome";
        }
        if (userAgent.indexOf("Safari") > -1) {
            return "Safari";
        } //判断是否Safari浏览器
        if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera) {
            return "IE";
        }
        ; //判断是否IE浏览器
        if (userAgent.indexOf("Trident") > -1) {
            return "Edge";
        } //判断是否Edge浏览器
    }
    function SaveAs5(imgURL) {
        var oPop = window.open(imgURL, "", "width=1, height=1, top=5000, left=5000");
        for (; oPop.document.readyState != "complete";) {
            if (oPop.document.readyState == "complete")break;
        }
        oPop.document.execCommand("SaveAs");
        oPop.close();

    }
    function oDownLoad(url) {
        myBrowser();
        if (myBrowser() === "IE" || myBrowser() === "Edge") {
            //IE自动下载
            // odownLoad.href = "#";
            // var oImg = document.createElement("img");
            // oImg.src = url;
            // oImg.id = "downImg";
            // var odown = document.getElementById("down");
            // alert("nihao");
            // console.log(oImg);
            // odown.appendChild(oImg);
            // SaveAs5(document.getElementById('downImg').src)
            //end
            var canvs=document.createElement('canvas');
            var img=document.createElement('img');
            img.onload=function (e) {
                canvs.width=img.width;
                canvs.height=img.height;
                var context=canvs.getContext('2d');
                context.d
            }

        } else {
            //!IE
            odownLoad.href = url;
            odownLoad.download = "";
        }
    }
    // var odownLoad = document.getElementById("downLoad");
    // odownLoad.onclick = function () {
    //     var img_url=document.getElementById("qrcode");
    //     var img_src=img_url.childNodes[1].src;
    //     oDownLoad(img_src);
    // }
</script>
<script type="text/javascript">
    $(function () {
        $(".run_er").addClass("");
    });
</script>
</body>
</html>
