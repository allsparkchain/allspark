<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;

use App\Lib\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Auth;

/**
 * Class WeiXinController
 * @Controller(prefix="/auth/weixin")
 * @Middleware("web")
 * @package App\Http\Controllers\Auth
 */
class WeiXinController extends Controller
{

    /**
     * @Get("/showlogin", as="s_weixin_show_login")
     */
    public function showlogin(Request $request)
    {
        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);

        //return view('auth.weixinlogin')->with("pc_jzstate", $jzstate);
    }


    /**
     * @POST("/weChatAjax", as="s_weixin_ajax_login")
     * @Get("/weChatAjax", as="s_weixin_ajax_login")
     */
    public function weChatAjax(Request $request)
    {

        $key = "weChatAjax" . \Session::getId();
        $token = \Cache::get($key);
        $cacheToken = \Cache::get($key);
        if ($cacheToken != $token) {
            return new JsonResponse([
                "type"=> 3
            ]);
        }

        $code = \Session::get('pc_jzstate', '');
        $type = 3;
        if($code){
            $openCode = \Cache::get('wxdl_'.$code);
            if($openCode != $code){
                $openCode = json_decode($openCode,true);
                if (Arr::get($openCode['data']['user'], 'uid', 0)) {
                    Auth::attempt(['code' => $code, 'mode' => 2, 'login_type'=>4 ],true);
                    $type = 1;
                }else{
                    \Session::put("advert_reg_code", $code);
                    \Session::put("wxUserInfo", json_encode($openCode['data']));
                    $type = 2;

                    //记步2 从注册扫描微信过来
                    \Session::put("regBindWxs", json_encode(["regBindWxs"=>2]));

                }
                \Cache::forget('wxdl_'.$code);
            }

        }

        return new JsonResponse([
            "type"=> $type
        ]);

    }



}