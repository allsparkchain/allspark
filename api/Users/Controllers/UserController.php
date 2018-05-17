<?php

namespace App\Users\Controllers;
use App\Users\Entities\BankBindEntity;
use App\Users\Entities\UserEntity;
use App\Users\Services\User;
use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Lib\Message\Factory;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Symfony\Component\HttpFoundation\Request;

/**
 * @path /user
 */
class UserController
{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Message
     */

    /**
     * @inject
     * @var User
     */
    public $user;

    /**
     * @inject
     * @var ErrorConst
     */
    public $errorConst;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;

    /**
     * @inject
     * @var Factory
     */
    public $factory;


    /**
     * 注册
     *
     * @route POST /register
     * @param UserEntity $entity {@bind request.request}
     *
     * @return array
     */
    public function register(UserEntity $entity) {
        try {
            $rs = $this->user->register($entity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 用户登录
     * @route POST /login
     * @param string $mobile
     * @param string $passwd
     * @return array
     */
    public function login($mobile, $passwd) {
        try {
            $rs = $this->user->login($mobile, $passwd);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 检测手机号是否已经存在
     * @route POST /checkMobile
     * @param string $mobile
     * @return array
     */
    public function checkMobile($mobile) {
        try {
            $rs = $this->user->checkMobile($mobile);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 修改密码
     * @route POST /editPassword
     * @param string $username {@v lengthMin:6}
     * @param string $oldpasswd {@v lengthMin:6|lengthMax:16|alphaNum}
     * @param string $passwd {@v lengthMin:6|lengthMax:16|alphaNum}
     * @param string $passconfirm {@v lengthMin:6|lengthMax:16|alphaNum}
     * @return array
     */
    public function editPassword($username, $oldpasswd, $passwd, $passconfirm) {
        try {
            $rs = $this->user->editPassword($username, $oldpasswd, $passwd, $passconfirm);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 重置密码
     * @route POST /resetPassword
     * @param string $mobile {@v lengthMin:11}
     * @param string $newpasswd {@v lengthMin:6|lengthMax:16|alphaNum}
     * @param string $order_number {@v lengthMin:1}
     * @return array
     */
    public function resetPassword($mobile, $newpasswd, $order_number) {
        try {
            $rs = $this->user->resetPassword($mobile, $newpasswd, $order_number);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 绑定银行卡
     * @route POST /bindBankCard
     * @param BankBindEntity $bankBindEntity {@bind request.request}
     *
     * @return array
     */
    public function bindBankCard(BankBindEntity $bankBindEntity) {
        try {
            return $this->mutex->getMutex('bindBank'.$bankBindEntity->getBanknumber())->synchronized(function() use($bankBindEntity){
                $rs = $this->user->bindBankCard($bankBindEntity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 解除绑定银行卡
     * @route POST /unbindBankCard
     * @param int $bid {@v min:1}
     *
     * @return array
     */
    public function unbindBankCard($bid) {
        try {
            $rs = $this->user->unbindBankCard($bid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获得绑定的银行卡
     * @route POST /getBindBankCard
     * @param int $uid {@v min:1}
     * @return array
     */
    public function getBindBankCard($uid) {
        try {
            $rs = $this->user->getBindBankCard($uid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
    * 提现申请
     * @route POST /withdrawalApplication
     * @param int $uid {@v min:1}
     * @param int $bank_id {@v min:1}
     * @param float $account {@v numeric}
     * @param string $order_number
     * @return array
     */
    public function withdrawalApplication($uid, $bank_id, $account, $order_number) {
        try {
            $rs = $this->user->withdrawalApplication($uid, $bank_id, $account, $order_number);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

             } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 用户账户信息页面
     * @route POST /userAccountPage
     * @param int $uid {@v min:1}
     * @return array
     */
    public function userAccountPage($uid) {

        try {
            $rs = $this->user->userAccountInfo($uid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 用户资金流水信息
     * @route POST /getUserCommission_records
     * @param int $uid {@v min:1}
     * @param int $type {@v min:1}
     * @param int $day {@v min:1}
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function getUserCommissionRecords($uid, $type = -1, $day = -1, $page = 1, $pagesize = 10) {
        try {
            $rs = $this->user->getUserCommissionRecord($uid, $page, $pagesize, $type, $day);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 用户资金流水分页信息
     * @route POST /getUserCommissionRecordPage
     * @param int $page {@v min:1}
     * @param int $uid {@v min:1}
     * @param int $seetype {@v min:1}
     * @param int $type {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function getUserCommissionRecordPage($uid, $type = 1, $seetype = 1, $page = 1, $pagesize = 10) {
        try {
            $rs = $this->user->getUserCommissionRecordPage($page, $pagesize, $uid, $type, $seetype);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
    * 资金流水
     * @route POST /accountFlowingWater
     * @param int $uid {@v min:1}
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function accountFlowingWater($uid, $page = 1, $pagesize = 10) {
        try {
            $rs = $this->user->accountFlowingWater($page, $pagesize, $uid);
                 return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 推广数据详情
     * @route POST /accountSpreadDataDetail
     * @param int $uid {@v min:1}
     * @param int $spread_id {@v min:1}
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function accountSpreadDataDetail($uid, $spread_id, $page = 1, $pagesize = 10) {
        try {
            $rs = $this->user->accountSpreadDataDetail($page, $pagesize, $uid, $spread_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 推广数据列表
     * @route POST /accountSpreadData
     * @param int $uid {@v integer}
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function accountSpreadData($uid, $page = 1, $pagesize = 10) {
        try {
            $rs = $this->user->accountSpreadData($page, $pagesize, $uid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 添加收货地址
     * @route POST /addAddress
     * @param int $uid {@v required|min:1}
     * @param int $mobile {@v required|min:1}
     * @param string $realname {@v required|lengthMin:1}
     * @param string $address {@v required|lengthMin:1}
     * @return array
     */
    public function addAddress($uid, $mobile, $realname, $address) {
        try {
            $rs = $this->user->addAddress($uid, $mobile, $realname, $address);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 修改收货地址
     * @route POST /editAddress
     * @param int $id {@v required|min:1}
     * @param int $uid {@v required|min:1}
     * @param int $mobile {@v min:1}
     * @param int $status {@v min:1}
     * @param string $realname {@v lengthMin:1}
     * @param string $address {@v lengthMin:1}
     * @return array
     */
    public function editAddress($id, $uid, $mobile, $status , $realname, $address) {
        try {
            $rs = $this->user->editAddress($id, $uid, $mobile, $status, $realname, $address);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 查看收货地址
     * @route POST /getUserAddress
     * @param int $uid {@v required|min:1}
     * @param int $status {@v min:1}
     * @return array
     */
    public function getUserAddress($uid, $status = 1) {
        try {
            $rs = $this->user->getUserAddress($uid,$status);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 生成推广二维码
     * @route POST /createSpreadQRcode
     * @param int $spreadUid {@v min:1}
     * @param int $aprs {@v min:1}
     * @return array
     */
    public function createSpreadQRcode($spreadUid, $aprs) {
        try {
            $data = $this->user->createSpreadQRcode($spreadUid, $aprs);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     *
     * @route POST /getInfoBySpreadId
     * @param int $spreadid {@v min:1}
     * @return array
     */
    public function getInfoBySpreadId($spreadid) {
        try {
            $data = $this->user->getInfoBySpreadId($spreadid);

            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 根据用户id获得 被邀请人信息
     * @route POST /getInviteFriendByUId
     * @param int $uid {@v min:1}
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function getInviteFriendByUId($uid, $page = 1, $pagesize = 10) {
        try {
            $data = $this->user->getInviteFriendByUId($uid, $page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 佣金明细页面
     * @route POST /settlementDetail
     * @param int $uid {@v integer}
     * @param int $today {@v integer}
     * @param string $startDay
     * @param string $endDay
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function settlementDetail($uid, $today = -1, $startDay = '', $endDay = '', $page = 1, $pagesize = 10) {
        //目前就只有渠道用户所以目前user_commision.type=2  1写手，3网站
        try {
            $rs = $this->user->settlementDetail($uid, $today, $startDay, $endDay, $page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    private function _newRegister($var)
    {
        try {

            $code = session('zmt_reg_code');

            $invite_code = session('recommendCode');

            $invite = Curl::post('/user/getUserPreInvite', ['code'=>$code]);
            if($invite['status'] == 200 && $invite['data']){
                $invite_code = $invite['data'];
            }
            $reg_arr = [
                'mobile' => $var['mobile'],
                'password' => $var['passwd'],
                'order_number' => $var['order_number'],
                'invite_code' => $invite_code,
            ];

            $post = Curl::post('/user/register', $reg_arr);
            if (isset($post['status']) && $post['status'] == 200) {

                if ($code) {
                    $openUser = Curl::post('/weixin/bindOpenId', [
                        'uid' => $post['data']['id'],
                        'code' => $code,
                        'type' => 1,
                    ]);
                    $post['data']['nickname'] = $openUser['data']['nickname'];
                    $post['data']['headimgurl'] = $openUser['data']['headimgurl'];
                }
                session(['newRegisterData' => $post['data']]);
            } else {
                return new JsonResponse(['status' => 400, 'message' => '注册失败']);
            }
        } catch (ApiException $e) {
            return new JsonResponse(['status' => $e->getCode(), 'message' => $e->getMessage(),]);
        }
    }

    /**
     * 协议
     * @Get("/agreement", as="s_agreement")
     */
    public function agreement()
    {

        return view("Register.agreement");
    }


    /**
     * 发送注册验证码
     * @Post("/sendRegisterSms", as="s_sms_register")
     */
    public function sendRegisterSms(Request $request) {
        try {
            $mobile = esaDecode($request->get("mobile",''));
            clearAES();
            //$mobile = $request->get("mobile",'');
            if(strlen($mobile)<=0){
                return new JsonResponse([
                    "status"=>477,
                    "message"=>'手机号输入格式错误',
                ]);
            }
            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => 1
            ]);

            if($post['status']==200){
                $data = $post['data'];
                unset($post['data']);
                \Session::put("registerSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
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
     *
     * @Post("/checkMobilenew", as="s_sms_checkMobilenew")
     */
    public function checkMobilenew(Request $request) {
        try {

            $mobile = esaDecode($request->get("mobile",''));
            //clearAES();
            $post = Curl::post('/user/checkMobile', [
                'mobile' => $mobile,
            ]);
            if(is_null($post['data'])){
                return new JsonResponse(['msg'=>'ok','status'=>200]);
            }
            return new JsonResponse(['msg'=>'手机号已存在','status'=>201]);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),

            ]);
        }
    }


    /**
     *
     * @Post("/validatorRegisterSms", as="s_validator_register")
     */
    public function validatorRegisterSms(Request $request) {
        try {
            $mobile = esaDecode($request->get("mobile"));
            clearAES();
            $code = $request->get("code");
            $session = \Session::get("registerSms");
            $session = json_decode($session, true);
            if($mobile != Arr::get($session, 'mobile', '')){

                return new JsonResponse([
                    "status"=>203,
                    "message"=>'传递手机号与刚才发送手机号不符',
                ]);

            }
            if(!$code){
                return new JsonResponse([
                    "status"=>204,
                    "message"=>'请输入正确的验证码',
                ]);
            }

            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 1,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);



            //这册实名验证码(不发送只做验证)
            $authSMSverify = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 6,
                'order_number' => Arr::get($session, 'order_number', '').'_6',
            ]);
//            var_dump($authSMSverify);die;
//            $session['order_number_auth'] = Arr::get($session, 'order_number', '').'_6';
            $session['order_number_auth'] = $authSMSverify['data']['order_number'];
            $session['order_number'] = $data['order_number'];

            \Session::put("validatorResisterSms", json_encode($session));


            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * @post("/auth/registers", as="s_auth_registers")
     */
    public function registers(Request $request)
    {

        if($request->ajax()){
            try {
                $var = \Session::get("validatorResisterSms");
                $var = json_decode($var, true);
                $passwd = Arr::get($var, "passwd",'');
                $passwd_confirmation = Arr::get($var, "passwd_confirmation",'');
                $request['passwd'] = $passwd;
                $request['passwd_confirmation'] = $passwd_confirmation;


                $response = parent::register($request);
                if($response instanceof JsonResponse){
                    return new JsonResponse(['msg'=>'注册失败','status'=>208]);
                }
                \Session::forget("registerSms");
                \Session::forget("validatorResisterSms");
                return new JsonResponse(['msg'=>'ok','status'=>200]);
            } catch (\Exception $e) {
                return new JsonResponse(['msg'=>'注册失败','status'=>209]);
            }
        }

        $response = parent::register($request);
        \Session::forget("registerSms");
        \Session::forget("validatorResisterSms");
        return $response;
    }

    /**
     * 点击购买跳至确认订单页面
     * @Post("/prepare", as="s_user_prepare")
     */
    public function prepare(Request $request) {
        $num = $request->get("num",1);
        if(!intval($num) || $num < 1){
            $num = 1;
        }

        $specification_id = $request->get("specificationId",0);
        if(!intval($specification_id) || $specification_id < 1){
            return new JsonResponse(['status'=>202,'message'=>'规格选择有误']);
        }

        $session = \Session::get("productDetail");
        $session = json_decode($session, true);
        if (!isset($session['proinfo'])) {
            return new JsonResponse(['status'=>201,'message'=>'数据过期']);
        }

        $info = $session;

        $chose = false;
        foreach ($session['proinfo']['specificationsList'] as $key=>$value){
            if($specification_id == $value['id']){
                $info['selling_price'] = $value['selling_price'];
                $info['choose_specification_id'] = $specification_id;
                $chose = true;
                break;
            }
        }
        if(!$chose){
            return new JsonResponse(['status'=>203,'message'=>'所选规格未找到']);
        }
        $extra = $request->get("extra",'');


        $info['num'] = $num;
        $info['extra'] = $extra;


        \Session::put('productDetail', json_encode($info));
        return new JsonResponse(['status'=>200,'message'=>'ok']);



    }

    /**
     * 确认订单页面
     * @Get("/confirmOrder", as="s_user_confirmOrder")
     */
    public function confirmOrder(Request $request) {
        $uid = $this->getUserId();
        $session = \Session::get("productDetail");
        $session = json_decode($session, true);
        if (!isset($session['proinfo'])) {
            return redirect(route('s_order_orderHistoryList'));
//            var_dump('无之前的数据，非法');die;
            //没有之前页面的数据，，，非法过来
        }else{
            $num  = $session['num'];
            $selling_price = $session['selling_price'];
            $proinfo = $session['proinfo'];
            $extra = $session['extra'];
        }
        $address = [];
        try{
            $address = Curl::post('/user/getUserAddress',['uid'=>$uid])['data'];
            if(!empty($address)){
                $address = $address[0];
            }

        }catch (ApiException $e){

        }
        return view("Order.confirmOrder")
            ->with('address',$address)
            ->with('num',$num)
            ->with('proinfo',$proinfo)
            ->with('extra',$extra)
            ->with('selling_price',$selling_price)
            ;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return \Validator::make($data, [
            'passwd' => 'required|max:20|confirmed',
            'passwd_confirmation' => 'required|max:20',
        ]);
    }
}
