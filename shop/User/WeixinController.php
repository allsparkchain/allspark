<?php

namespace App\Http\Controllers\User;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * Class ProductController
 * @Controller(prefix="/auth/weixin")
 * @Middleware("web", except={"paymentCallback"})
 * @package App\Http\Controllers
 */
class WeixinController extends Controller
{
    /**
     * @Get("/login/{jzstate}/{login_type}", as="s_weixin_login")
     */
    public function login(Request $request, $jzstate, $login_type) {
        /**
         * @var \SocialiteProviders\Weixin\Provider $weixin
         */
        $weixin = \Socialite::with('weixin');
        \Session::put("wexin_jzstate", $jzstate);
        \Session::put("wexin_login_type", $login_type);

        //$url = $weixin->redirect()->getTargetUrl();


        $redirectResponse = $weixin->redirect();

        return $redirectResponse;
        //return view('auth.weixinlogin')->with("url", $url);
    }

    /**
     * @Get("/payment", as="s_weixin_payment")
     */
    public function payment() {

        //$payment = app('Wechat');
        /**
         * @var \EasyWeChat\Payment\Application $application
         */
        $application = \Wechat::payment("default");
        $client = $application->jssdk;
        $json = $client->bridgeConfig(1);
        try {
            $result = $application->order->unify([
                'body' => '腾讯充值中心-QQ会员充值',
                'out_trade_no' => time(),
                'spbill_create_ip' => '106.15.198.178', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                'total_fee' => 1,
                'notify_url' => 'http://weixin.pugongying.link/auth/weixin/paymment/callback', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI',
                'openid' => 'ojQ65wbKI97dP6MfbbgIR3GoM9Us',
            ]);
        } catch (InvalidConfigException $e) {
        }

        //$application->order->
        $json = $client->bridgeConfig($result['prepay_id']);

        return view('Auth.payment')->with('json', $json);

    }

    /**
     * @Get("/payment/callback", as="s_weixin_payment_callback")
     * @Post("/payment/callback", as="s_weixin_payment_callback")
     */
    public function paymentCallback() {
        $response = null;
        try {
            /**
             * @var \EasyWeChat\Payment\Application $app
             */
            $app = \Wechat::payment("default");
            $response = $app->handlePaidNotify(function ($message, $fail) {
                // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
                ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
                if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                    // 用户是否支付成功
                    Curl::post('/product/buyReturn', ['pay_order_number'=>$message['out_trade_no'],'return_code'=>$message['return_code'],'message'=>json_encode($message)]);
                    \Log::info("支付成功", $message);


                    $weixin_message  = Curl::post('/weixin/getWeiXinUserMessage', ['pay_order_number'=>$message['out_trade_no']]);
                    \Log::info("推送日志", $weixin_message);
                    //推送微信信息
                    if($weixin_message['status']==200 && $weixin_message['data']){
                       $app = app('wechat.official_account');
                        $list = $app->customer_service->list();
                        \Log::info("推送客服", $list);
                        $kf = $list['kf_list'][0]['kf_account'];
                        // \Log::info("推送客服", $kf);
                        $service_weixin = $app->customer_service->message($weixin_message['data']['content'])
                        ->from($kf)
                        ->to($weixin_message['data']['openid'])
                        ->send();
                        \Log::info("推送结果", $service_weixin);
                    }
                } else {
                    \Log::info("支付失败", $message);
                    Curl::post('/product/buyReturn', ['pay_order_number'=>$message['out_trade_no'],'return_code'=>$message['return_code'],'message'=>json_encode($message)]);
                    return $fail('通信失败，请稍后再通知我');
                }


                return true; // 返回处理完成
            });
        } catch (\Exception $e) {
            \Log::info("异常状态", $e->getTrace());
        }

        return $response; // return $response;

    }


    /**
     * @Get("/token/check", as="s_weixin_token_check")
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @throws ApiException
     */
    public function tokenCheck(Request $request)
    {
        return redirect(route('s_weixin_token_checkback',$request->all()));
    }
    /**
     * @Get("/token/check", as="s_weixin_token_checkback")
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @throws ApiException
     */
    public function tokenCheckback(Request $request) {

        try {
            $wCode = ($request->session()->pull("wexin_jzstate"));
            $loginType = ($request->session()->pull("wexin_login_type"));//查询openId是否存在

            if(!$wCode){
                return redirect(route('s_article_success'));
            }

            /**
             * @var \SocialiteProviders\Weixin\Provider $weixin
             */
            $weixin = \Socialite::with('weixin');

            $user = $weixin->user();
            //$wCode = ($request->session()->pull("wexin_jzstate"));
            //$loginType = ($request->session()->pull("wexin_login_type"));//查询openId是否存在
            $openData = Curl::post('/weixin/getOpenUnionid', ['unionid' => $user->getRaw()['unionid']]);

            if (!$openData['data']) {
                //根据openID查一边
                $openData = Curl::post('/weixin/getOpenId', ['openid' => $user->getId()]);
                //不存在添加
                if(!$openData['data']){
                    //存openId
                    $arr = [
                        'openid' => $user->getId(),
                        'unionid' => $user->getRaw()['unionid'],
                        'nickname' => $user->getNickname(),
                        'sex' => $user->getRaw()['sex'],
                        'headimgurl' => $user->getAvatar(),
                        'content' => json_encode($user->getRaw()),
                        'add_time' => time(),
                    ];
                    $openId = Curl::post('/weixin/createOpenId', $arr);
                }else{//存在更新
                    $arr = [
                        'id' => $openData['data']['id'],
                        'openid' => $user->getId(),
                        'unionid' => $user->getRaw()['unionid'],
                        'nickname' => $user->getNickname(),
                        'sex' => $user->getRaw()['sex'],
                        'headimgurl' => $user->getAvatar(),
                        'content' => json_encode($user->getRaw()),
                    ];
                    $openId = Curl::post('/weixin/updateOpenId', $arr);
                }
            } elseif($openData['data']['openid'] == $user->getId() ) {
                //已存在
                $openId['data'] = $openData['data']['id'];
            } else{
                $arr = [
                    'id' => $openData['data']['id'],
                    'openid' => $user->getId(),
                    'unionid' => $user->getRaw()['unionid'],
                    'nickname' => $user->getNickname(),
                    'sex' => $user->getRaw()['sex'],
                    'headimgurl' => $user->getAvatar(),
                    'content' => json_encode($user->getRaw()),
                ];
                $openId = Curl::post('/weixin/updateOpenId', $arr);
            }
        } catch (ApiException $e) {
        }

        try {
                Curl::post('/weixin/editCode', ['code' => $wCode, 'openid' => $openId['data']]);
            } catch (ApiException $e) {

            }
            //微信端购买用户
                if ($loginType == 2) {
                    //创建用户并绑定
                    Curl::post('/weixin/createOpenUser', ['open_id' => $openId['data']]);
                    Auth::attempt(['code' => $wCode, 'mode' => 2, 'login_type' => 2], true);
                    return redirect($request->session()->pull('web_redirect_uri'));

                } else {
                    $openCode = Curl::post('/weixin/getOpenCode', ['code' => $wCode, 'login_type' => $loginType]);

                    //不存在
                    if (!$openCode['data']['user']) {
                        //1查询有没有其他业务的的用户
                        $loginData = Curl::post('/weixin/getLoginUser', ['open_id' => $openCode['data']['wx_open_id']]);
                        //if有的话 自动注册
                        if ($loginData['data']) {
                            if ($loginType == 1) {//pc  t_user_login
                                $post = Curl::post('/user/autoRegister', [
                                    'mobile' => $loginData['data']['username'],
                                    'password' => $loginData['data']['password'],
                                    'order_number' => 1
                                ]);

                            } elseif ($loginType == 3) {//广告主代理  t_advert_user_login
                                $post = Curl::post('/advert/autoRegister', [
                                    'mobile' => $loginData['data']['username'],
                                    'password' => $loginData['data']['password'],
                                    'order_number' => 1
                                ]);

                            } elseif ($loginType == 4) {//广告主  t_advert_user_login
                                $post = Curl::post('/advertOwner/autoRegister', [
                                    'mobile' => $loginData['data']['username'],
                                    'password' => $loginData['data']['password'],
                                    'order_number' => 1
                                ]);
                            } elseif ($loginType == 5) {//自媒体代理  t_advert_user_login
                                $post = Curl::post('/smedia/autoRegister', [
                                    'mobile' => $loginData['data']['username'],
                                    'password' => $loginData['data']['password'],
                                    'order_number' => 1
                                ]);
                            }


                            //绑定openId
                            Curl::post('/weixin/bindOpenId', [
                                'uid' => $post['data']['id'],
                                'code' => $wCode,
                                'status' => 'auto',
                                'type' => $loginType
                            ]);
                            $openCode = Curl::post('/weixin/getOpenCode', ['code' => $wCode, 'login_type' => $loginType]);
                        }
                    }
                    \Cache::put('wxdl_' . $wCode, json_encode($openCode), 10);
                }
        return redirect(route('s_article_success'));

    }




}