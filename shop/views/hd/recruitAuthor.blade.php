
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>招募写手</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no, email=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/flexible.js"></script>
    <link rel="stylesheet" href="{{ mix('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ mix('css/recruit.css') }}">
</head>
<body>
<div id="shareIndex" class="recruitReg" style="display: none;">
    <div class="avatorWrap" @click="chooseImage">
        <img :src="localImg" alt="">
        <i class="avatorIcon"></i>
    </div>
    <div class="regGender">
        <span :class="gender=='1'?'on':''" @click="chooseGender('1')">男</span><span :class="gender=='2'?'on':''" @click="chooseGender('2')">女</span>
    </div>
    <ul class="regUl">
        <li>
            <label for="">姓名</label>
            <input type="text" placeholder="请输入您的姓名" v-model="name">
        </li>
        <li>
            <label for="">年龄</label>
            <input type="number" placeholder="请输入您的年龄" v-model="age">
        </li>
        <li>
            <label for="">微信号</label>
            <input type="text" placeholder="请输入您的微信号" v-model="wx_num">
        </li>
        <li>
            <label for="">邮箱地址</label>
            <input type="text" placeholder="请输入您的邮箱地址" v-model="email">
        </li>
        <li>
            <label for="">作品链接</label>
            <input type="text" placeholder="请输入您的作品链接" v-model="works_link">
        </li>
    </ul>
    <div class="regJoin" @click="joinNow" :disabled="disabled">@{{btnValue}}</div>
    <div class="regError">@{{errMsg}}</div>
    <img class="regWant" src="../images/recruit03.png" alt="">
</div>
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/vue.js"></script>
    <script src="{{config('params.httpType')}}://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
    <script>
      $(function () {  
        wx.config(<?php echo $app->jssdk->buildConfig(array('chooseImage','uploadImage','downloadImage'), false) ?>);
        var app=new Vue({ 
            el:'#shareIndex',
            data:{
                localImg:"../images/recruit01.png",
                serverImg:"",
                gender:"",//1男,2女
                name:"",
                age:"",
                wx_num:"",
                email:"",
                works_link:"",
                errMsg:"",
                disabled:false,
                btnValue:"立即加入"
            },  
            created:function(){
                var _this=this;
                
            },
            mounted:function(){
                var _this=this;
                this.$nextTick(function() {
                    document.getElementById("shareIndex").style.display = "block";
                });
            },
            methods:{
                chooseImage:function(){
                    var _this=this;
                    wx.chooseImage({
                        count: 1, // 默认9
                        sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                        sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                        success: function (res) {
                            var localIds = res.localIds;
                            _this.localImg=res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                            wx.uploadImage({
                                localId: ''+localIds, // 需要上传的图片的本地ID，由chooseImage接口获得
                                isShowProgressTips: 1, // 默认为1，显示进度提示
                                success: function (res) {
                                    _this.serverImg=res.serverId; // 返回图片的服务器端ID
                                }
                            });
                        }
                    });

                },
                chooseGender:function(g){
                    var _this=this;
                    _this.gender=g;
                },
                checkEmail:function(){
                    var _this=this;
                    var re = /^[A-Za-z\d]+([-_.][A-Za-z\d]+)*@([A-Za-z\d]+[-.])+[A-Za-z\d]{2,4}$/; 
                    if (re.test(_this.email)) {
                        return true;
                    } else {
                        _this.errMsg="邮箱地址格式错误";
                        setTimeout(function(){
                            _this.errMsg="";
                        }, 3000);
                        return false;
                    }
                },
                joinNow:function(){
                    var _this=this;
                    if(!_this.gender){
                        _this.errMsg="请选择您的性别";
                        setTimeout(function(){
                            _this.errMsg="";
                        }, 3000);
                    }else if(!_this.name){
                        _this.errMsg="请输入您的姓名";
                        setTimeout(function(){
                            _this.errMsg="";
                        }, 3000);
                    }else if(!_this.age){
                        _this.errMsg="请输入您的年龄";
                        setTimeout(function(){
                            _this.errMsg="";
                        }, 3000);
                    }else if(!_this.wx_num){
                        _this.errMsg="请输入您的微信号";
                        setTimeout(function(){
                            _this.errMsg="";
                        }, 3000);
                    }else if(!_this.email){
                        _this.errMsg="请输入您的邮箱地址";
                        setTimeout(function(){
                            _this.errMsg="";
                        }, 3000);
                    }else if(!_this.checkEmail()){
                        return false;
                    }else{
                        $.ajax({
                            url:'{{route('s_recruitAuthorAdd')}}',
                            type:'POST',
                            async:true,
                            data:{
                                gender:_this.gender,
                                name:_this.name,
                                age:_this.age,
                                photo:_this.serverImg,
                                wx_num:_this.wx_num,
                                email:_this.email,
                                works_link:_this.works_link
                            },
                            "headers": {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            beforeSend:function(XMLHttpRequest){
                                var _this=this;
                                _this.disabled=true;
                                _this.btnValue="提交中...";
                            },
                            success:function(res){
                                if (res.status == 200) {
                                    location.href="{{route('s_recruitAuthorComplete')}}";
                                }else{
                                    _this.errMsg=res.message;
                                    setTimeout(function(){
                                        _this.errMsg="";
                                    }, 3000); 
                                }
                            },
                            complete:function(XMLHttpRequest, textStatus){
                                _this.disabled=false;
                                _this.btnValue="立即加入";
                            }
                        });
                    }
                }
            }
        })
     })
    </script>
</body>
</html>
