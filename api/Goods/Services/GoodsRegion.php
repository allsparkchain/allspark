<?php
namespace App\Goods\Services;

use function App\getCurrentTime;
use App\Goods\Entities\ProductRegionCategoryEntity;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Pagination;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class GoodsRegion{
    use EnableDIAnnotations,HttpResponseTrait,ThrowResponseParamerTrait;////启用通过@inject标记注入依赖

    /**
     * @inject
     * @var DB
     */
    private $db;

    /**
     * @inject
     * @var Mutex
     */
    private $mutex;

    /**
     * @inject
     * @var Container
     */
    protected  $container;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;
    /**
     * 获取地域列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getRegionList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_region");
            if ($where) {
                $joinRule->where($where);
            }
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $joinRule->orderBy($key, $value);
                    }
                }
            } else {
                $joinRule->orderBy('parent_id', DB::ORDER_BY_ASC);
                $joinRule->orderBy('region_id', DB::ORDER_BY_ASC);
            }
            $pagination = new Pagination($joinRule, $page, $pageszie);
            return $pagination->get();
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 获取某个产品地域列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getproductRegionList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("t_product_region.*",DB::raw('t_region.region_name'))
                ->from("t_product_region")
                ->leftJoin('t_region')->on('t_product_region.region_id=t_region.region_id');
            if ($where) {
                $joinRule->where($where);
            }
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $joinRule->orderBy($key, $value);
                    }
                }
            } else {
                $joinRule->orderBy('add_time', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_product_region.region_id', DB::ORDER_BY_ASC);
            }
            $pagination = new Pagination($joinRule, $page, $pageszie);
            return $pagination->get();
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
        return [];
    }


    /**
     * 返回地域ID
     * @param string $name
     * @param bool $exactly
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    function searchRegionByName($name ='', $exactly = false)
    {
        $arr = [];
        //默认模糊查找可能获得多个
        $pagesize = 999;
        $search_arr = ['region_name' => ["like" => '%' . $name . '%']];

        if(!$exactly){
            $checkExist = $this->getRegionList(1, $pagesize,$search_arr);
            if($checkExist['count']>0){
                foreach ($checkExist['data'] as $v){
                    $arr[] = $v['region_id'];
                }
                return $arr;
            }
        }
        //精确查找只能完整匹配返回一个
        $search_arr = ['region_name' => $name];
        $pagesize = 1;
        $checkExist = $this->getRegionList(1, $pagesize,$search_arr);

        if($checkExist['count']>0){
            return $checkExist['data'][0]['region_id'];
        }

    }

    /**
     * 产品添加地域
     * @param $product_id
     * @param $region_id
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function addProdcutRegion($product_id, $region_id) {
        try{
            return $this->db->transaction(function(DB $db) use ($product_id, $region_id){
                $lastId = $db->insertInto('t_product_region')->values([
                    'product_id'=>$product_id,
                    'region_id'=>$region_id,
                    'add_time' =>getCurrentTime()
                ])->exec()->lastInsertId();
                if($lastId<=0){
                    throw $this->exception([
                        'code'=>ErrorConst::INSERT_ERROR,
                        'text'=>"产品地域创建新失败产品id".$product_id." 地域id".$region_id
                    ]);
                }
                return true;
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 删除添加地域
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function deleteProdcutRegion($id) {
        try{
            return $this->db->transaction(function(DB $db) use ($id){
                $rs = $db->deleteFrom('t_product_region')
                    ->where(['id'=>$id])->exec();
                if($rs->rows<=0){
                    throw $this->exception([
                        'code'=>ErrorConst::DELET_REGION_ERROR,
                        'text'=>"产品地域删除失败id".$id
                    ]);
                }
                return true;
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    ////////////////

    /**
     * 获取产品地域分类
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getProductRegionCategoryList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_region_category");
            if ($where) {
                $joinRule->where($where);
            }
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $joinRule->orderBy($key, $value);
                    }
                }
            } else {
                $joinRule->orderBy('add_time', DB::ORDER_BY_DESC);
                $joinRule->orderBy('status', DB::ORDER_BY_ASC);
            }
            $pagination = new Pagination($joinRule, $page, $pageszie);
            return $pagination->get();
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 更新地域分类状态
     * @param $cid
     * @param $status
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function categoryChangeStatus($cid, $status)
    {
        try{
            $execResult = $this->db->update('t_region_category')->set([
                'status' => $status
            ])->where(['id' => $cid])->exec();

            if ($execResult->rows != 1) {
                throw $this->exception([
                    'code'=>ErrorConst::CHANGE_STATUS_ERROR,
                    'text'=>"产品分类id:".$cid."状态更新失败"
                ]);
            }
            return true;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 地域分类添加
     * @param ProductRegionCategoryEntity $productRegionCategoryEntity
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function categoryAdd(ProductRegionCategoryEntity $productRegionCategoryEntity)
    {
        try{
            return $this->db->transaction(function(DB $db) use ($productRegionCategoryEntity){
                $db->insertInto('t_region_category')->values([
                    'category_name' => $productRegionCategoryEntity->getCategoryName(),
                    'parent' => $productRegionCategoryEntity->getParent(),
                    'status' => $productRegionCategoryEntity->getStatus(),
                    'add_time' =>getCurrentTime()
                ])->exec()->lastInsertId();
                return true;
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 获取单个分类详情
     * @param $id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getCategory($id)
    {
        $nextWhereRule = $this->db->select('*')
            ->from('t_region_category')
            ->where(['id' => $id]);
        $data = $nextWhereRule->getFirst();
        return $data;
    }

    /**
     * 分类编辑
     * @param ProductRegionCategoryEntity $productRegionCategoryEntity
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function editCategory(ProductRegionCategoryEntity $productRegionCategoryEntity){
        try{
            return $this->db->transaction(function(DB $db) use ($productRegionCategoryEntity){
                $db->update('t_region_category')->set([
                    'category_name' => $productRegionCategoryEntity->getCategoryName(),
                    'parent' => $productRegionCategoryEntity->getParent(),
                ])->where(['id'=>$productRegionCategoryEntity->getId()])->exec();
                return true;
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }


    /**
     * 获取某个产品地域包含的地域列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getproductCategoryRegionList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("t_region_category_relate.*",DB::raw('t_region.region_name'))
                ->from("t_region_category_relate")
                ->leftJoin('t_region')->on('t_region_category_relate.region_id=t_region.region_id');
            if ($where) {
                $joinRule->where($where);
            }
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $joinRule->orderBy($key, $value);
                    }
                }
            } else {
                $joinRule->orderBy('add_time', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_region_category_relate.region_id', DB::ORDER_BY_ASC);
            }
            $pagination = new Pagination($joinRule, $page, $pageszie);
            return $pagination->get();
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 产品地域分类 添加地域
     * @param $category_id
     * @param $region_id
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function addRegionForRegionCategory($category_id, $region_id) {
        try{
            return $this->db->transaction(function(DB $db) use ($category_id, $region_id){
                $lastId = $db->insertInto('t_region_category_relate')->values([
                    'category_id'=>$category_id,
                    'region_id'=>$region_id,
                    'add_time' =>getCurrentTime()
                ])->exec()->lastInsertId();
                if($lastId<=0){
                    throw $this->exception([
                        'code'=>ErrorConst::INSERT_ERROR,
                        'text'=>"产品地域创建新失败产品id".t_region_category_relate." 地域id".$region_id
                    ]);
                }
                return true;
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 删除对应地域分类 下的 某个 地域
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function deleteCategoryRegion($id) {
        try{
            return $this->db->transaction(function(DB $db) use ($id){
                $rs = $db->deleteFrom('t_region_category_relate')
                    ->where(['id'=>$id])->exec();
                if($rs->rows<=0){
                    throw $this->exception([
                        'code'=>ErrorConst::DELETE_REGIONCATEGORY_REGION,
                        'text'=>"该地域分类下的地域删除失败id".$id
                    ]);
                }
                return true;
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 资讯页面 获得所有 地域分类，，及所属的地域
     * @param $page
     * @param $pageszie
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function categoryRegionList($page, $pageszie,  $where = [], $order = [])
    {
        //t_product_region = proc + (t_region) +  t_region_category =  t_region_category_relate
        try {
            $joinRule = $this->db->select("category_name",DB::raw("id as categoryId"))
                ->from("t_region_category");
            $whereDefault = ['status'=>1];
            if ($where) {
                $whereDefault = array_merge($where,$whereDefault);

            }
            $joinRule->where($whereDefault);
            $joinRule->orderBy('showorder', DB::ORDER_BY_ASC);
            $joinRule->orderBy('parent', DB::ORDER_BY_ASC);
            $joinRule->orderBy('add_time', DB::ORDER_BY_DESC);

            $pagination = new Pagination($joinRule, $page, $pageszie);
            $data = $pagination->get();
            foreach ($data['data'] as $key => $value){
                $joinRule = $this->db->select("t_region.region_name",DB::raw('t_region.region_id as regionId'))
                    ->from('t_region_category_relate')
                    ->innerJoin('t_region')->on('t_region_category_relate.region_id=t_region.region_id')
                    ->where([
                        't_region_category_relate.status'=>1,
                        't_region_category_relate.category_id'=>$value['categoryId']
                    ])
                    ->orderBy('t_region.region_id',DB::ORDER_BY_ASC);
                $list = $joinRule->get();
                $data['data'][$key]['regionlist'] = $list;
            }
            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }
}