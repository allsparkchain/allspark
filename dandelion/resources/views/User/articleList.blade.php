@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="articleList" style=" display: none;">

    <div class="navBg">
        <ul class="ggzdl_admin_dayf">
            <li  :class="{'on':showstatus==0}" @click="tabs(0)" >全部内容(@{{count1}})</li>
            <li  :class="{'on':showstatus==1}" @click="tabs(1)" >已发布(@{{count2}})</li>
            <li  :class="{'on':showstatus==3}" @click="tabs(3)" >审核中(@{{count3}})</li>
            <li  :class="{'on':showstatus==4}" @click="tabs(4)" >草稿箱(@{{count4}})</li>
            
        </ul>
    </div>
<!-- 删除 -->
<div class="ggzdl_popup_wrap"  v-show="popupFlag">
    <div class="ggzdl_popup_box" v-show="popupDiv==='detelteShow'">
            <span class="popup_close" @click="closePopup"></span>
            <div class="popup_title">草稿箱</div> 
            <div class="popup_successed" style="width:240px;">
                <img src="/image/errorbink.png" alt="">
                <p>删除之后将无法恢复文章</p>
                <p>请确认</p>
            </div>
            <div class="popup_confirm">
                <span @click="closeDetelte()">取消</span>
                <span @click="sureDetelte(goalShowstatus,goalId)">确认</span>
            </div>
    </div>
    <div class="ggzdl_popup_box" v-show="popupDiv==='shstatus'">
            <span class="popup_close" @click="contrSend(goalShowstatus)"></span>
            <div class="popup_title">绑定结果</div> 
            <div class="popup_successed">
                <img src="/image/oksuccess.png" alt="">
                <p>文章已提交审核</p>
            </div>
            <div class="popup_btn" @click="contrSend(goalShowstatus)">确定</div>
           
    </div>

  </div>         
    <div class="pgy_messageList" v-if="showstatus==0">
    <ul>
            <li v-for="t in tableData" >
                <div  v-if="t.status==1">
                    <div class="pgy_litters">
                        <p><span class="yfb" >已发布</span>@{{t.add_time|timeFormat}}发表:@{{t.name}}</p>                    
                        <div class="pgy_meihao"  v-if="t.imgs">
                            <div v-for="i in t.imgs.slice(0,4)" ><img :src="i.newpath285" alt=""></div>
                        </div>
                        <div class="sendtimes" v-if="t.publish_time>0">发布时间：@{{t.publish_time|timeFormat}}</div>
                    </div>
                    
                    <div class="pgy_largers">
                        <div class="pgy_editDelete">
                            <!-- <div>分享</div> -->
                            <div><a :href="'/Article/detail?id='+t.id" target="_blank">详情</a></div>
                        </div>
                    </div>    
                </div> 
                <div  v-if="t.status==3">
                    <div class="pgy_litters">
                        <p><span class="shz">审核中</span>@{{t.add_time|timeFormat}}发表:@{{t.name}}</p>
                        <div class="pgy_meihao" v-if="t.imgs">
                            <div v-for="i in t.imgs.slice(0,4)" ><img :src="i.newpath285" alt=""></div>
                        </div>
                        <div class="sendtimes" v-if="t.verify_time>0">提交时间：@{{t.verify_time|timeFormat}}</div>
                    </div>
                </div> 
                <div  v-if="t.status==5">
                    <div class="pgy_litters">
                        <p><span class="cgx">草稿箱</span>@{{t.add_time|timeFormat}}发表:@{{t.name}}</p>
                        <div class="pgy_meihao"  v-if="t.imgs">
                            <div v-for="i in t.imgs.slice(0,4)" ><img :src="i.newpath285" alt=""></div>
                        </div>
                        <div class="sendtimes" v-if="t.save_time>0">保存时间：@{{t.save_time|timeFormat}}</div>
                    </div>
                    
                    <div class="pgy_largers" style="position:relative">
                        <div class="pgy_editDelete">
                            <div @click="contribute(showstatus,t.id)">投稿</div>
                            <div><a :href="'/Goods/editor?article_id='+t.id">继续写</a></div>
                            
                        </div>
                        <div class="pgyz_iconDelete" @click="deleteArticle(showstatus,t.id)"></div>
                    </div>       
                </div>
                <div v-if="t.status==2||t.status==4">
                <div class="pgy_litters">
                    <p><span class="cgx">已下线</span>@{{t.add_time|timeFormat}}发表:@{{t.name}}</p>
                    <div class="pgy_meihao"  v-if="t.imgs">
                        <div v-for="i in t.imgs.slice(0,4)" ><img :src="i.newpath285" alt=""></div>
                    </div>
                    <div class="sendtimes" v-if="t.publish_time>0">发布时间：@{{t.publish_time|timeFormat}}</div>
                </div>
                
                <div class="pgy_largers">
                       <div class="pgy_editDelete">
                           <!-- <div>分享</div> -->
                           <!-- <div><a :href="'/Article/detail?id='+t.id" target="_blank">详情</a></div> -->
                       </div>
                </div>         
                </div> 
                                                    
            </li>
            <li v-if="!tableData.length">
                <div class="nowithData" >
                <img src="/image/nullpic.jpg" alt="">
                <p>你还没有相关数据，<a style="color:#005dd1;" href="{{route('s_goods_lists')}}">写作</a>或<a style="color:#005dd1;" href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
                </div>
            </li>
            
        </ul>

        <div style="background: #FFFFFF;height: 177px;padding-top: 105px">

        <div v-if="page_count!=0" class="zmtdl_pageWrap">
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
    <div class="pgy_messageList" v-if="showstatus==1" >    
        <ul>
            <li v-for="t in tableData">
                <div class="pgy_litters">
                    <p><span class="yfb">已发布</span>@{{t.add_time|timeFormat}}发表:@{{t.name}}</p>
                    <div class="pgy_meihao"  v-if="t.imgs">
                        <div v-for="i in t.imgs.slice(0,4)" ><img :src="i.img_path" alt=""></div>
                    </div>
                    <div class="sendtimes" v-if="t.publish_time>0">发布时间：@{{t.publish_time|timeFormat}}</div>
                </div>
                
                <div class="pgy_largers">
                       <div class="pgy_editDelete">
                           <!-- <div>分享</div> -->
                           <div><a :href="'/Article/detail?id='+t.id" target="_blank">详情</a></div>
                       </div>
                </div>                        
            </li>
            <li v-if="!tableData.length">
                <div class="nowithData" >
                <img src="/image/nullpic.jpg" alt="">
                <p>你还没有相关数据，<a style="color:#005dd1;" href="{{route('s_goods_lists')}}">写作</a>或<a style="color:#005dd1;" href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
                </div>
            </li>
        </ul>
        <div style="background: #FFFFFF;height: 177px;padding-top: 105px">

        <div v-if="page_count!=0" class="zmtdl_pageWrap">
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
    <div class="pgy_messageList" v-if="showstatus==3">
        <ul>
            <li v-for="t in tableData">
                <div class="pgy_litters">
                    <p><span class="shz">审核中</span>@{{t.add_time|timeFormat}}发表:@{{t.name}}</p>
                    <div class="pgy_meihao" v-if="t.imgs">
                        <div v-for="i in t.imgs.slice(0,4)" ><img :src="i.img_path" alt=""></div>
                    </div>
                    <div class="sendtimes" v-if="t.verify_time>0">提交时间：@{{t.verify_time|timeFormat}}</div>
                </div>                

            </li>
            <li v-if="!tableData.length">
                <div class="nowithData" >
                <img src="/image/nullpic.jpg" alt="">
                <p>你还没有相关数据，<a style="color:#005dd1;" href="{{route('s_goods_lists')}}">写作</a>或<a style="color:#005dd1;" href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
                </div>
            </li>
            
           
        </ul> 
        <div style="background: #FFFFFF;height: 177px;padding-top: 105px">

        <div v-if="page_count!=0" class="zmtdl_pageWrap">
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
    <div class="pgy_messageList" v-if="showstatus==4" >
        <ul>
            <li v-for="t in tableData">
                <div class="pgy_litters">
                    <p><span class="cgx">草稿箱</span>@{{t.add_time|timeFormat}}发表:@{{t.name}}</p>
                    <div class="pgy_meihao"  v-if="t.imgs">
                        <div v-for="i in t.imgs.slice(0,4)" ><img :src="i.img_path" alt=""></div>
                    </div>
                    <div class="sendtimes" v-if="t.save_time>0">保存时间：@{{t.save_time|timeFormat}}</div>
                </div>
                
                <div class="pgy_largers" style="position:relative">
                       <div class="pgy_editDelete">
                           <div @click="contribute(showstatus,t.id)">投稿</div>
                           <div><a :href="'/Goods/editor?article_id='+t.id">继续写</a></div>
                        </div>
                       <div class="pgyz_iconDelete" @click="deleteArticle(showstatus,t.id)"></div>
                </div>                        
            </li>
            <li v-if="!tableData.length">
                <div class="nowithData" >
                <img src="/image/nullpic.jpg" alt="">
                <p>你还没有相关数据，<a style="color:#005dd1;" href="{{route('s_goods_lists')}}">写作</a>或<a style="color:#005dd1;" href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
                </div>
            </li>
        </ul>

        <div style="background: #ffffff;height: 177px;padding-top: 105px">

        <div v-if="page_count!=0" class="zmtdl_pageWrap">
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
    $(function(){
        Vue.filter("numberFormat",function(value){
            return Number(Number(value).toFixed(2)).toLocaleString('en-US');
        });

        Vue.filter("timeFormat",function(value){
            var date = new Date(value * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
            Y = date.getFullYear() + '-';
            M = (date.getMonth()+1 < 10 ? '0' + (date.getMonth()+1) : date.getMonth()+1) + '月';
            D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '日 ';
            h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
            m = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':';
            s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
         
            return M+D;
        });
        var app=new Vue({
            el:"#articleList",
            data:{
                tableData:[],
                name:"",
                imgs:[],
                shares:"",
                ordernums:"",
                commissions:"",
                showstatus:0,
                page:1,
                page_count:"",
                count1:0,
                count2:0,
                count3:0,
                count4:0,
                contrId:"",
                detelteShow:false,
                popupFlag:false,
                popupDiv:"",
                goalShowstatus:""

            },
            created:function(){
                
                // var _this=this;
                this.getTableData();
                var _this=this;
             
                        $.ajax({
                        url:'{{route('s_user_getMyArticleList')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            showstatus:0,
                            page:_this.page,
                            pagesize:10
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType:'json',
                        success:function(res){
                             if(res.status==200) {
                                 _this.count1=res.data.count;
                             }else if(res.status==403){
                                location.reload();
                            }
                            
                        }
                    });
                    var _this=this;
                        $.ajax({
                        url:'{{route('s_user_getMyArticleList')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            showstatus:1,
                            page:_this.page,
                            pagesize:10
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                             if(res.status==200) {
                                 _this.count2=res.data.count;
                             }
                           
                        }
                    });
                    var _this=this;
                        $.ajax({
                        url:'{{route('s_user_getMyArticleList')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            showstatus:3,
                            page:_this.page,
                            pagesize:10
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                             if(res.status==200) {
                                 _this.count3=res.data.count;
                             }
                             
                        }
                    });
                    $.ajax({
                        url:'{{route('s_user_getMyArticleList')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            showstatus:4,
                            page:_this.page,
                            pagesize:10
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                             if(res.status==200) {
                                 _this.count4=res.data.count;
                             }
                            
                        }
                    });
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
                    document.getElementById("articleList").style.display = "block";
                });
                // _this.showPopupDiv("shstatus");

                if(_this.getQueryVariable('status_id')==22){
                    _this.showstatus=3;
                    _this.tabs(3);
                   
                }

                
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
                tabs:function(index) { 
                        var _this=this;
                        if(index==0){
                            this.showstatus=0;
                            this.page=1;
                            this.getTableData();
                        }else if(index==1){
                            this.showstatus=1;
                            this.page=1;
                            this.getTableData();
                        }else if(index==3){
                            this.showstatus=3;
                            this.page=1;
                            this.getTableData();
                        }else if(index==4){
                            this.showstatus=4;
                            this.page=1;
                            this.getTableData();
                        }
                        
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
                    },
                toPage:function(page){
                        this.page=page;
                        this.getTableData();
                    },
                getTableData:function(){

                    var _this=this;
                        $.ajax({
                        url:'{{route('s_user_getMyArticleList')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            showstatus:_this.showstatus,
                            page:_this.page,
                            pagesize:10
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                             if(res.status==200) {
                                _this.tableData=res.data.data;
                                _this.page=res.data.page;
                                _this.page_count=res.data.page_count;
                             }
                            
                        }
                    });

                },
                deleteArticle:function(showstatus,ids){//草稿箱删除
                    var _this=this;
                    _this.goalShowstatus=showstatus;
                    _this.goalId=ids;
                    // _this.detelteShow=true;
                    _this.showPopupDiv("detelteShow");
                },
                contribute:function(showstatus,ids){
                    var _this=this;
                    _this.contrId=ids;
                    _this.goalShowstatus=showstatus;
                    $.ajax({
                        url:'{{route('s_user_pulishArticle')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            article_id:_this.contrId
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                 _this.showPopupDiv("shstatus");

                             }
                            
                        }
                    });
                    
                    // _this.goalShowstatus=showstatus;
                    // 
                },
                contrSend:function(showstatus){
                        var _this=this;
                        _this.popupDiv="";
                        _this.popupFlag=false;
                        _this.goalShowstatus=showstatus;
                       _this.tabs(_this.goalShowstatus);
                    if(_this.goalShowstatus==4){
                          if(_this.count4>0){                                       
                               _this.count4--; 
                               _this.count3++;                                     
                          }
                    }else{
                                     
                     }
                },
                sureDetelte:function(goalShowstatus,goalId){
                    var _this=this;
                    _this.goalShowstatus=goalShowstatus;
                    $.ajax({
                        url:'{{route('s_user_delArticle')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            article_id:goalId
                        },
                        "headers": {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        success:function(res){
                            if(res.status==200) {
                                 _this.tabs(_this.goalShowstatus);                                
                                 _this.popupDiv="";
                                _this.popupFlag=false;
                                 if(goalShowstatus==4){
                                     if(_this.count4>0){                                       
                                            _this.count4--;
                                            _this.count1--;
                                      
                                     }
                                 }else if(goalShowstatus==0){
                                  
                                     _this.count4--;
                                     _this.count1--;
                                   
                                 }
                             }
                            
                        }
                    });
                },
                closeDetelte:function(){
                    // var _this=this;
                    // _this.detelteShow=false;
                    var _this=this;
                        _this.popupDiv="";
                        _this.popupFlag=false;
                }
              
            }


        })

    })

    </script>
@endsection

