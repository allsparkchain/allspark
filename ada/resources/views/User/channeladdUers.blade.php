@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
    <div id="accountInfo" >
        <div class="zmtdl_admin_crumbs">
            广告主数据 > <span class="on">信息</span>
            <a href="javascript:window.history.go(-1);">返回</a>
        </div>
        <div class="addCentuser">
            <div style="width:594px;margin:0 auto;overflow: hidden;">
                <div class="gcompanys">
                    <label for="company">广告主公司全称</label>
                    <div class="pline">|</div>
                    <div class="compangyDetail">{{$data['advert_company_name']}}</div>
                </div>
                <div class="gaddress">
                    <label for="address">公司办公地址</label>
                    <div class="pline">|</div>
                    <div class="compangyDetail">{{$data['advert_company_address']}}</div>
                </div>
                <div class="gwangzhi">
                    <label for="wangzhi">公司官网</label>
                    <div class="pline">|</div>
                    <div class="compangyDetail">{{$data['advert_company_website']}}</div>
                </div>
                <div class="gfuzeng">
                    <label for="fuzeng">负责人姓名</label>
                    <div class="pline">|</div>
                    <div class="fuzengDetail" >{{$data['advert_company_head']}}</div>
                    <label for="concat">负责人联系电话</label>
                    <div class="pline">|</div>
                    <div class="concatDetail" >{{$data['advert_company_tel']}}</div>
                </div>
                <div class="gcommdity">
                    <label for="commdity">商品描述</label>
                    <div class="pline">|</div>
                    <div class="commdityDetail">{{$data['advert_company_describe']}}</div>
                </div>
                <div class="gupfile">
                    <label for="">资质认证</label>
                    <div class="pline">|</div>
                    <div class="upfileDetail"><img src="{{$data['advert_company_img']}}" alt="" width="182px" height="122px"></div>
                </div>
                @if($data['advert_company_zip'])
                <div class="gzip">
                    <label for="">物料信息</label>
                    <div class="pline">|</div>
                    <div class="compangyDetail">
                    <span class="imgZips"></span>
                        @if($data['advert_company_zip'])
                    <a  href="{{$data['advert_company_zip']}}"  download="w3logo">资源包.zip<i class="downtext" ></i></a></div>
                    @else
                        <a  href="javascript:;"  download="w3logo">无<i class="downtext" ></i></a></div>
                        @endif
                    
                </div>
        @elseif($attachment)
            <div class="gzip">
                <label for="">物料图片</label>
                <div class="pline">|</div>
                @if($imageArr)
                    @foreach($imageArr as $k => $v)
                        <div class="compangyDetail">
                            <span class="upImgs"></span>{{ isset(explode('/', $imageNameArr[$k])[3])?explode('/', $imageNameArr[$k])[3]:$imageNameArr[$k]}}
                            <a href="{{ $v }}" download="w3logo"><i class="downtext" ></i></a>
                        </div>
                    @endforeach
                @else
                    <div class="compangyDetail">
                        <a download="w3logo">无</a>
                    </div>
                @endif
            </div>
            <div class="gzip">
                <label for="">物料文件</label>
                <div class="pline">|</div>
                    @if($textArr)
                        @foreach($textArr as $k => $v)
                        <div class="compangyDetail">
                            <span class="upFiles"></span>{{ isset(explode('/', $textNameArr[$k])[3])?explode('/', $textNameArr[$k])[3]:$textNameArr[$k]}}
                            <a href="{{ $v }}" download="w3logo"><i class="downtext" ></i></a>
                        </div>
                        @endforeach
                    @else
                    <div class="compangyDetail">
                        <a   download="w3logo">无</a>
                    </div>
                    @endif

            </div>
            <div class="gzip">
                <label for="">物料视频</label>
                <div class="pline">|</div>
                @if($videoArr)
                    @foreach($videoArr as $k => $v)
                        <div class="compangyDetail">
                            <span class="upVideos"></span>{{ isset(explode('/', $videoNameArr[$k])[3])?explode('/', $videoNameArr[$k])[3]:$videoNameArr[$k]}}
                            <a href="{{ $v }}" download="w3logo"><i class="downtext" ></i></a>
                        </div>
                    @endforeach
                @else
                    <div class="compangyDetail">
                        <a   download="w3logo">无</a>
                    </div>
                @endif
            </div>
            @else
            <div class="gzip"></div>
        @endif
                <div class="gbeizhu">
                    <label for="beizhu">备注</label>
                    <div class="pline">|</div>
                    <div class="beizhuDetail">{{$data['advert_company_note']}}</div>
                </div>
               
            </div>
            
            
        </div>
        
    </div>
@endsection




