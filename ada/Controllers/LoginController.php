<?php

namespace App\Http\Controllers;

use App\Lib\Curl;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController as BaseLoginController;


/**
 * Class IndexController
 * @Controller(prefix="/")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class LoginController extends BaseLoginController
{
    use AuthenticatesUsers;
    protected $redirectTo = "/User/accountInfo";

    /**
     * @Get("/login", as="s_login")
     */
    public function showLoginForm()
    {
        if(\Auth::user()){
            return redirect(route('s_user_accountInfo'));
        }
        $request = app('request');
        $url = $request->get('url','');


        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);
        \Session::put("pc_jzstate", $jzstate);
        \Cache::put("wxdl_".$jzstate, $jzstate,30);

        $token = time().rand(10000, 90000);
        $key = "weChatAjax" . \Session::getId();
        \Cache::forget($key);
        \Cache::add($key, $token, 60);

        $wxHost = config('params.wx_host');

        $advert_host = config('params.advert_host');

        $wx_qrurl = config('params.pc_qr_page').'?return_url='.$advert_host.'auth/weixin/QRreturn'.'&login_type=3';


        return view("login")->with('url',$url)->with('pc_jzstate',$jzstate)->with('wxHost',$wxHost)->with('wx_qrurl',$wx_qrurl);
    }

    /**
     * @Get("/logout", as="s_logout")
     */
    public function logout(Request $request) {
        \Auth::logout();
        \Session::flush();
        $changepwd = $request->get('changepwd',-1);
        if($changepwd){
            return redirect('/User/accountInfo');
        }
        return redirect(route('s_login'));
//        return view("login");

    }

    public function username()
    {
        return "username";
    }


    /**
     * @Post("/auth/login", as="s_auth_login")
     */
    public function login(Request $request)
    {
        if($request->ajax()){
            try {
                $result = parent::login($request);
                if($result instanceof JsonResponse){
                    return new JsonResponse(['msg'=>'用户名或密码错误','status'=>208]);
                }
                return new JsonResponse(['msg'=>'ok','status'=>200]);
            } catch (\Exception $e) {
                return new JsonResponse(['msg'=>'用户名或密码错误','status'=>209]);
            }
        }
        //api接口 登录
        return parent::login($request);
    }

    /**
     * 邮箱登录
     * @Post("/emailLogin", as="s_login_emailLogin")
     */
    public function emailLogin(Request $request) {
        if($request->ajax()){
            try {
                $email = $request->get("email",'');
                $passwd = $request->get("passwd",'');
                if(strlen($email)<7 || mb_strlen($passwd)<7){
                    return new JsonResponse([
                        "status"=>'333',
                        "message"=>'传递参数非法或者缺少参数',
                    ]);
                }
                $res = \Auth::attempt(['email_login' => 1, 'email' => $email, 'password'=>$passwd ],true);
                if($res){
                    return new JsonResponse([
                        "status"=>'200',
                        "message"=>'成功',
                    ]);
                }else{
                    return new JsonResponse([
                        "status"=>'208',
                        "message"=>'邮箱地址或密码错误',
                    ]);
                }
            } catch (\ApiException $e) {

                return new JsonResponse([
                    "status"=>$e->getCode(),
                    "message"=>$e->getMessage(),
                ]);
            }
        }
    }

    protected function authenticated(Request $request, $user)
    {
        $url = $request->get('url');
        if($url){
            return redirect($url);
        }
        return redirect($this->redirectPath());
    }


}