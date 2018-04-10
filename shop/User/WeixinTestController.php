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
 * @Controller(prefix="/test/auth/weixin")
 * @Middleware("web", except={"paymentCallback"})
 * @package App\Http\Controllers
 */
class WeixinTestController extends Controller
{
    /**
     * @Get("/login/{jzstate}/{login_type}", as="s_weixin_login_test")
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
     * @Get("/token/check", as="s_weixin_token_check_test")
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @throws ApiException
     */
    public function tokenCheck(Request $request) {


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
            //Auth::attempt(['code' => $wCode, 'mode' => 2, 'login_type' => 2], true);
            $url = 'http://weixin.pugongying.link/test/auth/weixin/loginTest?wCode='.$wCode;
            return redirect($url);

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


    /**
     * @Get("/loginTest", as="s_weixin_loginTest")
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @throws ApiException
     */
    public function loginTest(Request $request) {
        $wCode = $request->get('wCode');
        Auth::attempt(['code' => $wCode, 'mode' => 2, 'login_type' => 2], true);
        return redirect(\Cache::get('web_redirect_uri'));
    }






}
