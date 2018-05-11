<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Lib\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


/**
 * Class IndexController
 * @Controller(prefix="/")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class IndexController extends Controller
{
    /**
     * @Get("/", as="s_index_index")
     */
    public function index() {
//        if(\Auth::user()){
            return redirect(route('s_user_accountInfo'));
//        }
        return view("index");
        //return redirect('/User/accountInfo');
    }

    /**
     * @Get("/notAuth", as="s_user_not_auth")
     */
    public function notAuth() {
        if(!\Auth::user()){
            return redirect(route('s_user_accountInfo'));
        }
        return view("notAuth");
        //return redirect('/User/accountInfo');
    }

    /**
     * 协议
     * @Get("/agreement", as="s_agreement")
     */
    public function agreement(){

        return view("Register.agreement");
    }

    /**
     * Show the Index Page
     * @Get("/getkeys", as="index_keys")
     */
    public function keys(Request $request)
    {
        $return = getA( $request->get('A'), 'B');

        return new JsonResponse(['B'=>$return]);
    }



    /**
     * 忘记密码1 验证手机号
     * @Get("/forgetVerifyTel", as="s_forgetVerifyTel")
     */
    public function forgetVerifyTel()
    {
        return view("Forget.forgetVerifyTel");
    }

    /**
     * 忘记密码2 设置新密码
     * @Get("/forgetSetPwd", as="s_forgetSetPwd")
     */
    public function forgetSetPwd()
    {
        $var = \Session::get("validatorForgetSms");
        $var = json_decode($var, true);
        if (!isset($var['order_number'])) {
            return  redirect(route('s_forgetVerifyTel'));
        } else {
            return view("Forget.forgetSetPwd");
        }

    }

    /**
     * 忘记密码3 设置新密码成功
     * @Get("/forgetSetPwdSuccess", as="s_forgetSetPwdSuccess")
     */
    public function forgetSetPwdSuccess()
    {
        return view("Forget.forgetSetPwdSuccess");
    }


    /**
     * 发送验证码
     * @Post("/sendForgetSms", as="s_sms_sendForgetSms")
     */
    public function sendForgetSms(Request $request) {
        try {
//            $mobile = esaDecode($request->get("mobile"));
            $mobile = $request->get("mobile",'');
            clearAES();
            //检查是否注册？checkMobile

            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => 2
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("forgetSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
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
     * @Post("/validatorForgetSms", as="s_validatorForgetSms")
     */
    public function validatorForgetSms(Request $request) {
        try {
            $mobile = $request->get("mobile");
            $code = $request->get("code");
            $session = \Session::get("forgetSms");
            $session = json_decode($session, true);
            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 2,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);
            \Session::put("validatorForgetSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 重置密码提交
     * @Post("/restsetPasswordPost", as="s_restsetPasswordPost")
     */
    public function restsetPasswordPost(Request $request) {
        try {
            $newpass = $request->get("newpass");
            $session = \Session::get("validatorForgetSms");
            $session = json_decode($session, true);

            $post = Curl::post('/user/resetPassword', [
                'mobile' => Arr::get($session, 'mobile', ''),
                'newpasswd' => $newpass,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);
            \Session::forget("forgetSms");
            \Session::forget("validatorForgetSms");
            \Session::flush();
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 邀请码分享地址
     * @Get("/r/{inviteCode}", as="s_recommend_registration")
     */
    public function recommendRegistration($inviteCode) {
        \Session::put("recommendCode", $inviteCode);

        return redirect('/register');
    }

    /**
     * 获取银行卡列表数据
     * @Post("/getBankRelative", as="s_index_getBankRelative")
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

}