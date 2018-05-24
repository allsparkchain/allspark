<?php

namespace App\Http\Controllers\Goods;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * Class GoodsController
 * @Controller(prefix="/Goods")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class GoodsController extends Controller
{
    
    /**
     * 商品详情信息
     * @Get("/detailData", as="s_goods_detailData")
     * @param Request $request
     * @return JsonResponse
     */
    public function detailData(Request $request) {
        $errorResponseArr = [
            'status' => '404',
            'message' => ''
        ];
        $id = $request->get('id');
        
        //商品信息
        try {
            $post = Curl::post('/product/getGoodsShow', $arr = [
                'product_id' => $id
            ]);            
        } catch (ApiException $e) {
        }
        
        if(empty($post['data'])){
            return new JsonResponse($errorResponseArr);
        }
        
        $post['data']['percent_commission'] = 0;
        $post['data']['percent_account'] = 0;

        if($post['data']['percent_key'] && is_array($post['data']['percent_key'])){
            foreach ($post['data']['percent_key'] as $k => $v){
                if($v['type']==3){
                    $post['data']['percent_commission'] = $v['contents']['percent'];
                    $post['data']['percent_account'] = $v['contents']['account'];
                }
            }
        }
        
        $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';

        try {
            //统计 查看页面次数
             Curl::post('/weixin/addProductQuantity', $arr = [
                'product_id' => $id,
                'uid' => $user ? $user : 0
            ]);
        } catch (ApiException $e) {

        }

        $ori_path = $post['data']['img_path'];
        $extension = explode('/',$ori_path);
        $name = $extension[count($extension) -1];

        $new_path325 = md5($name.'_325_325');
        $post['data']['new_path325'] = str_replace($name,$new_path325,$ori_path);
        
        return new JsonResponse($post);
    }
    
    /**
     * 商品列表页面
     * @Get("/lists", as="s_goods_lists")
     * @param Request $request
     * @return mixed
     */
    public function lists(Request $request) {

        return view("Goods.lists");
    }
    
    /**
     * 商品详情页面
     * @Get("/detail", as="s_goods_detail")
     */
    public function detail0521(Request $request) {
        return view("Goods.detail");
    }

    /**
     * 商品详情页面(旧)
     * 
     */
    public function detail(Request $request) {
        $id = $request->get('id');
        if(!$id || is_null($id) || !intval($id)){
            return redirect(route('s_goods_lists'));
        }
        try {
            $post = Curl::post('/product/getGoodsShow', $arr = [
                'product_id' => $id
            ]);
        } catch (ApiException $e) {

        }

        if(empty($post['data'])){
            $status = 404;
            if (view()->exists("errors.{$status}")){
                return response()->view("errors.{$status}", [], $status);
            }

            return redirect(route('s_goods_lists'));
        }


        $post['data']['percent_commission'] = 0;
        $post['data']['percent_account'] = 0;

        if($post['data']['percent_key'] && is_array($post['data']['percent_key'])){
            foreach ($post['data']['percent_key'] as $k => $v){
                if($v['type']==3){
                    $post['data']['percent_commission'] = $v['contents']['percent'];
                    $post['data']['percent_account'] = $v['contents']['account'];
                }
            }
        }
        $wxHost = config('params.wx_host');
        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);
        \Session::put("pc_jzstate", $jzstate);
        \Cache::put("wxdl_".$jzstate, $jzstate,30);

        $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';


        try {
            //统计 查看页面次数
             Curl::post('/weixin/addProductQuantity', $arr = [
                'product_id' => $id,
                'uid' => $user ? $user : 0
            ]);
        } catch (ApiException $e) {

        }


        $ori_path = $post['data']['img_path'];
        $extension = explode('/',$ori_path);
        $name = $extension[count($extension) -1];

        $new_path325 = md5($name.'_325_325');
        $post['data']['new_path325'] = str_replace($name,$new_path325,$ori_path);

        return view("Goods.detail")
            ->with('user', $user)
            ->with('pc_jzstate', $jzstate)
            ->with('wxHost',$wxHost)
            ->with('res',$post['data']);
    }

    /**
     * 商品详情页面
     * @Get("/previewDetail", as="s_goods_previewDetail")
     */
    public function previewDetail(Request $request) {
        $id = $request->get('id');
        if(!$id || is_null($id) || !intval($id)){
            return redirect(route('s_goods_lists'));
        }
        try {
            $post = Curl::post('/product/getGoodsShow', $arr = [
                'product_id' => $id,
                'status' =>'1,2'
            ]);
        } catch (ApiException $e) {

        }

        if(empty($post['data'])){
            $status = 404;
            if (view()->exists("errors.{$status}")){
                return response()->view("errors.{$status}", [], $status);
            }

            return redirect(route('s_goods_lists'));
        }


        $post['data']['percent_commission'] = 0;
        $post['data']['percent_account'] = 0;

        if($post['data']['percent_key'] && is_array($post['data']['percent_key'])){
            foreach ($post['data']['percent_key'] as $k => $v){
                if($v['type']==3){
                    $post['data']['percent_commission'] = $v['contents']['percent'];
                    $post['data']['percent_account'] = $v['contents']['account'];
                }
            }
        }
        $wxHost = config('params.wx_host');
        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);
        \Session::put("pc_jzstate", $jzstate);
        \Cache::put("wxdl_".$jzstate, $jzstate,30);

        $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';


        try {
            //统计 查看页面次数
            Curl::post('/weixin/addProductQuantity', $arr = [
                'product_id' => $id,
                'uid' => $user ? $user : 0
            ]);
        } catch (ApiException $e) {

        }


        $ori_path = $post['data']['img_path'];
        $extension = explode('/',$ori_path);
        $name = $extension[count($extension) -1];

        $new_path325 = md5($name.'_325_325');
        $post['data']['new_path325'] = str_replace($name,$new_path325,$ori_path);

        return view("Goods.previewDetail")
            ->with('user', $user)
            ->with('pc_jzstate', $jzstate)
            ->with('wxHost',$wxHost)
            ->with('res',$post['data']);
    }

    /**
     * 文章边界页面
     * @Get("/editor", as="s_goods_edit")
     * @Middleware("auth")
     */
    public function editor(Request $request) {
        $id = $request->get('id');
        $article_id = $request->get('article_id');

        if($article_id){
            $article = Curl::post('/article/getArticle',['articleid'=>$article_id]);
            $id = $article['data']['product_id'];
        }
        try {
            $post = Curl::post('/product/getGoodsShow', $arr = [
                'product_id' => $id
            ]);
        } catch (ApiException $e) {

        }

        try {
//            $article_category = getRedisData('articleCategoryWrite'.$id,'/industryCategory/getCategoryWithIndustryList',['type'=>2]);
//            $article_category = Curl::post('/industry/getLists', ['status'=>1,'type'=>2]);
            $article_category = getRedisData('articleCategoryWrite'.$id,'/industryCategory/getIndustryCategoryListWithIndustry',['type'=>2]);
            $article_category = $article_category['data']['data'];
//            var_dump($article_category);
        } catch (ApiException $e) {
            $article_category = [];
        }

        return view("Goods.editor")->with('res',$post['data'])->with('article_category',$article_category);
    }

    /**
     * 文章边界页面
     * @Get("/preview", as="s_goods_preview")
     */
    public function preview(Request $request) {

        return view("Goods.preview");

    }

    /**
     * 文章预览
     * @Get("/success", as="s_goods_success")
     */
    public function success(Request $request) {

        return view("Goods.success");

    }



    /**
     * 商品列表页面Ajax
     * @Post("/getGoodsList", as="s_goods_getGoodsList")
     */
    public function getGoodsList(Request $request) {
        try {

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $category_id = $request->get("category_id",0);
            $region_id = $request->get("region_id",0);
            $arr = [
                'page' => $page,
                'pagesize' => $pagesize,
            ];
            if($category_id >0 ){
                $arr['category_id']= $category_id;
            }
            if($region_id >0 ){
                $arr['region_id']= $region_id;
            }
            $post = getRedisData('getGoodsList'.md5(json_encode($request->all())),'/product/getGoodsShowList',$arr);

            foreach ($post['data']['data'] as $key =>$val){
                if($val['percent_key'] && is_array($val['percent_key'])){
                    foreach ($val['percent_key'] as $k => $v){
                        if($v['type']==3){
                            $post['data']['data'][$key]['percent_commission'] = $v['contents']['percent'];
                            $post['data']['data'][$key]['percent_account'] = $v['contents']['account'];
                        }
                    }
                }
                $post['data']['data'][$key]['synopsis'] = $val['synopsis']?$val['synopsis']:'';
            }

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 商品分类列表页面Ajax
     * @Post("/getCategorList", as="s_goods_getCategorList")
     */
    public function getCategorList(Request $request) {
        try {
            $arr = ['status'=>1,'type'=>1];
//            $post = Curl::post('/productCategory/getProductCategoryList', ['status'=>1]);
//            $post = Curl::post('/industry/getLists',$arr );
//            $post = getRedisData('GoodsgetCategoryList'.md5(json_encode($request->all())),'/industry/getLists',$arr);
            $arr = ['type'=>1,'status'=>1];
            $post = getRedisData('GoodsgetCategoryList'.md5(json_encode($request->all())),'/industryCategory/getLists',$arr);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     *
     * @Post("/articleAdd", as="s_article_add")
     * @Middleware("auth")
     */
    public function articleAdd(Request $request) {
        try {

            $paramer['id'] = $request->get('id', 0);
            $paramer['name'] = $request->get('name');
            $paramer['content'] = $request->get('content');
            $paramer['article_product_id'] = $request->get('product_id');
            $paramer['spiltway'] = 3;
            $paramer['author'] = $this->getUserId();

            $paramer['article_category_id'] = $request->get('article_category_id',0);

            $status = $request->get('status');


            if($status==1){
                $paramer['status'] = 3;//发布
            }else{
                $paramer['status'] = 5;//草稿
            }

            if($paramer['id']){
                $data = Curl::post('/article/editArticle',
                    $paramer
                );
            }else{
                $data = Curl::post('/article/add',
                    $paramer
                );
            }



            if($data['status']==200){
                $phpPath = config('params.php_path');
                shell_exec("nohup ".$phpPath.' '.public_path('../').'artisan command:ossUploadStript '.$data['data'].' >> /tmp/out.file 2>&1  &');
            }

            return new JsonResponse($data);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    private function getUserId() {
        return \Auth::getUser()->getAuthIdentifier();

    }

    private function getUserName() {
        return \Auth::getUser()->getUserMobile();
    }

    private function getRecommendCode() {
        return \Auth::getUser()->getRecommendCode();
    }
}