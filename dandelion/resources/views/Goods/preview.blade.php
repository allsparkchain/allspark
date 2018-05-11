@extends('layout')

@section("title", "首页")


@section("css")
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
    <link rel="stylesheet" href="{{ mix('css/swiper.min.css') }}">
@endsection

@section("content")
<!--内容区域-->
<style>

/* .pgy_article2{
    position: absolute;
    background:#fff;
    top:286px;
    width:875px;
    left:50%;
    margin-left:-600px;
}     */
</style>
<!--内容-->
<div class="pgy_infoDetail" style="padding: 0px;">
    <div class="pgy_1200">

        <div class="pgy_detail_left">
            <div style="width: 1200px; margin: 0px auto;">
                <div class="pgy_y_copy" style="margin-bottom: 20px;">
                    <div class="pgy_y_time"></div>
                </div>
            </div>
            <h1 id="articleTitle"></h1>
            <div class="pgy_article" style=" min-height: 1000px;"></div>
        </div>

        <div class="pgy_detail_right">
            <div class="pgy_show_commodity">
                <div class="pgy_comodity_t"><img src="/image/pgy_shoubiao.jpg" alt=""></div>
                <div class="pgy_comodity_c">
                    <p ><span class="pgy_comodity_money"><i>¥</i>1231,456</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>111111人付款</span></p>
                    <p class="pgy_comodity_p">天王表男士皮带自动机械手表商务男表5844</p>
                    <hr style="height:1px;border:none;border-top:1px dashed #7e7e7e;" />
                    <p class="pgy_comodity_p">剩余库存 <span>123456789</span>件</p>
                    <p class="pgy_comodity_p">剩余时间 <span>124</span>天</p>
                </div>

                <div id="pgy2" class="pgy_generalize_href" >
                    <h1>推广后，完成订单即可获得分成</h1>
                    <h2>你可以通过以下方式推广</h2>
                    <div class="pgy_step1"><span>方式1</span></div>
                    <a class="pgy_generalize_link" href="#" target="_blank">一键转至公微</a>
                    <h2>授权后可快速转载到你的公微</h2>
                    <div class="pgy_step1"><span>方式2</span></div>
                    <div id="goodsqrcode" class="er_d" style=" width: 180px; margin: 0 auto;"></div>
                    <h2>微信扫二维码进行转发</h2>
                    <div class="pgy_step1"><span>方式3</span></div>
                    <p id="urlcode" style="width: 250px; margin: 0 auto; font-size: 14px; color: #7e7e7e; word-break: break-word;"></p>
                    <p style="width: 250px; margin: 20px auto 40px auto; font-size: 13px; line-height: 20px; text-align: center; color: #7e7e7e; word-break: break-word;">手动复制原文并附带商品购买链接<br>也可以将此链接复制到公微的阅读原文内</p>
                    <div class="pgy_finish_order">
                        <span class="first">完成订单即可获得</span><br>
                        <span class="second">5%</span>
                    </div>
                </div>
                <div style=" font-size: 14px; color: #7e7e7e; text-align: center; margin: 20px 0 0 0;">Tips：收益收益可在用户中心进行查看</div>
                
            </div>
        </div>
    </div>

    <div style=" width: 100%; height:100%; position:fixed; top:0; left:0; background: rgba(0,0,0,0.3);"></div>
    <div style=" position: absolute; background: #fff; top: 72px; width: 875px; left: 50%; padding: 0 20px; margin-left: -640px;">
        <i class="pgy_close"></i>
        <div style="overflow:hidden"> 
            <div style="width: 1200px; margin: 0px auto;">
                <div class="pgy_y_copy" style="margin-bottom: 20px;">
                    <div class="pgy_y_time2"></div>
                </div>
            </div>
            <h1 id="articleTitle2" style="font-size: 30px; color: #373737; font-weight: normal; line-height: 36px;"></h1>
            <div class="pgy_article2"></div>
        </div>
    </div>

</div>
@endsection
@section("script")
<script>
$(function(){
    $("#articleTitle").html(sessionStorage.getItem('articleTitle'));
    $("#articleTitle2").html(sessionStorage.getItem('articleTitle'));
    $(".pgy_article").html(sessionStorage.getItem('articleContent'));
    $(".pgy_article2").html(sessionStorage.getItem('articleContent'));
    $(".pgy_y_time").html(sessionStorage.getItem('articleTime'));
    $(".pgy_y_time2").html(sessionStorage.getItem('articleTime'));
    $(".pgy_close").on("click",function(){
        window.close();
    })
})    
</script>
@endsection
