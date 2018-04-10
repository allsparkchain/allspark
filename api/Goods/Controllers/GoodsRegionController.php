<?php
namespace App\Goods\Controllers;

use App\Goods\Entities\ProductRegionCategoryEntity;
use App\Goods\Services\GoodsRegion;
use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DI\Traits\EnableDIAnnotations;

/**
 * Class GoodsRegionController
 * @path productRegion
 */
class GoodsRegionController{
    use EnableDIAnnotations,HttpResponseTrait,ThrowResponseParamerTrait;//启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var GoodsRegion
     */
    public $goodsRegion;

    /**
     * @inject
     * @var Paramers
     */
    public $paramer;

    /**
     * @inject
     * @var Container
     */
    public $container;
    /**
     * 地域列表
     * @route POST /regionList
     * @param int $parent_id {@v min:1}
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function regionList($parent_id = 1, $page = 1, $pagesize = 20){
        $data = $this->goodsRegion->getRegionList($page, $pagesize,['parent_id'=>$parent_id]);
        return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
    }

    /**
     * 获取某个产品地域列表
     * @route POST /productRegionList
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @param int $product_id {@v min:1}
     * @param string $region_name
     * @return array
     */
    public function productRegionList($product_id, $page = 1, $pagesize = 20, $region_name =''){
        $where = [];
        if($product_id >0){
            $where['product_id'] = $product_id;
        }
        if(strlen($region_name)>0){
            $region_id = $this->goodsRegion->searchRegionByName($region_name);
            if(count($region_id)>0){
                $where['t_region.region_id'] =  ['IN'=>$region_id];
            }
        }
        $data = $this->goodsRegion->getproductRegionList($page, $pagesize,$where);
        return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
    }

    /**
     * 商品添加地域
     * @route POST /addRegion
     * @param int $product_id
     * @param string $region_name
     * @return array
     */
    public function addRegion($product_id , $region_name) {
        try{
            return $this->mutex->getMutex('addRegion'.$product_id)->synchronized(function() use($product_id, $region_name){
                //查询
                $id = $this->goodsRegion->searchRegionByName($region_name,true);
                if($id<=0){
                    throw $this->exception([
                        'code'=>ErrorConst::NO_MATCH_REGION_NAME,
                        'text'=>"无效的地域名".$region_name
                    ]);
                }
                //查pid rid 是否已经存在
                $checkExist = $this->goodsRegion->getproductRegionList(1,1, ['product_id'=>$product_id,'t_product_region.region_id'=>$id]);
                if($checkExist['count']>0){
                    throw $this->exception([
                        'code'=>ErrorConst::PRODUCT_EXIST_REGION,
                        'text'=>"该产品".$product_id."已经存在此地域".$region_name
                    ]);
                }
                $this->goodsRegion->addProdcutRegion($product_id,$id);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 某地域分类 删除一个地域
     * @route POST /delRegion
     * @param int $id {@v min:1}
     * @return array
     */
    public function delRegion($id) {
        try {
            $data = $this->goodsRegion->deleteProdcutRegion($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
    ////////////

    /**
     * 产品地域分类列表
     * @route POST /getProductCategoryList
     * @param string $category_name
     * @param int $status
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @return array
     */
    public function getProductRegionCategoryList($category_name = '', $status = -1, $page = 1, $pagesize = 10) {
        try {
            $where = [];
            if(strlen($category_name) > 0){
                $where['category_name'] = ["like"=>'%'.$category_name.'%'];

            }
            if($status >=0){
                $where['status'] = $status;
            }
            $data = $this->goodsRegion->getProductRegionCategoryList($page, $pagesize,$where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 产品地域分类态更新
     * @route POST /categoryChangeStatus
     * @param int $cid {@v min:1}
     * @param int $status {@v min:1|max:20}
     * @return array
     */
    public function categoryChangeStatus($cid, $status) {
        try {
            $data = $this->goodsRegion->categoryChangeStatus($cid, $status);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 分类新增
     * @route POST /categoryAdd
     * @param ProductRegionCategoryEntity $productRegionCategoryEntity {@bind request.request}
     * @return array
     */
    public function categoryAdd(ProductRegionCategoryEntity $productRegionCategoryEntity) {
        try {
            $data = $this->goodsRegion->categoryAdd($productRegionCategoryEntity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取分类详情
     * @route POST /getCategory
     * @param int $id {@v min:1}
     * @return array
     */
    public function getCategory($id) {
        try {
            $data = $this->goodsRegion->getCategory($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 分类更新
     * @route POST /editCategory
     * @param ProductRegionCategoryEntity  $productRegionCategoryEntity {@bind request.request}
     * @return array
     */
    public function editCategory(ProductRegionCategoryEntity $productRegionCategoryEntity) {
        try {
            $data = $this->goodsRegion->editCategory($productRegionCategoryEntity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, []);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取某个产品地域包含的地域列表
     * @route POST /productCategoryRegionList
     * @param int $product_region_category_id {@v min:1}
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @param string $region_name
     * @return array
     */
    public function productCategoryRegionList($product_region_category_id =-1, $page = 1, $pagesize = 20, $region_name =''){
        $where = [];
        if($product_region_category_id >0){
            $where['category_id'] = $product_region_category_id;
        }
        if(strlen($region_name)>0){
            $region_id = $this->goodsRegion->searchRegionByName($region_name);
            if(count($region_id)>0){
                $where['t_region.region_id'] = ['IN'=>$region_id];
            }
        }
        $data = $this->goodsRegion->getproductCategoryRegionList($page, $pagesize,$where);
        return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
    }

    /**
     * 某个地域分类添加地域
     * @route POST /addRegionForRegionCategory
     * @param int $product_region_category_id
     * @param string $region_name
     * @return array
     */
    public function addRegionForRegionCategory($product_region_category_id, $region_name) {
        try{
            return $this->mutex->getMutex('addRegionForRegionCategory'.$product_region_category_id)->synchronized(function() use($product_region_category_id, $region_name){
                //精确查询
                $id = $this->goodsRegion->searchRegionByName($region_name,true);
                if($id <=0){
                    throw $this->exception([
                        'code'=>ErrorConst::NO_MATCH_REGION_NAME,
                        'text'=>"无效的地域名".$region_name
                    ]);
                }

                //查pid rid 是否已经存在
                $checkExist = $this->goodsRegion->getproductCategoryRegionList(1,1, ['category_id'=>$product_region_category_id,'t_region_category_relate.region_id'=>$id]);
                if($checkExist['count']>0){
                    throw $this->exception([
                        'code'=>ErrorConst::CATEGORY_EXIST_REGION,
                        'text'=>"该地域分类".$product_region_category_id."已经存在此地域".$region_name
                    ]);
                }
                $this->goodsRegion->addRegionForRegionCategory($product_region_category_id,$id);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 删除该地域分类下的 某个地域
     * @route POST /deleteCategoryRegion
     * @param int $id {@v min:1}
     * @return array
     */
    public function deleteCategoryRegion($id) {
        try {
            $data = $this->goodsRegion->deleteCategoryRegion($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 资讯页面 获得所有 地域分类，，及所属的地域
     * @route POST /categoryRegionList
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function categoryRegionList($page = 1, $pagesize = 20){
        $data = $this->goodsRegion->categoryRegionList($page, $pagesize);
        return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
    }
}