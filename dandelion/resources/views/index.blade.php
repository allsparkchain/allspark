<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>首页-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <!--<link rel="stylesheet" href="http://cdn.dowebok.com/77/css/jquery.fullPage.css">-->
    <link rel="stylesheet" href="{{ mix('css/myindex.css') }}">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    {{--<link rel="stylesheet" href="{{ mix('css/header.css') }}">--}}

    <script src="/js/vendor/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="/js/vendor/jquery-3.2.1.min.js"></script>
    {{--<script src="/js/vendor/fulPage.js"></script>--}}
    <script src="/js/vendor/easing.min.js"></script>
    <script>
        // $(function () {
        //     $('#dowebok').fullpage({
        //         'navigation': true,
        //         anchors: ['page1', 'page2', 'page3', 'page4'],
        //         menu: '#menu',
        //         // afterLoad: function (anchorLink, index) {
        //         //     if (index == 2) {
        //         //         $('.section2').find('h2').delay(500).animate({
        //         //             left: '0'
        //         //         }, 1500, 'easeOutExpo');
        //         //     }
        //         //     if (index == 3) {
        //         //         $('.section3').find('p').delay(500).animate({
        //         //             bottom: '0'
        //         //         }, 1500, 'easeOutExpo');
        //         //     }
        //         //     if (index == 4) {
        //         //         $('.section4').find('p').fadeIn(2000);
        //         //     }
        //         // },
        //         // onLeave: function (index, direction) {
        //         //     if (index == '2') {
        //         //         $('.section2').find('p').delay(500).animate({
        //         //             left: '-120%'
        //         //         }, 1500, 'easeOutExpo');
        //         //     }
        //         //     if (index == '3') {
        //         //         $('.section3').find('p').delay(500).animate({
        //         //             bottom: '-120%'
        //         //         }, 1500, 'easeOutExpo');
        //         //     }
        //         //     if (index == '4') {
        //         //         $('.section4').find('p').fadeOut(2000);
        //         //     }
        //         // }
        //     });
        // });
    </script>
</head>

<div id="dowebok">
    <div class="section section1">
        <div class="header_border">
            <div class="header">
                <div class="login">
                    <a href=""></a>
                </div>
                <div class="header_navWrop">
                    <div class="header_nva">
                        <div class="nav_list">
                            <a href="javascript:;">首页</a>
                            <a href="{{route('s_article_lists')}}" class="active">资讯</a>
                            <a href="https://www.pugongying.link/jzinter" target="_blank">关于我们</a>
                        </div>
                        @if(strlen($user) >0)
                            {{--登录状态--}}
                            <div class="user_logged" style="display:block;float: right">
                                <div class="userHides" onclick="window.location.href='{{Route('s_user_accountInfo')}}'" style="display: flex;display: flex;justify-content: center;align-items: center;overflow: hidden;margin-left: 60px;line-height: 70px; cursor: pointer;">
                                    <div class="usserImg"  style="width:37px;height:37px;font-size: 0; border-radius: 50%;"><img width="100%" height="100%" style="border-radius: 50%;" src="{{\Auth::getUser()->getHeadImgurl()}}"></div>
                                    <span style="margin-left: 13px" class="loggedname">{{\Auth::getUser()->getUserNickname()}}</span>
                                </div>
                                <div class="user_hide" style="display: none;float: right">
                                    <span class="userAs" id="userAs" onclick="window.location.href='{{Route('s_user_accountInfo')}}'">个人中心</span>
                                    <span class="userAs" onclick="window.location.href='{{Route('s_logout')}}'">退出</span>
                                </div>
                            </div>
                        @else
                            {{--未登录状态--}}
                            <div class="user_login" style="">
                                <span class="spacing" onclick="window.location.href='/login'">登录</span>
                                <i class="spacing">/</i>
                                <span class="spacing" onclick="window.location.href='/register'">注册</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="f_content" style="overflow:hidden;height: 740px;">
            <!--<div style="clear: both"></div>-->
            <div class="f_tentent">
                <div class="f_tentent_p">
                    <p>让每个人的所拍、所写、所分享都产生价值</p>
                    {{--<a class="section1_top_animation" href="#"></a>--}}
                </div>
            </div>
        </div>
    </div>
    <div class="section section2" style="height: 947px;">
        <div class="e_centent">
            <div class="e_div_w">
                <h2>内容价值最大化</h2>
                <p>充分激活撰稿人的脑细胞创作内容，抒发情感同时还能有一份现金回报</p>
                <div class="e_from_span">
                    <a href="javascritp:void(0)">每单现金分成</a>
                    <a href="javascritp:void(0)">每月准时结算</a>
                    <a href="javascritp:void(0)">实时计算佣金</a>
                </div>
            </div>
            <a class="e_article_img" ></a>
            <a class="e_wen_z"></a>
            <a class="e_img_img"></a>
            <a class="e_jokes_img"></a>
            <a class="e_voice_img"></a>
            <div class="e_article" style="min-height: 530px !important;"></div>
        </div>
    </div>
    <div class="section section3">
        <div class="s_centent" style="">
            <div class="s_worp">
                <div class="s_centent_left">
                    <h2>多种题材任你选</h2>
                    <div class="s_span">获取更多与你渠道匹配的内容，能更好的利用粉丝与社群进行流量变现</div>
                    <div class="s_min_login">
                        <a href="javascritp:void(0)">海量精品原创</a>
                        <a href="javascritp:void(0)">实时数据查询</a>
                        <a href="javascritp:void(0)">每月准时结算</a>
                    </div>
                </div>
                <div class="s_centent_right" >
                    <a class="s_icon_l" href="javascritp:void(0)"></a>
                    <a class="s_icon_j" href=" javascritp:void(0)"></a>
                    <a class="s_icon_m" href="javascritp:void(0)"></a>
                    <a class="s_icon_learning" href=" javascritp:void(0)"></a>
                </div>
            </div>
        </div>
    </div>
    <div class="section section4">
        <div class="four_centent">
            <div class="four_flexs">
                <div class="four_center_la">
                    <div class="four_worp">
                        <div class="four_centent_right"></div>
                        <div class="four_centent_left">
                            <h2>目标客户群更精准</h2>
                            <div class="four_span">写手选择自己的兴趣产品进行包装，渠道选择符合自己调性的产品进行推广</div>
                            <div class="four_min_login">
                                <a href="javascritp:void(0)">产品定向包装</a>
                                <a href="javascritp:void(0)">精准渠道接单</a>
                                <a href="javascritp:void(0)">CPS无需预付</a>
                            </div>
                        </div>
                    </div>
                    <div class="footer" style="margin-bottom: 50px"><p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #595959;">沪ICP备16017440号</a>　</p></div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(function () {
        //第一屏最后一个div高度
        var F_H=$(".section1").height();
        $(".f_tentent").height(F_H);
        $(".section1").css("overflow","hidden");
        //第二屏最后一个div高度
        var E_H=$(".section").height();
        $(".e_article").height(E_H-295);
        $(".section3").css("overflow","hidden");
        //第三屏
        var S_H=$(".section2").height();
        $(".s_worp").height(S_H-100);
        // 第四屏
        var S_H=$(".section4").height();
        $(".four_centent").height(S_H);
    })

    //    鼠标移动显示

    $(".userHides").mouseover(function () {
//        alert(1);
        $(".user_hide").show();
    });
    $(".userHides").mouseout(function () {
        $(".user_hide").hide();
    });

    $(".userAs").mouseover(function () {
        $(".user_hide").show();
        // $(this).css("background","rgba(225, 225, 225, 0.1)");
    });
    $(".userAs").mouseout(function () {
        $(".user_hide").hide();
        // $(this).css("background","rgba(225, 225, 225, 0.05)")
    });


    $('.TitleRist span').mouseover(
        function(){
            $(this).css("background","rgba(225, 225, 225, 1)")
        },function(){ //鼠标移入
         } //鼠标移出
    );

</script>
</body>
</html>