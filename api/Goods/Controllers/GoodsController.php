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

    /**
     * 绑定虚拟码与商品
     * @param  string $name
     * @param string $good_id
     * @param  int $number
     * @param int $code_number
     * @param string $remark
     *
     * @return mixed
     * @throws
     */
    public function bindVirtual($name, $good_id, $number ,$remark = '', $code_number = 1)
    {
        try {

            return $this->mutex->getMutex('bindVirtual' . $name)->synchronized(function () use ($name, $good_id, $number, $code_number, $remark) {
                //如果存在good_id则报错
                $rst = $this->db->select('id')
                    ->from('t_virtual_bind')
                    ->where([
                        'good_id' => $good_id
                    ])->getFirst();

                $rs = $this->virtualTool->addVirtual($name, $number, $code_number);
                if ($rs) {
                    $rd = $this->db->insertInto('t_virtual_bind')
                        ->values([
                            'base_id' => $rs,
                            'good_id' => $good_id,
                            'bind_number' => $number,
                            'used_number' => 0,
                            'remark' => $remark,
                            'add_time' => time(),
                        ])->exec()->lastInsertId();
                    if (!$rd) {
                        throw $this->exception([
                            'code' => ErrorConst::VIRTUAL_BIND_ERROR,
                            'text' => '绑定虚拟码与商品失败',
                        ]);
                    }
                    //添加log记录
                    $ru = $this->db->insertInto('t_virtual_bind_log')
                        ->values([
                            'bind_id' => $rd,
                            'create_type' => '新增',
                            'bind_number' => $number,
                            'add_time' => time(),
                        ])->exec();
                }
                return $rs;
            }
            );
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }
    /**
     * 获取商品绑定的虚拟码
     * @param string $good_id
     * @param string $buyer_id
     * @param string $order_id
     * @return mixed
     */
    public function getVirtual($good_id, $buyer_id, $order_id)
    {
        try {
            $rs = $this->db->select('id','base_id')
                ->from('t_virtual_bind')
                ->where(['good_id' => $good_id])
                ->getFirst();
            if (!$rs) {
                throw $this->exception([
                    'code' => ErrorConst::VIRTUAL_NO_GRANT,
                    'text' => '没有获取绑定的虚拟码',
                ]);
            }
            $codeGroup = [];

            $rd = $this->db->select('id', 'code_group')
                ->from('t_virtual_detail')
                ->where([
                    'base_id' => $rs['base_id'],
                    'data_status' => 1,
                ])->getFirst();
            if (!$rs) {
                throw $this->exception([
                    'code' => ErrorConst::VIRTUAL_USED,
                    'text' => '激活码被使用完',
                ]);
            }
            $codeGroup[] = $rd['code_group'];

            $ru = $this->db->update('t_virtual_detail')
                ->set([
                    'data_status' => 0,
                ])->where([
                    'id' => $rd['id']
                ])->exec();

            $rut = implode(',', $codeGroup);
            $rt = $this->db->insertInto('t_virtual_buyer')
                ->values([
                    'code_id' => $rd['id'],
                    'buyer_id' => $buyer_id,
                    'add_time' => time(),
                    'good_id' => $good_id,
                    'order_id' => $order_id,
                    'data_status' => 1,
                    'is_used' => 0,
                    'contents' => '你获得的激活码是' . $rut . '请妥善保存',
                ])->exec()->lastInsertId();
            return $rt;
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }



    /**
     * 更新添加虚拟码与商品
     * @param int $base_id
     * @param string $good_id
     * @param int $number
     * @param int $code_number
     * @param string $remark
     *
     * @return mixed
     */
    public function updateVirtual($good_id, $number, $code_number, $remark = '')
    {
        try {
            return $this->mutex->getMutex('updateVirtual' . $good_id)->synchronized(function () use ($good_id, $number, $code_number, $remark) {
                $number = $number == -1 ? 10000 : ($number > 10000 ? 10000 : $number);
                //通过good_id查找base_id
                $rm = $this->db->select('base_id')
                    ->from('t_virtual_bind')
                    ->where([
                        'good_id' => $good_id
                    ])->getFirst();
                if(!$rm){

                    $rt = $this->bindVirtual('虚拟商品'.date('YmdHis').rand('100','999'), $good_id, $number ,$remark , $code_number);
                    return $rt;
                }else{
                    $rs = $this->virtualTool->updateVirtual($rm['base_id'], $number, $code_number);
                    $rss = $this->db->select('id', 'bind_number')
                        ->from('t_virtual_bind')->where([
                            'base_id' => $rm['base_id'],
                            'good_id' => $good_id,
                        ])->getFirst();
                    $rd = $this->db->update('t_virtual_bind')
                        ->set([
                            'bind_number' => $rss['bind_number'] + $number,
                        ])->where([
                            'base_id' => $rm['base_id'],
                            'good_id' => $good_id,
                        ])->exec();
                    $rt = $this->db->insertInto('t_virtual_bind_log')->values([
                        'bind_id' => $rss['id'],
                        'create_type' => '添加',
                        'bind_number' => $number,
                        'add_time' => time(),
                    ])->exec();
                    return $rt->success;
                }


            });
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }


    /**
     * 判断激活码是否为对应的广告主的的
     */
    public function isBelongAdvert($code_group, $advert_id)
    {

        //通过advert_id取得为对应的广告主的
        $rs = $this->db->select('t_virtual_buyer.id')
            ->from('t_virtual_buyer')
            ->leftJoin('t_virtual_detail')
            ->on('t_virtual_detail.id = t_virtual_buyer.code_id')
            ->leftJoin('t_advert_product_relate')
            ->on('t_advert_product_relate.product_id = t_virtual_buyer.good_id')
            ->where([
                't_advert_product_relate.advert_relative_uid' => $advert_id,
                't_virtual_detail.code_group' => $code_group
            ])->getFirst();

        if(isset($rs['id'])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 验证并使用验证码组
     * @param  string  $code_group
     * @param string $advert_id
     * @return mixed
     * @throws
     */
    public function verifyVirtual($code_group, $advert_id = null)
    {
        try {
            $code_group = strtolower($code_group);
            if($this->isBelongAdvert($code_group, $advert_id)){}else{
                throw $this->exception([
                    'code'=> ErrorConst::VIRTUAL_CODE_ERROR,
                    'text'=> '激活码错误',
                ]);
            }

            $rt = $this->virtualTool->verifyVirtual($code_group);
            if(!$rt){
                throw $this->exception([
                    'code'=> ErrorConst::VIRTUAL_CODE_ERROR,
                    'text'=> '激活码错误',
                ]);
            }
            //通过code_group获取对应的商品名称
            $data = $this->db->select('t_product.product_name')
                ->from('t_virtual_detail')
                ->leftJoin('t_virtual_base')
                ->on('t_virtual_detail.base_id = t_virtual_base.id')
                ->leftJoin('t_virtual_bind')
                ->on('t_virtual_base.id = t_virtual_bind.base_id')
                ->leftJoin('t_product')
                ->on('t_product.id = t_virtual_bind.good_id')
                ->where(['t_virtual_detail.code_group' => $code_group])
                ->getFirst();

            if($data) {
                return ['message' => $data['product_name']];
            }else{
                return ['message' => ''];
            }
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }







    /**
     * 添加分类条目统计
     * @param string $avert_id
     * @param string $first
     * @param int $second
     *
     * @return mixed
     */
    public function addItemStatistics($avert_id, $first, $second)
    {
        try {
            return $this->mutex->getMutex('AddItemStatistics' . $avert_id . $second)->synchronized(function () use ($avert_id, $first, $second) {
                $rs = $this->db->select('id')
                    ->from('t_item_category')
                    ->where([
                        'advert_uid' => $avert_id,
                        'pid' => $first,
                    ])->get();
                //判断是否存在分类
                if(!empty($rs) && ($first != 0)){
                    if(!in_array($second, array_column($rs, 'id'))){
                        throw $this->exception([
                            'code' => ErrorConst::VIRTUAL_TWO_ERROR,
                            'text' => '输入二级分类不正确'
                        ]);
                    }
                    $config = ['item_id' => $second];
                }else{
                    $config = [];
                }
                $rd = $this->db->select('id', 'activity_num')
                    ->from('t_good_statistics')
                    ->where(array_merge([
                        'relation_id' => $avert_id,
                        'date' => date('Y-m-d'),
                        'data_status' => 1,
                    ], $config))->getFirst();
                if ($rd['id']) {
                    //update
                    $this->db->update('t_good_statistics')
                        ->set([
                            'activity_num' => $rd['activity_num'] + 1,
                        ])->where([
                            'id' => $rd['id'],
                        ])->exec();
                } else {
                    //insert
                    $this->db->insertInto('t_good_statistics')
                        ->values(array_merge([
                            'relation_id' => $avert_id,
                            'activity_num' => 1,
                            'add_time' => time(),
                            'date' => date('Y-m-d'),
                            'data_status' => 1
                        ], $config))->exec()->lastInsertId();
                }
                return true;
            });
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }
    /**
     * 获取激活总数
     * @param string $avert_id
     * @param string $first
     * @param int $second
     */
    public function totalStatistics($avert_id, $first, $second)
    {
        try {
            return $this->mutex->getMutex('getTotalItemStatistics' . $avert_id)->synchronized(function () use ($avert_id, $first, $second) {
                $rs = $this->db->select('id')
                    ->from('t_item_category')
                    ->where([
                        'advert_uid' => $avert_id,
                        'pid' => $first,
                        'status' => 1
                    ])
                    ->get();

                //一级分类存在
                if(!empty($rs)){
                    $config = ['item_id' => ['in' => array_column($rs, 'id')]];
                }else{
                    $config = [];
                }
                if(!empty($second)){
                    $sArray = ['item_id' => $second];
                }else{
                    $sArray = [];
                }

                $rd = ['total' => 0];
                $ru = $this->db->select('activity_num')
                    ->from('t_good_statistics')
                    ->where(array_merge(['relation_id' => $avert_id], $config, $sArray))
                    ->get();
                if ($ru) {
                    $rd['total'] = array_sum(array_column($ru, 'activity_num'));
                }
                return $rd;
            });
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 获取分类条目统计
     * @param string $avert_id
     * @param string $first
     * @param int $page
     * @param int $page_size
     * @param int $second
     * @return mixed
     */
    public function getItemStatistics($avert_id, $first, $page, $page_size, $second)
    {
        try {
            return $this->mutex->getMutex('getItemStatistics' . $avert_id)->synchronized(function () use ($avert_id, $first, $page, $page_size, $second) {

                $rd['detail'] = [];
                $ru['page_count'] = 0;
                $ru['page'] = 1;
                if(!empty($second)){
                    $sArray = ['item_id' => $second];
                }else{
                    $sArray = [];
                }
                if(empty($first) && empty($second)){
                    $rd['detail'] = $this->db->select('activity_num', 'date')->from('t_good_statistics')
                        ->where([
                            'relation_id' => $avert_id,
                            'data_status' => 1,
                        ])->orderBy('t_good_statistics.date', 'DESC')
                        ->limit(($page - 1) * $page_size, $page_size)
                        ->get();
                }else {
                    $rd['detail'] = $this->db->select('t_item_category.name', 't_good_statistics.activity_num', 't_good_statistics.date')
                        ->from('t_good_statistics')
                        ->leftJoin('t_item_category')
                        ->on('t_good_statistics.item_id = t_item_category.id')
                        ->where(array_merge([
                            't_good_statistics.relation_id' => $avert_id,
                            't_good_statistics.data_status' => 1,
                            't_item_category.status' => 1
                        ], $sArray))->orderBy('t_good_statistics.date', 'DESC')
                        ->limit(($page - 1) * $page_size, $page_size)
                        ->get();
                }
                $ru['page_count'] = $this->db->select('t_item_category.name', 'activity_num', 'date')
                    ->from('t_good_statistics')
                    ->leftJoin('t_item_category')
                    ->on('t_good_statistics.item_id = t_item_category.id')
                    ->where(array_merge([
                        't_good_statistics.relation_id' => $avert_id,
                        't_good_statistics.data_status' => 1,
                        't_item_category.status' => 1
                    ], $sArray))->orderBy('date', 'DESC')
                    ->count();
                $ru['page'] = $page;

                return array_merge($ru, $rd);
            });
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }


    /**
     * 获取信息
     *
     * @param int $id
     *
     * @return mixed
     * @throws
     */
    public function getInfo($id)
    {
        try {
            $rs = $this->db->select('code_id', 'buyer_id', 'contents')
                ->from('t_virtual_buyer')
                ->where([
                    'id' => $id,
                    'data_status' => 1
                ])->getFirst();
            if(!$rs){
                throw $this->exception([
                    'code'=> ErrorConst::VIRTUAL_BUYER_CODE_ERROR,
                    'text'=> '获取购买者与激活码关系失败'
                ]);
            }
            $rd = $this->db->select('code_group')
                ->from('t_virtual_detail')
                ->where([
                    'id' => $rs['code_id'],
                    'data_status' => 0
                ])->getFirst();

            //获取对应的手机号码
            $ru = $this->db->select('mobile')
                ->from('t_user_address')
                ->where([
                    'status' => 1,
                    'uid' => $rs['buyer_id']
                ])->getFirst();

            //获取对应的微信openid
            $rt = $this->db->select('t_weixin_user.openid')
                ->from('t_weixin_user_relate')
                ->leftJoin('t_weixin_user')
                ->on('t_weixin_user_relate.wx_id = t_weixin_user.id')
                ->where([
                    't_weixin_user_relate.uid' => $rs['buyer_id'],
                ])->getFirst();

            return ['code' => $rd['code_group'], 'contents' => $rs['contents'], 'mobile' => $ru['mobile'], 'openid' => $rt['openid']];
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }


    /**
     * @Get("/edit", as="s_goods_edit")
     */
    public function edit(Request $request){
//        $categorylist = Curl::post('/productCategory/getProductCategoryList',['status'=>1]);
        $categorylist = Curl::post('/industry/getLists',['status'=>1,'type'=>1]);
//        $categorylist = Curl::post('/industry/getIndustryUserList');

        $categorylist = Curl::post('/industryCategory/getLists',['status'=>1,'type'=>1]);

        $brandlist = Curl::post('/product/brandList');
        $advertList = Curl::post('/advert/advertRelativeList');

        $data = Curl::post('/product/getProduct',
            ['product_id'=>$request->get('product_id', 1)]
        );
        if(isset($data['data']['contents'])){
            $data['data']['contents'] = str_replace(chr(10),'<br>',$data['data']['contents']);
            $data['data']['contents'] = str_replace(chr(13),'<br>',$data['data']['contents']);
        }else{
            $data['data']['contents'] = '';
        }


        foreach ($data['data']['product_division_methods'] as $k=>$v){
            $display_to_writer = '';

            $str = '';
            $percents = json_decode($v['percent'],true);

            if($v['type'] !=1){
                $value = 0;
                foreach ($percents as $kk=>$val){

                    if($val['type'] == 1){
                        if($val['contents']['percent']>0){
                            $value = $val['contents']['percent'];
                        }
                    }
                    if($val['type'] == 2){
                        if($val['contents']['account']>0){
                            $value = $val['contents']['account'];
                        }
                    }
//                    if($val['type'] == 1){
//                        $arr[] = '百分比'.$val['contents']['percent'].'%';
//                        $str .=  '百分比'.$val['contents']['percent'].'%     ';
//                    }
//                    if($val['type'] == 2){
//                        $arr[] = '固定金额'.$val['contents']['account'].'元';
//                        $str .=  '固定金额'.$val['contents']['account'].'元  ';
//                    }
//                    if($val['type'] == 3){
//                        $arr[] = '复合模式: 百分比'.$val['contents']['percent'].'%'.$val['contents']['account'].'元';
//                        $str .='复合模式: 百分比'.$val['contents']['percent'].'%'.$val['contents']['account'].'元  ';
//                    }
                }
                //获得 两个 模式用的 真正的值 可能都是0
                $percents[0]['value'] = $value;
            }else{
                $display_to_writer = json_decode($v['display_to_writer'],true);
//                var_dump($v['display_to_writer']);
            }

//            $data['data']['product_division_methods'][$k]['show'] = $str;
            $data['data']['product_division_methods'][$k]['display_to_writer_arr'] = $display_to_writer;
            $data['data']['product_division_methods'][$k]['product_division_methods_arr'] = $percents;
        }


        $data['data']['specifications'] = json_decode($data['data']['specifications'],true);

        return view("Goods.edit")->with('res',$data['data'])->with('categorylist',$categorylist['data']['data'])
            ->with('brandlist',$brandlist['data']['data'])
            ->with('advertList',$advertList['data']['data']);
    }

    /**
     * @Post("/newEditpost", as="s_good_newEditpost")
     */
    public function newEditpost(Request $request){
        session_start();
        $wxImgList =  isset($_SESSION['wxImgList'])?json_decode($_SESSION['wxImgList'],true):'';

        $product_type = $request->get('product_type',1);
        if($product_type == 3){
            $this->validate($request, [
                'id' => 'required',
                'product_name' => 'required',
                'synopsis' => 'required',
                'product_info' => 'required',
                'stock' => 'required|Integer|min:-1',
                'selling_price' => 'required',
                'landing_page' => 'required',
                'channelpercent' => 'required|Numeric|min:0',
                'mediaAgent' => 'required|Numeric|min:0',
                'sitepercent' => 'required|Numeric|min:0',
                'writerpercent' => 'required|Numeric|min:0',
                'advertpercent' => 'required|Numeric|min:0',
            ]);
        }else{
            $this->validate($request, [
                'id' => 'required',
                'product_name' => 'required',
                'synopsis' => 'required',
                'product_info' => 'required',
                'stock' => 'required|Integer|min:-1',
                'selling_price' => 'required',
                'channelpercent' => 'required|Numeric|min:0',
                'mediaAgent' => 'required|Numeric|min:0',
                'sitepercent' => 'required|Numeric|min:0',
                'writerpercent' => 'required|Numeric|min:0',
                'advertpercent' => 'required|Numeric|min:0',
            ]);
        }


        if($request->get('goods_type') == '2' && $request->get('generateType') == '1' && $request->get('stock') < '0') {
            return back()->withErrors('虚拟商品自动创建库存需>=0');
        }


        $expiration_time = $request->get('expiration_time');
        if(is_null($expiration_time)){
            $request['expiration_time'] = 0;
        }else{
            $request['expiration_time'] = strtotime($expiration_time) ? strtotime($expiration_time):0;
        }
        if($request->hasFile('images') && $request->file('images')->isValid()) {
            $extension = $request->file('images')->extension();
            $newname = time() . 'images.' . $extension;
            $res = $request->file('images')->storeAs('images', $newname, 'local2');
            $ossKey =  md5(time().rand().rand());
            OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
            $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);
            $img_path['oss'] = $publicObjectURL;

            //压缩原图
            $local_path = public_path('app/').$res;
            $fileFrom_325 = $local_path.'_325_325.jpeg';
            $height = \Intervention\Image\Facades\Image::make($local_path)->height();
            $width = \Intervention\Image\Facades\Image::make($local_path)->width();
            //echo $height.'='.$width;exit;
            if($width>$height){
                $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(null, 325, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }else{
                $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(null, 325, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $img285->resizeCanvas(325, 325)->save( $fileFrom_325,100 );
            OSS::publicUpload(\Config::get('alioss.BucketName'), md5($ossKey.'_325_325'), $fileFrom_325);
            OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), md5($ossKey.'_325_325'));


            //$publicObjectURL = fastdfs_storage_upload_by_filename(public_path('app/').$res);
            //$url = \Config::get('params.img_url').$publicObjectURL['group_name'].'/'.$publicObjectURL['filename'];
            $img_path['fastdfs'] = '';//$url;

            $request['img_path'] = json_encode($img_path);

        }else{
            $request['img_path'] = $request['img_path_old'];
        }

        $content = $request->get('product_info');
        if($wxImgList) {

            foreach ($wxImgList as $value) {
                $content = str_replace(html_entity_decode($value['source']),$value['url'],$content);
            }
        }
        $paramer = $request->all();

        $writerway = $paramer['writerway'];
        if($writerway==1){
            $paramer['writerpercent'] = $paramer['writerpercent'];
            $paramer['writerCombinepercent'] = $paramer['writerpercent'];
        }elseif($writerway==2){
            $paramer['writerMoney'] = $paramer['writerpercent'];
            $paramer['writerpercent'] = 0;
            $paramer['writerCombineaccount'] = $paramer['writerpercent'];
        }

        $paramer['product_info'] = $content;
        $sKey = 0;

        $array = [];
        while (true){
            if($sKey>0){
                $span =  $request->get('specName'.$sKey);
            }else{
                $span =  $request->get('specName');
            }
            if(!$span){
                break;
            }
            foreach ($span as $key =>$value){
                if($key==0){
                    $array[$sKey]['key'] = $value;
                }else{
                    $array[$sKey]['value'][] = $value;
                }
            }
            $sKey++;
        }

        $paramer['specifications'] = json_encode($array);

//        dd($request->all());
        $data = Curl::post('/product/editProduct',
            $paramer
        );

        $phpPath = config('params.php_path');

        exec("cd ".public_path('../')." && ".$phpPath.' artisan command:ossGoodsStript '.$paramer['id'].' &');

        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{

            if(array_get($paramer, 'goods_type', 0) == 2) {
                $stock = array_get( $paramer ,'stock', 0);

                $data = Curl::post('/utils/virtual/update_virtual',
                    [
                        'good_id' => $paramer['id'],
                        'number' => $stock,
                    ]
                );
            }
            return redirect(route('s_goods_lists'))->with('addsuccess', 'success');
        }
    }

    /**
     * @Post("/editpost", as="s_good_editpost")
     */
    public function editpost(Request $request){
        session_start();
        $wxImgList =  isset($_SESSION['wxImgList'])?json_decode($_SESSION['wxImgList'],true):'';

        $product_type = $request->get('product_type',1);
        if($product_type == 3){
            $this->validate($request, [
                'id' => 'required',
                'product_name' => 'required',
                'synopsis' => 'required',
                'product_info' => 'required',
                'stock' => 'required|Integer|min:-1',
                'selling_price' => 'required',
                'landing_page' => 'required',
                'channelpercent' => 'required|Numeric|min:0',
                'mediaAgent' => 'required|Numeric|min:0',
                'sitepercent' => 'required|Numeric|min:0',
//                'writerpercent' => 'required|Numeric|min:0',
                'advertpercent' => 'required|Numeric|min:0',
            ]);
        }else{
            $this->validate($request, [
                'id' => 'required',
                'product_name' => 'required',
                'synopsis' => 'required',
                'product_info' => 'required',
                'stock' => 'required|Integer|min:-1',
                'selling_price' => 'required',
                'channelpercent' => 'required|Numeric|min:0',
                'mediaAgent' => 'required|Numeric|min:0',
                'sitepercent' => 'required|Numeric|min:0',
//                'writerpercent' => 'required|Numeric|min:0',
                'advertpercent' => 'required|Numeric|min:0',
            ]);
        }

        if($request->get('goods_type') == '2' && $request->get('generateType') == '1' && $request->get('stock') <= '0') {
            return back()->withErrors('虚拟商品自动创建库存需>0');
        }



        $expiration_time = $request->get('expiration_time');
        if(is_null($expiration_time)){
            $request['expiration_time'] = 0;
        }else{
            $request['expiration_time'] = strtotime($expiration_time) ? strtotime($expiration_time):0;
        }
        if($request->hasFile('images') && $request->file('images')->isValid()) {
            $extension = $request->file('images')->extension();
            $newname = time() . 'images.' . $extension;
            $res = $request->file('images')->storeAs('images', $newname, 'local2');
            $ossKey =  md5(time().rand().rand());
            OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
            $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);
            $img_path['oss'] = $publicObjectURL;

            //压缩原图
            $local_path = public_path('app/').$res;
            $fileFrom_325 = $local_path.'_325_325.jpeg';
            $height = \Intervention\Image\Facades\Image::make($local_path)->height();
            $width = \Intervention\Image\Facades\Image::make($local_path)->width();
            //echo $height.'='.$width;exit;
            if($width>$height){
                $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(325, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }else{
                $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(null, 325, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $img285->resizeCanvas(325, 325)->save( $fileFrom_325,100 );
            OSS::publicUpload(\Config::get('alioss.BucketName'), md5($ossKey.'_325_325'), $fileFrom_325);
            OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), md5($ossKey.'_325_325'));


            //$publicObjectURL = fastdfs_storage_upload_by_filename(public_path('app/').$res);
            //$url = \Config::get('params.img_url').$publicObjectURL['group_name'].'/'.$publicObjectURL['filename'];
            $img_path['fastdfs'] = '';//$url;

            $request['img_path'] = json_encode($img_path);

        }else{
            $request['img_path'] = $request['img_path_old'];
        }

        $content = $request->get('product_info');
        if($wxImgList) {

            foreach ($wxImgList as $value) {
                $content = str_replace(html_entity_decode($value['source']),$value['url'],$content);
            }
        }
        $paramer = $request->all();
        $paramer['product_info'] = $content;
        $sKey = 0;

        $array = [];
        while (true){
            if($sKey>0){
                $span =  $request->get('specName'.$sKey);
            }else{
                $span =  $request->get('specName');
            }
            if(!$span){
                break;
            }
            foreach ($span as $key =>$value){
                if($key==0){
                    $array[$sKey]['key'] = $value;
                }else{
                    $array[$sKey]['value'][] = $value;
                }
            }
            $sKey++;
        }

        $paramer['specifications'] = json_encode($array);

//        dd($request->all());

        $data = Curl::post('/product/editProduct',
            $paramer
        );

        $phpPath = config('params.php_path');

        exec("cd ".public_path('../')." && ".$phpPath.' artisan command:ossGoodsStript '.$paramer['id'].' &');

        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            //todotodo
            //添加产品成功，请求生成验证码

            if(array_get($paramer, 'goods_type', 0) == 2) {
                $stock = array_get( $paramer ,'stock', 0);

                $data = Curl::post('/utils/virtual/update_virtual',
                    [
                        'good_id' => $paramer['id'],
                        'number' => $stock,
                    ]
                );
            }
            return redirect(route('s_goods_lists'))->with('addsuccess', 'success');
        }
    }

    /**
     * 商品地域列表页面
     * @Get("/rgionList", as="s_goods_regionlists")
     * @Post("/rgionList", as="s_goods_regionlists")
     */
    public function rgionList(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/productRegion/productRegionList',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'product_id'=>$request->get('product_id', -1),
                    'region_name'=>$request->get('region_name', ''),
                ]
            );
//            dd($data);
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }

        return view("Goods.regionlist")->with('product_id',$request->get('product_id', 0));
    }
    /**
     * @Post("/addRegion", as="s_goods_addRegion")
     */
    public function addRegion(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'product_id' => 'required',
                'region_name' => 'required',
            ]);
            $data = Curl::post('/productRegion/addRegion',
                [
                    'product_id' => $request->get('product_id', -1),
                    'region_name'=>$request->get('region_name', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
//        if($data['status'] != 200){
//            return back()->withErrors($data['message']);
//        }else{
//            return redirect(route('s_goods_lists'))->with('addsuccess', 'success');
//            return new JsonResponse(['']);
//        }
    }
    /**
     * @Post("/delRegion", as="s_goods_delRegion")
     */
    public function delRegion(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $data = Curl::post('/productRegion/delRegion',
                [
                    'id' => $request->get('id', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

    /**
     *
     * @Post("/changeSort", as="s_goods_change_sort")
     */
    public function changeSort(Request $request) {

        $data = Curl::post('/product/changeSort', [
            'product_id' => $request->get('product_id', 0),
            'sort' => $request->get('sort', 0),
        ]);
        return new JsonResponse($data);
    }

    /**
     * 虚拟商品下的code列表
     * @Get("/virtual_codelists", as="s_goods_virtual_codelists")
     * @Post("/virtual_codelists", as="s_goods_virtual_codelists")
     */
    public function virtual_codelists(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/utils/virtual/getVirtualList',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'good_id'=>$request->get('proid', 0),
                    'code'=>$request->get('code', ''),
                ]
            );
//            dd($data);
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'total_used' => $data['data']['total_used'],
                'generate_type' => $data['data']['generate_type'],

                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }

        return view("Goods.Virtual.codelist")->with('proid',$request->get('proid', 0));
    }

    /**
     * 导入虚拟商品下的code
     * @Post("/virtual_importCode", as="s_goods_virtual_importCode")
     */
    public function virtual_importCode(Request $request) {
        if ($request->ajax()) {

            $pid = $request->get('pid');
            if(!$pid){
                return new JsonResponse(['status' => 404, 'message' => '缺少产品参数']);
            }

            if($request->hasFile('excels') && $request->file('excels')->isValid()){
                $excel = $request->file('excels');
                $extension = $excel->getClientOriginalExtension();


                if(!in_array($extension, ['xls', 'xlsx'])){
                    return new JsonResponse(['status' => 402, 'message' => '文件格式错误']);
                }

                $newname = date('Y-m-d').'-'.time() . 'excel.' . $extension;

                $path = $path = $excel->storeAs('uploads', $newname,'local');

                if (!$path) {
                    return new JsonResponse(['status' => 403, 'message' => '文件上传失败']);
                }
                $filePath = public_path('../storage/app/' . $path);

                //file
                $array = [];
                Excel::load($filePath,function($reader) use($pid,&$array){
                    //get所有的sheet
                    $allsheet = $reader->getAllSheets();
                    for($i = 0; $i< count($allsheet);$i++){
                        $sheet = $reader->getSheet($i)->toArray();
                        //遍历单sheet下的行
                        foreach($sheet as $k=>$v){
                            //遍历行中的所有列
                            foreach ($v as $kk=>$vv){
                                if(strlen(trim($vv))>0){
                                    $array[] = trim($vv);
                                    //$array = array_unique($array);
                                }
                            }
                        }
                    }
                });
                if(count($array) == 0){
                    return new JsonResponse(['status' => 405, 'message' => '未读出任意码']);
                }

                $data = Curl::post('/utils/virtual/importCode',
                    [
                        'list' => $array,
                        'good_id'=> $pid
                    ]
                );

                if($data['status'] == 200){
                    if($data['data']>0){
                        //导入成功修改 产品 status 为1
//                        $changestatus = Curl::post('/product/changeStatus',
//                            [
//                                'product_id' => $pid,
//                                'status'=>1,
//                            ]
//                        );
                    }
                    $data['want'] = count($array);
                }
                //更新status 为1  save时 status=2如果是导入的


                return new JsonResponse($data);

            }else{
                return new JsonResponse(['status' => 400, 'message' => '请上传文件']);
            }
        }
    }

    private function getOrder(Request $request, $order) {
        $sortArr = [];
        if ($request->get('orderTime') == 1) {
            $sortArr = ['add_time' => 'ASC'];
        } else if ($request->get('orderTime') == 2) {
            $sortArr = ['add_time' => 'DESC'];
        } else if ($request->get('orderSort') == 1) {
            $sortArr = ['sort' => 'DESC', 'add_time' => 'DESC'];
        } else if ($request->get('orderSort') == 2) {
            $sortArr = ['sort' => 'ASC', 'add_time' => 'DESC'];
        }
        if ($sortArr) $order = json_encode($sortArr);
        return $order;
    }
}

}