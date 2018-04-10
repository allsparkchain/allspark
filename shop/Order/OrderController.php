<?php

namespace App\Http\Controllers\Order;

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
 * @Controller(prefix="/Order")
 * @Middleware("web")
 * @Middleware("authlogin")
 * @package App\Http\Controllers
 */
class OrderController extends Controller
{

    /**
     * 历史订单列表页面
     * @Get("/orderHistoryList", as="s_order_orderHistoryList")
     */
    public function orderHistoryList(Request $request) {
        $uid = $this->getUserId();

        $list = [];
        try{
            $arr = ['uid'=>$uid,'status'=>3,'pagesize'=>100];
            $list = Curl::post('/product/getProcOrderListByUid',$arr);
            foreach ($list['data']['data'] as $key =>$item) {
                $info = json_decode($item['contents'],true);
                $list['data']['data'][$key]['userAddress'] = $info['userAddress'];
                $list['data']['data'][$key]['goods'] = $info['goods'];
                if(isset($info['specifications'])){
                    $list['data']['data'][$key]['specifications'] = $info['specifications'];
                }else{
                    $list['data']['data'][$key]['specifications'] = '';
                }

            }

        }catch (ApiException $e){

        }

        return view("Order.orderHistoryList")
            ->with('list',$list);
    }

    /**
     * 历史订单详情面
     * @Get("/orderHistoryDetail", as="s_order_orderHistoryDetail")
     */
    public function orderHistoryDetail(Request $request) {
        $uid = $this->getUserId();

        $oid = $request->get("oid",-1);
//        $oid = 29;
        try{
            $detail = Curl::post('/product/getProcOrderDetailByOid',['oid'=>$oid]);
            $info = json_decode($detail['data']['contents'],true);
            $detail['data']['userAddress'] = $info['userAddress'];
            $detail['data']['goods'] = $info['goods'];

            //3受理中，4已处理' ;
            if($detail['data']['delivery_status'] == 3 || $detail['data']['delivery_status'] == 4){
                $detail['data']['showStatus'] = getAfterSaleStatus($detail['data']['extraStatus']);
            }else{
                $detail['data']['showStatus'] = '';
            }

            if(isset($info['specifications'])){
                $detail['data']['specifications'] = $info['specifications'];
            }else{
                $detail['data']['specifications'] = '';
            }

//            $detail['data']['specifications'] = $info['specifications'];
        }catch (ApiException $e){
            return redirect(route('s_order_orderHistoryList'));
        }
        return view("Order.orderHistoryDetail")
            ->with('detail',$detail['data']);
    }


    ////////////售后
    /**
     * 可维权商品商品列表
     * @Get("/afterOrderHistoryList", as="s_order_afterOrderHistoryList")
     */
    public function afterOrderHistoryList(Request $request) {
        $uid = $this->getUserId();
//
//        try{
//            $today = strtotime(date('Y-m-d'));
//            $starttime = strtotime('- 30 day',$today);
//            $arr = ['uid'=>$uid,'status'=>3,'starttime'=>$starttime];
//
//            $list = Curl::post('/product/getProcOrderListByUid',$arr);
//            foreach ($list['data']['data'] as $key =>$item) {
//                $info = json_decode($item['contents'],true);
//                $list['data']['data'][$key]['userAddress'] = $info['userAddress'];
//                $list['data']['data'][$key]['goods'] = $info['goods'];
//                if(isset($info['specifications'])){
//                    $list['data']['data'][$key]['specifications'] = $info['specifications'];
//                }else{
//                    $list['data']['data'][$key]['specifications'] = '';
//                }
//
//            }
//
//        }catch (ApiException $e){
//
//        }

        return view("Order.AfterSale.orderList");
    }

    /**
     * 可维权商品商品列表
     * @Post("/getAfterOrderHistoryList", as="s_order_getAfterOrderHistoryList")
     */
    public function getAfterOrderHistoryList(Request $request) {
        try {
            $uid = $this->getUserId();
            $starttime = 0;
            $delivery_status = 0;

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            $type = $request->get("showtype",1);
            $show_type = 0;
            if($type == 1){
                $today = strtotime(date('Y-m-d'));
                $starttime = strtotime('- 30 day',$today);
                $show_type = 1;
                $delivery_status = 2;
            }
            if($type == 2){
                $delivery_status = 3;
                $show_type = 2;
            }
            if($type == 3){
                $delivery_status = 4;
                $show_type = 3;
            }

            $list = [];
            $arr = [
                'uid'=> $uid,
                'status'=>3,
                'starttime' => $starttime,
                'delivery_status' =>$delivery_status,
                'page'=>$page,
                'pagesize'=>$pagesize
            ];

//            var_dump($arr);die;
            $list = Curl::post('/product/getProcOrderListByUid', $arr);
            foreach ($list['data']['data'] as $key =>$item) {
                $list['data']['data'][$key]['showTypeNow'] = $show_type;

                $info = json_decode($item['contents'],true);
                $list['data']['data'][$key]['userAddress'] = $info['userAddress'];
                $list['data']['data'][$key]['goods'] = $info['goods'];
                if(isset($info['specifications'])){
                    $list['data']['data'][$key]['specifications'] = $info['specifications'];
                }else{
                    $list['data']['data'][$key]['specifications'] = '';
                }

            }

            return new JsonResponse($list);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 填写维权申请信息页面
     * @Get("/applicationAdd", as="s_order_applicationAdd")
     */
    public function applicationAdd(Request $request) {
        $uid = $this->getUserId();
        $app = app('wechat.official_account');
//        $app = '';
        return view("Order.AfterSale.applicationAdd")->with('app',$app);
    }

    /**
     * 填写维权成功页面
     * @Get("/applicationSuccess", as="s_order_applicationSuccess")
     */
    public function applicationSuccess(Request $request) {
        $uid = $this->getUserId();

        return view("Order.AfterSale.applicationSuccess");
    }

    /**
     * 留言沟通页面
     * @Get("/afterSaleChat", as="s_order_afterSaleChat")
     */
    public function afterSaleChat(Request $request) {
        $uid = $this->getUserId();

        return view("Order.AfterSale.afterSaleChat");
    }

    /**
     * 受理结果页面
     * @Get("/afterSaleRes", as="s_order_afterSaleRes")
     */
    public function afterSaleRes(Request $request) {
        $uid = $this->getUserId();

        return view("Order.AfterSale.afterSaleRes");
    }


    /**
     * 7，8，9后台处理成功查看页面
     * @Get("/afterSaleSuccessChat", as="s_order_afterSaleSuccessChat")
     */
    public function afterSaleSuccessChat(Request $request) {
        $uid = $this->getUserId();

        return view("Order.AfterSale.afterSaleSuccessChat");
    }
    /**
     * 添加申请售后接口
     * @Post("/applicationAddPost", as="s_order_applicationAddPost")
     */
    public function applicationAddPost(Request $request) {
        try {
            $uid = $this->getUserId();
            $product_order_id = $request->get("product_order_id",-1);
            $reason = $request->get("reason",'');
            $img = $request->get("img",'');


            $new_img = '';
            if(!is_null($img)){
                if(is_array($img)){
                    $img_arr = $this->uploadImg($img);
                    $new_img =  json_encode($img_arr);
                }
            }

            $status = $request->get("status",0);
            if($product_order_id <=0){
                return new JsonResponse(['status'=>201,'message'=>'缺少参数']);
            }
            if(!in_array($status,[1,2,3])){
                return new JsonResponse(['status'=>202,'message'=>'传递参数异常']);
            }
            $list = [];
            $arr = [
                'uid'=> $uid,
                'product_order_id' =>$product_order_id,
                'status' => $status,
                'reason' => $reason,
                'img'=>$new_img
            ];

            $list = Curl::post('/productOrder/addProductAfterSales', $arr);

            return new JsonResponse($list);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 申请成功获得数据接口接口
     * @Post("/getApplicationSuccessInfo", as="s_order_getApplicationSuccessInfo")
     */
    public function getApplicationSuccessInfo(Request $request) {
        try {
            $uid = $this->getUserId();
            $sales_id = $request->get("sales_id",-1);

            if($sales_id <=0){
                return new JsonResponse(['status'=>201,'message'=>'缺少参数']);
            }
            $list = [];
            $arr = [
                'sales_id'=>$sales_id
            ];

            $list = Curl::post('/productOrder/getProductAfterSales', $arr);
            if($list['data']['contents']){
                $info = json_decode($list['data']['contents'],true);
                $list['data']['product_info'] = $info['goods'];
                $list['data']['product_info']['specifications'] = $info['specifications'];
            }
            return new JsonResponse($list);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 用户提交留言信息接口
     * @Post("/addProductAfterSalesMessage", as="s_order_addProductAfterSalesMessage")
     */
    public function addProductAfterSalesMessage(Request $request) {
        try {
            $uid = $this->getUserId();
            $product_order_id = $request->get("product_order_id",-1);
            $message = $request->get("message",'');

            if($product_order_id <=0){
                return new JsonResponse(['status'=>201,'message'=>'缺少参数']);
            }
            if(strlen($message) <=0){
                return new JsonResponse(['status'=>202,'message'=>'内容必填']);
            }
            $list = [];
            $arr = [
                'uid'=>$uid,
                'product_order_id'=>$product_order_id,
                'message'=>$message
            ];


            $list = Curl::post('/productOrder/addProductAfterSalesMessage', $arr);

            return new JsonResponse($list);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 用户获得订单对应的留言信息
     * @Post("/getAfterMessageList", as="s_order_getAfterMessageList")
     */
    public function getAfterMessageList(Request $request) {
        try {
            $uid = $this->getUserId();
            $product_order_id = $request->get("product_order_id",-1);
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            if($product_order_id <=0){
                return new JsonResponse(['status'=>201,'message'=>'缺少参数']);
            }

            $arr = [
                'page'=>$page,
                'pagesize'=>$pagesize,
                'product_order_id'=>$product_order_id
            ];

            $list = Curl::post('/productOrder/getAfterMessageList', $arr);
            $list['data']['headurl'] = \Auth::getUser()->getHeadImgurl();
            return new JsonResponse($list);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 退换货信息，获得广告主信息
     * @Post("/getAfterChangePrepear", as="s_order_getAfterChangePrepear")
     */
    public function getAfterChangePrepear(Request $request) {
        try {
            $uid = $this->getUserId();
            $sales_id = $request->get("sales_id",-1);
            if($sales_id <=0){
                return new JsonResponse(['status'=>201,'message'=>'缺少参数']);
            }



//            $sales_id = 2;

            $arr = [
                'sales_id'=>$sales_id,
            ];

            $list = Curl::post('/productOrder/getProductAfterSales', $arr);
            if(!is_null($list['data'])){
                $adver_info = json_decode($list['data']['advert_info'],true);
                $list['data']['adv_info'] = $adver_info;
            }

            return new JsonResponse($list);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 提交退换货信息
     * @Post("/submitChangeInfo", as="s_order_submitChangeInfo")
     */
    public function submitChangeInfo(Request $request) {
        try {
            $uid = $this->getUserId();
            $sales_id = $request->get("sales_id",-1);
            $express_name = $request->get("express_name",'');
            $express_no = $request->get("express_no",-1);
            if($sales_id <=0 || strlen($express_name)<=0 || strlen($express_no)<=0){
                return new JsonResponse(['status'=>201,'message'=>'缺少参数']);
            }

            $arr = [
                'sales_id'=>$sales_id,
                'express_name' =>$express_name,
                'express_no' => $express_no
            ];

            $list = Curl::post('/productOrder/editProductAfterSales', $arr);

            return new JsonResponse($list);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获得订单详情
     * @Post("/getOrderDetail", as="s_order_getOrderDetail")
     */
    public function getOrderDetail(Request $request) {
        try {
            $uid = $this->getUserId();
            $oid = $request->get("oid",-1);


            if(!is_numeric($oid) || $oid <=0 ){
                return new JsonResponse(['status'=>201,'message'=>'缺少参数']);
            }

            $detail = Curl::post('/product/getProcOrderDetailByOid',['oid'=>$oid]);
            $info = json_decode($detail['data']['contents'],true);
            $detail['data']['userAddress'] = $info['userAddress'];
            $detail['data']['goods'] = $info['goods'];

            //3受理中，4已处理' ;
            if($detail['data']['delivery_status'] == 3 || $detail['data']['delivery_status'] == 4){
                $detail['data']['showStatus'] = getAfterSaleStatus($detail['data']['extraStatus']);
            }else{
                $detail['data']['showStatus'] = '';
            }

            if(isset($info['specifications'])){
                $detail['data']['specifications'] = $info['specifications'];
            }else{
                $detail['data']['specifications'] = '';
            }


            return new JsonResponse($detail);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    private function getUserId() {
        return Auth::user()->getAuthIdentifier();

    }

    private function getUserName() {
        return Auth::getUser()->getUserMobile();
    }


    private function uploadImg($array) {

        $images = [];
        foreach ($array as $key => $val){
            $app = app('wechat.official_account');
            $stream = $app->media->get($val);
            // 以内容 md5 为文件名存到本地
            $img = $stream->save(public_path().'/');
            $ossKey =  md5(time().rand().rand());
            OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path().'/'.$img);
            $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);
            $images[] = $publicObjectURL;
            //删除原图
            unlink( public_path().'/'.$img);
        }

        return $images;
    }

}