@extends('User.layout')

@section("title", "好友邀请")

@section("css")
    <link rel="stylesheet" href="{{ mix('css/friend_detail.css') }}">
@endsection

@section("content")

    <div style="height: 30px;
    min-height: 30px;">
        <ul>
            <a href="{{route('s_user_accountFreiendInvite')}}" style="text-decoration: none;color: #373737;">数据查询</a >  -
            <a href="{{route('s_user_FreiendInviteDetail',['ituid'=>$ituid, 't'=>$t])}}" style="text-decoration: none;color: #373737;">{{$channel_name}}</a >
        </ul>

    </div>

    <div class="centent_top">
        <ul>
            <li><a href="{{route('s_user_FreiendInviteDetail',['t'=>1,'ituid'=>$ituid])}}" class="@if($t==1) activeTitle @endif title_one">所有时间</a ></li>
            <li><a href="{{route('s_user_FreiendInviteDetail',['t'=>2,'ituid'=>$ituid])}}" class="@if($t==2) activeTitle @endif title_two">最近30天</a > </li>
            <li><a href="{{route('s_user_FreiendInviteDetail',['t'=>3,'ituid'=>$ituid])}}" class="@if($t==3) activeTitle @endif title_two">最近7天</a > </li>
            <li style="text-align: inherit;"><a href="{{route('s_user_FreiendInviteDetail',['t'=>4,'ituid'=>$ituid])}}" class="@if($t==4) activeTitle @endif title_two">今日数据</a > </li>
        </ul>
    </div>

    <div class="centent_middle">
        <div class="centent_middle_left centent_middle_flex">
            <p class="centent_left_fontA">内容篇数</p>
            <p class="centent_left_fontB">{{$content_count or 0}}</p>
        </div>
        <div class="centent_middle_right centent_middle_flex" style="    margin-right: 21px;">
            <p class="centent_right_fontA">平均购买转化</p>
            <p class="centent_right_fontB">{{$avg_buy or 0}}%</p>
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
                <td style="padding-left: 0px;">内容标题</td>
                <td style="padding-left: 0px;">展示次数</td>
                <td style="padding-right: 0px; text-align: inherit;">购买次数</td>

                <td lang="buytransfer" long="sort" style="cursor: pointer;">购买转化<img lang="buytransfer"></td>
                <td lang="account" long="sort" style="cursor: pointer;">交易额<img lang="account"></td>
                <td lang="commission_account" long="sort" style="cursor: pointer;">佣金额<img lang="commission_account"></td>
            </tr>
            @if(count($list) > 0)
                @foreach($list as $k=>$detail)
                    <tr>
                        <td style="padding-left: 0px;">{{subtext($detail['name'], 8)}}</td>
                        <td style="padding-left: 0px;">{{number_format($detail['quantity'],0)}}</td>
                        <td style="padding-right: 0px; text-align: inherit;">{{number_format($detail['count'],0)}}</td>
                        <td>{{number_format($detail['ab']*100,2)}}%</td>
                        <td>{{number_format($detail['account'],2)}}</td>
                        <td>{{number_format($detail['commission_account'],2)}}</td>
                        <td><span style="cursor: pointer;" onclick="location='{{route('s_user_FreiendInviteArticleDetail',['sp'=>$detail['id'],'channel_name'=>subtext($detail['name'], 8)])}}'">详细</span></td>
                    </tr>
                @endforeach

            @endif
        </table>
        @if(count($list) > 0)
            @if($pageList)
                <div class="runT">
                    @if(isset($pageList['first']) && $pageList['first'])
                        <span class="fsle_one" onclick="window.location.href='{{route('s_user_FreiendInviteDetail',['page'=>$pageList['first'],'t'=>$t,'ituid'=>$ituid])}}'">首页</span>
                    @endif
                    <ul>
                        @if($pageList['prev'])
                            <li onclick="window.location.href='{{route('s_user_FreiendInviteDetail',['page'=>$pageList['prev'],'t'=>$t,'ituid'=>$ituid])}}'">
                                上一页</li>
                        @endif

                        @foreach($pageList['list'] as $page)
                            <li class="@if($page == $current_page)active @endif " onclick="window.location.href='{{route('s_user_FreiendInviteDetail',['page'=>$page,'t'=>$t,'ituid'=>$ituid])}}'">
                                {{$page}}</li>
                        @endforeach

                        @if($pageList['dot'])
                            <li onclick="window.location.href='{{route('s_user_FreiendInviteDetail',['page'=>$pageList['dot'],'t'=>$t,'ituid'=>$ituid])}}'">
                                ...</li>
                        @endif
                        @if($pageList['last'])
                            <li onclick="window.location.href='{{route('s_user_FreiendInviteDetail',['page'=>$pageList['last'],'t'=>$t,'ituid'=>$ituid])}}'">
                                {{$pageList['last']}}</li>
                        @endif
                        @if($pageList['next'])
                            <li onclick="window.location.href='{{route('s_user_FreiendInviteDetail',['page'=>$pageList['next'],'t'=>$t,'ituid'=>$ituid])}}'">
                                下一页</li>
                        @endif
                    </ul>
                    @if(isset($pageList['last']) && $pageList['last'])
                        <span class="fsle_two" onclick="window.location.href='{{route('s_user_FreiendInviteDetail',['page'=>$pageList['last'],'t'=>$t,'ituid'=>$ituid])}}'">末尾</span>
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
            $('img[lang=buytransfer]').attr('src','/img/Up.pic')
        }else if(asort==2){
            $('img[lang=buytransfer]').attr('src','/img/Dn.pic')
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
            if($(this).attr('lang')=='buytransfer'){
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
            //alert(sort);
            var url = "{{route('s_user_FreiendInviteDetail',['ituid'=>$ituid])}}"+'&t='+{{$t}}+'&sort='+sort;
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


