<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>资讯-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/information.css') }}">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <!--<link rel="stylesheet" href="css/header.css">-->
</head>
<body>
<div class="worp" >
    <div class="header_border">
        <div class="header">
            <div class="login">
                <a href="{{Route('s_index_index')}}"></a>
                {{--<div class="address" id="address" style="display: none;">上海</div>--}}
                {{--<!--地址隐藏块-->--}}
                {{--<div class="opacity_d" style="display: none">--}}
                {{--<div class="opacity_d_w ">--}}
                {{--<div class="opacity_host opacity_d_wActie">热门城市 :</div>--}}
                {{--<div class="opacity_host_font">--}}
                {{--<span>上海</span>--}}
                {{--<span>深圳</span>--}}
                {{--<span>北京</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>北京</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--</div>--}}
                {{--</div>--}}
                {{--<div class="opacity_d_w opacity_dPadding">--}}
                {{--<div class="opacity_host">华东地区 :</div>--}}
                {{--<div class="opacity_host_font">--}}
                {{--<span>上海</span>--}}
                {{--<span>深圳</span>--}}
                {{--<span>北京</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>北京</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--</div>--}}
                {{--</div>--}}
                {{--<div class="opacity_d_w opacity_dPadding1">--}}
                {{--<div class="opacity_host">中部地区 :</div>--}}
                {{--<div class="opacity_host_font">--}}
                {{--<span>上海</span>--}}
                {{--<span>深圳</span>--}}
                {{--<span>北京</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>北京</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--</div>--}}
                {{--</div>--}}
                {{--<div class="opacity_d_w opacity_dPadding2">--}}
                {{--<div class="opacity_host">华南地区 :</div>--}}
                {{--<div class="opacity_host_font">--}}
                {{--<span>上海</span>--}}
                {{--<span>深圳</span>--}}
                {{--<span>北京</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>北京</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>城都</span>--}}
                {{--<span>武汉</span>--}}
                {{--<span>武汉</span>--}}
                {{--</div>--}}
                {{--</div>--}}
                {{--</div>--}}
            </div>
            <div class="header_navWrop">
                <div class="header_nva">
                    <div class="nav_list">
                        <a href="{{Route('s_index_index')}}" >首页</a>
                        <a href="{{route('s_article_lists')}}" class="active">资讯</a>
                        <a href="https://www.pugongying.link/jzinter" target="_blank">关于我们</a>
                    </div>

                    @if(strlen($user) >0)
                        <div class="user_logged" style=" float: right;line-height: 70px; ">
                            <div class="userHides" onclick="window.location.href='{{Route('s_user_accountInfo')}}'" style="display: flex;display: flex;justify-content: center;align-items: center;overflow: hidden;margin-left: 60px;cursor: pointer;">
                                <div class="usserImg" style=" width:37px;height:37px;font-size: 0; border-radius: 50%;"><img width="100%" height="100%" style="border-radius: 50%;" src="{{\Auth::getUser()->getHeadImgurl()}}"></div>
                                <span style="margin-left: 13px" class="loggedname">{{\Auth::getUser()->getUserNickname()}}</span>
                            </div>
                            <div class="user_hide" style="display: none">
                                <span class="userAs" id="userAs" onclick="window.location.href='{{Route('s_user_accountInfo')}}'">个人中心</span>
                                <span class="userAs"  onclick="window.location.href='{{Route('s_logout')}}'">退出</span>
                            </div>
                        </div>
                    @else
                        <div class="user_login" style=""><span class="spacing" onclick="window.location.href='/login'">登录</span><i class="spacing">/</i><span class="spacing" onclick="window.location.href='/qrRegister'">注册</span> </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="centent">
        <div class="centent_title">
            <ul>
                <li class="@if($category_id == -1)active  @endif " onclick="location='{{route('s_article_lists')}}'">所有</li>
                @foreach($articlecategorylist as $cate)
                    <li id="{{$cate['id']}}" onclick="location='{{route('s_article_lists',['cate'=>$cate['id']])}}'" class="@if($category_id == $cate['id']) active @endif ">{{$cate['category_name']}}</li>
                @endforeach
            </ul>
        </div>
        <div class="centent_wrop">
            <div class="banner">
                <img src="/img/bg.jpg" width="100%" height="100%">
            </div>
            <div class="centent_display">
                @if(count($articlelist) > 0)
                    @foreach($articlelist as $article)

                        @if(count($article['imgs']) > 0)
                            @if(count($article['imgs']) >= 4)
                                <div class="centent_centWropAdvertising">
                                    <p class="TitleRist"  onclick="window.open('{{route('s_article_listdetail')}}?id={{$article['id']}}')">{{$article['name']}}</p>
                                    <div class="img_wrop"  onclick="window.open('{{route('s_article_listdetail')}}?id={{$article['id']}}')">
                                        <div class="ompositionTableBg">分成
                                            @if(isset($article['percent_arr']['mode_2']['percent']))
                                                <span>{{$article['percent_arr']['mode_2']['percent'] or 0}}%</span>
                                            @else
                                                <span>{{$article['percent_arr']['mode_2']['account'] or 0}}</span>
                                            @endif
                                        </div>
                                        @foreach($article['imgs'] as $k=> $img)
                                            @if($k<=3)
                                                <div style=" width:170px; height:170px; margin: 0 10px 0 0; position:relative; overflow:hidden;">
                                                    <img class="img" data-original="{{$img['img_path']}}" src="/img/WechatIMG205.jpeg">
                                                </div>
                                            @endif
                                            {{--<img src="img/bg.jpg" width="170x" height="170px">--}}

                                        @endforeach
                                    </div>
                                    <p style="font-size:12px;color:rgba(126,126,126,1);padding-top: 18px">{{time_tranx($article['add_time'])}}</p>
                                </div>
                            @else
                                <div class="centent_centWrop" >
                                    <div class="centent_imgLogin" onclick="window.open('{{route('s_article_listdetail')}}?id={{$article['id']}}')">
                                        <div class="ompositionTableBg">分成
                                            @if(isset($article['percent_arr']['mode_2']['percent']))
                                                <span>{{$article['percent_arr']['mode_2']['percent'] or 0}}%</span>
                                            @else
                                                <span>{{$article['percent_arr']['mode_2']['account'] or 0}}</span>
                                            @endif
                                        </div>
                                        @foreach($article['imgs'] as $k=> $img)
                                            @if($k<=3)
                                                <div style=" width:170px; height:170px; margin: 0 10px 0 0; position:relative; overflow:hidden;">
                                                    <img class="img" data-original="{{$img['img_path']}}" src="/img/WechatIMG205.jpeg">
                                                </div>
                                            @endif
                                            {{--<img src="img/bg.jpg" width="170x" height="170px">--}}

                                        @endforeach
                                    </div>
                                    <div class="font_centMeass"  onclick="window.open('{{route('s_article_listdetail')}}?id={{$article['id']}}')">
                                        <p class="title_td hostAertO" style="overflow: hidden">{{$article['name']}}</p>
                                        <span class="title_tdFont">{{time_tranx($article['add_time'])}}<span style="display: none;">nickname</span></span>
                                    </div>
                                </div>
                            @endif
                        @else
                            {{--临时，理论上文章不会没有图片--}}
                            <div class="centent_centWrop" >
                                <div class="centent_imgLogin" onclick="window.open('{{route('s_article_listdetail')}}?id={{$article['id']}}')">
                                    <div class="ompositionTableBg">分成
                                        @if(isset($article['percent_arr']['mode_2']['percent']))
                                            <span>{{$article['percent_arr']['mode_2']['percent'] or 0}}%</span>
                                        @else
                                            <span>{{$article['percent_arr']['mode_2']['account'] or 0}}</span>
                                        @endif
                                    </div>
                                    <img src="/img/WechatIMG205.jpeg" width="170px" height="170px" onclick="window.open('{{route('s_article_listdetail')}}?id={{$article['id']}}')">
                                </div>
                                <div class="font_centMeass" onclick="window.open('{{route('s_article_listdetail')}}?id={{$article['id']}}')">
                                    <p class="title_td hostAertT">{{$article['name']}}</p>
                                    <span class="title_tdFont">{{time_tranx($article['add_time'])}}<span style="display: none;">nickname</span></span>
                                </div>
                            </div>
                        @endif


                    @endforeach
                @endif
            </div>

            @if($pageList)
                <div class="centent_button">
                    <ul>
                        @if($pageList['prev'])
                            <li onclick="location='{{route('s_article_lists',['page'=>$pageList['prev'], 'cate'=>$category_id])}}'">上一页</li>
                        @endif

                        @foreach($pageList['list'] as $page)
                            <li class="@if($page == $current_page)active @endif " onclick="location='{{route('s_article_lists',['page'=>$page, 'cate'=>$category_id])}}'">{{$page}}</li>
                        @endforeach

                        @if($pageList['dot'])
                            <li onclick="location='{{route('s_article_lists',['page'=>$pageList['dot'], 'cate'=>$category_id])}}'">...</li>
                        @endif
                        @if($pageList['last'])
                            <li onclick="location='{{route('s_article_lists',['page'=>$pageList['last'], 'cate'=>$category_id])}}'">{{$pageList['last']}}</li>
                        @endif
                        @if($pageList['next'])
                            <li onclick="location='{{route('s_article_lists',['page'=>$pageList['next'], 'cate'=>$category_id])}}'">下一页</li>
                        @endif
                    </ul>
                </div>
            @endif


        </div>
        <div class="centent_right">
            <div class="centent_right_top">
                <p class="tetle">上周最佳</p>

                @if(count($lastweekrank) > 0)
                    @foreach($lastweekrank as $last)
                        <div class="centent_right_font"  onclick="window.open('{{route('s_article_listdetail')}}?id={{$last['article_id']}}')">
                            <div class="img_imgBg">
                                <img width="100%" height="100%" src="{{$last['imgs']['img_path']}}" >
                            </div>
                            <div class="font_centTiele">
                                <p class="hostAertO">{{$last['name']}}</p>
                                <div class="specificInformation">
                                    <div class="views"><span>{{number_format($last['spreadnum'],0)}}</span></div>
                                    <div class="money_icon">￥<span>{{number_format($last['totalcomission'],2)}}</span></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif



            </div>
            <div class="centent_right_cent">
                <p class="tetleTwos">热点文章</p>
                @if(count($lastweekhotrank) > 0)
                    @foreach($lastweekhotrank as $lasthot)
                        <div class="centent_right_font" onclick="window.open('{{route('s_article_listdetail')}}?id={{$lasthot['tarticleid']}}')">
                            <div class="img_imgBg">
                                <img width="100%" height="100%" src="{{$lasthot['imgs']['img_path']}}" >
                            </div>
                            <div class="font_centTieleTwo">
                                <p class="">{{$lasthot['name']}}</p>
                            </div>
                        </div>

                    @endforeach
                @endif

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
                    <div class="accountRemember ">
                        <input type="checkbox">
                        <span class="accountRememberOne">记住密码</span>
                        <span class="accountRememberTwo">忘记密码</span>
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

<script src="/js/vendor/jquery-3.2.1.min.js"></script>

<script type="text/javascript" src="/js/jquery.lazyload.js"></script>
{{--<script src="{{ mix('js/main.js') }}"></script>--}}

<script>

    {{--鼠标移到标题变颜色--}}
$(function () {
        $('.TitleRist').hover(
            function(){$(this).css("color","#ff7241")}, //鼠标移入
            function(){$(this).css("color","#373737")} //鼠标移出
        );



        $('.title_td').hover(
            function(){$(this).css("color","#ff7241")}, //鼠标移入
            function(){$(this).css("color","#373737")} //鼠标移出
        );
        // $(".hostAertO").mouseover(function () {
        //     $(".hostAertO").css("color","#ff7241");
        // });
        // $(".hostAertO").mouseout(function () {
        //     $(".hostAertO").css("color","#373737");
        // });

    });




    //end
    $("#address").mouseover(function () {
        $(".opacity_d").css("display","block");
        $("#address").addClass("addressBorder");

    });
    $(".opacity_d").mouseover(function () {
        $(".opacity_d").css("display","block");
        $("#address").addClass("addressBorder");

    });
    $("#address"). mouseout(function () {
        $(".opacity_d").css("display","none");
        $("#address").removeClass("addressBorder");
    });
    $(".opacity_d"). mouseout(function () {
        $(".opacity_d").css("display","none");
        $("#address").removeClass("addressBorder");
    });

    $(".active"). mouseout(function () {
        $(".opacity_d").css("display","none");
        $("#address").removeClass("addressBorder");
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
                    //要获得 手机号， bladeshow
                    var mo = data.mobile;
                    //登录后模板show
                    $('.loggedname').html(mo);
                    $(".mark").hide();
                    $('.user_login').hide();
                    $('.user_logged').show();
                }
                else {
                    console.log(data);
                }
            }
        })
    });
    $(function () {
        $("img").lazyload();
        {{--@if(strlen($mobile)>0)--}}
        {{--$('.user_logged').show();//已登录--}}
        {{--@else--}}
        {{--$('.user_login').show();--}}
        {{--@endif--}}

        //遮罩
        $(".dfre").click(function () {
            $(".mark").show();
        });
        $(".close").click(function () {
            $(".mark").hide();
        });

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

        });



        //用户名鼠标移动效果
        // $("#address").mouseover(function () {
        //     $(".opacity_d").css("display","block");
        //     $("#address").addClass("addressBorder");
        //
        // });
        // $("#address"). mouseout(function () {
        //     $(".opacity_d").css("display","none");
        //     $("#address").removeClass("addressBorder");
        // });
        // $(".opacity_d").mouseover(function () {
        //     $(".opacity_d").css("display","block");
        //     $("#address").addClass("addressBorder");
        //
        // });
        //
        // $(".opacity_d"). mouseout(function () {
        //     $(".opacity_d").css("display","none");
        //     $("#address").removeClass("addressBorder");
        // });


        //end

        $(".centent_button ul li").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".centent_button ul li").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });
        $(".centent_title ul li").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".centent_title ul li").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });
        $(".userAs").mouseover(function () {
            $(".opacity_d").css("display","block");
            $("#address").addClass("addressBorder");

        });
        $(".userAs"). mouseout(function () {
            $(".opacity_d").css("display","none");
            $("#address").removeClass("addressBorder");
        });

        function imgResize(){
            console.log($(".img"))
            $(".img").on("load",function(){
                var oWidth = $(this).width();
                var oHeight = $(this).height();
                var cWidth="";
                var cHeight="";
                var ratio=oWidth/oHeight;
                console.log(ratio);
                if(oHeight<170){
                    cHeight=170;
                    cWidth=ratio*cHeight;
                    $(this).css({"width":cWidth+'px',"height":cHeight+'px',"top":'50%',"left":'50%',"margin-top":-cHeight/2+"px","margin-left":-cWidth/2+"px"});
                }
            });
        }
        imgResize();
    });
</script>
</body>
</html>