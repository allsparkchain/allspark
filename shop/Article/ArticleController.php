<?php

namespace App\Http\Controllers\Article;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use App\Services\OSS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ProductController
 * @Controller(prefix="/Article")
 * @Middleware("web",except={"getQrCode"})
 * @Middleware("authlogin",except={"liteArticleInfo","getQrCode"})
 * @package App\Http\Controllers
 */
class ArticleController extends Controller
{

    /**
     * 商品分享页面
     * @Get("/articleInfo", as="s_article_articleInfo")
     */
    public function articleInfo(Request $request) {

        $spreadid = $request->get('spreadid');
        if($spreadid){
            $nid = $request->get('nid');
            try{
                $info = Curl::post('/user/getInfoBySpreadId',['spreadid'=>$spreadid])['data'];
                $article_id = $info['article_id'];

               if($info['order_no'] != $nid) {
                   $spreadid = '';
               }
            }catch (ApiException $e){
                return redirect(route('s_order_orderHistoryList'));
                //var_dump('没有找推广id');die;
            }
            $url = config('params.app_host').'User/productDetail?spreadid='.$spreadid .'&nid='.$nid;


        }else{

            $article_id = $request->get('article_id');
            $data  = Auth::user()->getData();
            $uid = Curl::post('/user/createZmtUser',['open_id'=>$data['openid']])['data'];

            $article_product_relate_id = Curl::post('/article/getArticleProductRelateInfoByArticleId',['article_id'=>$article_id])['data']['id'];

            if(!$article_product_relate_id){
                return response()->view("errors.404", [], 404);
            }

            $post = Curl::post('/user/createSpreadQRcode', [
                'aprs' => $article_product_relate_id,
                'spreadUid' => $uid
            ]);

            $spreadid = $post['data']['id'];

            return redirect(route('s_article_articleInfo').'?spreadid='.$spreadid.'&nid='.$post['data']['order_no']);

            $url = config('params.app_host').'User/productDetail?spreadid='.$spreadid .'&nid='.$post['data']['order_no'];
        }
        try{
            $info = Curl::post('/article/getArticleDetailForWx',['article_id'=>$article_id])['data'];
            if($info['product_type'] == 3) {
                $landing_page = $info['landing_page'];
                if (strstr($landing_page, '?')) $landing_page .= '&spreadid=' . $spreadid;
                else $landing_page .= '?spreadid=' . $spreadid;
                $url = $landing_page;
            }


        }catch (ApiException $e){
            return redirect(route('s_order_orderHistoryList'));
            //var_dump('没有找到该成品');die;
        }
        $img_path = '';
        $img = Curl::post('/article/getArticleImgList',['article_id'=>$article_id,'pagesize'=>10]);
        foreach ($img['data']['data'] as $key => $val){
            $fileType = getFileType($val['img_path']);
            if($fileType != 'gif'){
                $img_path = $val['img_path'];
                break;
            }

        }

        //查看页面 次数统计
        try{

            $session_id = $request->session()->getId();
            if(!\Cache::get($session_id)){
                $uid = 0;
                $open_id = 0;
                if(\Auth::user()){
                    $data = \Auth::user()->getData();
                    $uid = $data['id'];
                    $open_id = $data['openid'];
                }
                Curl::post('/weixin/addArticleInfoViewQuantity',['article_id'=>$article_id,'uid'=>$uid,'open_id'=>$open_id]);
                \Cache::put("$session_id", 1,24*60);
            }



        }catch (ApiException $e){

        }

        //微信扫码
        try{
            $pc_spread_scan = $request->get('pc_spread_scan');
            if(\Cache::get($pc_spread_scan)){
                \Cache::put($pc_spread_scan,'success',10);
            }
        }catch (ApiException $e){

        }

        $navigation = \Cache::get('navigator');
        if(is_null($navigation)){
            $show = 1;
            \Cache::put('navigator', time(),1);
        }else{
            $show = 2;
        }

        $app = app('wechat.official_account');

        //print_r($info['article_img_path']);exit;

        return view("Article.articleInfo")->with('info',$info)->with('url',$url)->with('spreadid', $spreadid)
            ->with('app', $app)->with('img_path',$img_path)->with('show',$show);
    }

    /**
     * 小程序商品分享页面
     * @Get("/liteArticleInfo", as="s_article_liteArticleInfo")
     */
    public function liteArticleInfo(Request $request) {
        $article_id = $request->get('article_id');
        $aprs = $request->get('aprs');
        $isIphoneX = $request->get('isIphoneX');

        if(!intval($article_id) || $article_id<=0 || !intval($aprs) || $aprs<=0){
            return response()->view("errors.404", [], 404);
        }

            $article_product_relate_id = Curl::post('/article/getArticleProductRelateInfoByArticleId',['article_id'=>$article_id])['data']['id'];

            if(!$article_product_relate_id){
                return response()->view("errors.404", [], 404);
            }
        try{
            $info = Curl::post('/article/getArticleDetailForWx',['article_id'=>$article_id])['data'];
            if($info['product_type'] == 3) {
                $landing_page = $info['landing_page'];
                $url = $landing_page;
            }

        }catch (ApiException $e){
            return redirect(route('s_order_orderHistoryList'));
            //var_dump('没有找到该成品');die;
        }
        

        return view("Article.liteArticleInfo")->with('info',$info)->with('article_id',$article_id)->with('aprs',$aprs)->with('isIphoneX',$isIphoneX)
            ;
            
    }

    /**
     * 商品分享预览页面
     * @Get("/articlePreviewInfo", as="s_article_articlePreviewInfo")
     */
    public function articlepreviewInfo(Request $request) {

        $code = $request->get('code');
        try {
            $codeData = Curl::post('/article/getArticlePreview',['code'=>$code]);
            if($codeData['status'] != 200){
                return response()->view("errors.404", [], 404);
            }
            $article_id = $codeData['data']['article_id'];
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
        try{
            $info = Curl::post('/article/getArticle',['articleid'=>$article_id])['data'];
//            var_dump($info);

        }catch (ApiException $e){
            return redirect(route('s_order_orderHistoryList'));
            //var_dump('没有找到该成品');die;
        }

        return view("Article.articlePreviewInfo")->with('info',$info);
    }

    /**
     * 商品分享页面
     * @Get("/success", as="s_article_success")
     */
    public function success(Request $request) {
        $app = app('wechat.official_account');
        return view("Auth.success")->with('app', $app);

    }

    /**
     * 获取文章分类数据
     * @Post("/getCategoryList", as="s_aricle_getCategoryList")
     */
    public function getCategoryList(Request $request) {
        try {
            $arr = ['status'=>1,'type'=>2,'pagesize'=>30];
            $post = Curl::post('/industryCategory/getLists', $arr);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取最热文章数据
     * @Post("/getHotArticleList", as="s_article_getHotArticleList")
     */
    public function getHotArticleList(Request $request) {
        try {
            $page = 1;
            $pagesize = 100;
            $column_id = 1;
            $post = Curl::post('/article/columnWithArticleList', $arr = [
                'column_id'=> $column_id,
                'page' =>$page,
                'pagesize'=>$pagesize,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取最新文章数据
     * @Post("/getNewArticleList", as="s_article_getNewArticleList")
     */
    public function getNewArticleList(Request $request) {
        try {
            $page = 1;
            $pagesize = 100;
            $column_id = 2;
            $post = Curl::post('/article/columnWithArticleList', $arr = [
                'column_id'=> $column_id,
                'page' =>$page,
                'pagesize'=>$pagesize,
            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取分类文章数据
     * @Post("/getArticleList", as="s_article_getArticleList")
     */
    public function getArticleList(Request $request) {
        try {
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            $category_id = $request->get("category_id",0);
            $post = Curl::post('/article/columnCatgoryArticleList', $arr = [
                'category_id'=>$category_id,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'region_id'=> $request->get("region_id",0),
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取栏目 及 下属 所有文章数据
     * @Post("/getcolumnListwithArticleList", as="s_articlegetcolumnListwithArticleList")
     */
    public function getcolumnListwithArticleList(Request $request) {
        try {
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $post = Curl::post('/article/columnListwithArticleList', $arr = [
                'region_id'=> $request->get("region_id",0),
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 二维码
     * @Post("/getQrCode", as="s_getQrCode")
     */
    public function getQrCode(Request $request) {
        try {

            $path = $request->get('path');
            $width = $request->get('width');
            $app = app('wechat.mini_program');
            $response = $app->app_code->getQrCode($path,$width);

            $fullPath = public_path('lite')."/_app_".ltrim($path, '/');
            exec("mkdir -p ".$fullPath);

            $filename = $response->save(public_path("lite"));

            return new Response(base64_encode(file_get_contents(public_path("lite")."/".$filename)));
        } catch (\Exception $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }


}