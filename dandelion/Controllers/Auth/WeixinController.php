<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Lib\Curl;
use EasyWeChat\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Overtrue\LaravelWeChat\Facade;
use SocialiteProviders\Weixin\WeixinExtendSocialite;
use Auth;
use EasyWeChat\OpenPlatform\Server\Guard;
use EasyWeChat\Kernel\Messages\Article;

/**
 * Class WeixinControllers
 * @Controller(prefix="/auth/weixin")
 * @Middleware("web", except={"openPlatform"})
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
                    Auth::attempt(['code' => $code, 'mode' => 2, 'login_type'=>1 ],true);
                    $type = 1;
                }else{

                    \Session::put("pc_reg_code", $code);
                    \Session::put("wxUserInfo", json_encode($openCode['data']));
                    $type = 2;

                }
                \Cache::forget('wxdl_'.$code);
            }

        }

        return new JsonResponse([
            "type"=> $type
        ]);

    }

    /**
     * @Post("/open-platform", as="s_weixin_open_platform")
     */
    public function openPlatform() {
        $openPlatform = \Wechat::openPlatform('default');

        $server = $openPlatform->server; // Done!

        return $server->serve(); // Done!
        //   $openPlatform->getPreAuthorizationUrl('https://easywechat.com/callback'); // 传入回调URI即可
    }

    /**
     * @Get("/callback", as="s_weixin_callback")
     */
    public function callback(Request $request) {


        $id = $request->get('id');

        $phpPath = config('params.php_path');

        $rand = 'CACHE'.date('YmdHis').rand(10000,99999);
        \Cache::put($rand, 1,10);

        $process = shell_exec($phpPath.' '.public_path('../').'artisan command:ArticleUploadStript '.$id.' '.$request->get('auth_code').' '.$rand.'  > /dev/null  & ');

       // return redirect(route('s_weixin_success',['rand'=>$rand]));

    return new Response('<p>同步中。。。。</p><script>location.href="'.route('s_weixin_success',['rand'=>$rand]).'"</script>');

    }

    /**
     * @Get("/uploadImage", as="s_weixin_uploadImage")
     */
    public function uploadImage(Request $request) {

        file_put_contents('abc.jpg',file_get_contents('https://jianzhiwangluo.oss-cn-shanghai.aliyuncs.com/203ca2adaa2a444a8ddbb3a6f88c5d6c'));
        $app = \Wechat::officialAccount('default');
        $res = $app->material->delete('l-eLfYmFrZqqbVV-QjvznFixzIfy3yfQdmaYlsceGpY');
        //$res = $app->material->uploadImage(public_path().'abc.jpg');
        dd($res);
    }

    /**
     * @Get("/success", as="s_weixin_success")
     */
    public function success(Request $request) {

       $rand = $request->get('rand');

        while (true){
            sleep(1);
            if(\Cache::get($rand) == 2){
                echo '同步成功';
                break;
            }
        }

    }



}