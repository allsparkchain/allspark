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
        return view("index");
    }

    /**
     * @Get("/userAgreement", as="s_index_useragreement")
     */
    public function userAgreement() {
        return view("userAgreement");
    }

    /**
     * @Post("/sendRegisterSms", as="s_sms_register")
     */
    public function sendRegisterSms(Request $request) {
        try {
            $mobile = $request->get("mobile");
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
            $mobile = $request->get("mobile");
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
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);
            \Session::put("validatorResisterSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * @Get("/setpassword", as="s_setpassword")
     */
    public function showLoginForm()
    {
        $var = \Session::get("validatorResisterSms");
        $var = json_decode($var, true);
        if (!isset($var['order_number'])) {

        } else {
            return view("setpassword");
        }
    }

    /**
     * 忘记密码页面
     * @Get("/forgetpassword", as="s_forgetpassword")
     */
    public function forgetpassword()
    {
       return view("forgetpassword");
    }
    /**
     * @Post("/sendForgetSms", as="s_sms_sendForgetSms")
     */
    public function sendForgetSms(Request $request) {
        try {
            $mobile = $request->get("mobile");

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
     * @Get("/restsetPassword", as="s_restsetPassword")
     */
    public function restsetPassword()
    {
        $var = \Session::get("validatorForgetSms");
        $var = json_decode($var, true);
        if (!isset($var['order_number'])) {
            return  redirect(route('s_index_index'));
        } else {
            return view("resetpassword");
        }
    }

    /**
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
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }




}