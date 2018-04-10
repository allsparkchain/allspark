<?php

namespace App\Http\Controllers\User;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductController
 * @Controller(prefix="/User")
 * @Middleware("web")
 * @Middleware("authlogin")
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * 商品详情页面
     * @Get("/productDetail", as="s_user_productDetail")
     */
    public function productDetail(Request $request) {
//        http://localhost:9095/User/productDetail?spread_id=7
        $uid = $this->getUserId();
        $article_id = $request->get('articleid',-1);
        $spreadid = $request->get('spreadid',29);
        $nid = $request->get('nid');
        if($spreadid<0){
            return redirect(route('s_order_orderHistoryList'));
        }
        try{
            $info = Curl::post('/user/getInfoBySpreadId',['spreadid'=>$spreadid])['data'];
            $article_id = $info['article_id'];


            if($info['order_no'] != $nid) {
                return redirect(route('s_order_orderHistoryList'));
//                var_dump('没有找推广id');die;
            }
        }catch (ApiException $e){
            return redirect(route('s_order_orderHistoryList'));
//            var_dump('没有找推广id');die;
        }

        $is_show = 1;
        try{
            $info = Curl::post('/article/getArticleDetailForWx2',['article_id'=>$article_id])['data'];
            if($info['specifications'] && strlen($info['specifications'])>0){
                $info['specifications'] = json_decode($info['specifications'],true);
                if(!is_array($info['specifications']) || empty($info['specifications'])){
                    $info['specifications'] = [];
                }
//                foreach ( $info['specifications'] as $key =>$val){
//                    if($val['key']){
//                        $is_show=2;
//                    }
//                }
            }else{
                $info['specifications'] = [];
            }

        }catch (ApiException $e){
            return redirect(route('s_order_orderHistoryList'));
//            var_dump('没有找到该成品');die;
        }

        Curl::post('/weixin/addQuantity',['spread_id'=>$spreadid]);



        // 每次进页面重新 设置
        \Session::forget('productDetail');

        $session = \Session::get("productDetail");
        $session = json_decode($session, true);


        if (isset($session['proinfo']) && $session['spreadid'] == $spreadid) {

        }else{
            \Session::put('productDetail', json_encode(["articleid"=>$article_id,'spreadid'=>$spreadid, "proinfo"=>$info]));
        }
        return view("Order.product_detail")
            ->with('info',$info)->with('is_show',$is_show);
    }

    /**
     * 点击购买跳至确认订单页面
     * @Post("/prepare", as="s_user_prepare")
     */
    public function prepare(Request $request) {
        $num = $request->get("num",1);
        if(!intval($num) || $num < 1){
            $num = 1;
        }
        $extra = $request->get("extra",'');

        $session = \Session::get("productDetail");
        $session = json_decode($session, true);
        if (!isset($session['proinfo'])) {
            return new JsonResponse(['status'=>201,'message'=>'数据过期']);
        }else{
            $info = $session;
            $info['num'] = $num;
            $info['extra'] = $extra;
            \Session::put('productDetail', json_encode($info));
            return new JsonResponse(['status'=>200,'message'=>'ok']);
        }
    }

    /**
     * 确认订单页面
     * @Get("/confirmOrder", as="s_user_confirmOrder")
     */
    public function confirmOrder(Request $request) {
        $uid = $this->getUserId();
        $session = \Session::get("productDetail");
        $session = json_decode($session, true);
//        var_dump($session);
        if (!isset($session['proinfo'])) {
            return redirect(route('s_order_orderHistoryList'));
//            var_dump('无之前的数据，非法');die;
            //没有之前页面的数据，，，非法过来
        }else{
            $num  = $session['num'];
            $proinfo = $session['proinfo'];
            $extra = $session['extra'];
        }
        $address = [];
        try{
            $address = Curl::post('/user/getUserAddress',['uid'=>$uid])['data'];
            if(!empty($address)){
                $address = $address[0];
            }

        }catch (ApiException $e){

        }
        return view("Order.confirmOrder")
            ->with('address',$address)
            ->with('num',$num)
            ->with('proinfo',$proinfo)
            ->with('extra',$extra)
            ;
    }

    /**
     * 购买
     * @Post("/postbuy", as="s_user_postbuy")
     */
    public function postbuy(Request $request) {
        $address = $request->get("address",-1);
        $session = \Session::get("productDetail");
        $session = json_decode($session, true);
        if (!isset($session['proinfo'])) {
            return new JsonResponse(['status'=>205,'message'=>'非法参数']);
        }
        try{

            $arr = [
                'uid' =>$this->getUserId(),
                'address_id' => $address,
                'spread_id' => $session['spreadid'],
                'extra' => $session['extra'],
                'number'=> $session['num'],
                'mode'=>1
            ];
            $post = Curl::post('/product/buyGoods', $arr);
            if(!is_null($post['data'])){

                $application = \Wechat::payment("default");
                $client = $application->jssdk;
                $debug = config('params.debug_account');


                $result = $application->order->unify([
                    'body' => $post['data']['goods_name'],
                    'out_trade_no' => $post['data']['pay_order_number'],
                    'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                    'total_fee' => $debug?'1':bcmul($post['data']['account'],100),
                    'notify_url' => config('params.notify_url'), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                    'trade_type' => 'JSAPI',
                    'openid' => $this->getUserOpenId(),
                ]);


                \Log::info('支付日志：'.json_encode([
                        'body' => $post['data']['goods_name'],
                        'out_trade_no' => $post['data']['pay_order_number'],
                        'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                        'total_fee' => $debug?'1':bcmul($post['data']['account'],100),
                        'notify_url' => config('params.notify_url'), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                        'trade_type' => 'JSAPI',
                        'openid' => $this->getUserOpenId(),
                        'result' => json_encode($result),
                    ]));

                $json = $client->bridgeConfig($result['prepay_id']);
                return new JsonResponse(['status'=>200,'message'=>'订单生成完成','json'=>$json]);
            }else{
                return new JsonResponse(['status'=>209,'message'=>'购买失败请重试']);
            }

        }catch (ApiException $e){
            return new JsonResponse(['status'=>$e->getCode(),'message'=>$e->getMessage()]);
        }
    }

    /**
     * 添加地址
     * @Post("/addAddress", as="s_user_addAddress")
     */
    public function addAddress(Request $request) {
        $mobile = $request->get("mobile",-1);
        if(!intval($mobile) || $mobile<=0){
            return new JsonResponse(['status'=>211,'message'=>'手机号输入非法']);
        }
        $realname = $request->get("realname",'');
        $address = $request->get("address",'');
        $remark = $request->get("remark",'');
        try{
            $post = Curl::post('/user/addAddress', [
                'mobile' => $mobile,
                'uid' => $this->getUserId(),
                'realname' => $realname,
                'address' => $address,
                'remark' =>$remark
            ]);
            if(!is_null($post['data'])){
                return new JsonResponse($post);
            }

        }catch (ApiException $e){
            return new JsonResponse(['status'=>$e->getCode(),'message'=>$e->getMessage()]);
        }
    }

    /**
     * 支付成功页面
     * @Get("/orderSuccess", as="s_user_orderSuccess")
     */
    public function orderSuccess(Request $request) {
        $uid = $this->getUserId();
        $session = \Session::get("productDetail");
        $session = json_decode($session, true);
        $list = [];
        if (!isset($session['proinfo'])) {
            return redirect(route('s_order_orderHistoryList'));
//            return new JsonResponse(['status'=>205,'message'=>'非法参数']);
        }
        $detail = $session;

        return view("Order.orderSuccess")
            ->with('detail',$detail);
    }

    private function getUserId() {
        return Auth::user()->getAuthIdentifier();
    }

    private function getUserName() {
        return Auth::getUser()->getUserMobile();
    }

    private function getUserOpenId() {
        return Auth::getUser()->getAuthOpenId();
    }

}