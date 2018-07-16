<?php

namespace App\Http\Controllers\User;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductController
 * @Controller(prefix="/User")
 * @Middleware("web")
 * @Middleware("auth")
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
 
    /**
     * 账户总览页面
     * @Get("/accountInfo", as="s_user_accountInfo")
     */
    public function accountInfo(Request $request) {

        $jzstate = \Session:: get('pc_jzstate');
        \Session::forget("pc_jzstate");
        \Cache::forget("wxdl_".$jzstate);

        $uid = $this->getUserId();
        $cacheKey = $this->getCacheKye('accountInfo');
        if (! $cacheData = \Cache::get($cacheKey)) {//
            $not_settle_money = 0.00;
            $canWithDraw = 0.00;
            $today_nums = 0;
            $all_commission_profit = 0.00;
            try{
                $user_accountInfo = Curl::post('/user/userAccountPage',['uid'=>$uid]);
                $user_accountInfo = $user_accountInfo['data'];
            }catch (ApiException $e){
                $user_accountInfo = [];


            }
            if(count($user_accountInfo) > 0 && isset($user_accountInfo['data']) && count($user_accountInfo['data'])>0){
                $canWithDraw = $user_accountInfo['data']['available_amount'];
                $not_settle_money = $user_accountInfo['data']['unsettled_amount'];
                $today_nums = $user_accountInfo['data']['today_nums'];
                $all_commission_profit = $user_accountInfo['data']['all_commission_profit'];

                $user_accountInfo = $user_accountInfo['data'];
            }

            try{
                $records = Curl::post('/user/getUserCommission_records',['uid'=>$uid,'day'=>7]);
            }catch (ApiException $e){
                $records = [];

            }


            if(isset($records['data']) && $records['data']['count'] >0){
                $info = [];
                $j = 0;
                for ($i=6;$i>=0;$i--){
                    $info[$j]['new_time'] = date('m-d',strtotime('-'.$i.' day'));
                    $info[$j]['account'] = 0;
                    foreach ($records['data']['data'] as $key => $value){
                        if( date('m-d',($value['add_time'])) ==  $info[$j]['new_time']){
                            $info[$j]['account'] = $value['account'];
                        }
                    }
                    $j++;
                }

                $list = json_encode($info);
            }else{
                //生成空的数据
                $now = time();
                $null_arr = [];
                for ($i = 1 ; $i<=7 ;$i++){
                    $null_arr[] = array('new_time'=>date('Y-m-d',strtotime('-'.$i.' day',$now)),'account'=>0);
                }
                $list = json_encode($null_arr);
            }
            \Cache::put($cacheKey, json_encode([
                $canWithDraw,
                $not_settle_money,
                $user_accountInfo,
                $today_nums,
                $all_commission_profit,
                $list
            ]), $this->getCacheMinute());
        }else{
            $cacheData = json_decode($cacheData, true);
            $canWithDraw = $cacheData[0];
            $not_settle_money = $cacheData[1];
            $user_accountInfo = $cacheData[2];
            $today_nums = $cacheData[3];
            $all_commission_profit = $cacheData[4];
            $list = $cacheData[5];

        }


        $mobile = substr_replace($this->getUserName(),'****',3,4);

        return view("User.newAccountInfo")
            ->with('mobile',$mobile)
            ->with('canWithDraw',$canWithDraw)
            ->with('not_settle_money',$not_settle_money)
            ->with('user_accountInfo',$user_accountInfo)
            ->with('today_nums',$today_nums)
            ->with('list',$list)
            ->with('all_commission_profit',$all_commission_profit);
    }

    /**
     * 账户提现页面
     * @Get("/accountWithdraw", as="s_user_accountWithdraw")
     */
    public function accountWithdraw(Request $request) {
        $uid =  $this->getUserId();
        //获取用户信息
        $userInfo = Curl::post('/user/userAccountPage', ['uid' => $uid]);
        $account = !empty($userInfo['data']['available_amount'])?$userInfo['data']['available_amount']:'0';


        $current_page = $request->get('page',1);
        if(!intval($current_page)){
            $current_page = 1;
        }
        $c = $request->get('c');

        $cacheKey = $this->getCacheKye('accountWithdraw',$request);
        if (! $cacheData = \Cache::get($cacheKey)) {
            //获取资金记录
            $pageList = false;
            $flowingList = Curl::post('/user/accountFlowingWater', [
                'uid' => $uid,
                'page' => $current_page,
                'pagesize' => 10,
            ]);

            if(isset($flowingList['data']['page_count']) && $flowingList['data']['count'] > 0){
                $pageList = getPageList($flowingList['data']['page_count'], $current_page);
                $flowingList = $flowingList['data']['data'];
            }else{
                $flowingList = [];
            }

            \Cache::put($cacheKey, json_encode([
                $pageList,
                $flowingList
            ]), $this->getCacheMinute());
        }else{
            $cacheData = json_decode($cacheData, true);
            $pageList = $cacheData[0];
            $flowingList = $cacheData[1];
        }


        //获取银行卡
        try {
            $bank = Curl::post('/user/getBindBankCard', ['uid' => $uid]);
            if(isset($bank['data']['id'])){
                $bid = $bank['data']['id'];
                $banknumber = $bank['data']['banknumber'];
                $realname = $bank['data']['realname'];
            }else{
                $bid = 0;
                $banknumber = 0;
                $realname = '';
            }

        } catch (ApiException $e) {
            $bid = 0;
            $banknumber = 0;
            $realname = '';
        }
        $account = (int)($account*100)/100;
        return view("User.newAccountWithdraw")
        ->with('current_page', $current_page)
        ->with('pageList', $pageList)
        ->with('c', $c)
        ->with('account', $account)
        ->with('bid', $bid)
        ->with('banknumber', $banknumber)
        ->with('realname',$realname)
        ->with('flowingList', $flowingList);
    }

    /**
     * 发送短信
     * @Post("/sendDrawSms", as="s_sms_sendDrawSms")
     */
    public function sendDrawSms(Request $request) {

        try {
            $mobile = $this->getUserName();
            $type = $request->get("type");
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
     * 验证短信
     * @Post("/validatorDrawSms", as="s_sms_validatorDrawSms")
     */
    public function validatorDrawSms(Request $request) {
        try {
            $mobile = $this->getUserName();
            $code = $request->get("code");
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
            $account = $request->get("account");
            $bank_id = $request->get("bank_id");
            $session = \Session::get("validatorDrawSms");
            $session = json_decode($session, true);
            $post = Curl::post('/user/withdrawalApplication', [
                'uid' => $this->getUserId(),
                'bank_id' => $bank_id,
                'account' => $account,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 账户佣金结算页面
     * @Get("/accountCommissionSettlement", as="s_user_accountCommissionSettlement")
     */
    public function accountCommissionSettlement(Request $request) {
        $uid = $this->getUserId();
        $user_accountInfo = [];
        $not_settle_money = 0;
        $unsettled_amount_last_month = 0;
        $unsettled_amount_month = 0;

        $cacheKey = $this->getCacheKye('accountCommissionSettlement');
        if (! $cacheData = \Cache::get($cacheKey)) {
            try{
                $user_accountInfo = Curl::post('/user/userAccountPage',['uid'=>$uid]);
            }catch (ApiException $e){

            }
            if(count($user_accountInfo) > 0 && $user_accountInfo['data']){
                //未结算佣金总额，表字段
                $not_settle_money = isset($user_accountInfo['data']['unsettled_amount'])?($user_accountInfo['data']['unsettled_amount']):0;
                //上个月未结算佣金总额 查询
                $unsettled_amount_last_month = isset($user_accountInfo['data']['last_month_total'])?($user_accountInfo['data']['last_month_total']):0;
                //本月未结算佣金总额，表字段
                $unsettled_amount_month = isset($user_accountInfo['data']['unsettled_amount_month'])?($user_accountInfo['data']['unsettled_amount_month']):0;
            }

            \Cache::put($cacheKey, json_encode([
                $not_settle_money,
                $unsettled_amount_last_month,
                $unsettled_amount_month
            ]), $this->getCacheMinute());
        }else{
            $cacheData = json_decode($cacheData, true);
            $not_settle_money = isset($cacheData[0])?$cacheData[0]:0;
            $unsettled_amount_last_month = isset($cacheData[1])?$cacheData[1]:0;
            $unsettled_amount_month = isset($cacheData[2])?$cacheData[2]:0;
        }

        $mobile = substr_replace($this->getUserName(),'****',3,4);
        return view("User.newAccountCommissionSettlement")
            ->with('mobile',$mobile)
            ->with('unsettled_amount_month',$unsettled_amount_month?$unsettled_amount_month:0)
            ->with('unsettled_amount_last_month',$unsettled_amount_last_month?$unsettled_amount_last_month:0)
            ->with('not_settle_money',$not_settle_money?$not_settle_money:0);
    }

    /**
     * 获取佣金结算数据接口查询
     * @Post("/getCommissionSettlement", as="s_user_getCommissionSettlement")
     */
    public function getCommissionSettlement(Request $request) {
        try {
            $uid = $this->getUserId();

            //type2 测试数据显示
//            $uid = 18;

            $seetype = $request->get("seetype");
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $post = Curl::post('/user/getUserCommissionRecordPage', $arr = [
                'uid'=> $uid,
                'seetype'=>$seetype,
                'page' =>$page,
                'pagesize'=>$pagesize,
            ]);
            //var_dump($post);die;
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 账户佣金明细页面
     * @Get("/accountCommissionSettlementDetail", as="s_user_accountCommissionSettlementDetail")
     */
    public function accountCommissionSettlementDetail(Request $request) {
        $uid = $this->getUserId();
        $current_page = $request->get('page',1);
        if(!intval($current_page)){
            $current_page = 1;
        }

        $current_page2 = $request->get('page2',1);
        $startDay = $request->get('startDay','');
        $endDay = $request->get('endDay','');
        if(!intval($current_page2)){
            $current_page2 = 1;
        }
        $cacheKey = $this->getCacheKye('accountCommissionSettlementDetail',$request);
        if (! $cacheData = \Cache::get($cacheKey)) {

            try {
                $today = 1;
                $arr = ['uid' => $uid,'pagesize'=>10,'page'=>$current_page,'today'=>$today];
                $flowList = Curl::post('/user/settlementDetail', $arr);
            } catch (ApiException $e) {
                $flowList = [];

            }
            $pageList = false;
            if(isset($flowList['data']['page_count']) && $flowList['data']['count'] > 0){
                $pageList = getPageList($flowList['data']['page_count'], $current_page,true);
                $flowList = $flowList['data']['data'];
            }else{
                $flowList = [];

            }

            try {
                $arr = ['uid' => $uid,'pagesize'=>10,'page'=>$current_page2,'startDay'=>$startDay,'endDay'=>$endDay];
                $flowList2 = Curl::post('/user/settlementDetail', $arr);

            } catch (ApiException $e) {
                $flowList2 = [];

            }
            $pageList2 = false;
            if(isset($flowList2['data']['page_count']) && $flowList2['data']['count'] > 0){
                $pageList2 = getPageList($flowList2['data']['page_count'], $current_page2,true);

                $flowList2 = $flowList2['data']['data'];
            }else{
                $flowList2 = [];

            }

            \Cache::put($cacheKey, json_encode([
                $flowList,
                $pageList,
                $pageList2,
                $flowList2
            ]), $this->getCacheMinute());

        }else{
            $cacheData = json_decode($cacheData, true);
            $flowList = $cacheData[0];
            $pageList = $cacheData[1];
            $pageList2 = $cacheData[2];
            $flowList2 = $cacheData[3];
        }


        $mobile = substr_replace($this->getUserName(),'****',3,4);
        return view("User.accountCommissionSettlementDetail")
            ->with('current_page',$current_page)
            ->with('flowList',$flowList)
            ->with('pageList',$pageList)

            ->with('mobile',$mobile)

            ->with('current_page2',$current_page2)
            ->with('flowList2',$flowList2)
            ->with('pageList2',$pageList2)
            ->with('startDay',$startDay)
            ->with('endDay',$endDay)
            ;
    }

    /**
     * 获取佣金明细数据接口查询
     * @Post("/getAccountCommissionSettlementDetail", as="s_user_getAccountCommissionSettlementDetail")
     */
    public function getAccountCommissionSettlementDetail(Request $request) {
        try {
            $uid = $this->getUserId();
            $today = $request->get("today",-1);
            $startDay = $request->get("startDay",'');
            $endDay = $request->get("endDay",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $post = Curl::post('/user/settlementDetail', $arr = [
                'uid'=> $uid,
                'today'=>$today,
                'startDay'=>$startDay,
                'endDay'=>$endDay,
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
     * 账户推广数据页面
     * @Get("/accountSpreadData", as="s_user_accountSpreadData")
     */
    public function accountSpreadData(Request $request) {

        $uid =  $this->getUserId();
        $current_page = $request->get('page',1);
        if(!intval($current_page)){
            $current_page = 1;
        }
        $cacheKey = $this->getCacheKye('accountSpreadData',$request);
        if (! $cacheData = \Cache::get($cacheKey)) {

            $spreadList = Curl::post('/user/accountSpreadData', [
                'uid' => $uid,
                'page' => $current_page,
                'pagesize' => 10,
            ]);


            $pageList = getPageList($spreadList['data']['page_count'], $current_page);
            $spreadList = $spreadList['data'];


            \Cache::put($cacheKey, json_encode([
                $pageList,
                $spreadList,

            ]), $this->getCacheMinute());
        }else{
            $cacheData = json_decode($cacheData, true);
            $pageList = $cacheData[0];
            $spreadList = $cacheData[1];
        }



        $mobile = substr_replace($this->getUserName(),'****',3,4);
        return view("User.accountSpreadData")
            ->with('current_page', $current_page)
            ->with('pageList', $pageList)
            ->with('mobile',$mobile)
            ->with('spreadList', $spreadList);
    }
    /**
     * 账户推广数据详情页面
     * @Get("/accountSpreadDataDetail", as="s_user_accountSpreadDataDetail")
     */
    public function accountSpreadDataDetail(Request $request) {
        $uid =  $this->getUserId();
        $current_page = $request->get('page',1);
        $activity_id = $request->get('id');
        if(!intval($current_page)){
            $current_page = 1;
        }
        $cacheKey = $this->getCacheKye('accountSpreadDataDetail',$request);
        if (! $cacheData = \Cache::get($cacheKey)) {

            $spreadList = Curl::post('/user/accountSpreadDataDetail', [
                'uid' => $uid,
                'spread_id'=>$activity_id,
                'page' => $current_page,
                'pagesize' => 10,
            ]);

            $pageList = getPageList($spreadList['data']['page_count'], $current_page);
            $spreadList = $spreadList['data'];

            \Cache::put($cacheKey, json_encode([
                $pageList,
                $spreadList,

            ]), $this->getCacheMinute());
        }else{
            $cacheData = json_decode($cacheData, true);
            $pageList = $cacheData[0];
            $spreadList = $cacheData[1];
        }


        $mobile = substr_replace($this->getUserName(),'****',3,4);
        return view("User.accountSpreadDataDetail")
            ->with('current_page', $current_page)
            ->with('pageList', $pageList)
            ->with('mobile',$mobile)
            ->with('spreadList', $spreadList);
    }

    /**
     * 好友邀请页面
     * @Get("/accountFreiendInvite", as="s_user_accountFreiendInvite")
     */
    public function accountFreiendInvite(Request $request) {
        $uid = $this->getUserId();
        $current_page = $request->get('page',1);
        if(!intval($current_page)){
            $current_page = 1;
        }
        $t = $request->get('t',1);
        if(!intval($t)){
            $t = 1;
        }
        $cacheKey = $this->getCacheKye('accountFreiendInvite',$request);
        if (! $cacheData = \Cache::get($cacheKey)) {
            try {
                $now = time();
                if($t==2){
                    $starttime = strtotime('-30 day',$now);
                    $endtime = 0;
                }elseif($t==3){
                    $starttime = strtotime('-7 day',$now);
                    $endtime = 0;
                }elseif($t==4){
                    $starttime = strtotime(date('Y-m-d',$now));
                    $endtime = 0;
                }else{
                    $starttime = 0;
                    $endtime = 0;
                }
                $userList = Curl::post('/user/getInviteFriendByUId', ['uid' => $uid,'endtime'=>$endtime,'starttime'=>$starttime,'pagesize'=>10,'page'=>$current_page]);
                $total_people_num = $userList['data']['count'];
                $total_settlement = 0;
            } catch (ApiException $e) {
                $userList = [];
                $total_people_num = 0;
                $total_settlement = 0;
            }
            $pageList = false;

            if(isset($userList['data']['page_count']) && $userList['data']['count'] > 0){
                $pageList = getPageList($userList['data']['page_count'], $current_page,true);
                $total_settlement = $userList['data']['sumTotal'];
                $userList = $userList['data']['data'];
            }else{
                $userList = [];
            }

            \Cache::put($cacheKey, json_encode([
                $total_people_num,
                $total_settlement,
                $pageList,
                $userList

            ]), $this->getCacheMinute());
        }else{
            $cacheData = json_decode($cacheData, true);
            $total_people_num = $cacheData[0];
            $total_settlement = $cacheData[1];
            $pageList = $cacheData[2];
            $userList = $cacheData[3];
        }


        return view("User.accountFriendInvite")
            ->with('pageList', $pageList)
            ->with('total_settlement',$total_settlement)
            ->with('total_people_num',$total_people_num)
            ->with('current_page',$current_page)
            ->with('userList',$userList)
            ->with('t',$t)
            ->with("code", $this->getRecommendCode());
    }

    /**
     * 好友邀请用户数据查询页面
     * @Get("/FreiendInviteDetail", as="s_user_FreiendInviteDetail")
     */
    public function FreiendInviteDetail(Request $request) {
        $uid = $this->getUserId();
        $current_page = $request->get('page',1);

        $channel_name = $request->get('name','');


        if($channel_name){
            \Session::put('channel_name',$channel_name);
        }else{
            $channel_name =  \Session::get('channel_name');
        }

        if(!intval($current_page)){
            $current_page = 1;
        }
        $t = $request->get('t',1);
        if(!intval($t)){
            $t = 1;
        }
        $ituid = $request->get("ituid",0);
        \Session::put('ituid',$ituid);
        if($ituid <= 0 || !intval($ituid)){
            return redirect(route('s_user_accountFreiendInvite'));
        }
        $sort = $request->get('sort',0);
        if(!intval($t)){
            $sort = 0;
        }

//testfor
//        $uid = 2;
//        $ituid = 18;
//

        $pageList = false;
        $content_count = 0;
        $avg_buy = 0;
        $sum_order = 0;
        $sum_commisssion = 0;
        $list = [];
        try {
            $now = time();
            if($t==2){
                $starttime = strtotime('-30 day',$now);
                $endtime = 0;
            }elseif($t==3){
                $starttime = strtotime('-7 day',$now);
                $endtime = 0;
            }elseif($t==4){
                $starttime = strtotime(date('Y-m-d',$now));
                $endtime = 0;
            }else{
                $starttime = 0;
                $endtime = 0;
            }
            $order = '';
            if($sort == 1){
                $order = '{"buytransfer":"asc"}';//购买转化
            }elseif ($sort == 2){
                $order = '{"buytransfer":"desc"}';
            }elseif ($sort == 3){
                $order = '{"account":"asc"}';//交易额 account
            }elseif ($sort == 4){
                $order = '{"account":"desc"}';
            }elseif ($sort == 5){
                $order = '{"commission_account":"asc"}';//佣金额 commission_account
            }elseif ($sort == 6){
                $order = '{"commission_account":"desc"}';
            }
            $infolist = Curl::post('/user/getFriendByInviteId', ['uid' => $uid,'order'=>$order,'inviteUid'=>$ituid,'endtime'=>$endtime,'starttime'=>$starttime,'pagesize'=>10,'page'=>$current_page]);
            if($infolist['status'] == 200){
                $list = $infolist['data']['data'];
                $content_count = $infolist['data']['count'];
                $avg_buy = number_format($infolist['data']['avgtransfer']*100,2);//
                $sum_order = number_format($infolist['data']['sumAccount'],2);
                $sum_commisssion = number_format($infolist['data']['sumCommission'],2);
                $pageList = getPageList($infolist['data']['page_count'], $current_page,true);

            }
        } catch (ApiException $e) {
            $content_count = 0;
            $avg_buy = 0;
            $sum_order = 0;
            $sum_commisssion = 0;
        }
        return view("User.FreiendInviteDetail")
            ->with('content_count',$content_count)
            ->with('avg_buy',$avg_buy)
            ->with('sum_order',$sum_order)
            ->with('sum_commisssion', $sum_commisssion)
            ->with('list',$list)
            ->with('current_page',$current_page)
            ->with('t',$t)
            ->with('channel_name',$channel_name)
            ->with('sort',$sort)
            ->with('ituid',$ituid)
            ->with('pageList', $pageList);
    }

    /**
     * 好友邀请用户文章数据查询页面
     * @Get("/FreiendInviteArticleDetail", as="s_user_FreiendInviteArticleDetail")
     */
    public function FreiendInviteArticleDetail(Request $request) {
        $current_page = $request->get('page',1);
        if(!intval($current_page)){
            $current_page = 1;
        }
        $t = $request->get('t',1);
        if(!intval($t)){
            $t = 1;
        }
        $sort = $request->get('sort',0);
        if(!intval($t)){
            $sort = 0;
        }
        $spread_id = $request->get("sp",0);
        if($spread_id <= 0 || !intval($spread_id)){
            return redirect(route('s_user_accountFreiendInvite'));
        }

        $ituid = $this->getUserId();

//testfor
//        $spread_id = 7;
//        $ituid = 30;//接口未使用
//

        $pageList = false;
        $nums = 0;
        $sum_order = 0;
        $sum_commisssion = 0;
        $list = [];
        try {
            $now = time();
            if($t==2){
                $starttime = strtotime('-30 day',$now);
                $endtime = 0;
            }elseif($t==3){
                $starttime = strtotime('-7 day',$now);
                $endtime = 0;
            }elseif($t==4){
                $starttime = strtotime(date('Y-m-d',$now));
                $endtime = 0;
            }else{
                $starttime = 0;
                $endtime = 0;
            }
            $order = '';
            if($sort == 1){
                $order = '{"number":"asc"}';//购买转化
            }elseif ($sort == 2){
                $order = '{"number":"desc"}';
            }elseif ($sort == 3){
                $order = '{"account":"asc"}';//交易额 account
            }elseif ($sort == 4){
                $order = '{"account":"desc"}';
            }elseif ($sort == 5){
                $order = '{"commission_account":"asc"}';//佣金额 commission_account
            }elseif ($sort == 6){
                $order = '{"commission_account":"desc"}';
            }
            $infolist = Curl::post('/user/getFriendSpreadById', ['spreadId' => $spread_id,'order'=>$order,'inviteUid'=>$ituid,'endtime'=>$endtime,'starttime'=>$starttime,'pagesize'=>10,'page'=>$current_page]);
            if($infolist['status'] == 200){
                $list = $infolist['data']['data'];
                $nums = $infolist['data']['count'];
                $sum_order = number_format($infolist['data']['sumAccount'],2);
                $sum_commisssion = number_format($infolist['data']['sumCommission'],2);
                $pageList = getPageList($infolist['data']['page_count'], $current_page,true);

            }
        } catch (ApiException $e) {
            $nums = 0;
            $sum_order = 0;
            $sum_commisssion = 0;
        }

        $article_name = $request->get('channel_name','');

         if($article_name){
             \Session::put('article_name',$article_name);
         }else{
             $article_name =  \Session::get('article_name');
         }

        $channel_name =  \Session::get('channel_name');
        $ituid =  \Session::get('ituid');

        return view("User.FreiendInviteArticleDetail")
            ->with('nums',$nums)
            ->with('sum_order',$sum_order)
            ->with('sum_commisssion',$sum_commisssion)
            ->with('list',$list)
            ->with('current_page',$current_page)
            ->with('t',$t)
            ->with('sort',$sort)
            ->with('sp',$spread_id)
            ->with('pageList', $pageList)
            ->with('channel_name', $channel_name)
            ->with('article_name', $article_name)
            ->with('ituid', $ituid);


    }

    /**
     * 账户设置页面
     * @Get("/accountSetting", as="s_user_accountSetting")
     */
    public function accountSetting(Request $request) {
        //绑定的银行卡  传递bid hidden
        //获取银行卡
        try {
            $bank = Curl::post('/user/getBindBankCard', ['uid' => $this->getUserId()]);
            if(isset($bank['data']['id'])){
                $bid = $bank['data']['id'];
                $banknumber = $bank['data']['banknumber'];
                $realname = $bank['data']['realname'];
            }else{
                $bid = 0;
                $banknumber = 0;
                $realname = '';
            }
        } catch (ApiException $e) {
            $bid = 0;
            $banknumber = 0;
            $realname = '';
        }
        $judge = $request->get("judge",0);
        return view("User.newAccountSetting")
            ->with('judge',$judge)
            ->with('bid',$bid)
            ->with('mobile',$this->getUserName())
            ->with('realname',$realname)
            ->with('banknumber',$banknumber);
    }

    /**
     * 修改密码
     * @Post("/changePassword", as="s_user_changePassword")
     */
    public function changePassword(Request $request) {
        try {
            $oldpas = $request->get("oldpas");
            $newpas = $request->get("newpas");
            $newpas2 = $request->get("newpas2");

            $post = Curl::post('/user/editPassword', [
                'username' => $this->getUserName(),
                'oldpasswd' => $oldpas,
                'passwd' => $newpas,
                'passconfirm' => $newpas2,
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
     * 绑定银行卡
     * @Post("/bindBankCard", as="s_user_bindBankCard")
     */
    public function bindBankCard(Request $request) {
        try {
            $realname = $request->get("bind_realname");
            $idnumber = $request->get("bind_idnumber");
            $bind_banknumber = $request->get("bind_banknumber");
            $bind_mobile = $request->get("bind_mobile");

            $post = Curl::post('/user/bindBankCard', [
                'realname' => $realname,
                'idnumber' => $idnumber,
                'banknumber' => $bind_banknumber,
                'mobile' => $bind_mobile,
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

                $code = $request->get("code");
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
                $code = $request->get("code");
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
     * 发送验证码
     * @Post("/sendBankSms", as="s_user_sendBankSms")
     */
    public function sendBankSms(Request $request) {
        try {
            $mobile = $request->get("mobile",-1);
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
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获得推广二维码
     * @Post("/createSpreadQRcode", as="s_user_createSpreadQRcode")
     */
    public function createSpreadQRcode(Request $request) {
        try {
            $aid = $request->get("aid",-1);
            $aprs = $request->get("aprs",-1);
            if(intval($aid) && $aid >0){
                $post = Curl::post('/user/createSpreadQRcode', [
                    'aprs' => $aprs,
                    'spreadUid' => $this->getUserId()
                ]);
                $data = $post['data'];
                if($post['status']==200){
                    $post['data']['url'] = config('params.wx_host').'User/productDetail?spreadid='.$post['data']['id'] .'&nid='.$post['data']['order_no'];
                    //$post['data']['url'] = file_get_contents('http://suo.im/api.php?url='.urlencode($post['data']['url']));
                }

//               var_dump($data);die;
                return new JsonResponse($post);
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    private function getCacheKye($pagename,$request = false){
        $key = $pagename.".".$this->getUserId();
        if($request){
            $key =  $key.'.'.md5(json_encode($request->all()));
        }
        return  $key;
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

    private function getCacheMinute(){
        return config('params.cache_minute');
    }

}