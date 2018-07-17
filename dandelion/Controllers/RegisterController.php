<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Lib\Curl;
use App\User;
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
     * @Get("/register", as="s_register")
     */
    public function showRegistrationForm()
    {

        $wxUserInfo = \Session::get('wxUserInfo');
        $code = \Session::get('wxUserInfo', null);
        $wxUserInfo = $wxUserInfo?json_decode($wxUserInfo,true):'';

        if(!isset($wxUserInfo['headimgurl']) && !isset($wxUserInfo['nickname'])){
            $code='';
        }

        if(!$wxUserInfo || !$code){
           // return redirect('/qrRegister');
        }

        return view("newRegister")->with('code', $code)->with('wxUserInfo', $wxUserInfo);
    }

    /**
     * @Get("/qrRegister", as="s_qrRegister")
     */
    public function qrRegister()
    {
        if(\Auth::user()){
            return redirect(route('s_user_accountInfo'));
        }
        $request = app('request');


        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);
        \Session::put("pc_jzstate", $jzstate);
        \Cache::put("wxdl_".$jzstate, $jzstate,30);

        $token = time().rand(10000, 90000);
        $key = "weChatAjax" . \Session::getId();
        \Cache::forget($key);
        \Cache::add($key, $token, 60);

        $wxHost = config('params.wx_host');

        return view("qrRegister")->with('pc_jzstate',$jzstate)->with('wxHost',$wxHost);

    }

    /**
     * @post("/auth/register", as="s_register")
     */
    public function register(Request $request)
    {
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
    protected function create(array $data)
    {
        $user = null;
        try {
            $recommendCode = \Session::get("recommendCode");
            $var = \Session::get("validatorResisterSms");
            $var = json_decode($var, true);
            $mobile = Arr::get($var, "mobile");
            $orderNumber = Arr::get($var, "order_number");
            $post = Curl::post('/user/register', [
                'mobile' => $mobile,
                'password' => $data['passwd'],
                'order_number' => $orderNumber,
                'invite_code' => $recommendCode,
            ]);
            //绑定openId
            $code = \Session::pull('pc_reg_code', '');
            if($code){
                $openUser = Curl::post('/weixin/bindOpenId', [
                    'uid' => $post['data']['id'],
                    'code' => $code
                ]);
                $post['data']['nickname'] = $openUser['data']['nickname'];
                $post['data']['headimgurl'] = $openUser['data']['headimgurl'];
            }


            $user = new User($post['data']);
            \Session::put("user", serialize($user));
            \Session::pull("recommendCode");

            //return new JsonResponse($post);
        } catch (ApiException $e) {

        }
        return $user;
    }



}