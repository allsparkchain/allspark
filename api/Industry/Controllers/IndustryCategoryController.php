<?php

namespace App\Industry\Controllers;
use App\Industry\Entities\IndustryCategoryEntity;
use App\Industry\Services\Industry;
use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DI\Traits\EnableDIAnnotations;


/**
 * Class IndustryCategoryController
 * @path /industryCategory
 */
class IndustryCategoryController{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Industry
     */
    public $industry;
    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;

    /**
     * @inject
     * @var Container
     */
    protected $container;

    /**
     * 行业分类列表
     * @route POST /getLists
     * @param int $type
     * @param string $name
     * @param int $status
     * @param string $order
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @return array
     */
    public function getLists($type = 1, $name = '', $status = -1, $order ='', $page = 1, $pagesize = 10) {
        try {
            $where = [];
            if(strlen($name) > 0){
                $where['name'] = ["like"=>'%'.$name.'%'];
            }
            if($status >=0){
                $where['status'] = $status;
            }
            if($type >0){
                $where['type'] = $type;
            }
            if(strlen($order)>0){
                $order = json_decode($order,true);
            }
            $data = $this->industry->getCategoryList($page, $pagesize,$where,$order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 行业分类添加
     * @route POST /add
     * @param IndustryCategoryEntity $entity {@bind request.request}
     * @return array
     */
    public function add(IndustryCategoryEntity $entity) {
        try{
            return $this->mutex->getMutex('addIndustryCategory')->synchronized(function() use($entity){
                //查询
                $checkExist = $this->industry->getCategoryList(1, 1,
                    [
                        'name'=>$entity->getName(),
                        'type'=>$entity->getType()
                    ]);
                if($checkExist && $checkExist['count']>0){
                    throw $this->exception([
                        'code'=>ErrorConst::DUPLICATE_INSERT,
                        'text'=>"行业分类重复添加".json_encode($entity->toArray())
                    ]);
                }
                $this->industry->AddCategory($entity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 行业编辑状态
     * @route POST /changeStatus
     * @param int $id {@v min:1}
     * @param int $status
     * @return array
     */
    public function changeStatus($id, $status) {
        try{
            return $this->mutex->getMutex('changeStatusIndustryCategory'.$id)->synchronized(function() use($id,$status){
                $this->industry->changeCategoryStatus($id,$status);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获得单个行业内容
     * @route POST /getCategory
     * @param $id
     * @return array
     */
    public function getCategory($id) {
        try {

            $data = $this->industry->getCategoryDetail($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 行业编辑提交
     * @route POST /editCategory
     * @param IndustryCategoryEntity $entity {@bind request.request}
     * @return array
     */
    public function editCategory(IndustryCategoryEntity $entity) {
        try{
            return $this->mutex->getMutex('editIndustryCategory'.$entity->getId())->synchronized(function() use($entity){
                $this->industry->editCategory($entity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 行业分类 下的 行业 列表
     * @route POST /getIndustryLists
     * @param int $id
     * @param int $type
     * @param string $name
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @return array
     */
    public function getIndustryLists($id = 0, $type = 0, $name = '', $page = 1, $pagesize = 10) {
        try {
            $where = [];
            if($id >0){
                $where ['t_industry_category_relate.category_id']= $id;
            }
            if($type >0){
                $where['t_industry.type'] = $type;
            }

            if(strlen($name) > 0){
                $where['t_industry.name'] = ["like"=>'%'.$name.'%'];
            }

            $data = $this->industry->getIndustryLists($page, $pagesize,$where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 添加行业 和 分类的 关联
     * @route POST /addIndustryCategoryRelate
     * @param int $industry_id
     * @param int $category_id
     * @type int $type
     * @return mixed
     */
    public function addIndustryCategoryRelate($industry_id, $category_id, $type) {
        try{
            return $this->mutex->getMutex('addIndustryCategoryRelate')->synchronized(function() use($industry_id, $category_id, $type){
                //查询
                $checkExist = $this->industry->getRelateList($industry_id, $category_id);
                if(!is_null($checkExist)){
                    throw $this->exception([
                        'code'=>ErrorConst::DUPLICATE_INSERT,
                        'text'=>"行业{$industry_id} 同 分类 {$category_id}重复添加"
                    ]);
                }
                $this->industry->addIndustryCategoryRelate($industry_id, $category_id, $type);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 删除行业 和 分类的 关联
     * @route POST /delRelate
     * @param int $id
     * @type int $type
     * @return mixed
     */
    public function delRelate($id) {
        try{

            $this->industry->delRelate($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 删除行业分类
     * @route POST /delIndustryCategory
     * @param int $id
     * @type int $type
     * @return mixed
     */
    public function delIndustryCategory($id) {
        try{

            $this->industry->delIndustryCategory($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获得所有 行业分类 及下方所属 行业
     * @route POST /getCategoryWithIndustryList
     * @param int $id
     * @param int $type
     * @param string $name
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @return array
     */
    public function getCategoryWithIndustryList($id = 0, $type = 0, $name = '', $page = 1, $pagesize = 10) {
        try {
            $where = ['t_industry_category.status'=>1];
//            if($id >0){
//                $where ['t_industry_category_relate.category_id']= $id;
//            }
            if($type >0){
                $where['t_industry_category.type'] = $type;
            }
//            if(strlen($name) > 0){
//                $where['t_industry.name'] = ["like"=>'%'.$name.'%'];
//            }

            $data = $this->industry->getCategoryWithIndustryList($page, $pagesize,$where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
    /**
     * 获得所有 行业分类 及下方所属 行业
     * @route POST /getIndustryCategoryListWithIndustry
     * @param int $id
     * @param int $type
     * @param string $name
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @param int $show_all
     *
     * @return array
     */
    public function getIndustryCategoryListWithIndustry($id = 0, $type = 0, $name = '', $page = 1, $pagesize = 10, $show_all = 0) {
        try {
            $order = [];
            if($show_all >0){

            }else{
                $where = ['t_industry_category.status'=>1];
            }


//            if($id >0){
//                $where ['t_industry_category_relate.category_id']= $id;
//            }
            if($type >0){
                $where['t_industry_category.type'] = $type;
            }
//            if(strlen($name) > 0){
//                $where['t_industry.name'] = ["like"=>'%'.$name.'%'];
//            }


            $data = $this->industry->getIndustryCategoryListWithIndustry($page, $pagesize,$where,$order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

}