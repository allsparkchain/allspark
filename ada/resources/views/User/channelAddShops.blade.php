@extends('User.layout')


@section("title", "添加商品")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
    <div id="channelAddShops" style="display:none">
    <div class="zmtdl_admin_crumbs">
        广告主数据 > 商品列表 > <span class="on">添加商品</span>
        <a href="javascript:window.history.go(-1);">返回</a>
    </div>
        <div class="addCentuser">
            <form class="form form-horizontal" id="form-product-add"  enctype="multipart/form-data" method="post" action="{{route('s_user_uploadZip')}}">
                {{ csrf_field() }}
                <div style=" width:594px; margin:0 auto;padding-top: 71px;">
                    <!--商品描述-->
                    <div class="gcommdity" style=" margin: 0;">
                        <label for="commdity">商品描述</label>
                        <textarea class="pay_commdity" name="" id="pay_commdity" cols="30" rows="10" placeholder="商品描述" v-model="Cdescribe"></textarea>
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
        $(function () {
            var app = new Vue({
                el: '#channelAddShops',
                data: {
                    Cdescribe:"",
                    errName:"",
                    imagePath:"",
                    picPath:[],
                    filePath:[],
                    videoPath:[],
                    path:"",
                    showPaths:false,
                    showPicPaths:false,
                    picPlaceHolder:"请使用JPG/JPEG/GIF/PNG图片格式上传，大小不超过5M。",
                    filePlaceHolder:"请使用TXT/DOCX/DOC/XLSX/XLS文档格式上传，大小不超过10M。",
                    videoPlaceHolder:"请使用MP4/SWF/FLV/AVI/RMVB/RM/MPG视频格式上传，大小不超过50M。",
                    xImg:false,
                    detail_id:"",
                    btnName:"提交",
                    btnDisabled:false
                },
                created:function(){
                    var _this=this;
                    _this.detail_id=_this.getQueryVariable('id');
                },
                mounted:function(){
                    var _this=this;
                    var url = "{{ route('s_user_new_uploadZip') }}";

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
                            console.log(e,data);
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
                            console.log(progress);
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
                            console.log(e,data);
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
                            console.log(progress);
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
                            console.log(e,data);
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
                            console.log(progress);
                        }
                    }).prop('disabled', !$.support.fileInput)
                        .parent().addClass($.support.fileInput ? undefined : 'disabled');

                    this.$nextTick(function() {
                        document.getElementById("channelAddShops").style.display = "block";
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
                        if(!_this.Cdescribe){
                            this.errName="商品描述不能为空";
                            setTimeout(function(){
                                _this.errName="";
                            }, 3000);
                            return;
                        }else if(_this.picPath.length!==0 || _this.filePath.length!==0 || _this.videoPath.length!==0) {
                            $.ajax({
                                url:'{{route('s_user_appendAdvertRelativeAttachment')}}',
                                type:'POST',
                                async:true,
                                data:{
                                    detail_id:_this.detail_id,
                                    describe:_this.Cdescribe,
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
                                            //history.back();
                                            window.location.href=document.referrer;
                                        }, 3000);
                                    }else{
                                        this.errName=res.message;
                                        setTimeout(function(){
                                            _this.errName="";
                                        }, 3000);
                                        _this.btnName="提交";
                                        _this.btnDisabled=false;
                                    }
                                }
                            });
                        }else{
                            this.errName="物料不能为空";
                            setTimeout(function(){
                                _this.errName="";
                            }, 3000);
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
                            console.log('success');
                        } else {
                            console.log('error')
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
        });

    </script>
@endsection


