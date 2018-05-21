<?php

namespace App\Goods\Controllers;
use function App\getCurrentTime;
use App\Goods\Entities\ImgTitleEntity;
use App\Goods\Entities\ProductEntity;
use App\Goods\Services\Goods;
use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DI\Traits\EnableDIAnnotations;

/**
 * @path /product
 */
class GoodsController
{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Goods
     */
    public $goods;

    /**
     * @inject
     * @var Container
     */
    protected $container;

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
     * 商品列表
     * @route POST /list
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @param string $product_name
     * @return array
     */
    public function list($page = 1, $pagesize = 10, $product_name = '') {
        try {
            $where = ['status'=>1];
            if(strlen($product_name)>0){
                $where['product_name']=["like"=>'%'.$product_name.'%'];
            }
            $data = $this->goods->getGoodsList($page, $pagesize,$where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
    /**
     * 商品列表包含分成
     * @route POST /listWithPercent
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function listWithPercent($page = 1, $pagesize = 1000) {
        try {
            $data = $this->goods->getGoodsListWithPercent($page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 品牌列表
     * @route POST /brandList
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function brandList($page = 1, $pagesize = 20){
        $data = $this->goods->getBrandList($page, $pagesize);
        return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
    }

    /**
     * 商品状态更新
     * @route POST /changeStatus
     * @param int $product_id {@v min:1}
     * @param int $status {@v min:1|max:20}
     * @return array
     */
    public function changeStatus($product_id, $status) {
        try {
            $data = $this->goods->changeStatus($product_id, $status);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 商品添加
     * @route POST /add
     * @param ProductEntity $entity {@bind request.request}
     * @return array
     */
    public function add(ProductEntity $entity) {
        try{
            return $this->mutex->getMutex('addProduct')->synchronized(function() use($entity){
               //查询
                $checkExist = $this->goods->getGoodsList(1, 1,
                    [
                        'product_name'=>$entity->getProductName(),
                        'add_time'=>['>='=>getCurrentTime() - $this->container->get("addduplicatetime")]
                    ]);
                if($checkExist && $checkExist['count']>0){
                    throw $this->exception([
                        'code'=>ErrorConst::DUPLICATE_INSERT,
                        'text'=>"添加产品重复参数为".json_encode($entity->toArray())
                    ]);
                }
                $this->goods->add($entity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
    /**
     * 商品详情
     * @route POST /getProduct
     * @param int $product_id {@v min:1}
     * @return array
     */
    public function getProduct($product_id) {
        try {
             $data = $this->goods->getProduct($product_id);
             return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 商品编辑
     * @route POST /editProduct
     * @param ProductEntity $entity {@bind request.request}
     * @return array
     */
    public function editProduct(ProductEntity $entity) {
        try{
            return $this->mutex->getMutex('editProduct'.$entity->getId())->synchronized(function() use ($entity){
                $this->goods->editProduct($entity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 根据用户id获得其所有的订单列表
     * @route POST /getProcOrderListByUid
     * @param int $uid {@v min:1}
     * @param string $status
     * @param int $type {@v min:1}
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function getProcOrderListByUid($uid, $status = -1, $type = 2, $page = 1, $pagesize = 10) {
        try {
            $where = [
                't_order.uid'=>$uid,
                't_order.type' => $type
            ];
            if($status != -1){
                $where['t_order.status'] = ['IN' =>[explode(',',$status)]];
            }
            //t_order status 1、未操作 2、操作中 3、操作成功 4、操作失败
            $data = $this->goods->getProcOrderListByUid($uid,$page,$pagesize,$where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 根据t_order表id获得详情
     * @route POST /getProcOrderDetailByOid
     * @param int $oid {@v min:1}
     * @return array
     */
    public function getProcOrderDetailByOid($oid) {
        try {
            $where = [
                't_order.id'=> $oid,
                'type' => 2,
            ];
            $data = $this->goods->getProcOrderDetailByOid($where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

     /**
     * 商品购买
     * @route POST /buyGoods
     * @param int $spread_id {@v min:1}
     * @param int $mode {@v min:1}
     * @param int $uid {@v min:1}
     * @param int $number {@v min:1}
     * @param int $address_id {@v min:1}
     * @return array
     */
    public function buyGoods($spread_id, $mode, $uid, $number, $address_id) {
        try {

            $data = $this->goods->buyGoods($spread_id, $uid, $number, $address_id, $mode);
              return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * 支付回调
     * @route POST /buyReturn
     * @param string $pay_order_number {@v required}
     * @param string $return_code {@v required}
     * @param string $message {@v required}
     * @return array
     */
    public function buyReturn($pay_order_number, $return_code, $message) {
        try {
            $data = $this->goods->buyReturn($pay_order_number, $return_code, $message);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 结算脚本
     * @route POST /settlementScript
     * @return array
     */
    public function settlementScript() {
        try {
            $data = $this->goods->settlementScript();
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 月初未结算清零脚本
     * @route POST /settledMonthScript
     * @return array
     */
    public function settledMonthScript() {
        try {
            $data = $this->goods->settledMonthScript();
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
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
}