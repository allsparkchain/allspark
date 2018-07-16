<?php

namespace App\Http\Controllers\Share;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use App\Services\OSS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductController
 * @Controller(prefix="/Share")
 * @Middleware("web")
 * @Middleware("authlogin",except={"share","liteshare"})
 * @package App\Http\Controllers
 */
class ShareController extends Controller
{

    /**
     * 菜单进入创建/登录用户，获得信息进入share页面
     * @Get("/wantshare", as="s_share_wantshare")
     */
    public function wantshare(Request $request) {


        $data  = \Auth::user()->getData();
        $inviteInfo = Curl::post('/user/createZmtUser',['open_id'=>$data['openid'],'getCode'=>1])['data'];
        \Cache::forever($inviteInfo['invite_code'],$inviteInfo);
        return redirect(route('s_share_share').'?invite_code='.$inviteInfo['invite_code']);

    }

    /**
     * 邀请分享页面
     * @Get("/share", as="s_share_share")
     */
    public function share(Request $request) {

        //没传值404
        $invite_code = $request->get("invite_code",'');
        $is_share = $request->get("is_share",'');
        if(strlen($invite_code)<=0 || is_null($invite_code)){
            return response()->view("errors.404", [], 404);
        }




        $handurl = \Cache::get($invite_code);
        \Auth::logout();
//        return view("Share.share")->with('invite_code',$invite_code)->with('handurl',$handurl['headImgurl'])->with('realname',$handurl['realname']);
        $app = app('wechat.official_account');

        if (!$handurl['headImgurl']) {
            $list = \DB::select('select * from t_user_info 
left join t_weixin_user_relate on t_weixin_user_relate.uid = t_user_info.uid
left join t_weixin_user on t_weixin_user.id = t_weixin_user_relate.wx_id
where t_user_info.invite_code = "'.$invite_code.'"');
            $handurl['headImgurl'] = $list[0] ? $list[0]->headimgurl : '';
            $handurl['realname'] = $list[0] ? $list[0]->nickname : '';
        }

        return view("Share.share")->with('app', $app)
            ->with('nickname',$handurl['realname'])
            ->with('headimgurl',$handurl['headImgurl'])
            ->with('invite_code',$invite_code)->with('is_share',$is_share);
    }

    /**
     * 邀请分享页面
     * @Get("/liteshare", as="s_share_liteshare")
     */
    public function liteshare(Request $request) {

        //没传值404
        $invite_code = $request->get("invite_code",'');
        $is_share = $request->get("is_share",'');
        if(strlen($invite_code)<=0 || is_null($invite_code)){
            //return response()->view("errors.404", [], 404);
            return view("Share.liteShare")
            ->with('nickname','')
            ->with('headimgurl','')
            ->with('invite_code',$invite_code)->with('is_share',$is_share);
        }




        $handurl = \Cache::get($invite_code);
        \Auth::logout();
//        return view("Share.share")->with('invite_code',$invite_code)->with('handurl',$handurl['headImgurl'])->with('realname',$handurl['realname']);

        return view("Share.liteShare")
            ->with('nickname',$handurl['realname'])
            ->with('headimgurl',$handurl['headImgurl'])
            ->with('invite_code',$invite_code)->with('is_share',$is_share);
    }


    /**
     * 邀请分享承载页面
     * @Get("/shareNext", as="s_share_shareNext")
     */
    public function shareNext(Request $request) {

        //授权，预绑定
        $app = app('wechat.official_account');
        $data  = Auth::user()->getData();
        $openData = Curl::post('/weixin/getOpenId', ['openid' => $data['openid']]);
        $inviteCode = $request->get("invite_code");
        if($inviteCode){
            $arr = [
                'invite_code' => $inviteCode,
                'wx_id' => $openData['data']['id'],
                'login_type' => 1,
            ];
            Curl::post('/user/userPreInvite', $arr);
        }
        try {
            Curl::post('/user/createZmtUser', ['open_id' => $data['openid']])['data'];
        } catch (ApiException $e) {
        }

        return view("Share.shareNext");
    }
     /**
     * spreadCity
     * @Get("/spreadCity", as="s_share_spreadCity")
     */
    public function spreadCity(Request $request) {

       


        return view("Share.spreadCity");
    }


    /**
     * 推广页面
     * @Get("/spread", as="s_share_spread")
     */
    public function spread(Request $request) {

        return view("Share.spread");
    }

    /**
     * 推广页面详情列表
     * @Get("/spreadList", as="s_share_spreadList")
     */
    public function spreadList(Request $request) {

        return view("Share.spreadList");
    }

    /**
     * 首页
     * @Get("/index", as="s_share_index")
     */
    public function index(Request $request) {
        $data  = Auth::user()->getData();
        $openData = Curl::post('/weixin/getOpenId', ['openid' => $data['openid']]);
        $inviteCode = $request->get("invite_code");
        if($inviteCode){
            $arr = [
                'invite_code' => $inviteCode,
                'wx_id' => $openData['data']['id'],
                'login_type' => 1,
            ];
            Curl::post('/user/userPreInvite', $arr);
        }


        try {
            Curl::post('/user/createZmtUser', ['open_id' => $data['openid']])['data'];
        } catch (ApiException $e) {
            
        }

        return view("Share.index")->with('data',$data);
    }

    /**
     * 分享完成页面
     * @Get("/success", as="s_share_success")
     */
    public function success(Request $request) {

        $code = Curl::post('/weixin/createCode',['return_url'=>'https://www.pugongying.link/Other/wechat/return']);

        $data  = Auth::user()->getData();

        $openData = Curl::post('/weixin/getOpenId', ['openid' => $data['openid']]);

        Curl::post('/weixin/editCode', ['code' => $code['data'], 'openid' => $openData['data']['id']]);

        $codeData = Curl::post('/weixin/getWxCode', ['code' =>$code['data']]);

        $url = ($codeData['data']['return_url'].'?code='.$code['data'].'&login_type=1');

        return view("Share.success")->with('url', $url);

    }


    /**
     * 绑定邀请用户
     * @Get("/inviteBind", as="s_share_inviteBind")
     */
    public function inviteBind(Request $request) {

            $app = app('wechat.official_account');
            $data  = Auth::user()->getData();
            $user = $app->user->get($data['openid']);


            $openData = Curl::post('/weixin/getOpenId', ['openid' => $data['openid']]);

            //echo $openData['data']['id'];exit;


            $inviteCode = $request->get("invite_code");
            $arr = [
                'invite_code' => $inviteCode,
                'wx_id' => $openData['data']['id'],
                'login_type' => 1,
            ];

            Curl::post('/user/userPreInvite', $arr);



            ///
            if(!isset($user['subscribe']) || $user['subscribe']!=1){

                return redirect(route('s_user_contactWechatGz'));
            }


            $code = Curl::post('/weixin/createCode',['return_url'=>'https://www.pugongying.link/Other/wechat/return']);

            $data  = Auth::user()->getData();

            $openData = Curl::post('/weixin/getOpenId', ['openid' => $data['openid']]);

            Curl::post('/weixin/editCode', ['code' => $code['data'], 'openid' => $openData['data']['id']]);

            $codeData = Curl::post('/weixin/getWxCode', ['code' =>$code['data']]);

            $url = ($codeData['data']['return_url'].'?code='.$code['data'].'&login_type=1');


           return redirect($url);

    }








}