@extends('User.layout')


@section("title", "账户总览")


@section("css")
    {{--<link rel="stylesheet" href="{{ mix('css/normalize.css') }}">--}}

@endsection

@section("content")
<div id="CommissionSettlementDetail" style="display:none">
    <div class="commission_account" v-show="num==0">
        <ul class="ggzdl_admin_days">
            <li v-for="(item,index) in tabs"  :class="{on:num == index}"  @click="tab(item,index)" v-text="item.text"></li>
        </ul>
        <div class="commissionTime" style="float:left">
            <div class="commission_Aleft">
                时间
            </div>
            <div class="ggzdl_admin_dayPickerWrap" style="border-radius: 0px;">
                <div class="ggzdl_admin_dayPicker" style="border-right:1px solid #eaeaea;">
                    <input type="text" id="timeStart" readonly v-model="startDay" placeholder="请选择查询日期"><i class="ggzdl_iconCalendar"></i>
                </div>
                <div class="ggzdl_admin_dayPicker">
                    <input id="timeEnd" type="text" readonly v-model="endDay" placeholder="请选择查询日期"><i class="ggzdl_iconCalendar"></i>
                </div>
            </div>
        </div>
        <div class="commissionTypes">
            <div class="commission_Aleft" style="text-indent:6px;">
                交易状态
            </div> 
            <div class="commissionSelectacc"  @mouseenter="showDown" @mouseleave="hidenDown">
                <input style="height:43px;" type="text" placeholder="请选择" v-model="comTypevalue" readonly="readonly">
                <div class="commis_support_bank"><span></span>
                    <ul class="commis_bank_ul" v-show="showDownchild">
                        <li v-for="t in comType" @click="showDownchecked(t.typeOne,t.text)">@{{t.text}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div style="float:right;margin-right: 127px;">
            <div class="commission_Aleft" style="text-indent:6px;">
                交易类型
            </div>
            <div class="commissionSelectacc" @mouseenter="showDowns" @mouseleave="hidenDowns">
                <input style="height:43px;" type="text" placeholder="请选择" v-model="comStatusvalue"  readonly="readonly" >
                <div class="commis_support_bank"><span></span>
                    <ul class="commis_bank_ul" v-show="showDownchilds">                    
                        <li v-for="t in comStatus" @click="showDowncheckeds(t.statusOne,t.text1)">@{{t.text1}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <input class="ggzdl_admin_queryBtn" style="margin-top: 14px;clear: both;" type="button" value="查 询" @click="submit_get">
    </div>
    <div class="commission_account" v-show="num==1">
        <ul class="ggzdl_admin_days">
            <li v-for="(item,index) in tabs"  :class="{on:num == index}"  @click="tab(item,index)" v-text="item.text"></li>
        </ul>
        <div class="commissionTime" style="width:620px;">
            <div class="commission_Aleft">
                时间
            </div>
            <div class="ggzdl_admin_dayPickerWrap" style="float:left;border-radius: 0px;margin:0 20px 0 0">
                <div class="ggzdl_admin_dayPicker" style="border-right:1px solid #eaeaea;">
                    <input type="text" id="timeStart2" readonly v-model="startDay1" placeholder="请选择查询日期"><i class="ggzdl_iconCalendar"></i>
                </div>
                <div class="ggzdl_admin_dayPicker">
                    <input id="timeEnd2" type="text" readonly v-model="endDay1" placeholder="请选择查询日期"><i class="ggzdl_iconCalendar"></i>
                </div>
            </div>
            
        </div>
        <input class="ggzdl_admin_queryBtn" style="margin-top: 14px;clear: both;"  type="button" value="查 询" @click="submit_get1">

    </div>
    
    <div class="ggzdl_reportWrap" v-show="num==0">
        
        <div class="ggzdl_reportTableWrap">
            <table class="ggzdl_reportTable" cellpadding="0" cellspacing="0" v-if="tableData.length">
                <thead>
                <tr>
                    <td style="text-align:left; padding-left:40px;">交易流水</td>
                    <td style="width:20%">交易时间</td>
                    <td>交易类型</td>
                    <td>交易状态</td>
                    <td>交易金额</td>
                    <td>当前余额</td>
                </tr>
                </thead>
                <tbody>
                <tr v-for="t in tableData">
                    <td style="text-align:left; width:30%; padding-left:40px;">
                        @{{t.order_number}}
                    </td>
                    <td v-if="t.add_time">@{{t.add_time|timeFormat}}</td>
                    <td v-if="!t.add_time"></td>
                    <td>@{{t.tradeType}}</td>
                    <td>@{{t.tradeStatus}}</td>
                    <td style="text-align: right; padding-right: 25px;">@{{t.account|numberFormat}}</td>
                    <td style="text-align: right; padding-right: 25px;">@{{t.available_amount|numberFormat}}</td>
                </tr>
                </tbody>
            </table>
            <div class="nowithData" v-if="!tableData.length">
                <img src="/image/nullpic.jpg" alt="">
                <p>你还没有相关数据，<a style="color:#005dd1;" href="{{route('s_goods_lists')}}">写作</a>或<a style="color:#005dd1;" href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
            </div>
        </div>
     
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
    <div class="ggzdl_reportWrap" v-show="num==1">
    <div class="commission_yong">累计收益&nbsp;&nbsp;<span>@{{totalcommission|numberFormat}}</span></div>
        <div class="ggzdl_reportTableWrap">
            <table class="ggzdl_reportTable" id="ggzdl_reportTable" cellpadding="0" cellspacing="0" v-if="tableData.length">
                <thead>
                <tr>
                    <td style="text-align:left; padding-left:40px;">时间</td>
                    <td style="width:20%">内容标题</td>
                    <td>收益类型</td>
                    <td>收益金额</td>
                </tr>
                </thead>
                <tbody>
                <tr v-if="tableData.length" v-for="t in tableData">
                    <td style="text-align:left; width:30%; padding-left:40px;">
                        @{{t.day|timeFormat}}
                    </td>
                    <td><p style="text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 1;-webkit-box-orient: vertical;overflow: hidden;">@{{t.name}}</p></td>
                    <td>@{{t.source}}</td>
                    <td>@{{t.account|numberFormat}}</td>
                </tr>
                <!-- <tr v-if="!tableData.length">
                    <td colspan="6">暂无数据</td>
                    
                </tr> -->
                <!-- <tr v-if="!tableData.length">
                   <td colspan="6" style="padding:0;"> 
                       <div class="nowithData" >
                        <img src="/image/nullpic.jpg" alt="">
                        <p>你还没有相关数据，<a style="color:#005dd1;" href="{{route('s_goods_lists')}}">写作</a>或<a style="color:#005dd1;" href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
                        </div>
                    </td>
                </tr> -->

                </tbody>
            </table>
            <div class="nowithData" v-if="!tableData.length">
                <img src="/image/nullpic.jpg" alt="">
                <p>你还没有相关数据，<a style="color:#005dd1;" href="{{route('s_goods_lists')}}">写作</a>或<a style="color:#005dd1;" href="{{route('s_aricle_lists')}}">推广</a>都可以获得收益哦!</p>
            </div>
        </div>

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

@endsection



@section("script")
    <script type="text/javascript">
        $(function(){
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

        Vue.filter("timeFormat",function(value){
            var date = new Date(value * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
            Y = date.getFullYear() + '-';
            M = (date.getMonth()+1 < 10 ? '0' + (date.getMonth()+1) : date.getMonth()+1) + '-';
            D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
            h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
            m = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':';
            s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
            return Y+M+D+h+m+s;
        });
        Vue.filter("numberFormat1",function(s){
                var result = '', counter = 0;
                s = (s || 0).toString();
                for (var i = s.length - 1; i >= 0; i--) {
                    counter++;
                    result = s.charAt(i) + result;
                    if (!(counter % 3) && i != 0) { result = ',' + result; }
                }
                return result;

        });
            var app=new Vue({
                el:"#CommissionSettlementDetail",
                data:{
                    today:1,
                   tabs:[{today:1,text:"余额明细"},{today:0,text: "收益明细"}],
                   comType:[{typeOne:0,text:"请选择"},{typeOne:2,text:"收益结算"},{typeOne:1,text:"提现"},{typeOne:3,text:"稿费结算"}],
                   comStatus:[{statusOne:0,text1:"请选择"},{statusOne:1,text1:"交易成功"},{statusOne:2,text1:"交易失败"}],
                   num:0,
                   startDay:"",
                   endDay:"",
                   startDay1:"",
                   endDay1:"",
                   tableData:[],
                   page:1,
                   page_count:"",
                   showDownchild:false,
                   showDownchilds:false,
                   comTypevalue:"",
                   comStatusvalue:"",
                   tradeStatus:0,
                   tradeType:0,
                   totalcommission:"",
                   
                },
                created:function(){
                        var _this=this;
                        // this.getTableData1();
                       _this.getTableData1();
                        // _this.getTableData2();
                       
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
                        $('#timeStart').datetimepicker({
                        bootcssVer:3,
                        language:  'zh-CN',
                        format:"yyyy-mm-dd",
                        autoclose: true,
                        todayHighlight: true,
                        maxViewMode: 1,
                        minViewMode:1,
                        startView: 2,
                        minView: 2,
                        forceParse: 0,
                        todayBtn: true
                    }).on('changeDate', function (ev) {  
                        _this.startDay=$("#timeStart").val();
                        $("#timeEnd").datetimepicker('setStartDate',_this.startDay);
                        $("#timeStart").datetimepicker('hide'); 
                    });

                    $('#timeEnd').datetimepicker({
                        bootcssVer:3,
                        language:  'zh-CN',
                        format:"yyyy-mm-dd",
                        autoclose: true,
                        todayHighlight: true,
                        maxViewMode: 1,
                        minViewMode:1,
                        startView: 2,
                        minView: 2,
                        forceParse: 0,
                        todayBtn: true
                    }).on('changeDate', function (ev) {  
                        _this.endDay=$("#timeEnd").val();
                        $("#timeStart").datetimepicker('setEndDate',_this.endDay);
                        $("#timeEnd").datetimepicker('hide'); 
                    });


                     $('#timeStart2').datetimepicker({
                        bootcssVer:3,
                        language:  'zh-CN',
                        format:"yyyy-mm-dd",
                        autoclose: true,
                        todayHighlight: true,
                        maxViewMode: 1,
                        minViewMode:1,
                        startView: 2,
                        minView: 2,
                        forceParse: 0,
                        todayBtn: true
                    }).on('changeDate', function (ev) {  
                        _this.startDay1=$("#timeStart2").val();
                        $("#timeEnd2").datetimepicker('setStartDate',_this.startDay1);
                        $("#timeStart2").datetimepicker('hide'); 
                    });

                    $('#timeEnd2').datetimepicker({
                        bootcssVer:3,
                        language:  'zh-CN',
                        format:"yyyy-mm-dd",
                        autoclose: true,
                        todayHighlight: true,
                        maxViewMode: 1,
                        minViewMode:1,
                        startView: 2,
                        minView: 2,
                        forceParse: 0,
                        todayBtn: true
                    }).on('changeDate', function (ev) {  
                        _this.endDay1=$("#timeEnd2").val();
                        $("#timeStart2").datetimepicker('setEndDate',_this.endDay1);
                        $("#timeEnd2").datetimepicker('hide'); 
                    });

                    this.$nextTick(function() {
                        document.getElementById("CommissionSettlementDetail").style.display = "block";
                    });
                    
                },
                methods:{
                    tab:function(item,index) {
                        var _this=this;
                        _this.today=item.today;
                        _this.num = index;
                        if(_this.num==0){
                            _this.page=1;
                            _this.getTableData1();
                           
                        }else if(_this.num==1){
                             _this.page=1;
                            _this.getTableData2();
                           
                        }
                        
                    },
                    toPage:function(page){
                        var _this=this;
                        _this.page=page;
                        if(_this.num==0){
                            _this.getTableData1(); 
                           
                        }else if(_this.num==1){
                            _this.getTableData2();   
                           
                        }
                    },
                    submit_get:function(){                    
                        this.page=1;
                        this.getTableData1();
                    },
                    submit_get1:function(){                    
                    this.page=1;
                    this.getTableData2();
                },
                    getTableData1:function(){                       
                        var _this=this;
                        _this.tableData=[];
                        $.ajax({
                        url:'{{route('s_user_getAccountFlowingWater')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            // today:_this.today,
                            tradeType:_this.tradeType,
                            tradeStatus:_this.tradeStatus,
                            tradeStatus:_this.tradeStatus,
                            startDay:_this.startDay,
                            endDay:_this.endDay,
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
                                _this.tableData=res.data.data;
                                _this.page=res.data.page;
                                _this.page_count=res.data.page_count;
                            }else if(res.status==403){
                                location.reload();
                            }
                        }
                       
                    });
                    
                    },
                    getTableData2:function(){
                        var _this=this;
                        _this.tableData=[];
                         $.ajax({
                        url:'{{route('s_user_getAccountCommissionSettlementDetail')}}',
                        type:'POST',
                        async:true, 
                        data:{
                            // today:_this.today,
                            startDay:_this.startDay1,
                            endDay:_this.endDay1,
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
                                _this.totalcommission=res.data.totalcommission;
                                _this.page=res.data.page;
                                _this.page_count=res.data.page_count;

                            }
                        }
                         })
                    },
                    showDown:function(){
                        var _this=this;
                        _this.showDownchild=true;
                    },
                    hidenDown:function(){
                        var _this=this;
                        _this.showDownchild=false;
                    },
                    showDownchecked:function(id,text){
                        var _this=this;
                        _this.comTypevalue=text;
                        if(_this.comTypevalue=="请选择"){
                            _this.tradeType="";
                        }else{
                            _this.tradeType=id; 
                        }
      
                        _this.showDownchild=false;
                        
                    },
                    showDowns:function(){
                        var _this=this;
                        _this.showDownchilds=true;
                    },
                    hidenDowns:function(){
                        var _this=this;
                        _this.showDownchilds=false;
                    },
                    showDowncheckeds:function(id,text){
                        var _this=this;
                        _this.comStatusvalue=text;
                        if(_this.comStatusvalue=="请选择"){
                            _this.tradeStatus="";
                        }else{
                            _this.tradeStatus=id; 
                       }

                        _this.showDownchilds=false;
                    }
                }
                
            })
        })
    </script>
@endsection

