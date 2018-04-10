<?php

namespace App\Goods\Controllers;

use App\Goods\Entities\ProductCategoryEntity;
use App\Goods\Services\Goods;
use App\Utils\Defines;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;

/**
 * Class GoodsCategoryController
 * @path /productCategory
 */
class GoodsCategoryController{
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
     * 产品分类列表
     * @route POST /getProductCategoryList
     * @param string $category_name
     * @param int $status
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @return array
     */
    public function getProductCategoryList($category_name = '', $status = -1, $page = 1, $pagesize = 10) {
        try {
            $where = [];
            if(strlen($category_name) > 0){
                $where['category_name'] = ["like"=>'%'.$category_name.'%'];

            }
            if($status >=0){
                $where['status'] = $status;
            }
            $data = $this->goods->getProductCategoryList($page, $pagesize,$where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 产品分类态更新
     * @route POST /categoryChangeStatus
     * @param int $cid {@v min:1}
     * @param int $status {@v min:1|max:20}
     * @return array
     */
    public function categoryChangeStatus($cid, $status) {
        try {
            $data = $this->goods->categoryChangeStatus($cid, $status);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 分类新增
     * @route POST /categoryAdd
     * @param ProductCategoryEntity $productCategoryEntity {@bind request.request}
     * @return array
     */
    public function categoryAdd(ProductCategoryEntity $productCategoryEntity) {
        try {
            $data = $this->goods->categoryAdd($productCategoryEntity);
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
            $data = $this->goods->getCategory($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 分类更新
     * @route POST /editCategory
     * @param ProductCategoryEntity  $productCategoryEntity {@bind request.request}
     * @return array
     */
    public function editCategory(ProductCategoryEntity $productCategoryEntity) {
        try {
            $data = $this->goods->editCategory($productCategoryEntity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, []);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

}