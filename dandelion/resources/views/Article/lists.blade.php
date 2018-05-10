<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>资讯-蒲公英 - 让每个人所拍、所写、所分享都产生价值！</title>
    <link rel="stylesheet" href="{{ mix('css/information.css') }}">
    <link rel="stylesheet" href="{{ mix('css/header.css') }}">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
</head>
<body style="overflow: hidden !important;background-color: ">
<div class="worp">
    <div class="runSign">
        <div class="runSign_mes">
            <div class="block_w">
                @if(\Auth::user())
                    <a class="runSign_mes_d" href="{{route('s_user_accountInfo')}}">{{substr_replace(\Auth::user()->getUserMobile(),'****',3,4)}}</a>
                    <a class="G_run" href="{{route('s_logout')}}">退出</a>
                @else
                    <a class="runSign_mes_d" href="{{route('s_login')}}">登录</a><a class="G_run">/</a><a href="/register">注册</a>
                @endif

            </div>

        </div>
    </div>
    <div class="header_border">
        <div class="header">
            <div class="login">
                <a href="/">LOGO</a>
                <div class="address" id="address" style="">上海</div>
                <!--地址隐藏块-->
                <div class="opacity_d" style="display: none">
                    <div class="opacity_d_w ">
                        <div class="opacity_host opacity_d_wActie">热门城市 :</div>
                        <div class="opacity_host_font">
                            <span>上海</span>
                            <span>深圳</span>
                            <span>北京</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                            <span>北京</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                        </div>
                    </div>
                    <div class="opacity_d_w opacity_dPadding">
                        <div class="opacity_host">华东地区 :</div>
                        <div class="opacity_host_font">
                            <span>上海</span>
                            <span>深圳</span>
                            <span>北京</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                            <span>北京</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                        </div>
                    </div>
                    <div class="opacity_d_w opacity_dPadding1">
                        <div class="opacity_host">中部地区 :</div>
                        <div class="opacity_host_font">
                            <span>上海</span>
                            <span>深圳</span>
                            <span>北京</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                            <span>北京</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                        </div>
                    </div>
                    <div class="opacity_d_w opacity_dPadding2">
                        <div class="opacity_host">华南地区 :</div>
                        <div class="opacity_host_font">
                            <span>上海</span>
                            <span>深圳</span>
                            <span>北京</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                            <span>北京</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                            <span>城都</span>
                            <span>武汉</span>
                            <span>武汉</span>
                        </div>
                    </div>

                </div>
            </div>
            <div class="header_nva">
                <ul>
                    <li><a href="/">首页</a></li>
                    <li><a class="active" href="{{route('s_article_lists')}}">资讯</a></li>
                    <li><a class="active"  href="{{route('s_user_accountInfo')}}">个人中心</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="centent">
        <div class="centent_title">
            <ul>
                <li class=""><a class="@if($category_id == -1)active  @endif " href="{{route('s_article_lists')}}">所有</a></li>
                @foreach($articlecategorylist as $cate)
                    <li id="{{$cate['id']}}" ><a class="@if($category_id == $cate['id']) active @endif " href="{{route('s_article_lists',['cate'=>$cate['id']])}}">{{$cate['category_name']}}</a></li>
                @endforeach
            </ul>
        </div>
        @if(count($articlelist) > 0)
            @foreach($articlelist as $article)
                <div class="centent_display" ids = {{$article['id']}}>
                    <div class="dispaly_img">
                        @if(count($article['imgs']) > 0)
                            @foreach($article['imgs'] as $key=> $img)
                                <div class="dispaly_img_{{$key+1}}">
                                    <img src="{{$img['img_path']}}" width="100%" height="126px">
                                    @if(count($article['imgs']) == $key+1)
                                        <div class="border_bBorderrudis">分成
                                            <p>
                                            @if(isset($article['percent_arr']['mode_2']['percent']))
                                                <span>{{$article['percent_arr']['mode_2']['percent'] or 0}}%</span>
                                            @else
                                                <span>{{$article['percent_arr']['mode_2']['account'] or 0}}</span>
                                            @endif
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="centent_font">
                        <p class="h2">{{$article['name']}}</p>
                        <span>{{date('Y.m.d',$article['add_time'])}}</span>
                        <p class="font_details">
                            {{$article['summary']}}
                        </p>
                    </div>
                </div>
            @endforeach
        @endif
        <!--分页按钮-->
        @if($pageList)
            <div class="centent_button">
                <ul>
                    @if($pageList['prev'])
                        <li><a href="{{route('s_article_lists',['page'=>$pageList['prev'], 'cate'=>$category_id])}}">上一页</a></li>
                    @endif

                    @foreach($pageList['list'] as $page)
                        <li class="@if($page == $current_page)active @endif "><a href="{{route('s_article_lists',['page'=>$page, 'cate'=>$category_id])}}">{{$page}}</a></li>
                    @endforeach

                    @if($pageList['dot'])
                        <li><a href="{{route('s_article_lists',['page'=>$pageList['dot'], 'cate'=>$category_id])}}">...</a></li>
                    @endif
                    @if($pageList['last'])
                        <li><a href="{{route('s_article_lists',['page'=>$pageList['last'], 'cate'=>$category_id])}}">{{$pageList['last']}}</a></li>
                    @endif
                    @if($pageList['next'])
                        <li><a href="{{route('s_article_lists',['page'=>$pageList['next'], 'cate'=>$category_id])}}">下一页</a></li>
                    @endif
                </ul>
            </div>
        @endif
        {{--<div class="centent_button">--}}
            {{--<ul>--}}

                {{--<li><a href="{{route('s_article_lists',['page'=>1])}}">上一页</a></li>--}}
                {{--<li class="active"><a>1</a></li>--}}
                {{--<li>2</li>--}}
                {{--<li>3</li>--}}
                {{--<li>...</li>--}}
                {{--<li>40</li>--}}
                {{--<li>下一页</li>--}}
            {{--</ul>--}}
        {{--</div>--}}

    </div>
    <footer>
        <p style="margin-top: 100px;">©2017 剑指网络 ALL RIGHTS RESERVED. <a  href="http://www.miitbeian.gov.cn" target="_blank" style="text-decoration: none; color: #FFF;">沪ICP备16017440号</a>　</p>
    </footer>
</div>
<script type="text/javascript" src="/js/vendor/jquery-3.2.1.min.js"></script>
<script src="{{ mix('js/main.js') }}"></script>
{{--<script src="/js/main.js"></script>--}}
<script>
    $(function () {
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




        $(".centent_display").click(function () {
            var id = $(this).attr('ids');
            window.location.href='{{route('s_article_listdetail')}}?id='+id;
        });
        $(".centent_button ul li a ").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".centent_button ul li a ").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });
        $(".centent_title ul li a").mouseover(function () {
            $(this).addClass("on_Mousemove");
        });
        $(".centent_title ul li a").mouseout(function () {
            $(this).removeClass("on_Mousemove");
        });

    });
</script>
</body>
</html>
