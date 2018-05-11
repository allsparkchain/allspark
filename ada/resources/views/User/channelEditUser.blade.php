@extends('User.layout')


@section("title", "广告主数据")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}
    <style>
        [readonly]{
            cursor: default;
        }
    </style>
@endsection

@section("content")
<div id="channelEditUser" style="display:none;">
    <div class="zmtdl_admin_crumbs">
        广告主数据 > <span class="on">编辑</span>
        <a href="javascript:window.history.go(-1);">返回</a>
    </div>
    <div class="addCentuser">
        <form class="form form-horizontal" id="form-product-add"  enctype="multipart/form-data" method="post" action="{{route('s_user_uploadZip')}}">
            {{ csrf_field() }}
            <div style=" width:594px; margin:0 auto;position:relative;">
                <div class="gremark" v-if="review_remark">@{{review_remark}}</div>
                <div class="gcompanys">
                    <label for="company">广告主公司全称</label>
                    <input class="pgy_company" type="text" style=" color: #373737;" placeholder="请输入你的公司全称" v-model="CompanyName" @blur="companyName" v-if="review_status==2" readonly="readonly">
                    
                    <input class="pgy_company" type="text" style=" color: #373737;" placeholder="请输入你的公司全称" v-model="CompanyName" @blur="companyName" v-else>
                    <!-- <span >@{{errName}}</span> -->
                </div>
                <div class="gaddress">
                    <label for="address">公司办公地址</label>
                    <input class="pgy_address" type="text" style=" color: #373737;" placeholder="请输入办公地址" v-model="Caddress" v-if="review_status==2" readonly="readonly">
                    <input class="pgy_address" type="text" style=" color: #373737;" placeholder="请输入办公地址" v-model="Caddress" v-else>
                </div>
                <div class="gwangzhi">
                    <label for="wangzhi">公司官网</label>
                    <input class="pgy_wangzhi" type="text" style=" color: #373737;" placeholder="请输入公司官方网址" v-model="Cwebsite" v-if="review_status==2" readonly="readonly">
                    <input class="pgy_wangzhi" type="text" style=" color: #373737;" placeholder="请输入公司官方网址" v-model="Cwebsite" v-else>
                </div>
                <div class="gfuzeng" style=" overflow: hidden;">
                    <label for="fuzeng">负责人姓名</label>
                    <input class="pgy_fuzeng" type="text" style="margin-right:17px;" placeholder="姓名" v-model="Cname" v-if="review_status==2" readonly="readonly">
                    <input class="pgy_fuzeng" type="text" style="margin-right:17px;" placeholder="姓名" v-model="Cname" v-else>
                    <label for="concat">负责人手机号</label>
                    <input class="pgy_concat" type="text" placeholder="手机号" maxlength="11" v-model="Ctel" v-if="review_status==2" readonly="readonly">
                    <input class="pgy_concat" type="text" placeholder="手机号" maxlength="11" v-model="Ctel" v-else>
                </div>
                <div style=" width: 100%; height:60px; line-height:60px; text-indent: 145px;">注：负责人手机号用于开通广告主后台</div>
                <div class="gcommdity" style=" margin: 0;">
                    <label for="commdity">商品描述</label>
                    <textarea class="pay_commdity" name="" id="pay_commdity" cols="30" rows="10" placeholder="商品描述" v-model="Cdescribe" v-if="review_status==2" readonly="readonly"></textarea>
                    <textarea class="pay_commdity" name="" id="pay_commdity" cols="30" rows="10" placeholder="商品描述" v-model="Cdescribe" v-else></textarea>
                </div>
                <div class="gupfile">
                    <label for="">资质认证</label>
                    <div class="pgy_upfile">
                        <div class="uploade_content" v-if="review_status!=2">
                            <input id="qualification" type="file" name="files[]">
                            <!-- <input type="file" name="zip" @change="GetFileUpload($event)"> -->
                        </div>
                        <div class="loadedImg" v-show="xImg">
                            <img :src="imagePath" alt="">
                            <i class="upcolse" @click="cImg"></i>
                        </div>
                    </div>
                    <div v-html="qualificationPlaceHolder" style="float: left; width: 455px; height: 35px; line-height:35px; font-size: 12px; color: #cfcfcf; margin: 6px 0px 0px 140px;">
                    </div>
                </div>
                <div id="qualificationPlaceHolderProgress" class="progress" style=" float: left; width: 455px; height:10px; margin: 0 0 0 140px; display: none;">
                    <div class="progress-bar progress-bar-success"></div>
                </div>
            
                <!--物料图片-->
                <div class="gzip" style=" min-height: 43px; margin-top: 50px;">
                    <label for="">物料图片</label>
                    <span class="pgy_zip" type="text" v-html="picPlaceHolder"></span>
                    <i class="loadups"></i>
                    <input id="pic" type="file" name="files[]">
                </div>
                <div class="wenj" v-for="i in picPath">
                    <span class="imgzips"></span>
                    <span style=" float: left; width: 412px; height: 28px; word-break: break-all;">@{{i}}</span>
                    <a href="javascript:;" @click="picDelete(i)">删除</a>
                </div>
                <div id="picProgress" class="progress" style=" float: left; width: 455px; height:10px; margin: 6px 0 0 140px; display: none;">
                    <div class="progress-bar progress-bar-success"></div>
                </div>

                <!--物料文件-->
                <div class="gzip" style=" min-height: 43px; margin-top: 50px;">
                    <label for="">物料文件</label>
                    <span class="pgy_zip" type="text" v-html="filePlaceHolder"></span>
                    <i class="loadups"></i>
                    <input id="fileupload" type="file" name="files[]">
                </div>
                <div class="wenj" v-for="i in filePath">
                    <span class="imgzips"></span>
                    <span style=" float: left; width: 412px; height: 28px; word-break: break-all;">@{{i}}</span>
                    <a href="javascript:;" @click="fileDelete(i)">删除</a>
                </div>
                <div id="fileProgress" class="progress" style=" float: left; width: 455px; height:10px; margin: 6px 0 0 140px; display: none;">
                    <div class="progress-bar progress-bar-success"></div>
                </div>

                <!--物料视频-->
                <div class="gzip" style=" min-height: 43px; margin-top: 50px;">
                    <label for="">物料视频</label>
                    <span class="pgy_zip" type="text" v-html="videoPlaceHolder"></span>
                    <i class="loadups"></i>
                    <input id="videoupload" type="file" name="files[]">
                </div>
                <div class="wenj" v-for="i in videoPath">
                    <span class="imgzips"></span>
                    <span style=" float: left; width: 412px; height: 28px; word-break: break-all;">@{{i}}</span>
                    <a href="javascript:;" @click="videoDelete(i)">删除</a>
                </div>
                <div id="videoProgress" class="progress" style=" float: left; width: 455px; height:10px; margin: 6px 0 0 140px; display: none;">
                    <div class="progress-bar progress-bar-success"></div>
                </div>
                
                <div class="gbeizhu">
                    <label for="beizhu">备注</label>
                    <textarea class="pay_beizhu" name="" id="pay_beizhu" cols="30" rows="10" placeholder="备注信息" v-model="Cnote"></textarea>
                </div>
                <input type="button" v-model="btnName" :disabled="btnDisabled" style="margin-top: 30px" @click="csubmit">
                <div class="pgy_error">@{{errName}}</div>

            </div>
        </div>
        
    </div>
    <div class="pgy_alert">
        <div class="pgy_closes" @click="pgy_closes"></div>
        <div class="pgy_articleTips"></div>
    </div>
    
</div>
@endsection

@section("script")
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/js/jquery.ui.widget.js"></script>
    <script src="/js/jquery.iframe-transport.js"></script>
    <script src="/js/jquery.fileupload.js"></script>
    <script type="text/javascript">
    $(function(){

        var app=new Vue({
            el:"#channelEditUser",
            data:{
                detail_id:'',
                Cid:"",
                CompanyName:"",
                Caddress:"",
                Cwebsite:"",
                Cname:"",
                Ctel:"",
                Cdescribe:"",
                Cnote:"",
                errName:"",
                imagePath:"",
                picPath:[],
                filePath:[],
                videoPath:[],
                path:"",
                showPaths:false,
                showPicPaths:false,
                qualificationPlaceHolder:"请使用JPG/JPEG/GIF/PNG图片格式上传，大小不超过5M。",
                picPlaceHolder:"请使用JPG/JPEG/GIF/PNG图片格式上传，大小不超过5M。",
                filePlaceHolder:"请使用TXT/DOCX/DOC/XLSX/XLS文档格式上传，大小不超过10M。",
                videoPlaceHolder:"请使用MP4/SWF/FLV/AVI/RMVB/RM/MPG视频格式上传，大小不超过50M。",
                xImg:false,
                review_remark:"",
                nowCompanyName:"",
                review_status:"",
                btnName:"重新提交",
                btnDisabled:false
            },
            created:function(){
                var _this=this;
                _this.detail_id=_this.getQueryVariable('id');
                if(_this.detail_id){
                    _this.getDetail();
                }
            },
            mounted:function(){
                var _this=this;
                
                var url = "{{ route('s_user_new_uploadZip') }}";

                $('#qualification').fileupload({
                    url: url,
                    formData: {type:1},
                    dataType: 'json',
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    done: function (e, data) {
                        if (data._response.result.status==200) {
                            _this.xImg=true;
                            _this.imagePath=data._response.result.data.url;
                        } else if(data._response.result.status==400) {
                            _this.qualificationPlaceHolder="<span style='color:red;'>请使用JPG/JPEG/GIF/PNG图片格式上传，大小不超过5M。</span>";
                            setTimeout(function(){
                                _this.qualificationPlaceHolder="请使用JPG/JPEG/GIF/PNG图片格式上传，大小不超过5M。";
                            }, 3000);
                        }
                        $.each(data.result.files, function (index, file) {
                            $('<p/>').text(file.name).appendTo('#files');

                        });
                    },
                    progressall: function (e, data) {
                        //console.log(e,data);
                        $('#qualificationPlaceHolderProgress').show();
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#qualificationPlaceHolderProgress .progress-bar').css(
                            'width',
                            progress + '%'
                        );
                        if(progress==100){
                            setTimeout(function(){
                                $('#qualificationPlaceHolderProgress').hide();
                            }, 2000);
                        }
                        //console.log(progress);
                    }
                }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');


                $('#pic').fileupload({
                    url: url,
                    dataType: 'json',
                    formData: {type:1},
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    done: function (e, data) {
                        if (data._response.result.status==200) {
                            _this.picPath.push(data._response.result.data.url);
                            _this.picPlaceHolder="请使用JPG/JPEG/GIF/PNG图片格式上传，大小不超过5M。";
                        } else if(data._response.result.status==400) {
                            _this.picPlaceHolder="<span style='color:red;'>请使用JPG/JPEG/GIF/PNG图片格式上传，大小不超过5M。</span>";
                            setTimeout(function(){
                                _this.picPlaceHolder="请使用JPG/JPEG/GIF/PNG图片格式上传，大小不超过5M。";
                            }, 3000);
                        }
                        $.each(data.result.files, function (index, file) {
                            $('<p/>').text(file.name).appendTo('#files');

                        });
                    },
                    progressall: function (e, data) {
                        //console.log(e,data);
                        $('#picProgress').show();
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#picProgress .progress-bar').css(
                            'width',
                            progress + '%'
                        );
                        if(progress==100){
                            setTimeout(function(){
                                $('#picProgress').hide();
                            }, 2000);
                        }
                        //console.log(progress);
                    }
                }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');


                $('#fileupload').fileupload({
                    url: url,
                    dataType: 'json',
                    formData: {type:0},
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    done: function (e, data) {
                        if (data._response.result.status==200) {
                            _this.filePath.push(data._response.result.data.url);
                            _this.filePlaceHolder="请使用TXT/DOCX/DOC/XLSX/XLS文档格式上传，大小不超过10M。";
                        } else if(data._response.result.status==400) {
                            _this.filePlaceHolder="<span style='color:red;'>请使用TXT/DOCX/DOC/XLSX/XLS文档格式上传，大小不超过10M。</span>";
                            setTimeout(function(){
                                _this.filePlaceHolder="请使用TXT/DOCX/DOC/XLSX/XLS文档格式上传，大小不超过10M。";
                            }, 3000);
                        }
                        $.each(data.result.files, function (index, file) {
                            $('<p/>').text(file.name).appendTo('#files');

                        });
                    },
                    progressall: function (e, data) {
                        //console.log(e,data);
                        $('#fileProgress').show();
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#fileProgress .progress-bar').css(
                            'width',
                            progress + '%'
                        );
                        if(progress==100){
                            setTimeout(function(){
                                $('#fileProgress').hide();
                            }, 2000);
                        }
                        //console.log(progress);
                    }
                }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');

                $('#videoupload').fileupload({
                    url: url,
                    formData: {type:2},
                    dataType: 'json',
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    done: function (e, data) {
                        if (data._response.result.status==200) {
                            _this.videoPath.push(data._response.result.data.url);
                            _this.videoPlaceHolder="请使用MP4/SWF/FLV/AVI/RMVB/RM/MPG视频格式上传，大小不超过50M。";
                        } else if(data._response.result.status==400) {
                            _this.videoPlaceHolder="<span style='color:red;'>请使用MP4/SWF/FLV/AVI/RMVB/RM/MPG视频格式上传，大小不超过50M。</span>";
                            setTimeout(function(){
                                _this.videoPlaceHolder="请使用MP4/SWF/FLV/AVI/RMVB/RM/MPG视频格式上传，大小不超过50M。";
                            }, 3000);
                        }
                        $.each(data.result.files, function (index, file) {
                            $('<p/>').text(file.name).appendTo('#files');

                        });
                    },
                    progressall: function (e, data) {
                        //console.log(e,data);
                        $('#videoProgress').show();
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#videoProgress .progress-bar').css(
                            'width',
                            progress + '%'
                        );
                        if(progress==100){
                            setTimeout(function(){
                                $('#videoProgress').hide();
                            }, 2000);
                        }
                        //console.log(progress);
                    }
                }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');
                
                this.$nextTick(function() {
                    document.getElementById("channelEditUser").style.display = "block";
                });
            },
            methods:{
                getQueryVariable:function(variable){
                    var query = window.location.search.substring(1);
                    var vars = query.split("&");
                    for (var i=0;i<vars.length;i++) {
                            var pair = vars[i].split("=");
                            if(pair[0] == variable){return pair[1];}
                    }
                    return("");
                },
                getDetail:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_user_getAdvertRelativeDetail')}}',
                        type:'POST',
                        async:true,
                        data:{
                            id:_this.detail_id
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                //console.log(res.data)
                                _this.Cid=res.data.id;
                                _this.CompanyName=res.data.advert_company_name;
                                _this.nowCompanyName=res.data.advert_company_name;
                                _this.Caddress=res.data.advert_company_address;
                                _this.Cwebsite=res.data.advert_company_website;
                                _this.Cname=res.data.advert_company_head;
                                _this.Ctel=res.data.advert_company_tel;
                                _this.Cdescribe=res.data.advert_company_describe;
                                _this.Cnote=res.data.advert_company_note;
                                _this.review_remark=res.data.review_remark?"审核失败："+res.data.review_remark:"";
                                if(res.data.advert_company_img){
                                    _this.xImg=true;
                                    _this.imagePath=res.data.advert_company_img;
                                }
                                _this.picPath=res.data.attachmentArr.image;
                                _this.filePath=res.data.attachmentArr.text;
                               _this.videoPath=res.data.attachmentArr.video;
                               _this.review_status=res.data.review_status;
                            }else{
                                this.errName=res.message;
                                setTimeout(function(){
                                    _this.errName="";
                                }, 3000);
                            }
                        }
                    });
                },
                picDelete:function(todo){
                    let index = this.picPath.indexOf(todo);
                    this.picPath.splice( index , 1 );
                },
                fileDelete:function(todo){
                    let index = this.filePath.indexOf(todo);
                    this.filePath.splice( index , 1 );
                },
                videoDelete:function(todo){
                    let index = this.videoPath.indexOf(todo);
                    this.videoPath.splice( index , 1 );
                },
                pgy_closes:function(){
                    $('.pgy_alert').hide();
                },
                csubmit:function(){
                    var _this=this;
                    if (!_this.CompanyName){
                        this.errName="公司名称不能为空";
                        setTimeout(function(){
                            _this.errName="";
                        }, 3000);
                        return;
                    }else if( _this.Ctel && (/^1[34578]\d{9}$/.test(_this.Ctel)) ){
                        $.ajax({
                            url:'{{route('s_user_editAdvertRelativeDetail')}}',
                            type:'POST',
                            async:true,
                            data:{
                                id:_this.Cid,
                                name:_this.CompanyName,
                                address:_this.Caddress,
                                website:_this.Cwebsite,
                                head:_this.Cname,
                                describe:_this.Cdescribe,
                                tel:_this.Ctel,
                                note:_this.Cnote,
                                img:_this.imagePath,
                                zip:{
                                    "image":_this.picPath.join(","),
                                    "text":_this.filePath.join(","),
                                    "video":_this.videoPath.join(",")
                                }
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            beforeSend: function () {
                                _this.btnName="提交中...";
                                _this.btnDisabled=true;
                            },
                            success:function(res){
                                if(res.status==200) {
                                    setTimeout(function(){
                                        window.location.href="{{route('s_user_channelList')}}"
                                    }, 3000);
                                }else{
                                    this.errName=res.message;
                                    setTimeout(function(){
                                        _this.errName="";
                                    }, 3000);
                                    _this.btnName="重新提交";
                                    _this.btnDisabled=false;
                                }
                            }
                        });
                    }else{
                        this.errName="负责人手机号格式错误";
                        setTimeout(function(){
                            _this.errName="";

                        }, 3000);
                    }

                },
                companyName:function(){
                    var _this=this;
                    if(_this.CompanyName && _this.nowCompanyName === _this.CompanyName){
                        return;
                    }

                    if ( _this.CompanyName ) {
                        $.ajax({
                            url:'{{route('s_user_addCheckDetail')}}',
                            type:'POST',
                            async:true,
                            data:{
                                name:_this.CompanyName
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status!=200) {
                                    _this.errName=res.message;
                                    setTimeout(function(){
                                        _this.errName="";
                                    }, 3000);

                                }else{
                                    this.errName="";
                                }
                            }
                        });
                        return true;
                    } else {
                        this.errName="公司名称不能为空";
                        setTimeout(function(){
                            _this.errName="";

                        }, 3000);
                        return false;
                    }
                },
                GetFileUpload:function(event){
                    var _this=this;
                    var formData = new FormData();
                    var file = event.target.files[0];
                    var fileType=file.name.split(".")[1];
                    if (fileType=='jpeg'&&fileType=='jpg'&&fileType=='png') {
                        $.ajax({
                            url:'{{route('s_user_uploadImg')}}',
                            type:'POST',
                            async:false,
                            processData:false,
                            contentType:false,
                            data: formData,
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    _this.xImg=true;
                                    _this.imagePath=res.image;
                                }
                            }
                        });
                        //console.log('success');
                    } else {
                        //console.log('error')
                    }
                    formData.append('images',file);
                },
                cImg:function(){
                    var _this=this;
                    _this.xImg=false;

                },
                fileText:function(event){
                    var _this=this;
                    var formData = new FormData();
                    var file = event.target.files[0];
                    formData.append('zip',file);
                    $.ajax({
                        url:'{{route('s_user_uploadZip')}}',
                        timeout : 1000000,
                        type:'POST',
                        async:false,
                        processData:false,
                        contentType:false,
                        data: formData,
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        beforeSend:function(){
                            $(".pgy_alert").show();
                            $(".pgy_articleTips").html("文件上传中…");
                        },
                        success:function(res){
                            if(res.status==200) {
                                setTimeout(function() {
                                    $(".pgy_alert").hide();
                                }, 2000);
                                _this.path=res.image;
                                _this.placeHolder="";
                                _this.showPaths=true;
                            }
                        }
                    });

                }

            }
        })
    })
    </script>
@endsection

