<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>激活验证码</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no, email=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <!--强制竖屏-->
    <meta name="screen-orientation" content="portrait">
    <!--点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/commodity.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/share.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/weui.min.css') }}">
</head>
<body style="background:#c84743" >
    <div id="activeCode" style="display:none">    
        <div class="model_message"  v-show="successMessage==1">
                <div id="messageModel">
                    <div class="messagetitle">
                        <img src="/images/successM.png" alt="">
                        <p>验证成功</p>
                        <p>@{{commodity_name}}</p>
                    </div>
                    <div class="closeSure" @click="closeSure">确认</div>    
                </div>
        </div>

        <div class="activeCode clearfloat" v-show="show_modal==1">
                <div class="divOption" >
                        <!-- 显示每一个框 -->
                        <div class="model_view" >
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                        <!-- 循环数据 -->
                        <div class="model_data" @click="modelClick">
                            <div v-for="item in modelData" >@{{item}}</div>
                        </div>
                        <!-- 隐藏的输入框 -->
                        <input type="text" id="model_input"  v-model="modelValue" maxlength="8"  @input="modelKey" @keydown="submit($event)" style="text-indent: -999em;width:100%;margin-left: -24%;">
                        <div class="commissionSelectacc"    v-if='Nulldata!=""'>
                                <div class="input" id="showPicker" @click="checkPicker">@{{comTypevalues}}</div>
                                <span></span>
                        </div>
                        <div class="model_yanz" style="background:#c84743;" @click="modelInformation">验证并使用</div>
                        <div class="model_error" v-show="successMessage==2">@{{messageModels}}</div>
                </div>
        </div>
        <div class="divAddtype" v-show="show_modal==2">
                <div class="divOption_last">
                    <!-- 信息   删除 -->
                    <div class="commissionSelectaccs"  >
                        <div class="input"  readonly="readonly" @click="showDownsTwo">@{{comTypevaluesTwo}}</div>
                        <div class="commis_support_bank"><span></span>
                            <ul class="commis_bank_ul" v-show="showDownchildsTwo">
                                <li @click="showDowncheckeds(0,'全部')">全部</li>
                                <li v-for="item in informationDatas" @click="showDowncheckeds(item.id,item.name)">@{{item.name}}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="model_back" @click="goback"><a href="javascript:void(0)">返回</a></div>
                    <div class="activeData">
                        激活数量&nbsp;&nbsp;<span>@{{total}}</span>
                    </div>
                    <!-- table -->
                    <div class="ggzdl_reportTableWrap" style="padding:10px 20px 0px 20px;box-sizing: border-box;">
                        <table class="active_reportTable" cellpadding="0" cellspacing="0" >
                            <thead>
                            <tr>
                                <td>时间</td>
                                <td>@{{comTypevalues}}</td>
                                <td>激活</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="activeDetail.length" v-for="t in activeDetail" style="margin-top:20px;">
                                <td>@{{t.date|timeFormat}}</td>
                                <td>@{{t.name}}</td>
                                <td>@{{t.activity_num}}</td>
                            </tr>
                            <tr v-if="!activeDetail.length">
                                    <td colspan="3">暂无数据</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                    <div class="div_pagination">
                        <div id="app" style="margin:0 auto" v-if="page_count!=0">
                            <navigation :pages="page_count" :current.sync="page" @navpage="msgListView" style="text-align: center;"></navigation>
                               
                        </div>
                    </div>
                </div>
                <div class="addBottom" @click="activeDatas" v-show="show_modal==1">激活数据</div>
    </div>
    
<script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="/js/vue.js"></script>
<script type="text/javascript" src="/js/qrcode.min.js"></script>
<script type="text/javascript" src="/js/pagination.js"></script>
<script src="/js/weui.min.js" type="text/javascript"></script>
<script src="/js/bootstrap.min.js" type="text/javascript"></script>

<script>
     $(function () {
        Vue.filter("timeFormat",function(value){
                // var date = new Date(value * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
                var date = new Date();
                Y = date.getFullYear() + '-';
                M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '月';
                D = date.getDate() <10 ? '0'+date.getDate()+ '日' : +date.getDate()+ '日';
                h = date.getHours() + ':';
                m = date.getMinutes() + ':';
                s = date.getSeconds() < 10 ? '0'+ date.getSeconds() : date.getSeconds();
                return M+D;
            });
            var app=new Vue({ 
                    el:'#activeCode',
                    data:{
                        showDownchilds:false,
                        comTypevalues:"请选择",
                        modelValue:"",
                        modelData:[],
                        informationData:[],
                        tradeStatus:0,
                        parentId:"",
                        informationDatas:[],
                        activeDetail:[],
                        total:0,
                        page_count:"",
                        page:1,
                        showDownchildsTwo:false,
                        show_modal:1,
                        activeDetail:[],
                        comTypevaluesTwo:"全部",
                        successMessage:0,
                        messageModels:"",
                        commodity_name:"",
                        tradeStatusTwo:0,
                        tradeSecond:0,
                        Nulldata:[],
                        activeIds:0,
                        getId:""
                        
                    },
                    created:function(){
                        var _this=this;
                        _this.getTypes();
                        _this.getId=_this.getQueryVariable('advert_id');
                        // _this.getType1();
                    },
                    mounted:function(){
                        var _this=this;
                        this.$nextTick(function() {
                            document.getElementById("activeCode").style.display = "block";
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
                            msgListView(curPage){
                                //根据当前页获取数据
                                var _this=this;
                                _this.page = curPage;
                                _this.getTableData();
                            },
                            showDowns:function(){
                            var _this=this;                                    
                                    if(_this.showDownchilds==false){
                                        _this.showDownchilds=true;                                    
                                    }else{
                                        _this.showDownchilds=false;
                                    }
                            },
                            showDownsTwo:function(){
                                    var _this=this;
                                    
                                    if(_this.showDownchildsTwo==false){
                                        _this.showDownchildsTwo=true;
                                    
                                    }else{
                                        _this.showDownchildsTwo=false;
                                    }
                            },
                            modelFocus:function(){
                                    var _this=this;
                                    $(this).trigger('blur');
                              
                            },
                            modelClick:function(){
                                    var _this=this;
                                    // _this.modelFocus();
                                    // _this.$refs.input.focus();
                            },
                            submit(e){
                                    var _this=this;
                                    if(e.keyCode === 8){
                                            var i = _this.modelData.length;
                                        while(i--){
                                            
                                            _this.modelData.splice(i,1);
                                        }

                                    }
                                },
                                modelKey:function(){
                                    var _this=this;
                                if(_this.modelValue.length<9){
                                        if(_this.modelData.length<8){
                                        _this.modelData= _this.modelValue.split('');
                                        // _this.modelInformation();
                                        }else{
                                            
                                        }
                                }else{
                                    
                                }
                            },
                                // 获取分类类型
                            getTypes:function(){
                                    var _this=this;
                                    $.ajax({
                                        url:'{{route('s_item_list')}}',
                                        type:'POST',
                                        async:true,
                                        data:{
                                            // avert_id:100,
                                            avert_uid:_this.getQueryVariable('advert_id'),
                                            level:2
                                        },
                                        "headers": {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        dataType:'json',
                                        success:function(res){
                                            if(res.status==200) {
                                                _this.newInfo=[];
                                                _this.informationData=res.data.children;
                                                _this.Nulldata=res.data;
                                                _this.tradeStatus=res.data.id;
                                                if( _this.Nulldata!=""){
                                                    _this.informationData.forEach(function(v,i,a){
                                                    _this.newInfo.push({
                                                            'label':v.name,
                                                            'value':v.id+','+v.advert_uid
                                                        });
                                                    });
                                                }else{

                                                }
                                               
                                            
                                            }else{

                                            }
                                        }
                                    });
                                    
                            }, 
                                showDownchecked:function(parentId,id,text){
                                    var _this=this;
                                    _this.comTypevalues=text;
                                    _this.parentId=parentId;
                                    _this.tradeStatus=id; 
                                    _this.showDowns();
                                },
                                showDowncheckeds:function(id,text){
                                    var _this=this;
                                    _this.comTypevaluesTwo=text;
                                    _this.activeIds=id; 
                                    _this.showDownsTwo();
                                    _this.activeTotal();
                                    _this.getTableData();

                                },
                                modelInformation:function(){
                                    var _this=this;
                                    
                                    if(_this.Nulldata==""){//如果选择没有返回值执行code码                                    
                                            if(_this.modelValue.length==8 ){
                                                $.ajax({
                                                        url:'{{route('s_verify_code')}}',
                                                        type:'POST',
                                                        async:true,
                                                        data:{
                                                            // advert_id:100,
                                                            advert_id:_this.getQueryVariable('advert_id'),
                                                            code:_this.modelValue
                                                        },
                                                        "headers": {
                                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                        },
                                                        dataType:'json',
                                                        success:function(res){
                                                            if(res.status==200) {
                                                                _this.successMessage=1;
                                                                _this.commodity_name=res.data.message;
                                                                $.ajax({
                                                                        url:'{{route('s_add_record')}}',
                                                                        type:'POST',
                                                                        async:true,
                                                                        data:{
                                                                            // advert_id:100,
                                                                            advert_id:_this.getQueryVariable('advert_id'),
                                                                            first:_this.tradeStatus,
                                                                            second:_this.tradeSecond
                                                                        },
                                                                        "headers": {
                                                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                        },
                                                                        dataType:'json',
                                                                        success:function(res){
                                                                            if(res.status==200) {
                                                                                
                                                                            }else{
                                                                                // alert(res.message);
                                                                            }
                                                                        }
                                                                });
                                                            }else{
                                                                _this.successMessage=2;
                                                                _this.messageModels=res.message;
                                                                setTimeout(function(){
                                                                    _this.messageModels="";
                                                                }, 3000);
                                                                
                                                            }
                                                        }
                                                });
                                            }
                                             
                                    }else{//如果选择有返回值
                                        if(_this.comTypevalues!=""){//如果选择了去执行code码
                                            if(_this.modelValue.length==0){
                                                    _this.successMessage=2;
                                                    _this.messageModels="请输入激活码";
                                                setTimeout(function(){
                                                    _this.messageModels="";
                                                }, 3000);
                                            }else{
                                                if(_this.modelValue.length==8 ){
                                                    $.ajax({
                                                        url:'{{route('s_verify_code')}}',
                                                        type:'POST',
                                                        async:true,
                                                        data:{
                                                            // advert_id:100,
                                                            advert_id:_this.getQueryVariable('advert_id'),
                                                            code:_this.modelValue
                                                        },
                                                        "headers": {
                                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                        },
                                                        dataType:'json',
                                                        success:function(res){
                                                            if(res.status==200) {
                                                                _this.successMessage=1;
                                                                _this.commodity_name=res.data.message;
                                                                
                                                                $.ajax({
                                                                        url:'{{route('s_add_record')}}',
                                                                        type:'POST',
                                                                        async:true,
                                                                        data:{
                                                                            // advert_id:100,
                                                                            advert_id:_this.getQueryVariable('advert_id'),
                                                                            first:_this.tradeStatus,
                                                                            second:_this.tradeSecond
                                                                        },
                                                                        "headers": {
                                                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                                        },
                                                                        dataType:'json',
                                                                        success:function(res){
                                                                            if(res.status==200) {

                                                                            }else{
                                                                                _this.successMessage=2;
                                                                                _this.messageModels=res.message;
                                                                                setTimeout(function(){
                                                                                    _this.messageModels="";
                                                                                }, 3000);
                                                                            }
                                                                        }
                                                                 });
                                                             }else{
                                                                _this.successMessage=2;
                                                                _this.messageModels=res.message;
                                                                setTimeout(function(){
                                                                     _this.messageModels="";
                                                                }, 3000);
                                                            }
                                                        }
                                                    }); 
                                            }
                                            }
                                            
                                        }else{//否则没有选择弹出请选择
                                                _this.successMessage=2;
                                                _this.messageModels="请选择分类";   
                                            setTimeout(function(){
                                                _this.messageModels="";             
                                            }, 3000);
                                           
                                            

                                        }

                                        
                                    }


                                },
                                goback:function(){
                                    var _this=this;
                                    _this.show_modal=1;
                                },
                                // 激活数据分类类型
                                getType1:function(){
                                    var _this=this;
                                    $.ajax({
                                        url:'{{route('s_item_list')}}',
                                        type:'POST',
                                        async:true,
                                        data:{
                                        level:2,
                                        // avert_id:100,
                                        avert_uid:_this.getQueryVariable('advert_id'),
                                        },
                                        "headers": {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        dataType:'json',
                                        success:function(res){
                                            if(res.status==200) {
                                                
                                                _this.informationDatas=res.data.children;
                                                _this.comTypevalues=res.data.name;                                               
                                                 if(res.message=="nodata"){
                                                    // _this.tradeStatusTwo=0;
                                                   //alert("aaaaa");
                                                     _this.activeTotal();
                                                     _this.getTableData();

                                                 }else{
                                                    _this.tradeStatusTwo=res.data.id;
                                                    _this.activeTotal();
                                                    _this.getTableData();
                                                 }

                                                
                                            }else{

                                            }
                                        }
                                    });
                                },
                                // 获取激活总数量
                            activeTotal:function(){
                                    var _this=this;
                                    $.ajax({
                                        url:'{{route('s_activity_total')}}',
                                        type:'POST',
                                        async:true,
                                        data:{
                                        first:_this.tradeStatusTwo,
                                        second:_this.activeIds,
                                        // avert_id:100
                                        avert_id:_this.getQueryVariable('advert_id'),
                                        },
                                        "headers": {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        dataType:'json',
                                        success:function(res){
                                            if(res.status==200) {
                                                _this.total=res.data.total;
                                            }else{

                                            }
                                        }
                                    });
                            },
                            getTableData:function(){
                                var _this=this;
                                    $.ajax({
                                        url:'{{route('s_activity')}}',
                                        type:'POST',
                                        async:true,
                                        data:{
                                            first:_this.tradeStatusTwo,
                                            second:_this.activeIds,
                                            page:_this.page,
                                            pagesize:10,
                                            // avert_id:100
                                            avert_id:_this.getQueryVariable('advert_id'),
                                        },
                                        "headers": {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        dataType:'json',
                                        success:function(res){
                                            if(res.status==200) {
                                                _this.activeDetail=res.data.detail;
                                                _this.page=res.data.page;
                                                _this.page_count=res.data.page_count;
                                            }
                                        }
                                    });
                            },
                                activeDatas:function(){
                                    var _this=this;
                                    _this.show_modal=2;
                                    _this.getType1();
                                },
                                closeSure:function(){
                                    var _this=this;
                                    _this.successMessage=0;
                                },
                                checkPicker: function () {
                                    var _this=this;
                                    _this.Values=[];
                                    weui.picker(_this.newInfo, {
                                        onConfirm: function (result) {
                                            _this.comTypevalues=result[0].label;
                                            _this.Values=result[0].value.split(",");
                                            _this.parentId=_this.Values[1];
                                            _this.tradeSecond=_this.Values[0];
                                        }
                                    });
                                }
                                
                    }
                })
     })
</script>
</body>
</html>
