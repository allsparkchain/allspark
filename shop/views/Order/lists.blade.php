@extends('layout')

@section("title", "由我分享")


@section("css")
<link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
<link rel="stylesheet" href="{{ mix('css/pgy2.css') }}">
<style>
    body{ padding: 60px 0 0 0;background: #f8f8f8;}
</style>
@endsection

@section("content")
<div style=" min-height: 950px;" >
<div id="lists" style=" display: none;">

<div class="pgy-article-list">   
    <!-- banner -->
    <div class="pgy-article-list-banner-modal"></div>
    <div class="pgy-article-list-banner"></div>
    
    <div class="pgy-article-list-filter">
        <ul class="pgy-article-list-category">
            <li class="on"><a href="/Article/lists" style=" color:#ff7841;">推荐</a></li>
            <li v-for="(c,index) in categoryList" @click="cateClick(c.id)">
                @{{c.name}}
            </li>
        </ul>
        <a class="more" href="javascript:;" @click="more" v-show="moreStatus||categoryList2.length">更多<i class="pgy_more_downArrow"></i></a>
        <div class="pgy-article-list-more" v-show="moreStatus">
            <ul class="pgy-article-list-more2">
                <li v-for="(c,index) in categoryList2" @click="cateClick2(c.id,c.name)">
                    @{{c.name}}
                </li>
            </ul>
        </div>
    </div>

    <!-- articleList -->
    <div class="pgy-article-list-modal" v-if="articleList.length==0">
        <div>
            <div class="pgy-article-list-classes">
                <i class="pgy-article-list-hot"></i>最热
            </div>
            <ul class="pgy-article-list-ul-modal">
                <li>
                    <div class="shell_img"></div>
                    <div class="shell_tit">
                        <p></p>
                        <p></p>
                    </div>
                    <div class="shell_msg">
                        <p></p>
                    </div>
                    <div class="shell_oth">
                        <span></span>
                        <span></span>
                    </div>
                </li>
                <li>
                    <div class="shell_img"></div>
                    <div class="shell_tit">
                        <p></p>
                        <p></p>
                    </div>
                    <div class="shell_msg">
                        <p></p>
                    </div>
                    <div class="shell_oth">
                        <span></span>
                        <span></span>
                    </div>
                </li>
                <li>
                    <div class="shell_img"></div>
                    <div class="shell_tit">
                        <p></p>
                        <p></p>
                    </div>
                    <div class="shell_msg">
                        <p></p>
                    </div>
                    <div class="shell_oth">
                        <span></span>
                        <span></span>
                    </div>
                </li>
                
            </ul>
        </div>
        
        <div class="pgy-article-list-classes">
            <i class="pgy-article-list-category"></i>旅游
        </div>
        <ul class="pgy-article-list-ul-modal2">
            <li>
                <div class="shell_content">
                    <div class="shell_tit">
                        <p></p>
                        <p></p>
                    </div>
                    <div class="shell_msg">
                        <p></p>
                        <p></p>
                    </div>
                    <div class="shell_oth">
                        <span class="o1"></span>
                        <span class="o2"></span>
                        <span class="o2"></span>
                    </div>
                </div>
                <div class="shell_img"></div>
            </li>
            <li>
                <div class="shell_content">
                    <div class="shell_tit">
                        <p></p>
                        <p></p>
                    </div>
                    <div class="shell_msg">
                        <p></p>
                        <p></p>
                    </div>
                    <div class="shell_oth">
                        <span class="o1"></span>
                        <span class="o2"></span>
                        <span class="o2"></span>
                    </div>
                </div>
                <div class="shell_img"></div>
            </li>
        </ul>
        <div class="pgy-look-more">点击查看更多></div>
    </div>

    <div v-for="a in articleList" v-else>

        <div v-if="a.ColumnId==1 && a.list.length!=0">
            <div class="pgy-article-list-classes">
                <i class="pgy-article-list-hot"></i>@{{a.name}}
            </div>
            <ul class="pgy-article-list-ul">
                <li v-for="l in a.list">  
                    <div class="pgy-article-list-img">
                        <a :href="'/Article/detail?id='+l.articleId" target="_blank">
                            <img :src="l.img_path285">
                        </a>
                    </div>
                    <div class="pgy-article-list-first">
                        <div class="pgy-article-list-title">
                            <a :href="'/Article/detail?id='+l.articleId" target="_blank">
                                @{{l.name}}
                            </a>
                        </div>
                        <div class="pgy-article-list-brief">
                            <span v-show="l.min_commission!=0">
                                分享获利：<span style=" color: #ff4141; font-weight: bold;">@{{l.min_commission|numberFormat}}</span>元/单起
                            </span>
                        </div>
                        <div class="pgy-article-list-earnings">
                            <i class="pgy-share" title="分享数"></i>@{{l.shareNums}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <i class="pgy-look" title="浏览数"></i>@{{l.viewNumsShow}}
                        </div>
                    </div>
                </li>
            </ul>
            
        </div>

        <div v-if="a.ColumnId==2 && a.list.length!=0">
            <div class="pgy-article-list-classes">
                <i class="pgy-article-list-hot"></i>@{{a.name}}
            </div>
            <ul class="pgy-article-list-ul">
                <li v-for="l in a.list">
                    <div class="pgy-article-list-img">
                        <a :href="'/Article/detail?id='+l.articleId" target="_blank">
                            <img :src="l.img_path285">
                        </a>
                    </div>
                    <div class="pgy-article-list-title">
                        <a :href="'/Article/detail?id='+l.articleId" target="_blank">
                            @{{l.name}}
                        </a>
                    </div>
                    <div class="pgy-article-list-brief">
                        <span v-show="l.min_commission!=0">
                            分享获利：<span style=" color: #ff4141; font-weight: bold;">@{{l.min_commission|numberFormat}}</span>元/单起
                        </span>
                    </div>
                    <div class="pgy-article-list-earnings">
                        <div v-show="l.min_commission!=0"><i class="pgy-money"></i><span class="red">￥@{{l.min_commission|numberFormat}}</span><span style=" color: #7e7e7e;">&nbsp;/单起</span>&nbsp;&nbsp;&nbsp;</div>
                        <i class="pgy-share" title="分享数"></i>@{{l.shareNums}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <i class="pgy-look" title="浏览数"></i>@{{l.viewNumsShow}}
                    </div>
                </li>
            </ul>
        </div>

        <div v-if="a.ColumnId!=1 && a.ColumnId!=2 && a.list.length!=0">
            <div class="pgy-article-list-classes">
                <i class="pgy-article-list-category"></i>@{{a.CategoryName}}
            </div>
            <ul class="pgy-article-list-ul2">
                <li v-for="l in a.list">               
                    <div class="pgy-article-list-left">
                        <div class="pgy-article-list-title">
                            <a :href="'/Article/detail?id='+l.articleId" target="_blank">
                                @{{l.name}}
                            </a>
                        </div>
                        <div class="pgy-article-list-brief">
                            @{{l.summary}}
                        </div>
                        <div class="pgy-article-list-earnings">
                            <span v-show="l.min_commission!=0">
                                分享获利：<span style=" color: #ff4141; font-weight: bold;">@{{l.min_commission|numberFormat}}</span>元/单起
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </span>
                            <i class="pgy-share" title="分享数"></i>@{{l.shareNums}}&nbsp;&nbsp;&nbsp;
                            <i class="pgy-look" title="浏览数"></i>@{{l.viewNumsShow}}
                        </div>
                    </div>
                    <div class="pgy-article-list-right">
                        <div class="pgy-article-list-img">
                            <a :href="'/Article/detail?id='+l.articleId" target="_blank">
                                <img :src="l.img_path285">
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="pgy-look-more" @click="cateClick3(a.relate_id,a.CategoryName)">点击查看更多></div>
        </div>

    </div>

</div>
<div class="pgy-arrow-up"></div>

    
</div>
</div>
@endsection
@section("script")
<script type="text/javascript">
    $(function () {
        setTimeout(function(){
            $('.pgy_area_dropDown .pgy_area_list:first').children('span').css('color','#FF7241');
        },500)

        Vue.filter("numberFormat",function(s){
            s=s.toString();
            if(/[^0-9\.]/.test(s)) return "invalid value";
            s=s.replace(/^(\d*)$/,"$1.");
            s=(s+"00").replace(/(\d*\.\d\d)\d*/,"$1");
            s=s.replace(".",",");
            var re=/(\d)(\d{3},)/;
            while(re.test(s))
                s=s.replace(re,"$1,$2");
            s=s.replace(/,(\d\d)$/,".$1");
            return s.replace(/^\./,"0.");
        });

        var app=new Vue({
            el:'#lists',
            data:{
                noData:false,
                loading:false,
                moreGoods:false,
                moreStatus:0,
                name:"",
                goodsIndex:0,
                category_id:0,
                categoryList:[],
                categoryList2:[],
                lastWeekList:[],
                page:1,
                page_count:"",
                articleList:[],
                articleList2:[],
                tis:"",
                tisId:"",
                region_id:""
            },
            created:function(){
                var _this=this;

            },
            mounted:function(){
                var _this=this;
                this.getCategoryList();
                
                this.$nextTick(function() {
                    document.getElementById("lists").style.display = "block";
                });

                $(".pgy-arrow-up").click(function() {
                    $("html,body").animate({scrollTop:0}, 500);
                });

                $(window).scroll(function() {
                    if($(this).scrollTop()>500){
                        $(".pgy-arrow-up").show();
                    }else{
                        $(".pgy-arrow-up").hide();
                    }
                });

                setTimeout(function(){
                    
                    $(".pgy-article-list-banner-modal").remove();
                    $(".pgy-article-list-banner").fadeIn();
                    _this.getArticleList();

                }, 1000);
                    
            },
            methods:{
                more:function(){
                    if(this.moreStatus==false){
                        this.moreStatus=true;
                    }else{
                        this.moreStatus=false;
                    }
                },
                toPgySearch:function(){
                    window.open("/Article/searchArticle?name="+encodeURI(this.name));
                },
                getArticleList:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_aricle_getArticleIndex')}}',
                        type:'POST',
                        data:{
                    
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.articleList=res.data.data;
                                
                            }else if(res.status==403){
                                location.reload();
                            }
                        }
                    });
                },
                getCategoryList:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_aricle_getCategoryList')}}',
                        type:'POST',
                        data:{
                        
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                _this.categoryList=res.data.data.splice(0,6);
                                _this.categoryList2=res.data.data.splice(0); //[]
                                _this.moreStatus=!_this.categoryList2.length; //0
                            }else if(res.status==403){
                                location.reload();
                            }
                        }
                    });
                },
                cateClick:function(id){
                    var _this=this;
                    _this.category_id=id;
                    window.open("/Article/moreLists?category_id="+_this.category_id);
                },
                cateClick2:function(id,name){
                    var _this=this;
                    _this.category_id=id;
                    window.open("/Article/moreLists?category_id="+_this.category_id+"&category_name="+encodeURI(name));
                },
                cateClick3:function(id,name){
                    var checkMore=false;
                    this.categoryList.forEach(function(v,i,a){
                        if(v.id==id){
                            checkMore=true;
                            return;
                        }
                    });
                    if (checkMore) {
                        window.open("/Article/moreLists?category_id="+id);
                    } else {
                        window.open("/Article/moreLists?category_id="+id+"&category_name="+encodeURI(name));
                    }
                },
                areaList:function(t,index){
                    this.tis=t.region_name;
                    this.region_id=t.regionId;
                    this.moreGoods=true;
                    this.page=1;
                    //this.getList();
                    $('.pgy_area_dropDown').hide();
                },
                getList:function(){
                    var _this=this;
                    $.ajax({
                        url:'{{route('s_aricle_getArticleList')}}',
                        type:'POST',
                        data:{
                            region_id:_this.region_id,
                            category_id:_this.category_id,
                            page:_this.page,
                            pagesize:9
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                if(_this.page==1){
                                    _this.articleList2=res.data.data;
                                    if ( _this.articleList2.length==0) {
                                        _this.noData=true;
                                    } else {
                                        _this.noData=false;
                                    }
                                }else{
                                    _this.articleList2=_this.articleList2.concat(res.data.data);
                                }
                                
                                if(_this.page!=1){
                                    _this.loading=false;
                                }
                            }else if(res.status==403){
                                location.reload();
                            }
                        }
                    });
                }
            }
        });
    });
</script>
@endsection
