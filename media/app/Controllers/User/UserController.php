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

        $zmtpc_host = config('params.pc_host');
        //邀请码
        $code = $this->getRecommendCode();
        return view("User.accountInfo")->with('inviteUrl',$zmtpc_host.$code);
    }

    /**
     * 渠道数据页面
     * @Get("/channelData", as="s_user_channelData")
     */
    public function channelData() {
        return view("User.channelData");
    }

    /**
     * 渠道数据用户详情页面2
     * @Get("/channelUserDetail", as="s_user_channelUserDetail")
     */
    public function channelUserDetail() {
        return view("User.channelUserDetail");
    }

    /**
     * 渠道数据用户文章详情页面3
     * @Get("/channelUserArticleDetail", as="s_user_channelUserArticleDetail")
     */
    public function channelUserArticleDetail() {
        return view("User.channelUserArticleDetail");
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

    /**
     * 好友邀请页面
     * @Get("/invitePage", as="s_user_invitePage")
     */
    public function invitePage() {

        $zmtpc_host = config('params.pc_host');
        //邀请码
        $code = $this->getRecommendCode();
        return view("User.invitePage")->with('inviteUrl',$zmtpc_host.$code);
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

            $post = Curl::post('/smedia/getSmediaAccountInfo', $arr = [
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
     * 下载渠道数据1
     * @Get("/DownLoadCommissionSettlement", as="s_user_DownLoadCommissionSettlement")
     */
    public function DownLoadCommissionSettlement(Request $request) {

        try {
            $uid = $this->getUserId();

//            $uid = 94;

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

//            $uid = 94;

            //初始化数据
            $now = getCurrentTime();
            $today = date('Y-m-d',$now);
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
                $starttime = strtotime('-30 day',strtotime($today));
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',strtotime($today));
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(strtotime($today));
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

//            var_dump($arr = [
//                'uid'=> $uid,
//                'name' => $name,
//                'order' => $order,
//                'page' =>$page,
//                'pagesize'=>$pagesize,
//                'starttime'=>$starttime,
//                'endtime'=>$endtime,
//            ]);die;
            $post = Curl::post('/smedia/getSmediaList', $arr = [
                'uid'=> $uid,
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
     * 获取渠道数据 个人详情页面 2
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


            $pass_uid = $request->get("invited_uid",1);
            $name = $request->get("name",'');


//            $uid = 94;
//            $pass_uid= 9;//invited_uid 9,18

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
            if($type == 'buytransfer'){
                $order = json_encode(['buytransfer'=>$sort]);
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

            $post = Curl::post('/smedia/getSmediaUserArticleList', $arr = [
                'invited_uid'=> $pass_uid,
                'invite_uid' => $uid,
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
     * 获取渠道数据 文章订单详情页面 3
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
            $pass_uid = $request->get("invited_uid",1);


//            $uid = 94;
//            $pass_uid= 9;//invited_uid 9,18
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
                $order = json_encode(['numbers'=>$sort]);
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

            $post = Curl::post('/smedia/getSmediaUserArticleDetail', $arr = [
                'invited_uid'=> $pass_uid,
                'invite_uid' => $uid,
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
     * 获取佣金提现页面数据1
     * @Post("/getUserWithdrawPage", as="s_user_getUserWithdrawPage")
     */
    public function getUserWithdrawPage(Request $request) {
        try {
            $uid = $this->getUserId();
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

//            $uid = 94;

            $post = Curl::post('/smedia/getSmediaUserWithdrawPage', $arr = [
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

            $post = Curl::post('/smedia/getUnsettledComissionList', $arr = [
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

            $post = Curl::post('/smedia/getSmediaUserAccFLowPage', $arr = [
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

            if(strlen($realname)<=0  || strlen($bind_banknumber)<=0){
                return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
            }
    
            $post = Curl::post('/user/bindBankCard', [
                'realname' => $realname,
                'idnumber' => '001',
                'banknumber' => $bind_banknumber,
                'mobile' => '001',
                'bank_relative'=>$bank_relative,
                'sub_branch_name' => '支行信息',
                'sub_branch_id' => '10011111',
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
                'account_prop'=>2,
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
     * 获取银行卡列表数据
     * @Post("/getUserBankRelative", as="s_user_getUserBankRelative")
     */
    public function getUserBankRelative(Request $request) {
        try {

            $id = $request->get("id",0);
            $post = Curl::post('/user/getUserBankRelative',
                [
                    //'id' => $id
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