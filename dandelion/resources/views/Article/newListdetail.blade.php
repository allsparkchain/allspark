<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>资讯详情-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <!--<link rel="stylesheet" href="css/header.css">-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/information_details.css') }}">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <style>

        .qrcodes{
            opacity:0;
            -webkit-animation-name: fadeIn;
            animation-name: fadeIn;
            -webkit-animation-duration: 3s;
            animation-duration: 3s;
            -webkit-animation-fill-mode: forwards ;
            animation-fill-mode: forwards ;

        }
        @-webkit-keyframes fadeIn {
            0% {
                opacity: 0
            }

            100% {
                opacity: 1
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0
            }

            100% {
                opacity: 1
            }
        }
    </style>
</head>



<body style="padding: 0 !important;margin: 0 !important;">
<div class="wrop">
    <!--头部导航-->
    <div class="header_border">
        <div class="header">
            <div class="login">
                <a href="{{Route('s_index_index')}}"></a>
            </div>
            <div class="header_navWrop">
                <div class="header_nva">
                    <div class="nav_list">
                        <a href="{{Route('s_index_index')}}">首页</a>
                        <a href="{{route('s_article_lists')}}" class="active">资讯</a>
                        <a href="https://www.pugongying.link/jzinter" target="_blank">关于我们</a>
                    </div>
                    @if(strlen($user) >0)
                        {{--登录状态--}}
                        <div class="user_logged" style="display:block;">
                            <div class="userHides" >
                                <div onclick="window.location.href='{{Route('s_user_accountInfo')}}'" class="" style="float: right;line-height: 70px;cursor: pointer;">
                                    <div class="usserImg" style="float: left; margin-top: 16px; font-size: 0; width:37px;height:37px;border-radius: 50%;"><img width="100%" height="100%" style="border-radius: 50%" src="{{\Auth::getUser()->getHeadImgurl()}}"></div>
                                    <span style="margin-left: 13px" class="loggedname">{{\Auth::getUser()->getUserNickname()}}</span>
                                </div>
                            </div>
                            <div class="user_hide" style="display: none">
                                <span class="userAs" id="userAs" onclick="window.location.href='{{Route('s_user_accountInfo')}}'">个人中心</span>
                                <span class="userAs" onclick="window.location.href='{{Route('s_logout')}}'">退出</span>
                            </div>
                        </div>
                    @else
                        {{--未登录状态--}}
                        <div class="user_login" style="">
                            <span class="spacing" onclick="window.location.href='/login'">登录</span>
                            <i class="spacing">/</i>
                            <span class="spacing" onclick="window.location.href='/qrRegister'">注册</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!--头部导航end-->
    <div class="centent" style="overflow: hidden">
        <div class="link_href">
            <div class="link_hrefLi" style="position: fixed">
                <p>分享</p>
                <div class="weiFont">
                    <i></i>微信
                </div>
            </div>
            <div class="link_hrefLi" id="link_hrefLi" style="text-align: center;display: none;position: fixed;  margin-top:170px;padding-top: 10px;">
                <div id="code1"></div>
            </div>
        </div>
        <a href="{{$openUrl}}">一键转载</a>
        <div class="centent_left" style="margin: 0 !important;">
            <div class="centent_lTitle ">
                <p class="headline big_b">{{$article['name']}}</p>
            </div>
            <p class="timeSsd">{{date('Y-m-d H:i',$article['add_time'])}}</p>
            <div>
                <div class="centent_fontText" style="text-indent: 0 !important;;">
                    {!! $article['content'] !!}
                </div>
                <input type = 'hidden' id="aid" value="{{$article['id'] or 0}}">
                <input type = 'hidden' id="aprs" value="{{$article['article_product_relateId'] or 0}}">
                <div>
                    <div class="titleFour"><p class="titleSfont" style="margin: 0">热点文章</p></div>
                    <div class="centent_display">
                        @if(count($lastweekhotrank) > 0)
                            @foreach($lastweekhotrank as $lasthot)
                                <div class="centent_centWrop">
                                    <div class="centent_imgLogin" onclick="window.location.href='{{route('s_article_listdetail')}}?id={{$lasthot['tarticleid']}}';">
                                        <div class="ompositionTableBg">分成
                                            @if(isset($lasthot['percent_arr']['mode_2']['percent']))
                                                <span>{{$lasthot['percent_arr']['mode_2']['percent'] or 0}}%</span>
                                            @else
                                                <span>{{$lasthot['percent_arr']['mode_2']['account'] or 0}}</span>
                                            @endif
                                        </div>
                                        <img src="{{$lasthot['imgs']['img_path']}}" width="100%" height="100%">
                                    </div>
                                    <div class="font_centMeass"  onclick="window.location.href='{{route('s_article_listdetail')}}?id={{$lasthot['tarticleid']}}';">
                                        <p class="title_td" style="overflow:hidden;">{{$lasthot['name']}}</p>
                                        <span class="title_tdFont">{{time_tranx($lasthot['add_time'])}}<span style="display: none;">呼噜呼噜啾</span></span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!--左边内容完-->
        <div class="centent_right">

            <div class="centent_right_t">推广分成比例 &nbsp;<span>{{$article['channlepercent']}}</span></div>
            <div class="centent_right_er">
                <div class="right_er_w show2" style="display: none;height: 295px;" >
                    <div id="qrcode" class="er_d"></div>
                    <p class="show_font">也可以复制以下地址<br>至公微的阅读原文内</p>
                    <p  style="color: #7e7e7e; font-size: 14px; height: 14px; line-height: 14px; margin-top: 5px; padding-bottom: 23px; text-align: center;" id="urlcode"></p>
                </div>
                <div class="right_er_w show1">
                    <div class="btonneWrop">
                        @if(strlen($user)>0)
                        <a href="javascript:;" class="btonne">我要推广</a>
                        @else
                        <a href="javascript:;" class="btonne dfre">我要推广</a>
                        @endif
                    </div>
                    <p class="show_font" >点击获取推广二维码</p>
                    <!--<div  class="btonneWrop"><a href="#" class="btonne">我要推广</a></div>-->
                    <!--<p class="show_font" >您可以通过以下方式获得分成</p>-->
                    <!--<a href="javascript:void(0)" class="run_er">点击获取推广二维码</a>-->
                </div>
                <div class="bg_white"></div>
            </div>


            <div class="asdWser">
                <div class="centent_right_t  s_margin">如何获得分成</div>
                <div class="tui_centent">
                    <p class=" tui_title">从推广开始</p>
                    <ul  class="font tui_mes">
                        <li>复制左侧资讯内容</li>
                        <li>粘贴至您的微信公号</li>
                        <li>二维码放入文内</li>
                        <li>发布它</li>
                    </ul>
                    <p class=" tui_title">获得成分</p>
                    <ul  class="font fen_mes">
                        <li>粉丝阅读这篇文章</li>
                        <li>扫码进入商品购买页</li>
                        <li>购买成功即获得分成</li>
                        <li>个人中心查看收益</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>
    </footer>
</div>
{{--隐藏登录--}}
<div class="mark" style="display: none">
    <div class="mask_bgColor"></div>
    <div class="markCentetn">
        <div class="mark_fontWrop">
            <span class="close"></span>
            <ul>
                <li><a href="#" class="Mactive tablEswitchO">手机登录</a></li>
                <li><a href="#" class="tablEswitchT">微信登录</a></li>
            </ul>
            <div class="centFonts">
                <div class="centFontUser">
                    <div class="inputWrop">
                        <input id="fast_username" name="username" >
                        <input id="fast_password"  name="password" type="password">
                    </div>
                    <div class="accountRemember " >
                        <input type="checkbox" style="display:none;">
                        <span class="accountRememberOne" style="display:none;">记住密码</span>
                        <span class="accountRememberTwo" onclick="location.href='{{Route('s_forgetpassword')}}'">忘记密码</span>
                    </div>
                    <div class="msers"><a id="fastlogin" href="javascript:;">立即登录</a></div>
                    <p class="wsera">还没有账号?<a href="/register">立即注册</a></p>
                </div>
                <div class="centFontWei" style="display: none">
                    <div class="erBgWrop"><div id="code"></div></div>
                    <p>请使用微信扫描二维码登录</p>
                </div>
            </div>
        </div>
    </div>
</div>
{{--end--}}
<script type="text/javascript" src="/js/vendor/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="/js/vendor/jquery.qrcode.min.js"></script>

<script type="text/javascript">
    $(".ompositionTableBg").hide();



    $("#code").qrcode({
        render: "canvas", //table方式
        width: 191, //宽度
        height:191, //高度
        text: "{{ $wxHost }}/auth/weixin/login/{{ $pc_jzstate }}/1" //任意内容
    });

    $("#code1").qrcode({
        render: "canvas", //table方式
        width: 120, //宽度
        height:120, //高度
        text: "{!! $url !!}" //任意内容
    });
    $('#fastlogin').click(function(){
        var uname = $('#fast_username').val();
        var pwd = $('#fast_password').val();
        //
        $.ajax({
            url:'{{route('s_auth_login')}}',
            type:'POST', //GET
            async:true,    //或false,是否异步
            data:{
                username:uname,
                password:pwd,
                fastlogin:'fastlogin'
            },
            "headers": {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            timeout:5000,    //超时时间
            dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
            success:function(data,textStatus,jqXHR){
                if(data.status==200){
                  window.location.reload();
                }
                else {
                    alert(data.msg);
                }
            }
        })
    });
    $(function () {

        @if(strlen($user)>0)
             $('.user_logged').show();//已登录
        @else
             $('.user_login').show();
        @endif

        $(".dfre").click(function () {
            $(".mark").show();
        });
        $(".close").click(function () {
            $(".mark").hide();
        });
        var wxLogin = 1;
        //弹框切换
        $(".tablEswitchO").click(function () {
            $(".centFontUser").show();
            $(".centFontWei").hide();
            $(".tablEswitchO").addClass("Mactive");
            $(".tablEswitchT").removeClass("Mactive");
        });
        $(".tablEswitchT").click(function () {
            $(".centFontWei").show();
            $(".centFontUser").hide();
            $(".tablEswitchT").addClass("Mactive");
            $(".tablEswitchO").removeClass("Mactive");

            if(wxLogin==1){
                wxLogin = 2;
                aaa();
            }
        });
        //end

        function ajaxLogin() {
            $.ajax({
                url:"{{route('s_weixin_ajax_login')}}?_date="+new Date(),
                type:'POST', //GET
                async:true,    //或false,是否异步
                data:{
                    code:"{{ $pc_jzstate }}",
                },
                "headers": {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout:600000,    //超时时间
                dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
                success:function(data,textStatus,jqXHR){
                    if(data.type==1){
                        //登录
                        window.location = "{{route('s_user_accountInfo')}}";
                    }else if(data.type==2){
                        //注册
                        window.location = "/register";
                    }else if(data.type==3){

                    }
                }
            })
        }

        function aaa(){
            setInterval(function () { ajaxLogin() },5000); //循环计数
        }



        $(".userHides").mouseover(function () {
            $(".user_hide").show();
            // $("#address").addClass("addressBorder");
        });
        $(".userHides").mouseout(function () {
            $(".user_hide").hide();
            // $("#address").addClass("addressBorder");

        });

        $(".userAs").mouseover(function () {
            $(".user_hide").show();
            // $("#address").addClass("addressBorder");
        });
        $(".userAs").mouseout(function () {
            $(".user_hide").hide();
            // $("#address").addClass("addressBorder");

        });




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

        $(".btonne").click(function () {
           //  $('.show2').hide();
            var user = "{{$user}}";
            if(!user){
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
                        var qRcode = '{{$qRcode}}';
                        if(qRcode!=1) {
                            window.location.reload();return false;
                        }
                        $('#urlcode').html(data.data.url);
                        function makeCode () {
                            $("#qrcode").qrcode({
                                render: "canvas", //table方式
                                width: 180, //宽度
                                height:180, //高度
                                text: data.data.url //任意内容
                            });
                        }

                        $(".show2").fadeIn(100,makeCode());

                        $(".run_er").remove();
                        $(".show_font").show();
                        $('.show1').hide();


                    }
                    else {
                        console.log(data);
                    }
                }
            });


        });
        var qRcode = '{{$qRcode}}';
        if(qRcode==1){
            $(".btonne").click();
        }

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
        $(".link_hrefLi").hover(function(){
            $('#link_hrefLi').show();
        },function(){
            $('#link_hrefLi').hide();
        });
    });
</script>


</body>
</html>
