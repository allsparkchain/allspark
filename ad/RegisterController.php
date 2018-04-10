<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Lib\Curl;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


/**
 * Class IndexController
 * @Controller(prefix="/")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class RegisterController extends \App\Http\Controllers\Auth\RegisterController
{
    /**
     * 注册1绑定微信
     * @Get("/register", as="s_register")
     */
    public function showRegistrationForm()
    {
        if(\Auth::user()){
            return redirect(route('s_user_accountInfo'));
        }
//        $request = app('request');

        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);
        \Session::put("pc_jzstate", $jzstate);
        \Cache::put("wxdl_".$jzstate, $jzstate,30);

        $token = time().rand(10000, 90000);
        $key = "weChatAjax" . \Session::getId();
        \Cache::forget($key);
        \Cache::add($key, $token, 60);

        $wxHost = config('params.wx_host');

        //记步1
        \Session::put("regBindWxs", json_encode(["regBindWxs"=>1]));

        return view("Register.bindWx")->with('pc_jzstate',$jzstate)->with('wxHost',$wxHost);
    }

    /**
     * 注册2绑定手机号
     * @Get("/regBindMobile", as="s_regBindMobile")
     */
    public function regBindMobile()
    {
        if(\Auth::user()){
            return redirect(route('s_user_accountInfo'));
        }

        $var = \Session::get("regBindWxs");
        $var = json_decode($var, true);
        if (!isset($var['regBindWxs']) || $var['regBindWxs'] != 2) {
            //注册微信绑定页面1 扫图后  尝试登录失败 传递来2
            return redirect('/register');
        }

        return view("Register.bindMobile");
    }

    /**
     * 注册3设置密码
     * @Get("/regSetPass", as="s_regSetPass")
     */
    public function regSetPass()
    {
        if(\Auth::user()){
            return redirect(route('s_user_accountInfo'));
        }
//        return view("Register.setPass");


        $var = \Session::get("validatorResisterSms");
        $var = json_decode($var, true);
        if (!isset($var['order_number']) ) {
            return redirect('/register');
        } else {
            return view("Register.setPass");
        }

    }

    /**
     * 注册4详细信息
     * @Get("/regSetDetail", as="s_regSetDetail")
     */
    public function regSetDetail()
    {
//        $var = \Session::get("validatorResisterSms");
//        $var = json_decode($var, true);
//        var_dump($var);

        if(\Auth::user()){
            return redirect(route('s_user_accountInfo'));
        }
//        return view("Register.setDetail");


        $var = \Session::get("validatorResisterSms");
        $var = json_decode($var, true);
        if (!isset($var['order_number']) ) {
            return redirect('/register');
        } else {
            return view("Register.setDetail");
        }

    }


    /**
     * 注册4注册成功
     * @Get("/regSuccess", as="s_regSuccess")
     */
    public function regSuccess()
    {
        return view("Register.success");
    }


    /**
     * 发送注册验证码
     * @Post("/sendRegisterSms", as="s_sms_register")
     */
    public function sendRegisterSms(Request $request) {
        try {
//            $mobile = esaDecode($request->get("mobile",''));
//            clearAES();
            $mobile = $request->get("mobile",'');
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

            $data = $post['data'];
            unset($post['data']);
            \Session::put("registerSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 检查手机号是否已经存在接口
     * @Post("/checkMobile", as="s_sms_checkMobile")
     */
    public function checkMobile(Request $request) {
        try {
            $mobile = $request->get("mobile",'');
            $post = Curl::post('/advertOwner/checkMobile', [
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
     * 注册3提交密码
     * @Post("/setRegPwd", as="s_reg_setRegPwd")
     */
    public function setRegPwd(Request $request) {
        try {

            $var = \Session::get("validatorResisterSms");
            $var = json_decode($var, true);
            if (!isset($var['order_number']) ) {
                return redirect(Route('s_register'));
            }


            $passwd = $request->get("passwd",'');
            $passwd_confirmation = $request->get("passwd_confirmation",'');



            if(strlen($passwd)<=7 && strlen($passwd_confirmation)<=7){
                return new JsonResponse([
                    "status"=>477,
                    "message"=>'密码错误',
                ]);
            }

            $var['passwd'] = $passwd;
            $var['passwd_confirmation'] = $passwd_confirmation;

            \Session::put("validatorResisterSms", json_encode($var));

            return new JsonResponse(['status'=>200,'message'=>'ok']);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 验证 提交的验证码是否正确
     * @Post("/validatorRegisterSms", as="s_validator_register")
     */
    public function validatorRegisterSms(Request $request) {
        try {
            $mobile = $request->get("mobile");
            $code = $request->get("code");
            $session = \Session::get("registerSms");
            $session = json_decode($session, true);
            if($mobile != Arr::get($session, 'mobile', '')){

                return new JsonResponse([
                    "status"=>203,
                    "message"=>'传递手机号与刚才发送手机号不符',
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
//            \Session::put("validatorResisterSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
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
     * @post("/auth/register", as="s_auth_register")
     */
    public function register(Request $request)
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
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
//    protected function create(array $data)
//    {
//        $user = null;
//        try {
//            $recommendCode = \Session::get("recommendCode",'');
//
//            $var = \Session::get("validatorResisterSms");
//            $var = json_decode($var, true);
//            $mobile = Arr::get($var, "mobile");
//            $orderNumber = Arr::get($var, "order_number");
//            $post = Curl::post('/advertOwner/register', [
//                'mobile' => $mobile,
//                'password' => $data['passwd'],
//                'order_number' => $orderNumber,
//                'invite_code' => $recommendCode,
//            ]);
//
//            //绑定openId
//            $code = \Session::pull('advert_reg_code', '');
//            if($code){
//                $openUser = Curl::post('/weixin/bindOpenId', [
//                    'uid' => $post['data']['id'],
//                    'code' => $code,
//                    'type' => 4
//                ]);
//                $post['data']['nickname'] = $openUser['data']['nickname'];
//                $post['data']['headimgurl'] = $openUser['data']['headimgurl'];
//            }
//
//            $user = new User($post['data']);
//            \Session::put("user", serialize($user));
//            \Session::pull("recommendCode");
//            //return new JsonResponse($post);
//        } catch (ApiException $e) {
//
//        }
//        return $user;
//    }
    protected function create(array $data)
    {
        $user = null;
        try {
            $recommendCode = \Session::get("recommendCode",'');

            $var = \Session::get("validatorResisterSms");
            $var = json_decode($var, true);
            $mobile = Arr::get($var, "mobile");
            $orderNumber = Arr::get($var, "order_number");

            $reg_arr =[
                'mobile' => $mobile,
                'password' => $data['passwd'],
                'order_number' => $orderNumber,
                'invite_code' => $recommendCode,
            ];
            if(isset($data['email']) && isset($data['bankcard'])){
                $reg_arr['email'] = $data['email'];
                $reg_arr['bankcard'] = $data['bankcard'];
            }
  
            if(isset($data['bank_relative']) ){
                $reg_arr['bank_relative'] = $data['bank_relative'];
            }

            if(isset($data['idcard']) && isset($data['realname'])){
                $reg_arr['idcard'] = $data['idcard'];
                $reg_arr['realname'] = $data['realname'];
            }

            $post = Curl::post('/advertOwner/register', $reg_arr);

            //绑定openId
            $code = \Session::pull('advert_reg_code', '');
            if($code){
                $openUser = Curl::post('/weixin/bindOpenId', [
                    'uid' => $post['data']['id'],
                    'code' => $code,
                    'type' => 4
                ]);
                $post['data']['nickname'] = $openUser['data']['nickname'];
                $post['data']['headimgurl'] = $openUser['data']['headimgurl'];
            }

            if(isset($data['idcard']) && isset($data['realname']) && isset($var['order_number_auth'])){
                $postInfo = Curl::post('/user/userAuthentication', [
                    'uid' => $post['data']['id'],
                    'mobile' => $mobile,
                    'idno' => $data['idcard'],
                    'realname' => $data['realname'],
                    'order_number' => Arr::get($var, 'order_number_auth', ''),
                    'verify_type'=>6
                ]);

                $postLast = Curl::post('/user/userBindAuthentication', [
                    'id' => $post['data']['id'],
                    'order_number' => $postInfo['data'],
                ]);
            }

            $user = new User($post['data']);
//            \Session::put("user", serialize($user));
            \Session::pull("recommendCode");

        } catch (ApiException $e) {

        }
        return $user;
    }

}