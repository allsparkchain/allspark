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

}