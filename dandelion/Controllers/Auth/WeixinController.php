<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Lib\Curl;
use EasyWeChat\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Overtrue\LaravelWeChat\Facade;
use SocialiteProviders\Weixin\WeixinExtendSocialite;
use Auth;

/**
 * Class WeixinControllers
 * @Controller(prefix="/auth/weixin")
 * @Middleware("web")
 * @package App\Http\Controllers\Auth
 */
class WeixinController extends Controller
{

    /**
     * @Get("/showlogin", as="s_weixin_show_login")
     */
    public function showlogin(Request $request)
    {
        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);

        return view('auth.weixinlogin')->with("pc_jzstate", $jzstate);
    }


    /**
     * @POST("/weChatAjax", as="s_weixin_ajax_login")
     * @Get("/weChatAjax", as="s_weixin_ajax_login")
     */
    public function weChatAjax(Request $request)
    {
        ignore_user_abort(false);
        $code = \Session::get('pc_jzstate', '');
        $key = "weChatAjax" . \Session::getId();
        $token = \Cache::get($key);
        if ($code){
            $i = 1;
            while ($i<=10 && true){
                sleep(5);
                $cacheToken = \Cache::get($key);
                if ($cacheToken != $token) {
                    return new JsonResponse([
                        "type"=> 3
                    ]);
                }
                $openCode = Curl::post('/weixin/getOpenCode',['code'=>$code, 'login_type'=>1]);
                if(Arr::get($openCode['data'], 'status', 0) == 2){
                    break;
                }
                $i++;
            }

            if (Arr::get($openCode['data']['user'], 'uid', 0)) {
                Auth::attempt(['code' => $code, 'mode' => 2, 'login_type'=>1 ],true);
            }else{
                \Session::put("pc_reg_code", $code);
                \Session::put("wxUserInfo", json_encode($openCode['data']));
            }

        }
        return new JsonResponse([
            "type"=> !$code ? 3 : (Arr::get($openCode['data']['user'], 'uid', 0) ? 1 : 2) ,//1 代表登录（跳转至用户中心） 2 代表注册（跳转至注册页面）
        ]);
    }



}