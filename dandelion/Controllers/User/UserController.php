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
        $uid = $this->getUserId();
        $cacheKey = $this->getCacheKye('accountInfo');
        if (! $cacheData = \Cache::get($cacheKey)) {
            $not_settle_money = 0.00;
            $canWithDraw = 0.00;
            $today_nums = 0;
            $all_commission_profit = 0.00;
            try{
                $user_accountInfo = Curl::post('/user/userAccountPage',['uid'=>$uid]);
            }catch (ApiException $e){
                $user_accountInfo = [];


            }
            if(count($user_accountInfo) > 0 && $user_accountInfo['data']){
                $canWithDraw = $user_accountInfo['data']['available_amount'];
                $not_settle_money = $user_accountInfo['data']['unsettled_amount'];
                $today_nums = $user_accountInfo['data']['today_nums'];
                $all_commission_profit = $user_accountInfo['data']['all_commission_profit'];
            }

            try{
                $records = Curl::post('/user/getUserCommission_records',['uid'=>$uid,'day'=>7]);
            }catch (ApiException $e){
                $records = [];

            }
            if(isset($records['data']) && $records['data']['count'] >0){
                foreach ($records['data']['data'] as $key => $value){
                    $records['data']['data'][$key]['new_time'] = date('Y-m-d',$records['data']['data'][$key]['add_time']);
                }
                $list = json_encode($records['data']['data']);
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
            ]), 20);
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
            ->with('user_accountInfo',$user_accountInfo['data'])
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
            ]), 20);
        }else{
            $cacheData = json_decode($cacheData, true);
            $pageList = $cacheData[0];
            $flowingList = $cacheData[1];
        }


        //获取银行卡
        try {
            $bank = Curl::post('/user/getBindBankCard', ['uid' => $uid]);
            $bid = $bank['data']['id'];
            $banknumber = $bank['data']['banknumber'];
        } catch (ApiException $e) {
            $bid = 0;
            $banknumber = 0;
        }
        $account = (int)($account*100)/100;
        return view("User.newAccountWithdraw")
        ->with('current_page', $current_page)
        ->with('pageList', $pageList)
        ->with('c', $c)
        ->with('account', $account)
        ->with('bid', $bid)
        ->with('banknumber', $banknumber)
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
        $not_settle_money = 0.00;
        $unsettled_amount_last_month = 0.00;
        $unsettled_amount_month = 0.00;

        $cacheKey = $this->getCacheKye('accountCommissionSettlement');
        if (! $cacheData = \Cache::get($cacheKey)) {
            try{
                $user_accountInfo = Curl::post('/user/userAccountPage',['uid'=>$uid]);
            }catch (ApiException $e){

            }
            if(count($user_accountInfo) > 0 && $user_accountInfo['data']){
                //未结算佣金总额，表字段
                $not_settle_money = number_format($user_accountInfo['data']['unsettled_amount'],2);
                //上个月未结算佣金总额 查询
                $unsettled_amount_last_month = number_format($user_accountInfo['data']['last_month_total'],2);
                //本月未结算佣金总额，表字段
                $unsettled_amount_month = number_format($user_accountInfo['data']['unsettled_amount_month'],2);
            }

            \Cache::put($cacheKey, json_encode([
                $not_settle_money,
                $unsettled_amount_last_month,
                $unsettled_amount_month
            ]), 20);
        }else{
            $cacheData = json_decode($cacheData, true);
            $not_settle_money = $cacheData[0];
            $unsettled_amount_last_month = $cacheData[1];
            $unsettled_amount_month = $cacheData[2];
        }



        $mobile = substr_replace($this->getUserName(),'****',3,4);
        return view("User.newAccountCommissionSettlement")
            ->with('mobile',$mobile)
            ->with('unsettled_amount_month',$unsettled_amount_month)
            ->with('unsettled_amount_last_month',$unsettled_amount_last_month)
            ->with('not_settle_money',$not_settle_money);
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
            ]), 20);

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

            ]), 20);
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

            ]), 20);
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
        $cacheKey = $this->getCacheKye('accountFreiendInvite',$request);
        if (! $cacheData = \Cache::get($cacheKey)) {
            try {
                $userList = Curl::post('/user/getInviteFriendByUId', ['uid' => $uid,'pagesize'=>10,'page'=>$current_page]);
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

            ]), 20);
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
            ->with("code", $this->getRecommendCode());
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
            $bid = $bank['data']['id'];
            $banknumber = $bank['data']['banknumber'];
        } catch (ApiException $e) {
            $bid = 0;
            $banknumber = 0;
        }
        $judge = $request->get("judge",0);
        return view("User.newAccountSetting")
            ->with('judge',$judge)
            ->with('bid',$bid)
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

}