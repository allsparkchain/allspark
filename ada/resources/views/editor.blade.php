<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>创作编辑器 - 蒲公英</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=1400">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
    <link rel="stylesheet" href="{{ mix('css/center.css') }}">
    <link rel="stylesheet" href="{{ mix('css/pgy2.css') }}">
    <link rel="stylesheet" href="/css/cropper3.css">
    <style>html,body{ height: 100%;}</style>
    <script type="text/javascript" src="/js/vue.js"></script>
    <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/cropper3.js" type="text/javascript"></script>
    <script src="/js/ueditor.config.js" type="text/javascript" charset="UTF-8"></script>
    <script src="/js/ueditor.all.js" type="text/javascript" charset="UTF-8"></script>
</head>
<body>
<div id="editor" style=" height: 100%;">
    <div class="pgy-editor-left">
        <div class="pgy-editor-left-options">
            商品信息
        </div>
        <div class="pgy-editor-left-info">
            <div class="pgy-pro-intro">
                <div class="pgy-pro-title">
                    <span></span>产品简介
                </div>
                <p>@{{synopsis}}</p>
            </div>

            <div class="pgy-pro-intro">
                <div class="pgy-pro-title">
                    <span></span>产品详情
                </div>
                <div class="pgy-pro-detail" v-html="contents">
                </div>
            </div>

        </div>
    </div>

    <div class="pgy-editor-right">
       
        <div class="pgy-editor-nav">
            <ul class="pgy-editor-ul">
                <li @click="toPreview"><i class="pgy-editor-i preview"></i>预览全文</li>
                <li @click="toSave"><i class="pgy-editor-i save"></i>保存草稿</li>
                <!-- <li @click="toRelease"><i class="pgy-editor-i issue"></i>文章发布</li> -->
                <li @click="startWriting"><i class="pgy-editor-i issue"></i>文章发布</li>
                
            </ul>
            <div class="pgy-word">
                <form id="wordform">
                    <input id="File1" type="file" accept=".docx,.doc" @change="importWord" />
                </form>
                <i class="pgy-editor-i word"></i>导入word文档
            </div>
            <a class="pgy-editor-drafts" href="/User/articleList" target="_blank">前往草稿箱</a>
            <a class="pgy-editor-back" href="javascript:window.history.back();">返回</a>
        </div>
        
        <div class="pgy-editor-title">
            <input type="text" placeholder="请在这里输入标题" v-model="name">
        </div>
        <div class="pgy-editor-summary" style=" position: relative;">
            <label style=" font-size: 16px; position: absolute; top: 23px;">简介：</label>
            <input type="text" placeholder="请输入文章简介，20到80个汉字，文章简介不会出现在正文。" maxlength="80" v-model="summary">
        </div>
        <script id="container" name="content" style="width: 100%; height: 600px;" type="text/plain"></script>
        
        <div class="pgy-editor-upload">
            <div class="pgy-editor-head">上传封面</div>
            <div class="pgy-editor-cover">
                <div class="pgy-editor-coverimg">
                    <div class="pgy-editor-cover-uploadImg" v-show="!imgBase64" @click="uploadCover">
                    +
                    </div>
                    <div class="pgy-editor-cover-chooseImg" v-show="imgBase64">
                        <img :src="imgBase64">
                        <div class="pgy-editor-cover-chooseImg-layout">
                            <i class="pgy-editor-cover-choose-icon modify" @click="uploadCover"></i>
                            <i class="pgy-editor-cover-choose-icon del" @click="uploadDel"></i>
                        </div>
                    </div>
                </div>
                <div class="pgy-editor-covertxt">
                    图片建议选择高清大图<br>
                    支持jpg、jpeg、png。bmp、gif等格式
                </div>
            </div>
        </div>

    </div>

        <!-- 弹框 -->
        <div class="ggzdl_popup_wrap" v-show="popupFlag" style=" z-index: 3000;">
            <div class="ggzdl_popup_box popup_box_renz" v-show="popupDiv==='goToRenzheng'">
                <span class="popup_close" @click="closePopup"></span>
                <div class="popup_title">实名认证</div> 
                <div class="popup_con">
                    <p class="renz_msg">简单认证，是为了确保账户收益安全哦！</p>
                    <div class="rez_text" v-if="mobileStatus">@{{realData.mobile|mobileFormat}}</div>
                    <div class="renz_input" v-else>
                        <i class="pay_account_mobile"></i>
                        <input type="text" placeholder="手机号" v-model="realData.mobile" @blur="checkMobile" maxlength="11">
                        <span class="error_msg" v-html="realData.errMobile">@{{realData.errMobile}}</span>
                    </div>
                    <div class="renz_code">
                        <input class="renz_code_input" type="text" placeholder="输入验证码" v-model="realData.code" maxlength="4" @blur="checkCode">
                        <input class="renz_code_btn" type="button" value="发送验证码" :disabled="realData.disabled" v-model="realData.validCode" @click="sendRenzmsg">
                        <span class="error_msg" v-html="realData.errMsg">@{{realData.errMsg}}</span>
                    </div>
                    <div class="renz_input">
                        <i class="pay_account_name"></i>
                        <input type="text" placeholder="姓名" v-model="realData.realname" @blur="checkName">
                        <span class="error_msg" v-html="realData.errName">@{{realData.errName}}</span>
                    </div>
                    <div class="renz_input">
                        <i class="pay_account_id"></i>
                        <input type="text" placeholder="身份证号码" v-model="realData.idCard" @blur="checkIdentity">
                        <span class="error_msg" v-html="realData.errIdentity">@{{realData.errIdentity}}</span>
                    </div>
                    <div class="popup_btn" @click="renzSubmit">认证</div>
                </div>
            </div>
            <div class="ggzdl_popup_box" v-show="popupDiv==='renZSuccess'">
                <span class="popup_close" @click="closePopup"></span>
                <div class="popup_title">实名认证</div> 
                <div class="popup_renresult">
                    <img src="/images/renz_suc.png" alt="">
                    <h3>恭喜您认证成功</h3>
                </div>
                <div class="popup_btn" @click="towithdraw">继续创作 （@{{realData.renzTime}}S）</div>
            </div>
            <div class="ggzdl_popup_box" v-show="popupDiv==='renZFail'" style="height:380px;margin-top:-190px;">
                <span class="popup_close" @click="closePopup"></span>
                <div class="popup_title">实名认证</div> 
                <div class="popup_renresult">
                    <img src="/images/renz_fail.png" alt="">
                    <h3>身份信息匹配失败</h3>
                    <p>若确认身份无误，可以联系客服邮箱</p>
                    <p><a href="mailto:hi@pugongying.link">hi@pugongying.link</a>工作人员核实后帮您处理</p>
                </div>
                <div class="popup_btn" style="margin-top:30px;" @click="closePopup;showPopupDiv('goToRenzheng')">重新输入</div>
            </div>

        </div>


    <div class="ggzdl_popup_wrap" style="z-index: 3000"  v-show="layout">

        <!--保存草稿-->
        <div class="ggzdl_popup_box" v-show="saveStatus">
            <span class="popup_close" @click="saveClose"></span>
            <div class="popup_title">保存草稿</div> 
            <div class="popup_renresult">
                <img width="50" height="50" src="/images/zmtdl_success.png" alt="">
                <h3>已保存到草稿箱</h3>
                <p>可在账户中心-内容助手-草稿箱查看</p>
            </div>
            <div class="popup_btn" @click="saveClose">继续创作 （@{{times}}S）</div>
        </div>

        <!--文章发布-->
        <div class="ggzdl_popup_box" style=" margin-top: -190px; height: 380px;" v-show="articleStatus">
            <span class="popup_close" @click="articleClose"></span>
            <div class="popup_title">提交成功，文章进入审核中</div> 
            <div class="popup_renresult">
                <img width="120" height="120" src="/images/pgy_footer_img2.png" alt="">
                <p style=" font-size: 14px; color: #7e7e7e;">关注公众号可及时获知审核进度</p>
                <p>你还可以<a href="/Goods/lists" target="_blank" style=" color: #ff7841;">继续创作</a></p>
                <p style=" margin: 50px 0 0 0;">你还可以在账户中-<a href="/User/articleList" target="_blank" style=" color: #ff7841;">内容助手</a>查看文章状态</p>
            </div>
        </div>
        
        <!--提示消息-->
        <div class="pgy-toast" v-show="toastShow">@{{errMsg}}</div>

        <!--提示窗口-->
        <div class="ggzdl_popup_box" v-show="coverImgStatus">
            <span class="popup_close" @click="errMsgClose"></span>
            <div class="popup_title">提示</div> 
            <div class="popup_successed">
                <p style=" text-align: left;">@{{errMsg}}</p>
            </div>
            <div class="popup_btn" @click="errMsgClose">确认</div>
        </div>

        <!-- 封面列表 -->
        <div class="pgy-editor-cover-wrap" v-show="coverListStatus">
            <div class="pgy-editor-cover-wrap-head">
                请先上传封面<i class="pgy-editor-wrap-close" @click="uploadCoverClose"></i>
            </div>
            <div class="pgy-editor-wrap-ul-wrap">
                <ul class="pgy-editor-wrap-ul">
                    <li :class="{'on':index==itemIndex}" v-for="(item,index) in imgCover">
                        <img :src="item" @click="chooseImg(item,index)">
                    </li>
                </ul>
            </div>
            <div class="pgy-editor-wrap-next" @click="uploadCoverNext">下一步</div>
        </div>

        <!--封面剪裁-->
        <div class="pgy-editor-crop-wrap" v-show="coverCropStatus">
            <div class="pgy-editor-crop-head">
                封面裁剪<i class="pgy-editor-crop-close" @click="uploadCropClose"></i>
            </div>
            <div class="img-container">
                <img id="image" :src="imgSelectedSrc" alt="Picture">
            </div>
            <div class="docs-preview clearfix">
                <div class="img-preview preview-lg"></div>
                <div class="pgy-editor-crop-btn enter" @click="uploadCropConfirm">确定</div>
                <div class="pgy-editor-crop-btn reselect off" @click="uploadCropLastStep">重新选择</div>
            </div>

        </div>

    </div>
    
</div>
<script>

    Vue.filter("mobileFormat",function(value){
        if(value){
            return value.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2');
        }else{
            return value;
        }
    });
    var app=new Vue({
        el:"#editor",
        data:{
            article_id:null,
            times:3,
            Category_ids:0,
            product_id:{{$res['id']}}, //产品id
            saveStatus:false, //保存状态
            articleStatus:false, //文章状态
            errMsg:"", //错误信息
            toastShow:false, //错误信息状态
            synopsis:"", //商品简介
            contents:"", //商品详情
            editor:null,
            name:"",
            summary:"",
            content:"",
            id:"",
            imgCount:0,
            imgCover:[],
            itemIndex:null,
            imgSelectedSrc:null,
            imgBaseTmp:null,
            imgBase64:null,
            layout:false,
            coverImgStatus:false,
            coverListStatus:false,
            coverCropStatus:false,
            cropper:null,
            popupFlag:false,
            popupDiv:"",
            realData: {
                isBind:0,//是否实名
                countStart:"",
                validCode:"发送验证码",
                disabled:false,
                times:60,
                errMobile:"",
                errMsg:"",
                errCode:"",
                errName:"",
                errIdentity:"",
                mobile:"",
                code:"",
                realname:"",
                idCard:"",
                renzTime:3
            },
            mobileStatus:false,
            isLogin:false,
            editorClose:true //浏览器关闭状态
        },
        created:function(){
            this.getMobileStatus();
        },
        mounted:function(){

            // window.addEventListener("beforeunload", function(event) {
            //     if(){

            //     }else{

            //     }
            //     //event.returnValue = "我在这写点东西...";
            // });


            var ue=null;
            var _this=this;
            
            UE.delEditor('container');   //先删除之前实例的对象
            this.editor = UE.getEditor('container',{
                toolbars:[['undo','redo', 'bold', 'italic', 'underline', 'backcolor', 'fontsize', 'simpleupload',
                    'justifyleft','justifycenter', 'justifyright', 'justifyjustify', 'strikethrough', 'removeformat', 'formatmatch', 'pasteplain',
                    'forecolor', 'insertorderedlist', 'insertunorderedlist', 'link', 'unlink','insertframe']],
                    'filterTxtRules' : function(){
                        function transP(node){
                            node.tagName = 'p';
                            node.setStyle();
                        }
                        return {
                            //直接删除及其字节点内容
                            '-' : 'script style object embed input select',
                            'p': {$:{}},
                            'br':{$:{}},
                            'div':"",
                            'section':"",
                            'li':{'$':{}},
                            'caption':transP,
                            'th':transP,
                            'tr':transP,
                            'h1':transP,'h2':transP,'h3':transP,'h4':transP,'h5':transP,'h6':transP,
                            'td':function(node){
                                //没有内容的td直接删掉
                                var txt = !!node.innerText();
                                if(txt){
                                    node.parentNode.insertAfter(UE.uNode.createText(' &nbsp; &nbsp;'),node);
                                }
                                node.parentNode.removeChild(node,node.innerText())
                            }
                        }
                    }()
            });
            ue=this.editor;
            

            this.editor.ready(function(){

                ue.addListener("keydown",function(type,e){
                    var currKey=0, e=e||event||window.event;  
                    currKey = e.keyCode||e.which||e.charCode;  
                    if(currKey == 83 && (e.ctrlKey||e.metaKey)){
                        e.preventDefault(); 
                        console.log("save");
                        _this.toSave();
                        return false;  
                    }
                })

                ue.addListener("catchremotesuccess",function(){
                    console.log("img upload success");
                });
            });

            //获取商品详情
            this.getGoodsDetailData();

            if(this.getQueryVariable("article_id")){
                this.article_id=this.getQueryVariable("article_id");
                this.getArticle();
                this.editor.ready(function(){
                    
                    setTimeout(function(){
                        ue.execCommand('insertHtml', _this.content);
                    },500);
                });
            }

            window.addEventListener("beforeunload", function(event) {
                if(_this.editorClose){
                    event.returnValue = "我在这写点东西...";
                }else{
                    console.log(2)
                }
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
                return(false);
            },
            importWord:function(e){
                var _this=this;
                var file = document.getElementById('File1');
                var formData =new FormData();
                formData.append('upload',e.target.files[0]);
                $.ajax({
                    url:'{{route('s_ueditor_word2html')}}',
                    type:'POST',
                    async:true,
                    data:formData,
                    processData: false,  // 告诉jQuery不要去处理发送的数据
                    contentType: false,
                    beforeSend: function(){
                        _this.layout=true;
                        _this.errMsg="word文件上传中";
                        _this.toastShow=true;
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            _this.editor.ready(function(){
                                UE.getEditor('container').execCommand('insertHtml', res.data)
                            });
                            _this.layout=false;
                            _this.errMsg="";
                            _this.toastShow=false;
                        }else{
                            _this.layout=true;
                            _this.errMsg=res.message;
                            _this.toastShow=true;
                            setTimeout(function(){
                                _this.layout=false;
                                _this.errMsg="";
                                _this.toastShow=false;                       
                            }, 3000);
                        }
                        file.value="";
                    }
                });
            },
            uploadCover:function(){
                var _this=this;
                this.itemIndex=null;
                this.content=this.editor.getContent();
                this.imgCover=[];
                var imgReg = /<img.*?(?:>|\/>)/gi;
                //匹配src属性
                var srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i;
                var arr = this.content.match(imgReg);
                if(!arr){
                    this.layout=true;
                    this.errMsg="封面只能从正文配图中选取。您的正文中无可用图片，请先为文章配图。";
                    this.coverImgStatus=true;
                    return false;
                }
                for (var i = 0; i < arr.length; i++) {
                    var src = arr[i].match(srcReg);
                    //获取图片地址
                    if(src[1]){
                        this.imgCover.push(src[1]);
                        //alert('已匹配的图片地址'+(i+1)+'：'+src[1]);
                    }
                }
                //console.log(this.imgCover);
                console.log(this.article_id);
                $.ajax({
                    url:'{{route('s_ueditor_imgageGet')}}',
                    type:'POST',
                    async:true,
                    data:{
                        action:"catchimage",
                        source:_this.imgCover
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.state=="SUCCESS") {
                            if(res.list[0].url){
                                _this.imgCover=[];
                                res.list.forEach(function(v,i,a){
                                    _this.imgCover.push(v.url);
                                });
                            }else{

                            }

                        }
                        console.log(_this.imgCover);
                    }
                });

                this.layout=true;
                this.coverListStatus=true;
            },
            chooseImg:function(src,i){
                var _this=this;
                this.itemIndex=i;
                this.imgSelectedSrc=src;

                if(this.cropper){
                    this.cropper.replace(this.imgSelectedSrc);
                }
                
                console.log(this.imgSelectedSrc);
            },
            uploadCoverNext:function(){
                var _this=this;
                if (this.imgSelectedSrc) {
                    this.coverListStatus=false;
                    this.coverCropStatus=true;
                    this.layout=true;

                    if(!this.cropper){
                        var image = document.getElementById('image');
                        var options = {
                            aspectRatio: 305 / 232,
                            cropBoxResizable:false,
                            preview: '.img-preview',
                            ready: function (e) {
                            console.log(e.type);
                            },
                            cropstart: function (e) {
                            console.log(e.type, e.detail.action);
                            },
                            cropmove: function (e) {
                            console.log(e.type, e.detail.action);
                            },
                            cropend: function (e) {
                            console.log(e.type, e.detail.action);
                            },
                            crop: function (e) {
                            var data = e.detail;

                            console.log(e.type);

                            },
                            zoom: function (e) {
                            console.log(e.type, e.detail.ratio);
                            }
                        };
                        this.cropper = new Cropper(image, options);
                    }

                } else {
                    alert("请选择封面图片");
                }
            },
            uploadCoverClose:function(){
                this.layout=false;
                this.coverListStatus=false;
            },
            uploadCropConfirm:function(){
                var _this=this;
                this.layout=false;
                //var croppedCanvas;
                var result = this.cropper.getCroppedCanvas({
                    width: 458,
                    height: 350,
                    minWidth: 458,
                    minHeight: 350,
                    maxWidth: 458,
                    maxHeight: 350,
                    fillColor: '#fff',
                    imageSmoothingEnabled: false,
                    imageSmoothingQuality: 'high'
                });
                this.imgBaseTmp=result.toDataURL("image/png");
                this.imgBase64=this.imgBaseTmp;

                $.ajax({
                    url:'{{route('s_ueditor_imgageGet')}}',
                    type:'POST',
                    async:true,
                    data:{
                        action:"uploadbase64image",
                        upfile:_this.imgBaseTmp
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.state=="SUCCESS") {
                            _this.imgBase64=res.url;
                        }

                    }
                });


                this.coverCropStatus=false;
                this.cropper.destroy();
                this.cropper=null;
            },
            //提示窗口关闭
            errMsgClose:function(){
                this.layout=false;
                this.errMsg="";
                this.coverImgStatus=false;
            },
            //封面裁剪上一步
            uploadCropLastStep:function(){
                this.itemIndex=null;
                this.layout=true;
                this.coverListStatus=true;
                this.coverCropStatus=false;
                this.cropper.destroy();
                this.cropper=null;
            },
            //封面裁剪关闭
            uploadCropClose:function(){
                this.layout=false;
                this.coverCropStatus=false;
                this.cropper.destroy();
                this.cropper=null;
            },
            uploadDel:function(){
                this.imgBase64=null;
            },
            getQueryString:function (name) {//获取url参数
                var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
                var r = window.location.search.substr(1).match(reg);
                if (r != null) return unescape(r[2]); return null;
            },
            //获取商品详情页数据
            getGoodsDetailData:function(){
                var _this=this;
                $.ajax({
                    url:'{{route('s_goods_detailData')}}',
                    type:'GET',
                    async:true,
                    data:{
                        id:_this.product_id
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            //console.log(res.data)
                            _this.synopsis=res.data.synopsis;
                            _this.contents=res.data.contents;
                            _this.$nextTick(function(){
                                _this.editor.ready(function(){
                                    $(".pgy-pro-detail img").on("click",function(){
                                        UE.getEditor('container').execCommand('insertHtml', '<img src="'+$(this)[0].src+'">')
                                    });
                                });

                            });
                        }
                    }
                });
            },
            //发布文章
            toRelease:function(){
                var _this=this;
                this.content=this.editor.getContent();
                var c = "img"; // 要计算的字符
                var regex = new RegExp(c, 'g'); // 使用g表示整个字符串都要匹配
                var result = this.content.match(regex);
                this.imgCount = !result ? 0 : result.length;
                
                if( !this.name ) {
                    window.scrollTo(0,0);
                    this.layout=true;
                    this.errMsg="请输入标题";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);

                    return false; 
                }
                if( !this.content ) { 
                    this.layout=true;
                    this.errMsg="请输入文章内容";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);

                    return false;
                 }
                if( !this.imgCount>=1 ) {
                    this.layout=true;
                    this.errMsg="必须上传一张图片";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);
                    return false; 
                }
                // if(!this.summary){
                //     window.scrollTo(0,0);
                //     this.layout=true;
                //     this.errMsg="请输入摘要内容";
                //     this.toastShow=true;

                //     setTimeout(function(){
                //         _this.layout=false;
                //         _this.errMsg="";
                //         _this.toastShow=false;                       
                //     }, 2000);
                //     return false;                     
                // }else{
                //     if(this.summary.length<20||this.summary.length>80){
                //         window.scrollTo(0,0);
                //         this.layout=true;
                //         this.errMsg="摘要字数需在20字到80字之间";
                //         this.toastShow=true;

                //         setTimeout(function(){
                //             _this.layout=false;
                //             _this.errMsg="";
                //             _this.toastShow=false;                       
                //         }, 2000);
                //         return false;
                //     }
                // }
                if( !this.imgBase64 ){
                    this.uploadCover();
                    return false; 
                }
                $.ajax({
                    url:'{{route('s_article_add')}}',
                    type:'POST',
                    async:true,
                    data:{
                        name:_this.name,
                        content:_this.content,
                        product_id:_this.product_id,
                        article_category_id:_this.Category_ids,
                        status:1,
                        id:_this.id,
                        summary:_this.summary,
                        front_cover:_this.imgBase64
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    beforeSend: function(){
                    // Handle the beforeSend event
                        _this.layout=true;
                        _this.errMsg="文章发布中…";
                        _this.toastShow=true;
                    },
                    success:function(res){
                        if(res.status==200) {
                            _this.layout=true;
                            _this.articleStatus=true;
                            _this.toastShow=false;
                            _this.editorClose=false;
                            console.log("editorClose",_this.editorClose);
                            //location.href="{{route('s_goods_success')}}";
                        }else{
                            _this.layout=true;
                            _this.errMsg=res.message;
                            _this.toastShow=true;
                            setTimeout(function(){
                                _this.layout=false;
                                _this.errMsg="";
                                _this.toastShow=false;                       
                            }, 2000);
                        }
                    }
                }); 
            },
            // 关闭保存
            articleClose:function(){
                this.layout=false;
                this.articleStatus=false;
                location.href="/Article/lists";
            },
            //保存草稿
            toSave:function(){
                var _this=this;
                this.content=this.editor.getContent();
                var c = "img"; // 要计算的字符
                var regex = new RegExp(c, 'g'); // 使用g表示整个字符串都要匹配
                var result = this.content.match(regex);
                this.imgCount = !result ? 0 : result.length;
                if( !this.name ) { 
                    window.scrollTo(0,0);
                    this.layout=true;
                    this.errMsg="请输入标题";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);

                    return false; 
                }
                if( !this.content ) { 
                    this.layout=true;
                    this.errMsg="请输入文章内容";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);

                    return false;
                 }
                if( !this.imgCount>=1 ) {
                    this.layout=true;
                    this.errMsg="必须上传一张图片";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);
                    return false; 
                }
                // if(!this.summary){
                //     window.scrollTo(0,0);
                //     this.layout=true;
                //     this.errMsg="请输入摘要内容";
                //     this.toastShow=true;

                //     setTimeout(function(){
                //         _this.layout=false;
                //         _this.errMsg="";
                //         _this.toastShow=false;                       
                //     }, 2000);
                //     return false;                     
                // }else{
                //     if(this.summary.length<20||this.summary.length>80){
                //         window.scrollTo(0,0);
                //         this.layout=true;
                //         this.errMsg="摘要字数需在20字到80字之间";
                //         this.toastShow=true;

                //         setTimeout(function(){
                //             _this.layout=false;
                //             _this.errMsg="";
                //             _this.toastShow=false;                       
                //         }, 2000);
                //         return false;
                //     }
                // }
                if( !this.imgBase64 ){
                    this.uploadCover();
                    return false; 
                }

                $.ajax({
                    url:'{{route('s_article_add')}}',
                    type:'POST',
                    async:true,
                    data:{
                        name:_this.name,
                        content:_this.content,
                        product_id:_this.product_id,
                        status:2,
                        id:_this.id,
                        summary:_this.summary,
                        front_cover:_this.imgBase64
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    beforeSend: function(){
                    // Handle the beforeSend event
                        _this.layout=true;
                        _this.errMsg="文章保存中…";
                        _this.toastShow=true;
                    },
                    success:function(res){
                        if(res.status==200) {
                            _this.id=res.data;
                            _this.toastShow=false;
                            _this.layout=true;
                            _this.saveStatus=true;
                            _this.times=3;
                            _this.editorClose=false;
                            console.log("editorClose",_this.editorClose);
                            var _timer = setInterval(function() {
                                if(_this.times<=0) {
                                    window.clearInterval(_timer);  
                                    _this.saveClose();
                                }
                                _this.times--;
                                
                            }, 1000);

                        }else{

                            _this.layout=true;
                            _this.errMsg=res.message;
                            _this.toastShow=true;
                            setTimeout(function(){
                                _this.layout=false;
                                _this.errMsg="";
                                _this.toastShow=false;                       
                            }, 2000);

                        }
                    }
                });
            },
            // 关闭保存
            saveClose:function(){
                this.layout=false;
                this.saveStatus=false;
            },
            
            //预览文章
            toPreview:function(){
                var _this=this;
                this.content=this.editor.getContent();
                var c = "img"; // 要计算的字符
                var regex = new RegExp(c, 'g'); // 使用g表示整个字符串都要匹配
                var result = this.content.match(regex);
                this.imgCount = !result ? 0 : result.length;
                if( !this.name ) { 
                    window.scrollTo(0,0);
                    this.layout=true;
                    this.errMsg="请输入标题";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);

                    return false; 
                }
                if( !this.content ) { 
                    this.layout=true;
                    this.errMsg="请输入文章内容";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);

                    return false;
                 }
                if( !this.imgCount>=1 ) {
                    this.layout=true;
                    this.errMsg="必须上传一张图片";
                    this.toastShow=true;

                    setTimeout(function(){
                        _this.layout=false;
                        _this.errMsg="";
                        _this.toastShow=false;                       
                    }, 2000);
                    return false; 
                }
                // if(!this.summary){
                //     window.scrollTo(0,0);
                //     this.layout=true;
                //     this.errMsg="请输入摘要内容";
                //     this.toastShow=true;

                //     setTimeout(function(){
                //         _this.layout=false;
                //         _this.errMsg="";
                //         _this.toastShow=false;                       
                //     }, 2000);
                //     return false;                     
                // }else{
                //     if(this.summary.length<20||this.summary.length>80){
                //         window.scrollTo(0,0);
                //         this.layout=true;
                //         this.errMsg="摘要字数需在20字到80字之间";
                //         this.toastShow=true;

                //         setTimeout(function(){
                //             _this.layout=false;
                //             _this.errMsg="";
                //             _this.toastShow=false;                       
                //         }, 2000);
                //         return false;
                //     }
                // }
                if( !this.imgBase64 ){
                    this.uploadCover();
                    return false; 
                }
                sessionStorage.setItem("content", this.content);
                sessionStorage.setItem("title", this.name);
                sessionStorage.setItem("summary", this.summary);
                sessionStorage.setItem("product_id", this.product_id);
                sessionStorage.setItem("Category_ids", this.Category_ids);
                sessionStorage.setItem("id", this.id);
                sessionStorage.setItem("imgBase64", this.imgBase64);
                window.open("{{route('s_goods_preview')}}");
            },

            // 获取文章
            getArticle:function(){
                var _this=this;
                $.ajax({
                    url:'{{route('s_user_getArticle')}}',
                    type:'POST',
                    async:true,
                    data:{
                        article_id:_this.article_id
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            _this.name=res.data.name;
                            _this.content=res.data.content;
                            _this.product_id=res.data.product_id;
                            _this.id=res.data.id;
                            _this.summary=res.data.summary;
                            _this.category_id=res.data.category_id;     
                            _this.imgBase64=res.data.front_cover;
                        }
                    }
                });                
            },
            startWriting:function(){
                var _this=this;   
                if(_this.realData.isBind===0){
                    _this.showPopupDiv('goToRenzheng');
                }else{
                    _this.toRelease();
                    //window.location.href="/Goods/editor?id="+_this.detailData.product_id;
                }
                
            },
            checkMobile:function(){
                var _this=this;
                if ( _this.realData.mobile.length!=11 || !(/^1[34578]\d{9}$/.test(_this.realData.mobile)) ) {
                    _this.realData.errMobile="<i></i>请输入正确格式的手机号";
                    setTimeout(function(){
                        _this.realData.errMobile="";
                    }, 3000);
                    return false;
                } else {
                    return true;
                }                        
            },
            checkCode:function(){
                var _this=this;
                if ( _this.realData.code.length<4 ) {
                    _this.realData.errMsg="<i></i>请输入正确的验证码";
                    setTimeout(function(){
                        _this.realData.errMsg="";
                    }, 3000);
                    return false;
                } else {
                    return true;
                }   
            },
            checkName:function(){
                var _this=this;
                if ( !(/^[\u4e00-\u9fa5]{2,4}$/i.test(_this.realData.realname)) ) {
                    _this.realData.errName="<i></i>请输入正确格式的姓名";
                    setTimeout(function(){
                        _this.realData.errName="";
                    }, 3000);
                    return false;
                } else {
                    return true;
                }
            },
            checkIdentity:function(){
                var _this=this;
                if ( !(/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(_this.realData.idCard)) ) {
                    _this.realData.errIdentity="<i></i>请输入正确格式的身份证号";
                    setTimeout(function(){
                        _this.realData.errIdentity="";
                    }, 3000);
                    return false;
                } else {
                    return true;
                }                        
            },
            getMobileStatus:function(){//判断是否有手机号
                var _this=this;
                $.ajax({
                    url:'{{route('s_user_checkisUserMobile')}}',
                    type:'POST',
                    async:false,
                    data:{
                        
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            //console.log(res.data)
                            _this.mobileStatus=res.data;
                            if(_this.mobileStatus){
                                _this.getAuthInfo();
                            }
                            
                        }
                    }
                });
            },
            getAuthInfo:function(){//获取实名信息
                var _this=this;
                $.ajax({
                    url:'{{route('s_user_getAuthInfo')}}',
                    type:'POST',
                    async:true,
                    data:{
                        
                    },
                    "headers": {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success:function(res){
                        if(res.status==200) {
                            //console.log(res.data)
                            _this.realData.mobile=res.data.mobile;
                            _this.realData.isBind=res.data.isBind;
                            _this.realData.realname=res.data.realname;
                            _this.realData.idCard=res.data.id_card;
                        }
                    }
                });
            },
            showPopupDiv:function(name){
                var _this=this;
                _this.popupDiv=name;
                _this.popupFlag=true;
            },
            closePopup:function(){
                var _this=this;
                _this.popupDiv="";
                _this.popupFlag=false;
                _this.realData.disabled=false;
                _this.realData.times=60;
                _this.realData.validCode= "发送验证码"; 
                clearInterval(_this.realData.countStart);
            },
            checkMobileExist:function(){//验证手机号是否存在
                var _this=this;
                var eflag=false;
                if(_this.mobileStatus){
                    eflag=true;
                }else{
                    $.ajax({
                        url:'{{route('s_user_checkMobileExist')}}',
                        type:'POST',
                        async:false,
                        data:{
                            mobile:_this.realData.mobile
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200 && res.data.mobileExist==1) {
                                eflag=false;  
                                _this.realData.errMobile="<i></i>手机号已存在";
                                setTimeout(function(){
                                    _this.realData.errMobile="";
                                }, 3000);
                                
                            }else{
                                eflag=true;
                            }
                        }
                    }); 
                }
                return eflag;
            },
            sendRenzmsg:function(){//发送实名验证短信
                var _this=this;
                console.log(_this.checkMobile());
                console.log(_this.checkMobileExist());
                if(_this.checkMobile() && _this.checkMobileExist()){
                    
                    $.ajax({
                        url:'{{route('s_sms_sendAuthSms')}}',
                        type:'POST',
                        async:true,
                        data:{
                            authmobile:_this.realData.mobile,
                            type:1
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        beforeSend: function () {
                            // 禁用按钮防止重复提交
                            _this.realData.disabled=true;
                        },
                        success:function(res){
                            if(res.status==200) {
                                _this.realData.countStart = setInterval(function () {
                                    _this.realData.validCode = _this.realData.times-- + '秒后重发';
                                    _this.realData.disabled=true;
                                    if (_this.realData.times < 0) {
                                        clearInterval(_this.realData.countStart);
                                        _this.realData.validCode = "发送验证码";
                                        _this.realData.times=60;
                                        _this.realData.disabled=false;
                                    }
                                }, 1000);
                            }else{
                                _this.realData.disabled=false;
                                _this.realData.errMsg='<i></i>'+res.message;
                                setTimeout(function(){
                                    _this.realData.errMsg="";
                                }, 3000);  
                            }
                        }
                    });
                }                        
            },
            renzSubmit:function(){//实名认证
                var _this=this;
                if( this.checkCode() && this.checkName() && this.checkIdentity() ){
                    $.ajax({
                        url:'{{route('s_sms_validatorAuthSms')}}',
                        type:'POST',
                        async:true,
                        data:{
                            code:_this.realData.code,
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {  
                                $.ajax({
                                    url:'{{route('s_auth_authVerify')}}',
                                    type:'POST',
                                    async:true,
                                    data:{
                                        idno:_this.realData.idCard,
                                        realname:_this.realData.realname
                                    },
                                    "headers": {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    dataType:'json',
                                    success:function(res){
                                        if(res.status==200) {
                                            _this.popupDiv='renZSuccess';
                                            _this.realData.isBind=1;
                                            var _timer = setInterval(function() {
                                                if(_this.realData.renzTime<=0) {
                                                    window.clearInterval(_timer);  
                                                    _this.realData.code="";
                                                    _this.closePopup();
                                                    _this.towithdraw();
                                                }
                                                _this.realData.renzTime--;
                                                
                                            }, 1000);
                                        }else{
                                            _this.realData.code="";
                                            _this.realData.idCard="";
                                            _this.realData.realname="";
                                            _this.popupDiv='renZFail';
                                            /* _this.realData.errMsg='<i></i>'+res.message;
                                            setTimeout(function(){
                                                _this.realData.errMsg="";
                                            }, 3000); */
                                        } 
                                    }
                                });
                            }else if(res.status==100009) {
                                _this.realData.errMsg='<i></i>'+res.message;
                                setTimeout(function(){
                                    _this.realData.errMsg="";
                                }, 3000);
                            }else{
                                _this.realData.code="";
                                _this.realData.idCard="";
                                _this.realData.realname="";
                                _this.popupDiv='renZFail';
                                /* _this.realData.errMsg='<i></i>'+res.message;
                                setTimeout(function(){
                                    _this.realData.errMsg="";
                                }, 3000); */
                            }
                        }
                    }); 
                }
            },
            towithdraw: function(){
                var _this=this;
                _this.closePopup();
                window.location.href="/Goods/editor?id="+_this.detailData.product_id;
            }
        }
    })
</script>
</body>
</html>