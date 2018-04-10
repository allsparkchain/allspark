<?php

namespace App\Http\Controllers\User;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * Class ProductController
 * @Controller(prefix="/User")
 * @Middleware("web")
 * @Middleware("auth")
 * @Middleware("authlogin")
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    /**
     * 账户总览页面
     * @Get("/accountInfo", as="s_user_accountInfo")
     */
    public function accountInfo() {
        //邀请码
        $code = $this->getRecommendCode();
        return view("User.accountInfo")->with('code',$code);
    }

    /**
     * 订单页面
     * @Get("/orderList", as="s_user_orderList")
     */
    public function orderList() {
        return view("User.orderList");
    }

    /**
     * 商品 数据页面1
     * @Get("/procData", as="s_user_procData")
     */
    public function procData() {
        return view("User.procData");
    }

    /**
     * 商品详情页面  数据页面2
     * @Get("/procDetail", as="s_user_procDetail")
     */
    public function procDetail() {
        return view("User.procDetail");
    }

    /**
     * 内容数据页面 数据页面3
     * @Get("/procContentDetail", as="s_user_procContentDetail")
     */
    public function procContentDetail() {
        return view("User.procContentDetail");
    }

    /**
     * 结算明细页面
     * @Get("/settledDetailFlow", as="s_user_settledDetailFlow")
     */
    public function settledDetailFlow() {
        return view("User.settledDetailFlow");
    }


    /**
     * 佣金提现页面1
     * @Get("/commissionInfo", as="s_user_commissionInfo")
     */
    public function commissionInfo() {
        return view("User.commissionInfo");
    }

    /**
     * 未结算佣金明细页面2
     * @Get("/unsettledCommissionFlow", as="s_user_unsettledCommissionFlow")
     */
    public function unsettledCommissionFlow() {
        return view("User.unsettledCommissionFlow");
    }

    /**
     * 余额流水页面3
     * @Get("/balanceFlow", as="s_user_balanceFlow")
     */
    public function balanceFlow() {
        return view("User.balanceFlow");
    }

    /**
     * 账户设置页面
     * @Get("/accountSetting", as="s_user_accountSetting")
     */
    public function accountSetting() {
        return view("User.accountSetting")->with('mobile',$this->getUserName());
    }


    //apiPost
    /**
     * 获取账户总览数据
     * @Post("/getAccountInfo", as="s_user_getAccountInfo")
     */
    public function getAccountInfo(Request $request) {
        try {
            $uid = $this->getUserId();



            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $type = $request->get("type",1);
            $showBy = $request->get("showBy",1);

            $post = Curl::post('/advertOwner/getAccountInfo', $arr = [
                'uid'=> $uid,
                'type' => $type,
                'showBy' => $showBy,
                'page' =>$page,
                'pagesize'=>$pagesize,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 下载订单数据
     * @Get("/DownLoadOrderlist", as="s_user_DownLoadOrderlist")
     */
    public function DownLoadOrderlist(Request $request) {

        try {
            $uid = $this->getUserId();


            $download = $request->get("download",false);

            //初始化数据

            $procname = $request->get("procname",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            $starttime = $request->get("starttime",'');
            $endtime = $request->get("endtime",'');
            $status = $request->get("status",'');


//            $uid = 24;


            if(strlen($starttime)>0){
                $starttime = strtotime(date('Y-m-d',strtotime($starttime)));
            }
            if(strlen($starttime)>0){
                $starttime = strtotime(date('Y-m-d',strtotime($starttime)));
            }
            if(strlen($endtime)>0){
                $endtime = strtotime(date('Y-m-d 23:59:59',strtotime($endtime)));
            }

            if(is_null($starttime) || strlen($starttime)<=0){
                $starttime = 0;
            }
            if(is_null($endtime)  || strlen($endtime)<=0){
                $endtime = 0;
            }


            $delivery = 0;
            if(strlen($status)>0){
                if($status == 'send'){
                    $delivery = 1;
                }
                if($status == 'notsend'){
                    $delivery = 2;
                }
            }

            if(is_null($status) || strlen($status)<=0){
                $delivery = 0;
            }
            $post = Curl::post('/advertOwner/getAdvertRelativeOrderList', $arr = [
                'advert_relative_id'=> $uid,
                'starttime' => $starttime,
                'endtime' => $endtime,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'procname'=>$procname,
                'status'=>$delivery,
            ]);
            if(isset($post['data']) && $post['data']['count'] >0){
                foreach ($post['data']['data'] as $key=>$value){
                    $info = json_decode($value['contents'],true);
                    $post['data']['data'][$key]['addresss'] = $info['userAddress'];
                }
            }


            if($download && count($post['data']['data'])>0){
                $data = $post['data']['data'];
                \Excel::create('订单数据',function($excel) use ($data){
                    $excel->sheet('score', function($sheet) use ($data){
                        $zarray=[];
                        foreach ($data as $key=> $value){
                            $zarray[$key]['订单号']         = $value['order_number'];
                            $zarray[$key]['时间']         =  date('Y-m-d H:i:s',$value['add_time']);
                            $zarray[$key]['产品名']  = $value['product_name'];
//                            if(is_array())
                            $zarray[$key]['地址']         = implode(',',$value['addresss']);
                            $zarray[$key]['数量']      = $value['number'];
                            $zarray[$key]['状态']         = $value['show_status'];
                        }
                        $sheet->fromArray($zarray);
                    });
                })->export('xls');
                exit;

            }else{
                redirect(Route('s_user_getOrderList'));
            }
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }
    /**
     * 获取账户订单列表
     * @Post("/getOrderList", as="s_user_getOrderList")
     */
    public function getOrderList(Request $request) {
        try {
            $uid = $this->getUserId();
            $procname = $request->get("procname",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            $starttime = $request->get("starttime",'');
            $endtime = $request->get("endtime",'');
            $status = $request->get("status",'');


//            $uid = 24;


            if(strlen($starttime)>0){
                $starttime = strtotime(date('Y-m-d',strtotime($starttime)));
            }

            if(strlen($endtime)>0){
                $endtime = strtotime(date('Y-m-d 23:59:59',strtotime($endtime)));
            }

            if(is_null($starttime)){
                $starttime = 0;
            }
            if(is_null($endtime)){
                $endtime = 0;
            }


            $delivery = 0;
            if(strlen($status)>0){
                if($status == 'send'){
                    $delivery = 1;
                }
                if($status == 'notsend'){
                    $delivery = 2;
                }
            }

            if(is_null($status)){
                $delivery = 0;
            }

//            var_dump(
//                ['advert_relative_id'=> $uid,
//                'starttime' => $starttime,
//                'endtime' => $endtime,
//                'page' =>$page,
//                'pagesize'=>$pagesize,
//                'procname'=>$procname,
//                'status'=>$delivery,
//            ]);die;

            $post = Curl::post('/advertOwner/getAdvertRelativeOrderList', $arr = [
                'advert_relative_id'=> $uid,
                'starttime' => $starttime,
                'endtime' => $endtime,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'procname'=>$procname,
                'status'=>$delivery,
            ]);
            if(isset($post['data']) && $post['data']['count'] >0){
                foreach ($post['data']['data'] as $key=>$value){
                    $info = json_decode($value['contents'],true);
                    $post['data']['data'][$key]['address'] = $info['userAddress'];
                }
            }

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 更改账户下的订单状态
     * @Post("/changeOrderStatus", as="s_user_changeOrderStatus")
     */
    public function changeOrderStatus(Request $request) {
        try {
            $uid = $this->getUserId();
            $prod_id = $request->get("prodIds",'');

            if(is_null($prod_id)){
                $prod_id = '';
            }

//            $uid = 24;


//            var_dump($prod_id2);die;
            if(is_array($prod_id)){
                $ss = implode(',',$prod_id);
            }

            $post = Curl::post('/advertOwner/changeAdvertRelativeOrderStatus', $arr = [
                'advert_relative_id'=> $uid,
                'product_order_id_arr'=>$ss,
                'status'=>2,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }



    /**
     * 下载渠道数据1
     * @Get("/DownLoadCommissionSettlement", as="s_user_DownLoadCommissionSettlement")
     */
    public function DownLoadCommissionSettlement(Request $request) {

        try {
            $uid = $this->getUserId();


            $download = $request->get("download",false);

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';

            $name = $request->get("name",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'add_time'){
                $order = json_encode(['times'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['account'=>$sort]);
            }
            if($type == 'commission'){
                $order = json_encode(['commission'=>$sort]);
            }
            if($type == 'nums'){
                $order = json_encode(['nums'=>$sort]);
            }


            $post = Curl::post('/smedia/getSmediaList', $arr = [
                'uid'=> $uid,
                'name' => $name,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);

            if($download && count($post['data']['data'])>0){

                $data = $post['data']['data'];
                \Excel::create('原创内容数据',function($excel) use ($data){
                    $excel->sheet('score', function($sheet) use ($data){
                        $zarray=[];
                        foreach ($data as $key=> $value){
                            $zarray[$key]['自媒体']         = $value['nickname'];
                            $zarray[$key]['接入时间']         =  date('Y-m-d H:i:s',$value['add_time']);
                            $zarray[$key]['媒体行业']         = $value['IndustryName'];
                            $zarray[$key]['成交次数']      = $value['nums'];
                            $zarray[$key]['交易总额']  = $value['account'];
                            $zarray[$key]['佣金']       = $value['commission'];

                        }
                        $sheet->fromArray($zarray);
                    });
                })->export('xls');
                exit;

            }
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取渠道数据1
     * @Post("/getCommissionSettlement", as="s_user_getCommissionSettlement")
     */
    public function getCommissionSettlement(Request $request) {
        try {
            $uid = $this->getUserId();

//            $uid = 137;

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';

            $name = $request->get("name",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'orderaccount'){
                $order = json_encode(['orderaccount'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['account'=>$sort]);
            }
            if($type == 'commission'){
                $order = json_encode(['commission'=>$sort]);
            }
            if($type == 'nums'){
                $order = json_encode(['nums'=>$sort]);
            }

            $post = Curl::post('/advertOwner/getAdvertRelativeProList', $arr = [
                'advert_relative_id'=> $uid,
                'name' => $name,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 商品详情页面  数据页面2
     * @Post("/getUserDetail", as="s_user_getUserDetail")
     */
    public function getUserDetail(Request $request) {
        try {
            $uid = $this->getUserId();

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';


            $pro_id = $request->get("pro_id",1);
            $name = $request->get("name",'');


//            $uid = 136;
//            $pro_id= 124;////136->124->41 137->123->37



            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'nums'){
                $order = json_encode(['nums'=>$sort]);
            }
            if($type == 'orderaccount'){
                $order = json_encode(['orderaccount'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['account'=>$sort]);
            }

            $post = Curl::post('/advertOwner/getAdvertRelativeProDetail', $arr = [
                'advert_relative_id'=> $uid,
                'pro_id' => $pro_id,
                'name' => $name,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 内容数据页面 数据页面3
     * @Post("/getArticleDetail", as="s_user_getArticleDetail")
     */
    public function getArticleDetail(Request $request) {
        try {
            $uid = $this->getUserId();

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';


            $spread_id = $request->get("spread_id",1);


//            $uid = 137;
////            $pass_uid= 9;//136->124->41 137->123->37
//            $spread_id=37;

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'numbers'){
                $order = json_encode(['number'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['orderaccount'=>$sort]);
            }
            if($type == 'commission'){
                $order = json_encode(['account'=>$sort]);
            }
//            if($type == 'nums'){
//                $order = json_encode(['nums'=>$sort]);
//            }

            $post = Curl::post('/advertOwner/getAdvertRelativeProOrderDetail', $arr = [
                'advert_relative_id'=> $uid,
//                'invite_uid' => $uid,
                'spread_id' => $spread_id,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取结算明细页面数据
     * @Post("/getSettledDetailFlow", as="s_user_getSettledDetailFlow")
     */
    public function getSettledDetailFlow(Request $request) {
        try {
            $uid = $this->getUserId();


//            $uid = 136;

            //时间过滤
            $starttime = $request->get("starttime",'');
            $endtime = $request->get("endtime",'');

            $name = $request->get("name",'');

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            if(strlen($starttime) >0){
                $starttime = strtotime(date('Y-m-d',strtotime($starttime)));
            }else{
                $starttime = 0;
            }

            if(strlen($endtime) >0){
                $endtime = strtotime(date('Y-m-d 23:59:59',strtotime($endtime)));
            }else{
                $endtime = 0;
            }

            $post = Curl::post('/advertOwner/settledDetailFlow', $arr = [
                'advert_relative_id'=> $uid,
                'starttime' =>$starttime,
                'endtime' =>$endtime,
                'name'=>$name,
                'page' =>$page,
                'pagesize'=>$pagesize,

            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取佣金提现页面数据1
     * @Post("/getUserWithdrawPage", as="s_user_getUserWithdrawPage")
     */
    public function getUserWithdrawPage(Request $request) {
        try {
            $uid = $this->getUserId();


//            $uid = 94;

            $post = Curl::post('/smedia/getSmediaUserWithdrawPage', $arr = [
                'advert_id'=> $uid,
            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取未结算佣金页面数据2
     * @Post("/getUserUnsettledPage", as="s_user_getUserUnsettledPage")
     */
    public function getUserUnsettledPage(Request $request) {
        try {
            $uid = $this->getUserId();


//            $uid = 94;

            //时间过滤
            $starttime = $request->get("starttime",'');
            $endtime = $request->get("endtime",'');

            $name = $request->get("name",'');

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            if(strlen($starttime) >0){
                $starttime = strtotime(date('Y-m-d',strtotime($starttime)));
            }else{
                $starttime = 0;
            }

            if(strlen($endtime) >0){
                $endtime = strtotime(date('Y-m-d 23:59:59',strtotime($endtime)));
            }else{
                $endtime = 0;
            }

            $post = Curl::post('/advertOwner/settledDetailFlow', $arr = [
                'advert_id'=> $uid,
                'starttime' =>$starttime,
                'endtime' =>$endtime,
                'name'=>$name,
                'page' =>$page,
                'pagesize'=>$pagesize,

            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取余额明细页面数据3
     * @Post("/getUserAccFLowPage", as="s_user_getUserAccFLowPage")
     */
    public function getUserAccFLowPage(Request $request) {
        try {
            $uid = $this->getUserId();


//            $uid = 94;

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            $post = Curl::post('/advert/getSmediaUserAccFLowPage', $arr = [
                'advert_id'=> $uid,
                'page' =>$page,
                'pagesize'=>$pagesize,
            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取账户设置数据
     * @Post("/getAccountSetting", as="s_user_getAccountSetting")
     */
    public function getAccountSetting(Request $request) {
        //绑定的银行卡  传递bid hidden  是否接口
        //获取银行卡
        try {
            $bank = Curl::post('/user/getBindBankCard', ['uid' => $this->getUserId()]);
//            $bid = $bank['data']['id'];
//            $banknumber = $bank['data']['banknumber'];
//            $realname = $bank['data']['realname'];
            $bank['data']['sendMobile'] = $this->getUserName();
            return new JsonResponse($bank);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }

    }

    /**
     * 获取银行卡列表数据
     * @Post("/getUserBankRelative", as="s_user_getUserBankRelative")
     */
    public function getUserBankRelative(Request $request) {
        try {

            $id = $request->get("id",0);
            $post = Curl::post('/user/getUserBankRelative',
                [
                    // 'id' => $id
                ]
            );
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 绑定银行卡
     * @Post("/bindBankCard", as="s_user_bindBankCard")
     */
    public function bindBankCard(Request $request) {
        try {
            $realname = $request->get("bind_realname",'');
            $idnumber = $request->get("bind_idnumber",'');
            $bind_banknumber = $request->get("bind_banknumber",'');
            $bind_mobile = $request->get("bind_mobile",'');
            $bank_relative = $request->get("bank_relative",0);

            if(strlen($realname)<=0 || strlen($idnumber)<=0 || strlen($bind_banknumber)<=0 || strlen($bind_mobile)<=0){
                return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
            }
            $post = Curl::post('/user/bindBankCard', [
                'realname' => $realname,
                'idnumber' => $idnumber,
                'banknumber' => $bind_banknumber,
                'mobile' => $bind_mobile,
                'bank_relative'=>$bank_relative,
                'uid' => $this->getUserId()
            ]);
            $aa['ids'] = $post['data']['id'];
            $aa['banknumbs'] = $post['data']['banknumber'];
            $aa['realname'] = $post['data']['realname'];
            $post['data'] = $aa;
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }

    }

    /**
     * 解绑银行卡
     * @Post("/unbindBankCard", as="s_user_unbindBankCard")
     */
    public function unbindBankCard(Request $request) {
        try {
            $session = \Session::get("unbind");
            $session = json_decode($session, true);
            if (!isset($session['order_number'])) {
                return new JsonResponse(['status'=>201,'message'=>'数据错误']);
            }else{

                $code = $request->get("code",0);
                $mobile = $request->get("mobile");
                $mobile = $this->getUserName();
                if(strlen($code)<=0 || !is_numeric($code)){
                    return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
                }
                $post = Curl::post('/utils/message/verificationSms', [
                    'mobile' => $mobile,
                    'code' => $code,
                    'type' => 3,
                    'order_number' => Arr::get($session, 'order_number', ''),
                ]);
                if(!is_null($post['data'])){
                    $bid = $request->get("bid",-1);

                    if(intval($bid) && $bid > 0){
                        $post = Curl::post('/user/unbindBankCard', [
                            'bid' => $bid,
                        ]);
                        return new JsonResponse($post);
                    }
                }

            }
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 切换银行卡
     * @Post("/changeBankCard", as="s_user_changeBankCard")
     */
    public function changeBankCard(Request $request) {
        try {
            $session = \Session::get("change");
            $session = json_decode($session, true);
            if (!isset($session['order_number'])) {

            }else{
                $code = $request->get("code",0);
                if(strlen($code)<=0 || !is_numeric($code)){
                    return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
                }
                $mobile = $request->get("mobile");
                $mobile = $this->getUserName();
                $post = Curl::post('/utils/message/verificationSms', [
                    'mobile' => $mobile,
                    'code' => $code,
                    'type' => 3,
                    'order_number' => Arr::get($session, 'order_number', ''),
                ]);
                if(!is_null($post['data'])){
                    $bid = $request->get("bid",-1);

                    if(intval($bid) && $bid > 0){
                        $post = Curl::post('/user/unbindBankCard', [
                            'bid' => $bid,
                        ]);
                        return new JsonResponse($post);
                    }
                }else{
//                    var_dump($post);

                }

            }
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }
    /**
     * 发送银行卡验证码
     * @Post("/sendBankSms", as="s_user_sendBankSms")
     */
    public function sendBankSms(Request $request) {
        try {
            $mobile = $this->getUserName();
            $type = $request->get("type",-1);
            if(intval($type) && $type >0){
                $post = Curl::post('/utils/message/createMsg', [
                    'mobile' => $mobile,
                    'type' => 3
                ]);
                $data = $post['data'];
                unset($post['data']);

                if($type == 1){
                    //解绑
                    $name = 'unbind';
                }else{
                    //切换
                    $name = 'change';
                }
                \Session::put($name, json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
                return new JsonResponse($post);
            }else{
                return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }catch (\Exception $e){
            return new JsonResponse([
                "status"=>477,
                "message"=>'输入错误',
            ]);
        }
    }

    /**
     * 修改密码
     * @Post("/changePassword", as="s_user_changePassword")
     */
    public function changePassword(Request $request) {
        try {
            $oldpas = $request->get("oldpas",'');
            $newpas = $request->get("newpas",'');
            $newpas2 = $request->get("newpas2",'');

            if(strlen($oldpas) <=0 || strlen($newpas) <=0 || strlen($newpas2) <=0){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }

            $post = Curl::post('/user/editPassword', [
                'username' => $this->getUserName(),
                'oldpasswd' => $oldpas,
                'passwd' => $newpas,
                'passconfirm' => $newpas2,
            ]);
            return new JsonResponse($post);
        }catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
        catch (\Exception $e){
            return new JsonResponse([
                "status"=>477,
                "message"=>'输入错误',
            ]);
        }
    }

    /**
     * 发送申请提现短信
     * @Post("/sendDrawSms", as="s_sms_sendDrawSms")
     */
    public function sendDrawSms(Request $request) {

        try {
            $mobile = $this->getUserName();
            $type = $request->get("type",0);

            if($type <=0 || !is_numeric($type)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }

            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => $type
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("DrawSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 提现申请验证短信
     * @Post("/validatorDrawSms", as="s_sms_validatorDrawSms")
     */
    public function validatorDrawSms(Request $request) {
        try {
            $mobile = $this->getUserName();
            $code = $request->get("code");
            if($code <=0 || !is_numeric($code)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("DrawSms");
            $session = json_decode($session, true);
            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 4,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);
            \Session::put("validatorDrawSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 申请提现
     * @Post("/withdrawalApplication", as="s_draw_withdrawalApplication")
     */
    public function withdrawalApplication(Request $request) {
        try {
            $account = $request->get("account",0);
            $bank_id = $request->get("bank_id",0);
            if($account <=0 || !is_numeric($account) || $bank_id <=0 || !is_numeric($bank_id)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("validatorDrawSms");
            $session = json_decode($session, true);



            $post = Curl::post('/user/withdrawalApplication', [
                'uid' => $this->getUserId(),
                'bank_id' => $bank_id,
                'account' => $account,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);


            $data = $post['data'];
            //unset($post['data']);
            return new JsonResponse($data);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 发送实名短信
     * @Post("/sendAuthSms", as="s_sms_sendAuthSms")
     */
    public function sendAuthSms(Request $request) {

        try {
            $mobile = $this->getUserName();
//            $type = $request->get("type",0);
            $type = 5;
            if($type <=0 || !is_numeric($type)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => $type
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("sendAuth", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 实名验证短信
     * @Post("/validatorAuthSms", as="s_sms_validatorAuthSms")
     */
    public function validatorAuthSms(Request $request) {
        try {
            $mobile = $this->getUserName();
            $code = $request->get("code");
            if($code <=0 || !is_numeric($code)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("sendAuth");
            $session = json_decode($session, true);
            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 5,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);
            \Session::put("validatorAuthSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 实名申请验证
     * @Post("/authVerify", as="s_auth_authVerify")
     */
    public function authVerify(Request $request) {
        try {
            $idno = $request->get("idno",'');
            $realname = $request->get("realname",'');
            if(strlen($idno)<15 || mb_strlen($realname)<2){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("validatorAuthSms");
            $session = json_decode($session, true);


            $post = Curl::post('/user/userAuthentication', [
                'uid' => $this->getUserId(),
                'mobile' => $this->getUserName(),
                'idno' => $idno,
                'realname' => $realname,
                'order_number' => Arr::get($session, 'order_number', ''),
                'verify_type'=>5
            ]);


            $post = Curl::post('/user/userBindAuthentication', [
                'id' => $this->getUserId(),
                'order_number' => $post['data'],
            ]);

            if($post['data']){
                return new JsonResponse([
                    "status" => '200',
                    "message" => '绑定成功',
                ]);
            }
            return new JsonResponse([
                "status" => '202',
                "message" => '绑定失败',
            ]);

//            $data = $post['data'];
            //unset($post['data']);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取实名信息
     * @Post("/getAuthInfo", as="s_user_getAuthInfo")
     */
    public function getAuthInfo(Request $request) {
        try {

            $post = Curl::post('/user/getAuthInfo', [
                'uid' => $this->getUserId(),
            ]);


//            $data = $post['data'];
            //unset($post['data']);
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    private function getUserId() {
        return \Auth::getUser()->getAuthIdentifier();

    }

    private function getUserName() {
        return \Auth::getUser()->getUserMobile();
    }

    private function getRecommendCode() {
        return \Auth::getUser()->getRecommendCode();
    }
}