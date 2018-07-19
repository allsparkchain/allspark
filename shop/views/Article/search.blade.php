@extends('layout')

@section("title", "由我分享")


@section("css")
    <link rel="stylesheet" href="{{ mix('css/pgy.css') }}">
    <link rel="stylesheet" href="{{ mix('css/swiper.min.css') }}">
@endsection

@section("content")
<!--内容区域-->
<div style=" min-height: 1080px; margin: 0 0 50px 0;">
<div id="lists" style=" display:none;">
<div class="pgy_session_info" style=" overflow: visible;">
    <div class="pgy_1200" style="position: relative; min-height: 1080px;">

        <div class="pgy_area" style="display:block"  >
            <span>@{{tis}}</span>
            <div class="pgy_area_dropDown" >
                <div style="height: 100%;overflow-y: auto;overflow-x: hidden">
                    <div class="pgy_area_list" v-for="item in areaWeekList">
                        <span class="pgy_color_orgin">@{{item.category_name}}:</span>
                        <ul >
                            <li v-for="(t,index) in item.regionlist"  @click="areaList(t,index)">@{{t.region_name}}</li>
                        </ul> 
                    </div>
                </div>
            </div>
        </div>

        <div class="pgy_info_navbar">
            <ul>
                <li :class="{Onlive:goodsIndex==0}"><a @click="getGoodsLists(0,0)" href="javascript:;">所有</a></li>
                <li v-for="(l,index) in leftList" :class="{Onlive:goodsIndex==index+1}"><a @click="getGoodsLists(l.id,index+1)" href="javascript:;">@{{l.name}}</a></li>
            </ul>
        </div>
        <div class="pgy_info_content">
            <a v-for="g in goodsList" :href="'/Goods/detail?id='+g.id" target="_blank">
                <div class="pgy_comodity_show"><img class="lazy" :data-original="g.newpath325" :src="g.img_path" style="width:323px; height:325px;"  alt=""></div>
                <div class="pgy_comodity_content" >
                    <p class="speacials">@{{g.product_name}}</p>
                    <p class="pgy_commodity_singwrite">@{{g.synopsis|nameFormat}}</p>
                    <!-- <div style="position: absolute;bottom: 3px;">
                        <div class="pgy_commodi_price">产品价格：<span>¥ @{{g.selling_price|numberFormat}}</span></div>
                        <div class="pgy_commodi_gaofei">
                            <div class="royalty" style="margin-top: 12px">稿费：<span>@{{g.percent_account|numberFormat}}</span></div>
                            <div>分成：<span>@{{g.percent_commission|numberFormat}}%</span></div>
                        </div>
                    </div> -->
                </div>
            </a>
        </div>

        <div style=" width: 100%; clear: both;"></div>
        <div v-if="page_count!=0" class="zmtdl_pageWrap" style="width:1035px;float:right;overflow:hidden">
        <ul class="zmtdl_page">
            <li class="first" disabled="true" :class="{'disabled':page==1}" @click="toPage(page==1 ? 1 : page-1)">
                上一页
            </li>
            <li v-for="(value,index) in showPages" :key="index" :class="{on:value===page}">
                <a v-if="value" @click="toPage(value)">@{{value}}</a>
                <span v-else>...</span>
            </li>
            <li class="last" :class="{'disabled':page==page_count}" @click="toPage(page==page_count ? page_count : page+1)">下一页</li>
        </ul>
    </div>
    </div>

    
    
</div>
</div>
</div>
@endsection
@section("script")
    <script type="text/javascript">
        $(function () {
            // console.log($('.pgy_area_list').eq(0));
            Vue.filter("numberFormat",function(s){
                s=parseFloat(s);
                s=s.toString();
                if(/[^0-9\.]/.test(s)) return "0.00";
                    s=s.replace(/^(\d*)$/,"$1.");
                    s=(s+"00").replace(/(\d*\.\d\d)\d*/,"$1");
                    s=s.replace(".",",");
                    var re=/(\d)(\d{3},)/;
                    while(re.test(s))
                        s=s.replace(re,"$1,$2");
                    s=s.replace(/,(\d\d)$/,".$1");
                    return s.replace(/^\./,"0.");
            });

            Vue.filter("nameFormat",function(value){
                if(value.length>50){
                    return value.substr(0,50)+"...";
                }else{
                    return value;
                }
                
            });

            var app=new Vue({
                el:'#lists',
                data:{
                    goodsIndex:0,
                    category_id:0,
                    leftList:[],
                    goodsList:[],
                    page:1,
                    page_count:"",
                    areaWeekList:[],
                    tis:"",
                    region_id:""
                },
                created:function(){
                    var _this=this;
                    this.getCategoryList();
                    this.getGoodsList(0,0);
                    this.getareaweekList();
                    
                },
                computed:{
                    showPages:function(){
                        let pageNumber = this.page_count;
                        let index = this.page;
                        let arr = [];
                        if( pageNumber <=5 ){
                            for ( let i=1; i <= pageNumber; i++ ) {
                            arr.push(i);
                            }
                            console.log(arr);
                            return arr;
                        }
                        if (index <= 2){
                            return [1,2,3,0,pageNumber];
                        }

                        if (index >= pageNumber - 1) {
                            return [1,0,pageNumber-2,pageNumber-1,pageNumber];
                        }

                        if (index >= pageNumber -2) {
                            return [1,0,pageNumber-3,pageNumber-2,pageNumber-1,pageNumber]
                        }

                        if (index == 3){
                            return [1,2,3,4,0,pageNumber];
                        }

                        return [1,0,index-1,index,index+1,0,pageNumber];
                    }
                },
                mounted:function(){
                    var _this=this;
                    this.$nextTick(function() {
                        document.getElementById("lists").style.display = "block";
                    });
                },
                methods:{
                    toPage:function(page){
                        window.scroll(0,0);
                        this.page=page;
                        this.getGoodsList(this.category_id,this.goodsIndex);                        
                    },
                    getCategoryList:function(){
                        var _this=this;
                        $.ajax({
                            url:'{{route('s_goods_getCategorList')}}',
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
                                    _this.leftList=res.data.data;
                                }else if(res.status==403){
                                    location.reload();
                                }
                            }
                        });                        
                    },
                    areaList:function(t,index){
                            this.tis=t.region_name;
                            this.region_id=t.regionId;
                            //console.log(this.region_id);
                            this.getGoodsList(this.category_id,this.goodsIndex);
                            //this.getGoodsList();
                            this.page=1;
                            $('.pgy_area_dropDown').hide();
                            
                    },
                    getareaweekList:function(){
                        var _this=this;
                        $.ajax({    
                            url:'{{route('s_index_categoryRegionList')}}',
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
                                    _this.areaWeekList=res.data.data;
                                    _this.areaWeekList.forEach(function(v,i,a) {
                                        a[i].regionlist.forEach(function(value, index, array){
                                            if(array[index].region_name=="全国"){ 
                                                _this.tis=array[index].region_name; 
                                                // _this.region_id=array[index].regionId;  
                                                setTimeout(function(){
                                                    $('.pgy_area_dropDown .pgy_area_list:first').children('span').css('color','#FF7241');
                                                },500)
                                                _this.region_id1=_this.region_id;
                                                //console.log(_this.region_id);                                               
                                                v.regionlist.unshift(array[index]);
                                                v.regionlist.splice(index,2);
                                            }
                                        })
                                            //console.log( "id1"+_this.region_id1);
                                    });
                                }else if(res.status==403){
                                    location.reload();
                                }else{

                                }
                            }
                        });
                    },
                    getGoodsLists:function(id,currentIndex){                        
                        this.page=1;
                        this.getGoodsList(id,currentIndex);
                    },
                    getGoodsList:function(id,currentIndex){
                        var _this=this;
                       
                        this.category_id=id;
                        this.goodsIndex=currentIndex;
                        this.goodsList=[];

                        $.ajax({
                            url:'{{route('s_goods_getGoodsList')}}',
                            type:'POST',
                            data:{
                                region_id:_this.region_id,
                                category_id:_this.category_id,
                                page:_this.page,//当前页数
                                pagesize: 30//每页条数                            
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            dataType:'json',
                            success:function(res){
                                if(res.status==200) {
                                    _this.goodsList=res.data.data;
                                    _this.page_count=res.data.page_count;
                                    _this.$nextTick(function() {
                                        document.getElementById("lists").style.display = "block";
                                        //console.log($("img.lazy").length);
                                        $("img.lazy").lazyload({});
                                    });
                                }else if(res.status==403){
                                    location.reload();
                                }
                            }
                        });                        
                    }

                }
            });
           
            $(".pgy_area").mouseover(function(){
                $(".pgy_area_dropDown").show();
            });
            $(".pgy_area").mouseout(function(){
                $(".pgy_area_dropDown").hide();
            });
        });
    </script>
@endsection
