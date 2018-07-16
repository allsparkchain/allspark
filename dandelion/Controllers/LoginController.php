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

        $wxHost = config('params.wx_host');

        $token = time().rand(10000, 90000);
        $key = "weChatAjax" . \Session::getId();
        \Cache::forget($key);
        \Cache::add($key, $token, 60);
        return view("newLogin")->with('url',$url)->with('pc_jzstate',$jzstate)->with('wxHost',$wxHost);
    }

    /**
     * @Get("/logout", as="s_logout")
     */
    public function logout(Request $request) {
        \Session::forget('wxUserInfo');
        $request->session()->flush();
        \Auth::logout();


        /*$changepwd = $request->get('changepwd',-1);
        if($changepwd){
            return redirect('/login');
        }*/
        return redirect(route('s_index_index'));
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

    protected function authenticated(Request $request, $user)
    {
        $url = $request->get('url');
        if($url){
            return redirect($url);
        }
        return redirect($this->redirectPath());
    }


}