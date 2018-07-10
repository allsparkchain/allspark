<?php
namespace App\Industry\Services;

use App\Exceptions\NullDataException;
use function App\getCurrentTime;
use App\Industry\Entities\IndustryCategoryEntity;
use App\Industry\Entities\IndustryEntity;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Pagination;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class Industry{
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
     * 行业列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_industry");
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
                $joinRule->orderBy('pid', DB::ORDER_BY_ASC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);
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
     * 添加行业
     * @param IndustryEntity $industryEntity
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function Add(IndustryEntity $industryEntity)
    {

        try{
            return $this->db->transaction(function(DB $db) use ($industryEntity){

                $fname = '';
                if($industryEntity->getPid()>0){
                    $pio = $this->getCategory($industryEntity->getPid());
                    if(is_null($pio)){
                        throw $this->exception([
                            'code'=>ErrorConst::PARENT_NOT_FOUND,
                            'text'=>"父类未找到".$industryEntity->getPid()
                        ]);
                    }
                    $fname = $pio['name'].' - ';
                }

                $db->insertInto('t_industry')->values([
                    'pid'=> $industryEntity->getPid(),
                    'type' => $industryEntity->getType(),
                    'fname' => $fname.$industryEntity->getName(),
                    'name' => $industryEntity->getName(),
                    'desc' => $industryEntity->getDesc(),
                    'status' => $industryEntity->getStatus(),
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
     * 编辑
     * @param IndustryEntity $entity
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function edit(IndustryEntity $entity){
        try{
            return $this->db->transaction(function(DB $db) use ($entity){
                $fname = '';
                //根据父id 查找 父类更新fname
                $pid = $entity->getPid();
                if($pid>0){
                    $parent = $this->getCategory($pid);
                    if(is_null($parent)){
                        throw $this->exception([
                            'code'=>ErrorConst::PARENT_NOT_FOUND,
                            'text'=>"父类未找到".$entity->getPid()
                        ]);
                    }
                    $fname = $parent['name'].' - ';
                }
                $db->update('t_industry')->set([
                    'pid'=> $entity->getPid(),
                    'type' => $entity->getType(),
                    'fname' => $fname.$entity->getName(),
                    'name' => $entity->getName(),
                    'desc' => $entity->getDesc(),
                    'status' => $entity->getStatus(),
                ])->where(['id'=>$entity->getId()])->exec();
                // 查找子类 更新
                $children = $this->getAll(['pid'=>$entity->getId()]);
                if(count($children)>0){
                    foreach ($children as $child){
                    $db->update('t_industry')->set([
                        'fname' => $entity->getName().' - '.$child['name'],
                    ])->where(['id'=>$child['id']])->exec();
                    }
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
     * 获取单个详情
     * @param $id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getCategory($id)
    {
        $nextWhereRule = $this->db->select('*')
            ->from('t_industry')
            ->where(['id' => $id]);
        $data = $nextWhereRule->getFirst();
        return $data;
    }

    /**
     * 获取所有的列表
     * @param array $where
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function getAll($where = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_industry");
            if ($where) {
                $joinRule->where($where);
            }
            return $joinRule->get();

        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 更新行业状态
     * @param $id
     * @param $status
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function changeStatus($id, $status)
    {
        try{
            $execResult = $this->db->update('t_industry')->set([
                'status' => $status
            ])->where(['id' => $id])->exec();

            if ($execResult->rows != 1) {
                throw $this->exception([
                    'code'=>ErrorConst::UPDATE_STATUS_ERROR,
                    'text'=>"行业分类id:".$id."状态更新失败"
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
     * @param $uid
     * @param $industry_id
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function AddUidIndustry($uid, $industry_id)
    {
        try{
            $check_exist = $this->getIndustryUser(['uid'=>$uid,'industry_id'=>$industry_id]);
            if(count($check_exist)>0){

                //已存在
                throw $this->exception([
                    'code' => ErrorConst::INDUSTRY_USER_EXIST,
                    'text' => "行业:$industry_id 用户:$uid 已存在关联记录"
                ]);
            }
            return $this->db->transaction(function(DB $db) use ($uid,$industry_id){
                if($industry_id > 0){
                    $db->insertInto('t_industry_user')->values([
                        'uid'=> $uid,
                        'industry_id' => $industry_id,
                        'add_time' =>getCurrentTime()
                    ])->exec()->lastInsertId();
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
     * 删除 行业
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function delIndustry($id){
        try {
            $this->db->deleteFrom('t_industry')->where(['id'=>$id])->exec();
            return true;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }



    //行业分类          //      //      //      //
    /**
     * 行业分类列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @param int $lite
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getCategoryList($page, $pageszie,  $where = [], $order = [],$lite = -1)
    {
        try {

            //过滤敏感词的分类
//            if($lite>0){
//               $where['id'] = ['>'=>DB::raw("0 and name not like '%营养%' and name not like '%食品%'")];
//            }
            $joinRule = $this->db->select("*")
                ->from("t_industry_category");
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
                $joinRule->orderBy('order', DB::ORDER_BY_DESC);
                $joinRule->orderBy('add_time', DB::ORDER_BY_DESC);
                $joinRule->orderBy('type', DB::ORDER_BY_ASC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);//die;
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
     * 删除 行业分类
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function delIndustryCategory($id){
        try {
            $this->db->deleteFrom('t_industry_category')->where(['id'=>$id])->exec();
            return true;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 添加行业
     * @param IndustryCategoryEntity $entity
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function AddCategory(IndustryCategoryEntity $entity)
    {

        try{
            return $this->db->transaction(function(DB $db) use ($entity){


                $db->insertInto('t_industry_category')->values([
                    'type' => $entity->getType(),
                    'name' => $entity->getName(),
                    'icon_img'=> $entity->getIconImg(),
                    'icon_heightLight_img'=> $entity->getIconHeightLightImg(),
                    'status' => $entity->getStatus(),
                    'order' => $entity->getOrder(),
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
     * 更新行业状态
     * @param $id
     * @param $status
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function changeCategoryStatus($id, $status)
    {
        try{
            $execResult = $this->db->update('t_industry_category')->set([
                'status' => $status
            ])->where(['id' => $id])->exec();

            if ($execResult->rows != 1) {
                throw $this->exception([
                    'code'=>ErrorConst::UPDATE_STATUS_ERROR,
                    'text'=>"行业分类id:".$id."状态更新失败"
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
     * 获取单个分类详情
     * @param $id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getCategoryDetail($id)
    {
        $nextWhereRule = $this->db->select('*')
            ->from('t_industry_category')
            ->where(['id' => $id]);
        $data = $nextWhereRule->getFirst();
        return $data;
    }

    /**
     * 编辑
     * @param IndustryCategoryEntity $entity
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function editCategory(IndustryCategoryEntity $entity){
        try{
            return $this->db->transaction(function(DB $db) use ($entity){

                $db->update('t_industry_category')->set([
                    'type' => $entity->getType(),
                    'name' => $entity->getName(),
                    'icon_img'=> $entity->getIconImg(),
                    'icon_heightLight_img'=> $entity->getIconHeightLightImg(),
                    'status' => $entity->getStatus(),
                    'order' => $entity->getOrder(),
                ])->where(['id'=>$entity->getId()])->exec();
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
     * 行业分类列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getIndustryLists($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("t_industry.name,t_industry_category_relate.id")
                ->from("t_industry_category_relate")
                ->leftJoin('t_industry')->on('t_industry.id = t_industry_category_relate.industry_id')
            ;
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
                $joinRule->orderBy('t_industry.add_time', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_industry.type', DB::ORDER_BY_ASC);
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
     * 添加行业 和 分类的 关联
     * @param int $industry_id
     * @param int $category_id
     * @type int $type
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function addIndustryCategoryRelate($industry_id, $category_id, $type)
    {

        try{
            return $this->db->transaction(function(DB $db) use ($industry_id, $category_id, $type){

                $db->insertInto('t_industry_category_relate')->values([
                    'industry_id' => $industry_id,
                    'category_id' => $category_id,
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
     * 检测  行业 和  分类 的关联是否存在
     * @param $industry_id
     * @param $category_id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getRelateList($industry_id, $category_id){

        $joinRule = $this->db->select('*')
            ->from("t_industry_category_relate")
            ->where([
                'industry_id'=>  $industry_id,
                'category_id'=>$category_id
            ]);
        return $joinRule->getFirst();
    }

    /**
     * 删除 行业 和  分类 的关联
     * @param $id
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function delRelate($id){
        try {
            $this->db->deleteFrom('t_industry_category_relate')->where(['id'=>$id])->exec();
            return true;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 获得所有 行业分类 及下方所属 行业
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getCategoryWithIndustryList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select(
                "t_industry_category.name,t_industry_category.id"
            )
                ->from('t_industry_category')
            ;
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
                $joinRule->orderBy('t_industry_category.add_time', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_industry_category.type', DB::ORDER_BY_ASC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);

            $pagination = new Pagination($joinRule, $page, $pageszie);
            $data = $pagination->get();
            if($data['count']>0){
                foreach ($data['data'] as $key=>$value){
                    $industry_info = $this->db->select('t_industry.name,t_industry.id')
                        ->from('t_industry_category_relate')
                        ->leftJoin('t_industry')->on('t_industry_category_relate.industry_id = t_industry.id')
                        ->where(['t_industry_category_relate.category_id' => $value['id']])->get();
//                    var_dump($industry_info);
                    $data['data'][$key]['list'] = [];
                    if(count($industry_info)>0){
                        $data['data'][$key]['list'] = $industry_info;
                    }
                }
            }
//            var_dump($data);die;
            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 同广告主有关联的行业列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getIndustryUserList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select(DB::raw("distinct(t_industry.id)"),DB::raw("t_industry.name"))
                ->from("t_industry_user")
                ->leftJoin('t_industry')->on('t_industry.id = t_industry_user.industry_id')
            ;
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
                $joinRule->orderBy('t_industry.add_time', DB::ORDER_BY_DESC);
//                $joinRule->orderBy('type', DB::ORDER_BY_ASC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);
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
     * 获得行业分类列表 及 下属 所有的行业列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getIndustryCategoryListWithIndustry($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select(DB::raw('t_industry_category.name as CategoryName'),"t_industry_category.id")
                ->from("t_industry_category")
            ;
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
                $joinRule->orderBy('t_industry_category.add_time', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_industry_category.type', DB::ORDER_BY_ASC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);
            $pagination = new Pagination($joinRule, $page, $pageszie);
            $data = $pagination->get();
//            var_dump($data);die;
            foreach ($data['data'] as $key=>$val){
                $joinRule = $this->db->select("t_industry.name",DB::raw('t_industry.id as Category_id'))
                    ->from("t_industry_category_relate")
                    ->leftJoin('t_industry')->on('t_industry_category_relate.industry_id = t_industry.id')
                    ->where(['t_industry_category_relate.category_id'=>$val['id']])->get();
                $data['data'][$key]['list'] = $joinRule;
            }
            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }


    /**
 * 获得自媒体用户是否选择了一个自媒体行业
 * @param array $where
 * @return array
 * @throws \App\Exceptions\RuntimeException
 */
    public function getIndustryUser($where = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_industry_user");
            if ($where) {
                $joinRule->where($where);
            }
            $rs = $joinRule->getFirst();
            if(!is_null($rs)){
                return  $rs;
            }
            return  [];
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }


    /**
     * 添加通用分类
     * @param $itemData
     * @return \PhpBoot\DB\impls\ExecResult|string
     * @throws \App\Exceptions\RuntimeException
     */
    public function createItemCategory($itemData)
    {
        try {
            $item = (json_decode($itemData,true));


            if(!isset($item['name']) && !isset($item['advert_uid']) && !isset($item['children'])){
                throw $this->exception([
                    'code'=>ErrorConst::SYSTEM_ERROR,
                    'text'=>'系统错误'
                ]);
            }

            $category = $this->db->select("*")
                ->from("t_item_category")->where(['advert_uid'=>$item['advert_uid'],'pid'=>0])->getFirst();
            if(!$category){
                //没有找到门店
                $lastid = $this->db->insertInto('t_item_category')->values([
                    'pid'=> 0,
                    'name' => $item['name'],
                    'advert_uid' => $item['advert_uid'],
                    'add_time' => getCurrentTime()
                ])->exec()->lastInsertId();

                foreach ( $item['children'] as $key => $val){

                    $this->db->insertInto('t_item_category')->values([
                        'pid'=> $lastid,
                        'name' => $val,
                        'advert_uid' => $item['advert_uid'],
                        'add_time' => getCurrentTime()
                    ])->exec();

                }
            }else{
                //找到门店
                $this->db->update('t_item_category')->set([
                    'pid'=> 0,
                    'name' => $item['name'],
                    'advert_uid' => $item['advert_uid'],
                    'status' => 1,
                    'add_time' => getCurrentTime()
                ])->where(['id'=>$category['id']])->exec();
                //更新子集
                $this->db->update('t_item_category')->set([
                    'status' => 0,
                ])->where(['pid'=>$category['id']])->exec();

                foreach ( $item['children'] as $key => $val){
                    $this->db->getConnection()->query('
INSERT into jzwl.t_item_category (`pid` ,`name` ,`advert_uid` ,`status`, `add_time`) value("'.$category['id'].'","'.$val.'", "'.$item['advert_uid'].'", "1", "'.getCurrentTime().'")  on 
DUPLICATE KEY UPDATE status=VALUES(status); ')->execute();

                }
                $lastid  = $category['id'];
            }
            return  $lastid;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 通用分类查询
     * @param $advert_uid
     * @return null
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function getItemCategory($advert_uid)
    {
        try {
            $category = $this->db->select("id","name","advert_uid")
                ->from("t_item_category")->where(['advert_uid'=>$advert_uid,'pid'=>0,'status'=>1])->getFirst();
            if($category){
                $children = $this->db->select("id","name","advert_uid")
                    ->from("t_item_category")->where(['advert_uid'=>$advert_uid,'pid'=>$category['id'],'status'=>1])->get();
                $category['children'] = $children;
            } else {
                throw new NullDataException();
            }
            return $category;
        }  catch (\Exception $e) {
            throw $e;
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 通用分类查询
     * @param $advert_uid
     * @return null
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function delItemCategory($advert_uid)
    {
        try {
            $this->db->update('t_item_category')->set([
                'status' => 0
            ])->where(['advert_uid'=>$advert_uid])->exec();
            $this->db->update('t_good_statistics')->set([
                'data_status' => 0
            ])->where(['relation_id'=>$advert_uid])->exec();
            return true;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }



}