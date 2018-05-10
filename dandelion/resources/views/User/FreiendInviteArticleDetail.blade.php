@extends('User.layout')

@section("title", "好友邀请")

@section("css")
    <link rel="stylesheet" href="{{ mix('css/friend_article_detail.css') }}">
@endsection

@section("content")

    <div style="height: 30px;
    min-height: 30px;">
        <ul>
            <a href="{{route('s_user_accountFreiendInvite')}}" style="text-decoration: none;color: #373737;">数据查询</a >  -
            <a href="{{route('s_user_FreiendInviteDetail',['ituid'=>$ituid, 't'=>$t])}}" style="text-decoration: none;color: #373737;">{{$channel_name}}</a > -
            <a href="#" style="text-decoration: none;color: #373737;">{{$article_name}}</a >
        </ul>

    </div>
    <div class="centent_top">
        <ul>
            <li><a href="{{route('s_user_FreiendInviteArticleDetail',['t'=>1,'sp'=>$sp])}}" class="@if($t==1) activeTitle @endif title_one">所有时间</a ></li>
            <li><a href="{{route('s_user_FreiendInviteArticleDetail',['t'=>2,'sp'=>$sp])}}" class="@if($t==2) activeTitle @endif title_two">最近30天</a > </li>
            <li><a href="{{route('s_user_FreiendInviteArticleDetail',['t'=>3,'sp'=>$sp])}}" class="@if($t==3) activeTitle @endif title_two">最近7天</a > </li>
            <li style="text-align: inherit;"><a href="{{route('s_user_FreiendInviteArticleDetail',['t'=>4,'sp'=>$sp])}}" class="@if($t==4) activeTitle @endif title_two">今日数据</a > </li>
        </ul>
    </div>

    <div class="centent_middle">
        <div class="centent_middle_left centent_middle_flex">
            <p class="centent_left_fontA">交易笔数</p>
            <p class="centent_left_fontB">{{$nums or 0}}</p>
        </div>
        <div class="centent_middle_right centent_middle_flex" style="    margin-right: 21px;">
            <p class="centent_right_fontA">累计交易额</p>
            <p class="centent_right_fontB">{{$sum_order or 0}}</p>
        </div>
        <div class="centent_middle_right centent_middle_flex" style="    margin-right: 21px;">
            <p class="centent_right_fontA">累计佣金</p>
            <p class="centent_right_fontB">{{$sum_commisssion or 0}}</p>
        </div>
    </div>
    <div class="centent_bottom">
        <table cellpadding="0" cellspacing="0" border="0" style="text-align: center;">
            <tr>
                <td style="padding-left: 0px;" >购买时间</td>
                <td style="padding-left: 0px;cursor: pointer;"  lang="number" long="sort">购买份数<img lang="number"></td>
                <td style="padding-right: 0px;cursor: pointer; text-align: inherit;"  lang="account" long="sort">交易额<img lang="account"></td>
                <td lang="commission_account" long="sort" style="cursor: pointer;">佣金额<img lang="commission_account"></td>
            </tr>
            @if(count($list) > 0)
                @foreach($list as $k=>$detail)
                    <tr>
                        <td style="padding-left: 0px;">{{date('Y.m.d H:i:s',$detail['add_time'])}}</td>
                        <td style="padding-left: 0px;">{{number_format($detail['number'],0)}}</td>
                        <td style="padding-right: 0px; text-align: inherit;">{{number_format($detail['account'],2)}}</td>
                        <td style="padding-left: 0px;">{{number_format($detail['commission_account'],2)}}</td>
                    </tr>
                @endforeach

            @endif
        </table>
        @if(count($list) > 0)
            @if($pageList)
                <div class="runT">
                    @if(isset($pageList['first']) && $pageList['first'])
                        <span class="fsle_one" onclick="window.location.href='{{route('s_user_FreiendInviteArticleDetail',['page'=>$pageList['first'],'t'=>$t,'sp'=>$sp])}}'">首页</span>
                    @endif
                    {{--<li><a href="{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['first']])}}">首页</a></li>--}}
                    <ul>
                        @if($pageList['prev'])
                            <li onclick="window.location.href='{{route('s_user_FreiendInviteArticleDetail',['page'=>$pageList['prev'],'t'=>$t,'sp'=>$sp])}}'">
                                上一页</li>
                        @endif

                        @foreach($pageList['list'] as $page)
                            <li class="@if($page == $current_page)active @endif " onclick="window.location.href='{{route('s_user_FreiendInviteArticleDetail',['page'=>$page,'t'=>$t,'sp'=>$sp])}}'">
                                {{$page}}</li>
                        @endforeach

                        @if($pageList['dot'])
                            <li onclick="window.location.href='{{route('s_user_FreiendInviteArticleDetail',['page'=>$pageList['dot'],'t'=>$t,'sp'=>$sp])}}'">
                                ...</li>
                        @endif
                        @if($pageList['last'])
                            <li onclick="window.location.href='{{route('s_user_FreiendInviteArticleDetail',['page'=>$pageList['last'],'t'=>$t,'sp'=>$sp])}}'">
                                {{$pageList['last']}}</li>
                        @endif
                        @if($pageList['next'])
                            <li onclick="window.location.href='{{route('s_user_FreiendInviteArticleDetail',['page'=>$pageList['next'],'t'=>$t,'sp'=>$sp])}}'">
                                下一页</li>
                        @endif
                    </ul>
                    @if(isset($pageList['last']) && $pageList['last'])
                        <span class="fsle_two" onclick="window.location.href='{{route('s_user_FreiendInviteArticleDetail',['page'=>$pageList['last'],'t'=>$t,'sp'=>$sp])}}'">末尾</span>
                    @endif
                </div>
            @endif
        @endif

    </div>
@endsection
@section("script")
    <script type="text/javascript">
        $('.centent_left_title').find('ul').find('li').each(function(){
            $(this).removeClass();
            $(this).find('a').removeClass('activeA1 activeA2 activeA3 activeA4 activeA5 activeA6');
            if($(this).attr('lang') == 'accountFreiendInvite'){
                $(this).addClass('active');
                $(this).find('a').addClass('activeA5');
            }
        });

        var asort = "{{$sort}}";
        if(asort==1){
            $('img[lang=number]').attr('src','/img/Up.pic')
        }else if(asort==2){
            $('img[lang=number]').attr('src','/img/Dn.pic')
        }else if(asort==3){
            $('img[lang=account]').attr('src','/img/Up.pic')
        }else if(asort==4){
            $('img[lang=account]').attr('src','/img/Dn.pic')
        }else if(asort==5){
            $('img[lang=commission_account]').attr('src','/img/Up.pic')
        }else if(asort==6){
            $('img[lang=commission_account]').attr('src','/img/Dn.pic')
        }

        $('td[long=sort]').click(function(){
            var sort = "{{$sort}}";
            if($(this).attr('lang')=='number'){
                if(sort==1){
                    sort = 2;
                }else{
                    sort = 1;
                }
            }else if($(this).attr('lang')=='account'){
                if(sort==3){
                    sort = 4;
                }else{
                    sort = 3;
                }
            }else if($(this).attr('lang')=='commission_account'){
                if(sort==5){
                    sort = 6;
                }else{
                    sort = 5;
                }
            }
            var url = "{{route('s_user_FreiendInviteArticleDetail',['sp'=>$sp])}}"+'&t='+{{$t}}+'&sort='+sort;
            window.location.href = url;
        });


        function doCopy(){

            var e=document.getElementById("inviteURL");//对象是content
            e.select(); //选择对象

            if(document.execCommand('copy', false, null)){
                alert('复制成功');
            } else{
                alert('浏览器不支持，请自行复制');
            }
        }
    </script>

@endsection


