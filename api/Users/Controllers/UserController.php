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


    /**
     * @Get("/getArticleImgList", as="s_article_getArticleImgList")
     * @Post("/getArticleImgList", as="s_article_getArticleImgList")
     */
    public function getArticleImgList(Request $request) {
        if ($request->ajax()) {
            $array = [
                'page' => $request->get('page', 1),
                'pagesize'=>$request->get('pagesize', 10),
                'article_id'=>$request->get('article_id', ''),
            ];


            $data = Curl::post('/article/getArticleImgList',
                $array
            );
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }
        $re = '-1';
        if($request->get('article_id')){
            $re = $request->get('article_id');
        }
        return view("Article.articleImgList")->with('article_id',$re);
    }

    /**
     * @Post("/getArticleImgDel", as="s_article_getArticleImgDel")
     */
    public function getArticleImgDel(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $data = Curl::post('/article/getArticleImgDel',
                [
                    'id' => $request->get('id', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

    /**
     * @Post("/getArticleImgOrder", as="s_article_getArticleImgOrder")
     */
    public function getArticleImgOrder(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $data = Curl::post('/article/getArticleImgOrder',
                [
                    'id' => $request->get('id'),
                    'orderby' => $request->get('orderby'),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

    /**
     * 文章栏目列表
     * @Get("/columnLists", as="s_article_columnLists")
     * @Post("/columnLists", as="s_article_columnLists")
     */
    public function columnLists(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/article/columnList',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'name'=>$request->get('name', ''),
//                    'adminlist' => 1
                ]
            );
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }
        return view("Article.Column.list");
    }

    /**
     * 组权限添加页面
     * @Get("/columnAdd", as="s_article_columnAdd")
     */
    public function columnAdd(Request $request) {
        $industry_media_list = Curl::post('/industryCategory/getLists', ['status' => 1,'type'=>2]);
        $industry_media_list = $industry_media_list['data']['data'];
        return view("Article.Column.add")->with('industry_media_list',$industry_media_list);
    }

    /**
     * 新增 文章栏目 提交
     * @Post("/columnAddPost", as="s_article_columnAddPost")
     */
    public function columnAddPost(Request $request) {
        $this->validate($request, [
            'name'=>'required',
            'article_category_id'=>'required'
        ]);
        $data = Curl::post('/article/columnAdd',
            [
                'status'=>$request->get('status', 1),
                'name'=>$request->get('name', ''),
                'article_category_id'=>$request->get('article_category_id', 0),
            ]
        );

        return redirect(route('s_article_columnLists'));
    }

    /**
     * @Get("/columnEdit", as="s_article_columnEdit")
     */
    public function columnEdit(Request $request){
        $data = Curl::post('/article/getColumnById',
            ['id'=>$request->get('id', '')]
        );
        $industry_media_list = Curl::post('/industryCategory/getLists', ['status' => 1,'type'=>2]);
        $industry_media_list = $industry_media_list['data']['data'];
        return view("Article.Column.edit")->with('res',$data['data'])->with('industry_media_list',$industry_media_list);
    }

    /**
     * @Post("/columnEditPost", as="s_article_columnEditPost")
     */
    public function columnEditPost(Request $request){
        $this->validate($request, [
            'id' => 'required',
            'name'=>'required',
            'article_category_id'=>'required'
        ]);
        $id = $request->get('id',0);
        $name = $request->get('name','');
        if($id>0 && strlen($name)>0){
            $data = Curl::post('/article/columnEdit',
                [
                    'id'=>$request->get('id',0),
                    'name'=>$request->get('name',''),
                    'article_category_id'=>$request->get('article_category_id',0)
                ]

            );

            if($data['status'] != 200){
                return back()->withErrors($data['message']);
            }else{

                return redirect(route('s_article_columnLists'))->with('addsuccess', 'success');
            }
        }

    }

    /**
     * @Post("/columnDel", as="s_article_columnDel")
     */
    public function columnDel(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $data = Curl::post('/article/columnDel',
                [
                    'id' => $request->get('id', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

    /**
     * 栏目  下的文章列表
     * @Get("/columnArticleLists", as="s_article_columnArticleLists")
     * @Post("/columnArticleLists", as="s_article_columnArticleLists")
     */
    public function columnArticleLists(Request $request) {
        $id = $request->get('id', '');
        if ($request->ajax()) {
            $data = Curl::post('/article/columnArticleList',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'column_id'=>$id,
//                    'adminlist' => 1
                ]
            );
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }
        return view("Article.Column.articlelist")->with('id',$id);
    }

    /**
     * 模糊搜索名称 相似的 已上架的产品
     * @Post("/searchArticlelists", as="s_article_searchArticlelists")
     */
    public function searchArticlelists(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/article/list',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 20),
                    'name'=>$request->get('article_name', ''),
                    'adminlist' => -1
                ]
            );
//            dd($data);
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);//
        }
    }

    /**
     * 删除 品牌下的关联文章
     * @Post("/columnArticleDel", as="s_article_columnArticleDel")
     */
    public function columnArticleDel(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'Id' => 'required',
            ]);
            $data = Curl::post('/article/columnArticleDel',
                [
                    'id' => $request->get('Id', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

    /**
     * 添加 品牌下的关联文章
     * @Post("/columnAddArticle", as="s_article_columnAddArticle")
     */
    public function columnAddArticle(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'articleId' => 'required',
                'colId' => 'required',
            ]);
            $data = Curl::post('/article/columnAddArticle',
                [
                    'article_id' => $request->get('articleId', -1),
                    'column_id' => $request->get('colId', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

    /**
     * 好友邀请页面
     * @Get("/inviteData", as="s_user_inviteData")
     */
    public function inviteData() {
        //https://wx.pugongying.link/Share/index?invite_code=f47d4374&handurl=https://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83eqLEBIHbbU5FoU3xazWaeoBQ4ozwpGoslB7jGS8Bw03zdsqCY7LPrMTIYCDxbUICLWTicIkavBgYhA/142

        $arr = [
            'headImgurl'=>\Auth::getUser()->getHeadImgurl(),
            'realname' =>\Auth::getUser()->getUserNickname()
        ];

        \Cache::forever($this->getRecommendCode(),$arr);

        $wxHost = config('params.wx_host');
        $fxurl = $wxHost.'Share/share?invite_code='.$this->getRecommendCode();


        return view("User.inviteData")->with("code", $this->getRecommendCode())->with('fxurl',$fxurl);
    }

    /**
     * 账户设置页面
     * @Get("/accountSetting", as="s_user_accountSetting")
     */
    public function accountSetting() {
        return view("User.accountSetting")->with('mobile',$this->getUserName());
    }

    /**
     * 我的资讯页面
     * @Get("/articleList", as="s_user_articleList")
     */
    public function articleList() {
        return view("User.articleList");
    }


    //apiPost-------------
    /**
     * 获取账户总览数据
     * @Post("/getAccountInfo", as="s_user_getAccountInfo")
     */
    public function getAccountInfo(Request $request) {
        try {
            $uid = $this->getUserId();

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $post = Curl::post('/user/userAccountPage', $arr = [
                'uid'=> $uid,
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
     * 获取账户页面图表数据
     * @Post("/getAccountInfoFlow", as="s_user_getAccountInfoFlow")
     */
    public function getAccountInfoFlow(Request $request) {
        try {
            $uid = $this->getUserId();
            $seetype = $request->get("seetype",1);
            /////
            try{
                $records = Curl::post('/user/getUserDayDraw',['uid'=>$uid,'seetype'=>$seetype]);
            }catch (ApiException $e){
                $records = [];
            }
//            if(isset($records['data']) && $records['data']['count'] >0){
//                var_dump($records['data']);die;
//                $info = [];
//                $j = 0;
//                for ($i=6;$i>=0;$i--){
//                    $info[$j]['new_time'] = date('Y.m.d',strtotime('-'.$i.' day'));
//                    $info[$j]['account'] = 0;
//                    foreach ($records['data']['data'] as $key => $value){
//                        if( date('Y.m.d',($value['add_time'])) ==  $info[$j]['new_time']){
//                            $info[$j]['account'] = $value['account'];
//                        }
//                    }
//                    $j++;
//                }
//                $records['data']['draw'] = $info;
//            }else{
//                //生成空的数据
//                $now = time();
//                $null_arr = [];
//                for ($i = 1 ; $i<=7 ;$i++){
//                    $null_arr[] = array('new_time'=>date('Y.m.d',strtotime('-'.$i.' day',$now)),'account'=>0);
//                }
//                $records['data']['draw'] = $null_arr;
//            }
            return new JsonResponse($records);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取收益结算数据接口查询
     * @Post("/getCommissionSettlement", as="s_user_getCommissionSettlement")
     */
    public function getCommissionSettlement(Request $request) {
        try {
            $uid = $this->getUserId();
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
     * 获取收益明细数据接口查询
     * @Post("/getAccountCommissionSettlementDetail", as="s_user_getAccountCommissionSettlementDetail")
     */
    public function getAccountCommissionSettlementDetail(Request $request) {
        try {
            $uid = $this->getUserId();
            $today = $request->get("today",-1);//1,-1
            $startDay = $request->get("startDay",'');
            $endDay = $request->get("endDay",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            if(strlen($startDay)>0 && strlen($endDay)>0){
                if(strtotime($startDay) > strtotime($endDay)){
                    return new JsonResponse([
                        "status"=>'309',
                        "message"=>'日期选择不合法',
                    ]);
                }
            }
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
     * 获取推广数据接口查询1
     * @Post("/getSpreadData", as="s_user_getSpreadData")
     */
    public function getSpreadData(Request $request) {
        try {
            $uid = $this->getUserId();

            //type2 测试数据显示
//            $uid = 18;



            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $post = Curl::post('/user/accountSpreadData', $arr = [
                'uid'=> $uid,
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
     * 获取推广 订单 数据接口查询2
     * @Post("/getSpreadDataDetail", as="s_user_getSpreadDataDetail")
     */
    public function getSpreadDataDetail(Request $request) {
        try {
            $uid = $this->getUserId();

            //type2 测试数据显示
//            $uid = 18;


            $spread_id = $request->get("spreadid",1);
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $post = Curl::post('/user/accountSpreadDataDetail', $arr = [
                'uid'=> $uid,
                'spread_id'=>$spread_id,
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
     * 新手机号修改
     * @Post("/ChangeNewMobile", as="s_user_ChangeNewMobile")
     */
    public function ChangeNewMobile(Request $request) {
        try {

//
//            if($code <=0 || !is_numeric($code)){
//                return new JsonResponse([
//                    "status"=>'333',
//                    "message"=>'传递参数非法或者缺少参数',
//                ]);
//            }
            $session = \Session::get("changeMobile");
            $session = json_decode($session, true);

            if(!isset($session['new']) && $session['new'] != 1){
                return redirect(route('s_user_accountInfo'));
            }

            if(!isset($session['newmobile'])){
                return redirect(route('s_user_accountInfo'));
            }
            $newmobile = Arr::get($session, 'newmobile', '');
            $mobile = Arr::get($session, 'mobile', '');
            $order_number = Arr::get($session, 'order_number', '');

            $post = Curl::post('/user/changeMobile', [
                'mobile' =>$mobile,
                'newmobile' => $newmobile,
                'orderNumber' => $order_number,
            ]);
            if($post['status']==200){
                $this->setMobile($newmobile);
                \Session::forget("changeMobile");
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
     * 检查手机号是否已经存在接口 user_info表
     * @Post("/checkMobileExist", as="s_user_checkMobileExist")
     */
    public function checkMobileExist(Request $request) {
        try {

//            $mobile = esaDecode($request->get("mobile",''));
            $mobile = $request->get("mobile",0);
            //clearAES();
            $post = Curl::post('/user/checkMobileByUserInfo', [
                'mobile' => $mobile,
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
     * 检查手机号是否绑定
     * @Post("/checkisUserMobile", as="s_user_checkisUserMobile")
     */
    public function checkisUserMobile(Request $request) {
        $post = $this->isUserMobile();
        return new JsonResponse($post);
    }

    /**
     * 查询是否绑定手机号
     * @return array
     */
    private function isUserMobile(){
        try {
            $uid = $this->getUserId();
            $post = Curl::post('/user/isUserMobile', [
                'uid' => $uid,
            ]);
            return $post;
        } catch (ApiException $e) {
            return [
                'data'=>[],
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),

            ];
        }
    }

    /**
     * 检查密码是否绑定
     * @Post("/checkisUserPassword", as="s_user_checkisUserPassword")
     */
    public function checkisUserPassword(Request $request) {
        try {
            $uid = $this->getUserId();
            $post = Curl::post('/user/isUserPassword', [
                'uid' => $uid,
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
     * 绑定密码
     * @Post("/bindSetPassword", as="s_user_bindSetPassword")
     */
    public function bindSetPassword(Request $request) {
        try {
            $mobile = $this->getUserMobile();
            $newpasswd = $request->get('newpasswd','');
            if(strlen($newpasswd)<6){
                return new JsonResponse([
                    "status"=>'201',
                    "message"=>'密码不小于6位',
                ]);
            }
            $post = Curl::post('/user/setPassword', [
                'mobile' => $mobile,
                'newpasswd'=>$newpasswd
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
     * 发送绑定手机验证码
     * @Post("/sendBindMobileSms", as="s_user_sendBindMobileSms")
     */
    public function sendBindMobileSms(Request $request) {
        try {
            $mobile = $request->get("mobile",'');
            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => 11
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("sendBindMobileSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 验证验证码
     * @Post("/validatorBindMobileSms", as="s_user_validatorBindMobileSms")
     */
    public function validatorBindMobileSms(Request $request) {
        try {

            $code = $request->get("code");
            $session = \Session::get("sendBindMobileSms");
            $session = json_decode($session, true);
            $mobile = Arr::get($session, 'mobile', '');
            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 11,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("sendBindMobileSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 提交绑定手机号
     * @Post("/bindSetMobile", as="s_user_bindSetMobile")
     */
    public function bindSetMobile(Request $request) {
        try {
            $session = \Session::get("sendBindMobileSms");
            $session = json_decode($session, true);
            $mobile = Arr::get($session, 'mobile', '');
            //绑定手机号
            $postbindmobile = Curl::post('/user/bindMobile', [
                'uid' => $this->getUserId(),
                'mobile' => $mobile,
                'orderNumber' => Arr::get($session, 'order_number', ''),
            ]);
            if($postbindmobile['data']){
                $this->setMobile($mobile);
            }
            \Session::forget("sendBindMobileSms");
            return new JsonResponse($postbindmobile);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),

            ]);
        }
    }

    /**
     * 点击购买跳至确认订单页面
     * @Post("/prepare", as="s_user_prepare")
     */
    public function prepareVersion(Request $request) {
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
    public function confirmOrderVersion(Request $request) {
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

        $show_address = 1;
        if($proinfo['goods_type'] == 2 ){
            if($proinfo['need_address'] == 2){
                //不需要地址
                $show_address = 2;
            }
        }
        return view("Order.confirmOrder")
            ->with('address',$address)
            ->with('num',$num)
            ->with('proinfo',$proinfo)
            ->with('show_address',$show_address)
            ->with('extra',$extra)
            ->with('selling_price',$selling_price)
            ;
    }

    /**
     * 获取好友邀请页面
     * @Post("/getInviteFriend", as="s_user_getInviteFriend")
     */
    public function getInviteFriend(Request $request) {
        try {

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;


            $name = $request->get("name",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today

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

            $uid = $this->getUserId();
//            $uid = 22;
            $post = Curl::post('/user/getInviteFriendByUId',
                [
                    'uid' => $uid,
                    'endtime'=>$endtime,
                    'starttime'=>$starttime,
                    'pagesize'=>$pagesize,
                    'page'=>$page
                ]
            );


            if($post['status'] == 200){
                foreach ($post['data']['data'] as $key =>$val){
                    $post['data']['data'][$key]['nickname'] = json_decode($val['content'],true)['nickname'];
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
     * 获取银行卡列表数据
     * @Post("/getUserBankRelative", as="s_user_getUserBankRelative")
     */
    public function getUserBankRelative(Request $request) {
        try {

            $id = $request->get("id",0);
            $post = Curl::post('/user/getUserBankRelative',
                [
//                    'id' => $id
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
     * @param int $bind_realname 真实姓名
     * @param int $code 短信验证码
     * @param int $order_number
     * @param int $bind_idnumber 身份证号码
     * @param int $bind_banknumber 银行卡号
     * @param int $bind_mobile 手机号码
     * @param int $bank_relative 银行编号
     * @param int $bank_id 银行编号
     * @param int $province_id 支行省份
     * @param int $city_id 支行城市
     * @param int $bank_name 支行名称
     * @param string $sub_branch_id 支行编号
     */
    public function bindBankCardTmp(Request $request) {
        try {
            $realname = $request->get('bind_realname', '');
            $code = $request->get('code', '');
            $idnumber = $request->get('bind_idnumber', '');
            $bind_banknumber = $request->get('bind_banknumber', '');
            $bind_mobile = $request->get('bind_mobile', '');
            $bank_relative = $request->get('bank_relative', 0);
            $bank_id = $request->get('bank_id', '');
            $province_id = $request->get('province_id', 0);
            $city_id = $request->get('city_id', 0);
            $bank_name = $request->get('bank_name', '');
            $sub_branch_id = $request->get('sub_branch_id', '');

            $validator = \Validator::make($request->all(), trans('custom_validator.bindBankCard.rules'),
                trans('custom_validator.bindBankCard.message'));
            if ($validator->fails()) {
                return new JsonResponse(['status'=>40, 'message'=> current($validator->errors()->all())]);
            }

            //验证短信验证码
            $order_number = json_decode(session('change'), true);
            $order_number = $order_number ? $order_number['order_number'] : '';
            $post = Curl::post('utils/message/verificationSms', [
                'order_number' => $order_number,
                'code' => $code,
                'mobile' => $bind_mobile,
                'type' => 9,
            ]);
            if ($post['status'] != 200) return new JsonResponse($post);

            //绑定银行卡
            if (! $sub_branch_id) $sub_branch_id = $bank_id . $province_id . $city_id;

            $post = Curl::post('/user/bindBankCard', [
                'realname' => $realname,
                'idnumber' => $idnumber,
                'banknumber' => $bind_banknumber,
                'mobile' => $bind_mobile,
                'bank_relative'=>$bank_relative,
                'uid' => $this->getUserId(),
                'sub_branch_name' => $bank_name,
                'sub_branch_id' => $sub_branch_id,
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
    public function unbindBankCardTmp(Request $request) {
        try {
            $uid = $this->getUserId();
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

//                    if(intval($bid) && $bid > 0){
//                        $post = Curl::post('/user/unbindBankCard', [
//                            'bid' => $bid,
//                        ]);
//                        return new JsonResponse($post);
//                    }
                    if(intval($bid) && $bid > 0){
                        $post = Curl::post('/user/unbindBankCardByUid', [
                            'uid' => $uid,
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
            $uid = $this->getUserId();
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

//                    if(intval($bid) && $bid > 0){
//                        $post = Curl::post('/user/unbindBankCard', [
//                            'bid' => $bid,
//                        ]);
//                        return new JsonResponse($post);
//                    }

                    if(intval($bid) && $bid > 0){
                        $post = Curl::post('/user/unbindBankCardByUid', [
                            'uid' => $uid,
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
                    'type' => $type
                ]);
                $data = $post['data'];
                unset($post['data']);
                $name = $type == 3 ? 'unbind' : 'change';
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
//            $type = $request->get("type",0);
            $type = 4;
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
    public function withdrawalApplicationTmp(Request $request) {
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
            $authmobile = $request->get('authmobile');
            if(!is_null($authmobile)){
                $hasbindmobile = $this->isUserMobile();
                if($hasbindmobile['data']){
                    $mobile = $this->getUserName();
                }else{
                    $mobile = $authmobile;
                }
            }else{
                $mobile = $this->getUserName();
            }

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
            $session = \Session::get("sendAuth");
            $session = json_decode($session, true);

            //检测是否已经绑定手机号
            $hasbindmobile = $this->isUserMobile();
            if($hasbindmobile['data']){
                $mobile = $this->getUserName();
            }else{
                $mobile = Arr::get($session, 'mobile', '');

            }

            $code = $request->get("code");
            if($code <=0 || !is_numeric($code)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }

            //实名短信验证
            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 5,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);


            $bindmobile_order_number = '';
            if(!$hasbindmobile['data']){
                //绑定手机号验证
                $mobilePost = Curl::post('/utils/message/verificationSms', [
                    'mobile' => $mobile,
                    'code' => $code,
                    'type' => 11,
                    'order_number' => Arr::get($session, 'order_number', '').'_11',
                ]);
                $bindmobile_order_number = $mobilePost['data']['order_number'];
            }



            $data = $post['data'];
            unset($post['data']);
            \Session::put("validatorAuthSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number'],'bindmobile_order_number'=>$bindmobile_order_number]));

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


            //检测是否已经绑定手机号
            $hasbindmobile = $this->isUserMobile();
            if(!$hasbindmobile['data']){
                $mobile = Arr::get($session, 'mobile', '');
                //绑定手机号
                $postbindmobile = Curl::post('/user/bindMobile', [
                    'uid' => $this->getUserId(),
                    'mobile' => $mobile,
                    'orderNumber' => Arr::get($session, 'bindmobile_order_number', ''),
                ]);
                if($postbindmobile['data']){
                    $this->setMobile($mobile);
                }
            }else{
                $mobile = $this->getUserName();
            }



            $post = Curl::post('/user/userAuthentication', [
                'uid' => $this->getUserId(),
                'mobile' => $mobile,
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
     * 获取我的资讯列表数据
     * @Post("/getMyArticleList", as="s_user_getMyArticleList")
     */
    public function getMyArticleList(Request $request) {
        try {

            $showstatus = $request->get("showstatus",1);
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $uid = $this->getUserId();

            $post = Curl::post('/article/getMyArticleList',
                [
                    'uid' => $uid,
                    'showstatus'=>$showstatus,
                    'pagesize'=>$pagesize,
                    'page'=>$page
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
     * 文章删除
     * @Post("/delArticle", as="s_user_delArticle")
     */
    public function delArticle(Request $request) {
        try {

            $article_id = $request->get("article_id",0);
            $uid = $this->getUserId();

            $article = Curl::post('/article/getArticle',['articleid'=>$article_id]);

            if($article['data']['author'] != $uid){
                return new JsonResponse([
                    "status"=>301,
                    "message"=>'不是该用户的文章',
                ]);
            }
            $post = Curl::post('/article/auditArticle',
                [
                    'article_id' => $article_id,
                    'status'=>6,
                    'admin_id'=>$uid,
                    'remark'=>'用户删除文章'
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
     * 文章查询
     * @Post("/getArticle", as="s_user_getArticle")
     */
    public function getArticle(Request $request) {
        try {
            $article_id = $request->get("article_id",0);
            $article = Curl::post('/article/getArticle',['articleid'=>$article_id]);
            $article['data']['content'] = html_entity_decode($article['data']['content']);
//            var_dump($article['data']['content'],'<br>',);die;
            return new JsonResponse($article);
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
     * 获取用户头像昵称等信息
     * @Post("/getUserInfo", as="s_user_getUserInfo")
     */
    public function getUserInfo(Request $request) {

        try {
            $userInfo = [];
            $userInfo['avatar'] = $this->getUserAvatar();
            $userInfo['nickname'] = $this->getNickname();
            $result = [
                'status' => 200,
                'data' => $userInfo,
            ];
            return new JsonResponse($result);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 提交修改用户昵称
     * @Post("/changeNickname", as="s_user_changeNickname")
     */
    public function changeNickname(Request $request) {
        try {
            $nickname = $request->get("nickname",'');
            if(strlen($nickname)>0){

                $post = Curl::post('/user/modifyNickname', [
                    'uid' => $this->getUserId(),
                    'nickname' => $nickname
                ]);
                if($post['status'] == 200){
                    //修改昵称
                    $this->setNickname($nickname);
//                    $this->setHeadImgurl('../images/zmtdl_avator.jpg');
                }
                return new JsonResponse($post);
            }else{
                return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 提交修改用户头像
     * @Post("/changeAvatar", as="s_user_changeAvatar")
     */
    public function changeAvatar(Request $request) {

        header('Content-type:text/html;charset=utf-8');
        $avatar = $request->get("avatar",'');
        if(strlen($avatar)<=0){
            return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
        }


        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $avatar, $result)){
            $type = $result[2];

            $tmp_file = public_path().'/'.'IMGAVATAR'.date('YmdHis').rand(10000,99999).'.'.$type;

            if (file_put_contents($tmp_file, base64_decode(str_replace($result[1], '', $avatar)))){
                $ossKey =  md5(time().rand().rand());
                if(OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, $tmp_file)){
                    $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);
                    @unlink($tmp_file);
                    try {

                        $post = Curl::post('/user/modifyAvatar', [
                            'uid' => $this->getUserId(),
                            'url' => $publicObjectURL
                        ]);
                        if($post['status'] == 200){
                            //修改昵称
                            $this->setHeadImgurl($publicObjectURL);
                            $post['newHeadurl'] = $publicObjectURL;
                        }else{

                        }
                        return new JsonResponse($post);
                    } catch (ApiException $e) {
                        return new JsonResponse([
                            "status"=>$e->getCode(),
                            "message"=>$e->getMessage(),
                        ]);
                    }
                }else{
                    return new JsonResponse(['status'=>335,'message'=>'图片保存异常，请重试']);
                }

            }else{
                return new JsonResponse(['status'=>334,'message'=>'图片保存异常，请重试']);
            }

//            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
//                echo '新文件保存成功：', $new_file;
//            }else{
//                echo '新文件保存失败';
//            }
        }



    }

    /**
     * 发布文章
     * @Post("/pulishArticle", as="s_user_pulishArticle")
     */
    public function pulishArticle(Request $request) {
        try {

            $article_id = $request->get("article_id",0);
            $uid = $this->getUserId();

            $article = Curl::post('/article/getArticle',['articleid'=>$article_id]);

            if($article['data']['author'] != $uid){
                return new JsonResponse([
                    "status"=>301,
                    "message"=>'不是该用户的文章',
                ]);
            }

            $post = Curl::post('/article/changeStatus',
                [
                    'article_id' => $article_id,
                    'status'=>3,

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
     * 获取用户邮箱信息
     * @Post("/getEmailStatus", as="s_user_getEmailStatus")
     */
    public function getEmailStatus(Request $request) {
        try {
            $uid = $this->getUserId();
            $mobile = $this->getUserMobile();
            $userInfo = Curl::post('/user/existMail', [
                'uid' => $uid,

            ]);
            return new JsonResponse($userInfo);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 发送邮件
     * @Post("/sendEmail", as="s_user_sendEmail")
     */
    public function sendEmail(Request $request) {
        try {

            $email = $request->get("email",'');
            if(strlen($email) <=0){
                return new JsonResponse([
                    "status"=>333,
                    "message"=>'缺少参数或则参数格式非法',
                ]);
            }
            $mobile = $this->getUserMobile();

            $post = Curl::post('/utils/email/send',
                [
                    'to_user' => $email,
                    'phone'=>$mobile,
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
     * 发送修改手机号 原手机号短信
     * @Post("/sendChangeSms", as="s_user_sendChangeSms")
     */
    public function sendChangeSms(Request $request) {
        try {
//            $mobile = esaDecode($request->get("mobile",''));
//            clearAES();
            $mobile = $this->getUserMobile();
//            if(strlen($mobile)<=0){
//                return new JsonResponse([
//                    "status"=>477,
//                    "message"=>'手机号输入格式错误',
//                ]);
//            }
            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => 7
            ]);

            if($post['status']==200){
                $data = $post['data'];
                unset($post['data']);
                \Session::put("changeMobile", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
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
     * 原手机号 验证码验证
     * @Post("/validatorChangeSMS", as="s_user_validatorChangeSMS")
     */
    public function validatorChangeSMS(Request $request) {
        try {

            $code = $request->get("code");
            if($code <=0 || !is_numeric($code)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("changeMobile");
            $session = json_decode($session, true);

            $mobile = Arr::get($session, 'mobile', '');

            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' =>$mobile,
                'code' => $code,
                'type' => 7,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);
            $data = $post['data'];
            unset($post['data']);

//            var_dump([
//                'mobile' =>$mobile,
//                'order_number' => $data['order_number'],
//            ]);
            $post = Curl::post('/user/oldMobileVerify', [
                'mobile' =>$mobile,
                'orderNumber' => $data['order_number'],
            ]);
            $data = $post['data'];
            unset($post['data']);

            //记录
            \Session::put("changeMobile", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number'],'new'=>1]));
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 发送修改手机号 新手机号短信
     * @Post("/sendChangeNewSms", as="s_user_sendChangeNewSms")
     */
    public function sendChangeNewSms(Request $request) {
        try {
//            $mobile = esaDecode($request->get("mobile",''));
//            clearAES();
            $mobile = $request->get("mobile",'');
//            if(strlen($mobile)<=0){
//                return new JsonResponse([
//                    "status"=>477,
//                    "message"=>'手机号输入格式错误',
//                ]);
//            }
            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => 8
            ]);

            if($post['status']==200){
                $data = $post['data'];
//                unset($post['data']);
                $session = \Session::get("changeMobile");
                $session = json_decode($session, true);
                if(!isset($session['new']) && $session['new'] != 1){
                    return redirect(route('s_user_accountInfo'));
                }
                $session['order_number'] = $data['order_number'];
                $session['newmobile'] = $mobile;
                \Session::put("changeMobile", json_encode($session));
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
     * 新手机号 验证码验证
     * @Post("/validatorChangeNewSMS", as="s_user_validatorChangeNewSMS")
     */
    public function validatorChangeNewSMS(Request $request) {
        try {

            $code = $request->get("code");
            if($code <=0 || !is_numeric($code)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("changeMobile");
            $session = json_decode($session, true);

            if(!isset($session['new']) && $session['new'] != 1){
                return redirect(route('s_user_accountInfo'));
            }

            if(!isset($session['newmobile'])){
                return redirect(route('s_user_accountInfo'));
            }
            $mobile = Arr::get($session, 'newmobile', '');

            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' =>$mobile,
                'code' => $code,
                'type' => 8,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);

            $session['order_number'] = $data['order_number'];
            //记录
            \Session::put("changeMobile", json_encode($session));

            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 新手机号修改
     * @Post("/ChangeNewMobile", as="s_user_ChangeNewMobile")
     */
    public function ChangeNewMobileTmp(Request $request) {
        try {

//
//            if($code <=0 || !is_numeric($code)){
//                return new JsonResponse([
//                    "status"=>'333',
//                    "message"=>'传递参数非法或者缺少参数',
//                ]);
//            }
            $session = \Session::get("changeMobile");
            $session = json_decode($session, true);

            if(!isset($session['new']) && $session['new'] != 1){
                return redirect(route('s_user_accountInfo'));
            }

            if(!isset($session['newmobile'])){
                return redirect(route('s_user_accountInfo'));
            }
            $newmobile = Arr::get($session, 'newmobile', '');
            $mobile = Arr::get($session, 'mobile', '');
            $order_number = Arr::get($session, 'order_number', '');

            $post = Curl::post('/user/changeMobile', [
                'mobile' =>$mobile,
                'newmobile' => $newmobile,
                'orderNumber' => $order_number,
            ]);
            if($post['status']==200){
                $this->setMobile($newmobile);
                \Session::forget("changeMobile");
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
     * 检查手机号是否已经存在接口 user_info表
     * @Post("/checkMobileExist", as="s_user_checkMobileExist")
     */
    public function checkMobileExistTmp(Request $request) {
        try {

//            $mobile = esaDecode($request->get("mobile",''));
            $mobile = $request->get("mobile",0);
            //clearAES();
            $post = Curl::post('/user/checkMobileByUserInfo', [
                'mobile' => $mobile,
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
     * 检查手机号是否绑定
     * @Post("/checkisUserMobile", as="s_user_checkisUserMobile")
     */
    public function checkisUserMobileTmp(Request $request) {
        $post = $this->isUserMobile();
        return new JsonResponse($post);
    }

    /**
     * 查询是否绑定手机号
     * @return array
     */
    private function isUserMobileTmp(){
        try {
            $uid = $this->getUserId();
            $post = Curl::post('/user/isUserMobile', [
                'uid' => $uid,
            ]);
            return $post;
        } catch (ApiException $e) {
            return [
                'data'=>[],
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),

            ];
        }
    }

    /**
     * 检查密码是否绑定
     * @Post("/checkisUserPassword", as="s_user_checkisUserPassword")
     */
    public function checkisUserPasswordTmp(Request $request) {
        try {
            $uid = $this->getUserId();
            $post = Curl::post('/user/isUserPassword', [
                'uid' => $uid,
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
     * 绑定密码
     * @Post("/bindSetPassword", as="s_user_bindSetPassword")
     */
    public function bindSetPasswordTmp(Request $request) {
        try {
            $mobile = $this->getUserMobile();
            $newpasswd = $request->get('newpasswd','');
            if(strlen($newpasswd)<6){
                return new JsonResponse([
                    "status"=>'201',
                    "message"=>'密码不小于6位',
                ]);
            }
            $post = Curl::post('/user/setPassword', [
                'mobile' => $mobile,
                'newpasswd'=>$newpasswd
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
     * 发送绑定手机验证码
     * @Post("/sendBindMobileSms", as="s_user_sendBindMobileSms")
     */
    public function sendBindMobileSmsTmp(Request $request) {
        try {
            $mobile = $request->get("mobile",'');
            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => 11
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("sendBindMobileSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 验证验证码
     * @Post("/validatorBindMobileSms", as="s_user_validatorBindMobileSms")
     */
    public function validatorBindMobileSmsTmp(Request $request) {
        try {

            $code = $request->get("code");
            $session = \Session::get("sendBindMobileSms");
            $session = json_decode($session, true);
            $mobile = Arr::get($session, 'mobile', '');
            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 11,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("sendBindMobileSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 提交绑定手机号
     * @Post("/bindSetMobile", as="s_user_bindSetMobile")
     */
    public function bindSetMobileTmp(Request $request) {
        try {
            $session = \Session::get("sendBindMobileSms");
            $session = json_decode($session, true);
            $mobile = Arr::get($session, 'mobile', '');
            //绑定手机号
            $postbindmobile = Curl::post('/user/bindMobile', [
                'uid' => $this->getUserId(),
                'mobile' => $mobile,
                'orderNumber' => Arr::get($session, 'order_number', ''),
            ]);
            if($postbindmobile['data']){
                $this->setMobile($mobile);
            }
            \Session::forget("sendBindMobileSms");
            return new JsonResponse($postbindmobile);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),

            ]);
        }
    }
}
