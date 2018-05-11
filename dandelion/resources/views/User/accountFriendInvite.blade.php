@extends('User.layout')

@section("title", "好友邀请")

@section("css")
<link rel="stylesheet" href="{{ mix('css/friend_links.css') }}">
@endsection

@section("content")
    <div class="centent_as">
        <div class="centent_top">
            <p class="centent_font_title">专属链接</p>
            <div class="centent_font">
                <div class="font_Ctrl"> <input type="text" id="inviteURL" value="{!! route("s_recommend_registration", ['code'=>$code]) !!}" readonly="true" style="width: 280px;outline: none;border: 0"><i class="top_icon" onclick="doCopy()" style="cursor: pointer"></i></div>
                <p class="centent_font_centent">好友通过您的链接注册并获得收益时您会获得佣金</p>
            </div>
        </div>
    </div>

    <div class="centent_top">
        <ul>
            <li><a href="{{route('s_user_accountFreiendInvite',['t'=>1])}}" class="@if($t==1) activeTitle @endif title_one">所有时间</a ></li>
            <li><a href="{{route('s_user_accountFreiendInvite',['t'=>2])}}" class="@if($t==2) activeTitle @endif title_two">最近30天</a > </li>
            <li><a href="{{route('s_user_accountFreiendInvite',['t'=>3])}}" class="@if($t==3) activeTitle @endif title_two">最近7天</a > </li>
            <li style="text-align: inherit;"><a href="{{route('s_user_accountFreiendInvite',['t'=>4])}}" class="@if($t==4) activeTitle @endif title_two">今日数据</a > </li>
        </ul>
    </div>

    <div class="centent_middle">
        <div class="centent_middle_left centent_middle_flex">
            <p class="centent_left_fontA">注册人数</p>
            <p class="centent_left_fontB">{{$total_people_num}}</p>
        </div>
        <div class="centent_middle_right centent_middle_flex">
            <p class="centent_right_fontA">我的累计佣金</p>
            <p class="centent_right_fontB">{{number_format($total_settlement,2)}}</p>
        </div>

    </div>
    <div class="centent_bottom">
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td>注册时间</td>
                <td>用户</td>
                <td>媒体行业</td>
                <td>累计佣金</td>
            </tr>
        @if(count($userList) > 0)
                @foreach($userList as $k=>$v)
                    <tr>
                        <td>{{date('Y.m.d H:i:s',$v['add_time'])}}</td>
                        <td><div class="user_as" onclick="location='{{route('s_user_FreiendInviteDetail',['ituid'=>$v['uid'],'name'=> substr_replace($v['username'],'****',3,4)])}}'"><i class="user_as_i"></i><span >{{substr_replace($v['username'],'****',3,4)}}</span></div></td>
                        <td>旅游</td>
                        <td>{{number_format($v['giveMoney'],2)}}</td>
                    </tr>
                @endforeach

        @endif
        </table>
        @if(count($userList) > 0)
            @if($pageList)
                <div class="runT">
                    @if(isset($pageList['first']) && $pageList['first'])
                        <span class="fsle_one" onclick="window.location.href='{{route('s_user_accountFreiendInvite',['page'=>$pageList['first'],'t'=>$t])}}'">首页</span>
                    @endif
                    {{--<li><a href="{{route('s_user_accountCommissionSettlementDetail',['page'=>$pageList['first']])}}">首页</a></li>--}}
                    <ul>
                        @if($pageList['prev'])
                            <li onclick="window.location.href='{{route('s_user_accountFreiendInvite',['page'=>$pageList['prev'],'t'=>$t])}}'">
                                上一页</li>
                        @endif

                        @foreach($pageList['list'] as $page)
                            <li class="@if($page == $current_page)active @endif " onclick="window.location.href='{{route('s_user_accountFreiendInvite',['page'=>$page,'t'=>$t])}}'">
                                {{$page}}</li>
                        @endforeach

                        @if($pageList['dot'])
                            <li onclick="window.location.href='{{route('s_user_accountFreiendInvite',['page'=>$pageList['dot'],'t'=>$t])}}'">
                                ...</li>
                        @endif
                        @if($pageList['last'])
                            <li onclick="window.location.href='{{route('s_user_accountFreiendInvite',['page'=>$pageList['last'],'t'=>$t])}}'">
                                {{$pageList['last']}}</li>
                        @endif
                        @if($pageList['next'])
                            <li onclick="window.location.href='{{route('s_user_accountFreiendInvite',['page'=>$pageList['next'],'t'=>$t])}}'">
                                下一页</li>
                        @endif
                    </ul>
                    @if(isset($pageList['last']) && $pageList['last'])
                        <span class="fsle_two" onclick="window.location.href='{{route('s_user_accountFreiendInvite',['page'=>$pageList['last'],'t'=>$t])}}'">末尾</span>
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


