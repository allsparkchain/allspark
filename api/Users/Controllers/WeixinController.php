<?php

namespace App\Users\Controllers;
use App\Utils\Defines;

use App\Users\Services\Weixin;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Symfony\Component\HttpFoundation\Request;

/**
 * @path /weixin
 */
class WeixinController
{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Message
     */

    /**
     * @inject
     * @var Weixin
     */
    public $weixin;

    /**
     * @inject
     * @var ErrorConst
     */
    public $errorConst;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;

    /**
     * @inject
     * @var Request
     */
    public $request;


    /**
     * 生成code
     *
     * @route POST /createCode
     *
     * @return array
     */
    public function createCode() {
        try {
            $rs = $this->weixin->createCode();
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 更新code
     *
     * @route POST /editCode
     * @param string $code {@v required}
     * @param int $openid {@v min:1}
     * @return array
     */
    public function editCode($code, $openid) {
        try {
            $this->weixin->editCode($code, $openid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 生成code
     *
     * @route POST /getOpenCode
     * @param string $code {@v required}
     * @param int $login_type
     *
     * @return array
     */
    public function getOpenCode($code, $login_type = 0) {
        try {
            $rs = $this->weixin->getOpenCode($code, $login_type);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }



    /**
     * 生成openId
     *
     * @route POST /createOpenId
     *
     * @param string $openid {@v required}
     * @param string $nickname
     * @param string $sex
     * @param string $headimgurl
     * @param string $content
     *
     * @return array
     */
    public function createOpenId($openid, $nickname, $sex, $headimgurl, $content) {
        try {
            $rs = $this->weixin->createOpenId($openid, $nickname, $sex, $headimgurl, $content);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 查询openId
     *
     * @route POST /getOpenId
     *
     * @param string $openid {@v required}
     *
     * @return array
     */
    public function getOpenId($openid) {
        try {
            $rs = $this->weixin->getOpenId($openid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 查询openId
     *
     * @route POST /bindOpenId
     * @param string $uid {@v required}
     * @param string $code {@v required}
     *
     * @return array
     */
    public function bindOpenId($uid, $code) {
        try {
            $rs = $this->weixin->bindOpenId($uid, $code);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 用户登录
     * @route POST /login
     * @param string $code
     * @param int $login_type
     * @return array
     */
    public function login($code, $login_type) {
        try {
            $rs = $this->weixin->login($code, $login_type);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 创建openId 用户
     * @route POST /createOpenUser
     * @param string $open_id
     * @return array
     */
    public function createOpenUser($open_id){
        try {
            $this->weixin->createOpenUser($open_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * @Get("/login/{jzstate}/{login_type}", as="s_weixin_login_test")
     */
    public function logins(Request $request, $jzstate, $login_type) {
        /**
         * @var \SocialiteProviders\Weixin\Provider $weixin
         */
        $weixin = \Socialite::with('weixin');
        \Session::put("wexin_jzstate", $jzstate);
        \Session::put("wexin_login_type", $login_type);

        $redirectResponse = $weixin->redirect();

        return $redirectResponse;
        //return view('auth.weixinlogin')->with("url", $url);
    }


    /**
     * @Get("/authlogin/{jzstate}/{login_type}", as="s_weixin_auth_login_test")
     * @param Request $request
     * @param $jzstate
     * @param $login_type
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws ApiException
     */
    public function authLogin(Request $request, $jzstate, $login_type) {

        /**
         * @var \SocialiteProviders\Weixin\Provider $weixin
         */
        $weixin = \Socialite::with('weixin');

        \Session::put("wexin_jzstate", $jzstate);
        \Session::put("wexin_login_type", $login_type);

        $redirect_url = config('services.weixin.redirect');
        $weixin->redirectUrl($redirect_url.'?pgy_code='.$jzstate.'&wexin_login_type='.$login_type);

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
            $wCode =        $request->get('pgy_code');
            $loginType =    $request->get('wexin_login_type');
            if(!$wCode && !$loginType){
                $wCode = ($request->session()->pull("wexin_jzstate"));
                $loginType = ($request->session()->pull("wexin_login_type"));//查询openId是否存在
                if(!$wCode){
                    return redirect(route('s_article_success'));
                }
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

        $codeData = Curl::post('/weixin/getWxCode', ['code' => $wCode]);
        if($codeData && $codeData['status']=200) {
            if(isset($codeData['data']['return_url']) && $codeData['data']['return_url'] ){
                return redirect($codeData['data']['return_url'].'?code='.$wCode.'&login_type='.$loginType);
            }

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
