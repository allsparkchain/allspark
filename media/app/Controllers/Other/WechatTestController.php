<?php
/**
 * Created by PhpStorm
 * author: viki
 * Date: 2018/04/11
 * Time: 12:00
 */

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Lib\Curl;

/**
 * Class WechatController
 * @Controller(prefix="/test/Other")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class WechatTestController extends Controller
{

    /**
     * @Get("/authLogin", as="s_wechat_authLogin_test")
     */
    public  function authLogin(){

        $code = Curl::post('/weixin/createCode',['return_url'=>'http://beta.pugongying.link/Other/wechat/return']);
        $wxHost = config('params.wx_host');
        $url = $wxHost.'auth/weixin/authlogin/'.$code['data'].'/1';
        return redirect($url);
    }


    /**
     * 开放平台微信网页登录
     * @Get("/login/openweixin/back", as="login_openweixin_back_test")
     * @param Request $request
     * @return mixed
     * @author: viki
     */
    public function index()
    {
        try {
            //解密
            $encrypt_data = request('encrypt_data');
            if($encrypt_data){
                $encrypt_data = json_decode(decrypt($encrypt_data),true);
                $state = isset($encrypt_data['wcode'])?$encrypt_data['wcode']:null;
                $return_url = isset($encrypt_data['return_url'])?$encrypt_data['return_url']:null;
                $login_type = isset($encrypt_data['login_type'])?$encrypt_data['login_type']:1;
            }else{
                $state = null;
            }

            $oauth = \Wechat::officialAccount('web')->oauth;
            if (!$state || !request('code') || !request('state')) return $this->jump($oauth,$encrypt_data);
            $user = $oauth->user();
            $openData = Curl::post('/weixin/getOpenUnionid', ['unionid' => $user->token->unionid]);//查询openId是否存在
            if (!$openData['data']) {//根据openID查一边
                $openData = Curl::post('/weixin/getOpenId', ['openid' => $user->id]);
                if (!$openData['data']) {//不存在添加
                    $arr = [//存openId
                        'openid' => $user->id,
                        'unionid' => $user->token->unionid,
                        'nickname' => $user->nickname,
                        'sex' => $user->original['sex'],
                        'headimgurl' => $user->original['headimgurl'],
                        'content' => json_encode($user->toArray()),
                        'add_time' => time(),
                    ];
                    $openId = Curl::post('/weixin/createOpenId', $arr);
                } else {//存在更新
                    $arr = [
                        'id' => $openData['data']['id'],
                        'openid' => $user->id,
                        'unionid' => $user->token->unionid,
                        'nickname' => $user->nickname,
                        'sex' => $user->original['sex'],
                        'headimgurl' => $user->original['headimgurl'],
                        'content' => json_encode($user->toArray()),
                    ];
                    $openId = Curl::post('/weixin/updateOpenId', $arr);
                }
            } elseif ($openData['data']['openid'] == $user->id) {//已存在
                $openId['data'] = $openData['data']['id'];
            } else {
                $arr = [
                    'id' => $openData['data']['id'],
                    'openid' => $user->id,
                    'unionid' => $user->token->unionid,
                    'nickname' => $user->nickname,
                    'sex' => $user->original['sex'],
                    'headimgurl' => $user->original['headimgurl'],
                    'content' => json_encode($user->toArray()),
                ];
                $openId = Curl::post('/weixin/updateOpenId', $arr);
            }
            Curl::post('/weixin/editCode', ['code' => $state, 'openid' => $openId['data']]);
            return redirect( $return_url  . '?code=' . $state . '&login_type=' . $login_type);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * 开放平台微信网页登录
     * @param $oauth
     * @return mixed
     * @author: viki
     */
    public function jump($oauth,$encrypt_data)
    {
        if(!$encrypt_data){
            $code = Curl::post('/weixin/createCode');
            $returnUrl = request('return_url', route('s_user_accountInfo'));
            $paramer['return_url'] = $returnUrl;
            $paramer['wcode'] = $code['data'];
            $paramer['login_type'] = request('login_type', 1);
            $encrypt_data = encrypt(json_encode($paramer));
        }

        if(strstr( env('WECHAT_OPEN_PLATFORM_WEB_CALLBACK'),'?')){
            $backurl = env('WECHAT_OPEN_PLATFORM_WEB_CALLBACK') . '&encrypt_data=' . $encrypt_data;
        }else{
            $backurl = env('WECHAT_OPEN_PLATFORM_WEB_CALLBACK') . '?encrypt_data=' . $encrypt_data;
        }


        return $oauth->scopes(['snsapi_login'])->redirect($backurl);
    }
    /**
     * 开放平台微信网页登录
     * @Get("/wechat/return", as="test_wechat_return_test")
     * @param Request $request
     * @return mixed
     * @author: viki
     */
    public function return()
    {
        $code = request('code');
        $loginType = request('login_type', 1);
        $openCode = Curl::post('/weixin/getOpenCode', ['code' => $code, 'login_type' => $loginType]);
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
                }
                //绑定openId
                Curl::post('/weixin/bindOpenId', [
                    'uid' => $post['data']['id'],
                    'code' => $code,
                    'status' => 'auto',
                    'type' => $loginType
                ]);
                $openCode = Curl::post('/weixin/getOpenCode', ['code' => $code, 'login_type' => $loginType]);
            }
        }
        if (\Illuminate\Support\Arr::get($openCode['data']['user'], 'uid', 0)) {
            \Auth::attempt(['code' => $code, 'mode' => 2, 'login_type' => $loginType], true);
            return redirect(route('s_index_index'));
        } else {
            session(['zmt_reg_code' => $code]);
            session(['wxUserInfo' => json_encode($openCode['data'])]);
            session(['regBindWxs' => '{"regBindWxs":2}']);
            return redirect(route('s_regBindMobile'));
        }
    }
}