@extends('layout')

@section("title", "首页")


@section("css")
<link rel="stylesheet" href="{{ mix('css/swiper.min.css') }}">
<link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
@endsection
<script src="/js/jquery-3.2.1.min.js" type="text/javascript" charset="UTF-8"></script>
<script src="/js/ueditor.config.js" type="text/javascript" charset="UTF-8"></script>
<script src="/js/ueditor.all.js" type="text/javascript" charset="UTF-8"></script>
@section("content")
<!--内容区域-->
<div id="editor" class="pgy_1200">






    <div class="pgy_commodity_edit">
        <!-- <span>{{$res['category_name']}}</span> -->
        <input type="hidden" id="article_category_id" value="-1">
        <h1>{{$res['product_name']}}</h1>
        <p>{{$res['synopsis']}}</p>
        <div class="com_price">
            <span>商品价格</span>
            <span class="pgy_color">¥ {{number_format($res['selling_price'],2)}}</span>
        </div>

       <!-- <i class="writeM">*</i>  -->
       <input class="pgy_article_title" id="pgy_article_title" type="text" v-model="name" placeholder="请在这里输入标题">
        <!-- 添加分类 -->
      
       
       

        <div class="pgy_commodity_textarea">
            <script id="container" name="content" style="width:900px; height:600px;" type="text/plain"></script>

        <!-- <div class="pgy_textarea_title"></div>
        <div class="pgy_textarea_content"></div> -->
        </div>
        <div class="pgy_footnote">注：上传图片数量必须大于或等于四张，单张图片请勿超过4M</div>
        <div class="choseType add_border" >
          <div @click="typeUp"><i class="addType" ></i>@{{fonting}}</div>      
          <div class="loginBgmedia" v-show="Typeshow" @click="loginBgmedia">
            <div class="pgy_fenleis" @click="f1"  style="position:absolute;margin-left:-450px;margin-top:-289px;top:50%;left: 50%;z-index:1000;">
                <div class="fenleis_title" style="height:82px;" >
                    <div class="bgWhite" style="background:#ffffff;width:900px;min-height: 500px;border-bottom: 1px solid #EAEAEA;">
                       <ul >
                                <ul class="fenl_content newfenl_content" >
                                    <li @click="chosedType(t.id,t.name)" v-for="t in mediaData" style="margin-right: 61px;overflow:hidden">@{{t.name}}</li>
                                    <!-- <li @click="chosedType()">时事热点</li>
                                    <li @click="chosedType()">时事热点</li>
                                    <li @click="chosedType()">时事热点</li>
                                    <li @click="chosedType()">时事热点</li> -->
                               </ul>
                       
                       </ul>
                    </div>
                
                </div>
            </div>  
    </div> 
               
            
        </div>
        <div class="pgy_send">
            <input class="pgy_send_left" id="sub" type="button" value="发布" @click="toRelease">
            <input class="pgy_send_right" id="save" type="button" value="保存" @click="toSave">
            <input class="pgy_send_right" id="review" type="button" value="预览" @click="toReview">
        </div>
        <div class="pgy_article_err" style=" width: 100%; height: 60px; line-height: 60px; text-align: center; color: #FF7241; font-size: 14px;"></div>
    </div>

    <div class="pgy_layerOut">
        <div class="pgy_articleTips"></div>
    </div>

</div>
@endsection
@section("script")
<script>
$(function(){
    var app = new Vue({
        el: '#editor',
        data: {
            editor:null,
            name:"",
            content:"",
            id:"",
            imgCount:0,
            product_id:{{$res['id']}},
            Typeshow:false,
            article_id:"",
            Category_id:"",
            Category_ids:"",
            fonting:'请选择内容类型',
            times:"",
            mediaData:[]
            
        },
        created:function(){
            var _this=this;
            this.getCategoryList();
        },
        mounted:function(){
            var ue=null;
            var _this=this;
            UE.delEditor('container');   //先删除之前实例的对象
            this.editor = UE.getEditor('container',{
                toolbars:[['source', 'bold', 'italic', 'underline', 'backcolor', 'fontsize', 'fontfamily', 'simpleupload','wordimage',
                    'justifyleft', 'justifyright','justifycenter', 'justifyjustify', 'strikethrough', 'removeformat', 'formatmatch', 'pasteplain', '|',
                    'forecolor', 'insertorderedlist', 'insertunorderedlist', 'link', 'unlink']],
                    'filterTxtRules' : function(){
                        function transP(node){
                            node.tagName = 'p';
                            node.setStyle();
                        }
                        return {
                            //直接删除及其字节点内容
                            '-' : 'script style object iframe embed input select',
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
                
                var submitBtn=true;
                $("#sub").attr("disabled",submitBtn);
                $("#save").attr("disabled",submitBtn);
                $("#review").attr("disabled",submitBtn);
                ue.addListener("contentChange",function(){
                    submitBtn=true;
                    _this.content=ue.getContent();
                    var c = "img"; // 要计算的字符
                    var regex = new RegExp(c, 'g'); // 使用g表示整个字符串都要匹配
                    var result = _this.content.match(regex);
                    _this.imgCount = !result ? 0 : result.length;
                    // console.log("img", _this.imgCount);
                    // console.log("name", _this.name);
                    if(  _this.imgCount>=4 ){
                        submitBtn=false;
                        $("#sub").attr("disabled",submitBtn);
                        $("#save").attr("disabled",submitBtn);
                        $("#review").attr("disabled",submitBtn);
                    }else{
                        submitBtn=true;
                        $("#sub").attr("disabled",submitBtn);
                        $("#save").attr("disabled",submitBtn);
                        $("#review").attr("disabled",submitBtn);
                    }
                });

                ue.addListener("keydown",function(type,e){
                    var currKey=0, e=e||event||window.event;  
                    currKey = e.keyCode||e.which||e.charCode;  
                    if(currKey == 83 && (e.ctrlKey||e.metaKey)){
                        e.preventDefault(); 
                        //console.log(33333);
                        _this.toSave();
                        return false;  
                    }
                })

                ue.addListener("catchRemoteImage",function(){
                    //console.log("img uploading...");
                    submitBtn=true;
                    $("#sub").attr("disabled",submitBtn);
                    $("#save").attr("disabled",submitBtn);
                    $("#review").attr("disabled",submitBtn);
                });
                ue.addListener("catchremotesuccess",function(){
                    //console.log("img upload success");
                    if(  _this.imgCount>=4 ){
                        submitBtn=false;
                        $("#sub").attr("disabled",submitBtn);
                        $("#save").attr("disabled",submitBtn);
                        $("#review").attr("disabled",submitBtn);
                    }else{
                        submitBtn=true;
                        $("#sub").attr("disabled",submitBtn);
                        $("#save").attr("disabled",submitBtn);
                        $("#review").attr("disabled",submitBtn);
                    }
                });

            });
            
            if(this.getQueryVariable("article_id")){
                this.article_id=this.getQueryVariable("article_id");
                this.getArticle();
                this.editor.ready(function(){
                    setTimeout(function(){
                        ue.execCommand('insertHtml', _this.content);
                    },500);
                });
            }
            
            
        },
        methods:{
            getTime:function(){
                var nowTime = new Date();
                Y = nowTime.getFullYear() + '-';
                M = (nowTime.getMonth()+1 < 10 ? '0'+(nowTime.getMonth()+1) : nowTime.getMonth()+1) + '-';
                D = nowTime.getDate() < 10 ? '0'+ nowTime.getDate()+' ' : nowTime.getDate() + ' ';
                h = nowTime.getHours() + ':';
                m = nowTime.getMinutes() < 10 ? '0'+ nowTime.getMinutes()+':' : nowTime.getMinutes() + ':';
                s = nowTime.getSeconds() < 10 ? '0'+ nowTime.getSeconds() : nowTime.getSeconds();
                return Y+M+D+h+m+s;
            },
            getQueryVariable:function(variable){
                var query = window.location.search.substring(1);
                var vars = query.split("&");
                for (var i=0;i<vars.length;i++) {
                        var pair = vars[i].split("=");
                        if(pair[0] == variable){return pair[1];}
                }
                return(false);
            },
            getCategoryList:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_aricle_getCategoryList')}}',
                            type:'POST',
                            data:{
                                page:1,//当前页数
                                pagesize:30//每页条数                            
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    _this.mediaData=res.data.data;
                                }else if(res.status==403){
                                    location.reload();
                                }
                            }
                        });                        
                    },
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
                            _this.category_id=res.data.category_id;
                            $.ajax({
                            url:'{{route('s_aricle_getCategoryList')}}',
                            type:'POST',
                            data:{
                               
                                page:1,//当前页数
                                pagesize:30//每页条数                            
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    _this.mediaData=res.data.data;
                                    _this.mediaData.forEach(function(v,i,a) {
                                        if(a[i].id==_this.category_id){
                                            _this.fonting=a[i].name;
                                        }
                                })
                            }
                           }
                        });           
                           
                        }
                    }
                });                
            },
            toRelease:function(){
                var _this=this;
                this.content=this.editor.getContent();
                var c = "img"; // 要计算的字符
                var regex = new RegExp(c, 'g'); // 使用g表示整个字符串都要匹配
                var result = this.content.match(regex);
                this.imgCount = !result ? 0 : result.length;
                // if((this.fonting!='请选择内容类型') && this.name && this.content && this.imgCount>=4 ){
                   if( this.name && this.content && this.imgCount>=4 ){
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
                            id:_this.id
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        beforeSend: function(){
                        // Handle the beforeSend event
                            $(".pgy_layerOut").show();
                            $(".pgy_articleTips").html("文章发布中…");
                        },
                        success:function(res){
                            if(res.status==200) {
                                location.href="{{route('s_goods_success')}}";
                            }else{

                            }
                        }
                    });
                }else{
                    $(".pgy_article_err").html("未输入标题");
                    // $(".pgy_article_err").html("未输入标题");
                    setTimeout(function(){
                        $(".pgy_article_err").html("");
                    }, 3000);
                }    
            },
            toSave:function(){
                var _this=this;               
                //console.log(_this.Category_ids);
                this.content=this.editor.getContent();
                var c = "img"; // 要计算的字符
                var regex = new RegExp(c, 'g'); // 使用g表示整个字符串都要匹配
                var result = this.content.match(regex);
                this.imgCount = !result ? 0 : result.length;
                // if((this.fonting!='请选择内容类型') && this.name && this.content && this.imgCount>=4 ){
                  if( this.name && this.content && this.imgCount>=4 ){
                    $.ajax({
                        url:'{{route('s_article_add')}}',
                        type:'POST',
                        async:true,
                        data:{
                            name:_this.name,
                            content:_this.content,
                            product_id:_this.product_id,
                            article_category_id:_this.Category_ids,
                            status:2,
                            id:_this.id
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        beforeSend: function(){
                        // Handle the beforeSend event
                            $(".pgy_layerOut").show();
                            $(".pgy_articleTips").html("文章保存中…");
                        },
                        success:function(res){
                            if(res.status==200) {
                                $(".pgy_articleTips").html("文章保存成功");
                                _this.id=res.data;
                                //console.log(_this.id);
                                setTimeout(function() {
                                    $(".pgy_layerOut").hide();
                                }, 3000);
                            }else{
                                $(".pgy_articleTips").html(res.message);
                                setTimeout(function() {
                                    $(".pgy_layerOut").hide();
                                }, 3000);
                            }
                        }
                    });
                }else{
                    $(".pgy_article_err").html("未输入标题");
                    // $(".pgy_article_err").html("未输入标题");
                    setTimeout(function(){
                        $(".pgy_article_err").html("");
                    }, 3000);
                }    
            },
            typeUp:function(){
                var _this=this;
                _this.Typeshow=true;
               
               
            },
            chosedType:function(Category_id,name){
                var _this=this;
                _this.Typeshow=false;
                _this.fonting=name;
                _this.Category_ids=Category_id;
            },
            loginBgmedia:function(e){
                var _this=this;
                _this.Typeshow=false;
            },
            f1:function(e){
                e.stopPropagation();
            },
            toReview:function(){
                var _this=this;
                this.times=this.getTime();
                this.content=this.editor.getContent();
                var c = "img"; // 要计算的字符
                var regex = new RegExp(c, 'g'); // 使用g表示整个字符串都要匹配
                var result = this.content.match(regex);
                this.imgCount = !result ? 0 : result.length;
                if( this.name && this.content && this.imgCount>=4 ){
                    sessionStorage.setItem("articleContent", this.content);
                    sessionStorage.setItem("articleTitle", this.name);
                    sessionStorage.setItem("articleTime", this.times);
                    window.open("{{route('s_goods_preview')}}");
                }else{
                    $(".pgy_article_err").html("未输入标题");
                    setTimeout(function(){
                        $(".pgy_article_err").html("");
                    }, 3000);
                }

            }
        }
    });

    $('#sub').click(function(){
        $('#form-product-add').submit();

    })
})    
</script>
@endsection
