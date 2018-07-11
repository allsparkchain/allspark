<?php

namespace App\Http\Middleware;
use App\Exceptions\ApiException;
use App\Lib\Curl;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthLogin{
    /**
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     * @throws ApiException
     */
    public function handle($request, Closure $next)
    {

        if(Auth::user()){
            $data = Auth::user()->getData();

        }
        if( !Auth::user() || !isset($data['openid']) ){
           \Session::put("web_redirect_uri", $request->getRequestUri());
           \Cache::add("web_redirect_uri", $request->getRequestUri(), 1);
            $code = Curl::post('/weixin/createCode');
            $jzstate  = ($code['data']);
            return redirect(route('s_weixin_login', [ 'jzstate'=>$jzstate, 'login_type'=>2 ]));
            /*$username = config('params.authlogin_username');
            $pass = config('params.authlogin_password');
            Auth::attempt(['username' => $username, 'password' => $pass],true);*/

        }
        return $next($request);
    }
}

