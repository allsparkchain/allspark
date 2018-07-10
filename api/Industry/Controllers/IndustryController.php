<?php

namespace App\Industry\Controllers;

use App\Exceptions\NullDataException;
use App\Industry\Entities\IndustryEntity;
use App\Industry\Services\Industry;
use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use App\Utils\Paramers;

/**
 * Class IndustryController
 * @path /industry
 */
class IndustryController{
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
     * 行业列表
     * @route POST /getLists
     * @param int $type
     * @param string $name
     * @param int $pid {@v min:0}
     * @param int $status
     * @param string $order
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @return array
     */
    public function getLists($type = 0, $name = '', $pid = -1, $status = -1, $order = '', $page = 1, $pagesize = 10) {
        try {
            $where = [];
            if(strlen($name) > 0){
                $where['name'] = ["like"=>'%'.$name.'%'];

            }
            if($pid >=0){
                $where['pid'] = $pid;
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
            $data = $this->industry->getList($page, $pagesize,$where,$order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 行业添加
     * @route POST /add
     * @param IndustryEntity $entity {@bind request.request}
     * @return array
     */
    public function add(IndustryEntity $entity) {
        try{
            return $this->mutex->getMutex('addIndustry')->synchronized(function() use($entity){
                //查询
                $checkExist = $this->industry->getList(1, 1,
                    [
                        'name'=>$entity->getName(),
                        'type'=>$entity->getType()
                    ]);
                if($checkExist && $checkExist['count']>0){
                    throw $this->exception([
                        'code'=>ErrorConst::DUPLICATE_INSERT,
                        'text'=>"行业重复添加".json_encode($entity->toArray())
                    ]);
                }
                $this->industry->add($entity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 行业编辑
     * @route POST /edit
     * @param IndustryEntity $entity {@bind request.request}
     * @return array
     */
    public function edit(IndustryEntity $entity) {
        try{
            return $this->mutex->getMutex('editIndustry'.$entity->getId())->synchronized(function() use($entity){
                $this->industry->edit($entity);
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
            return $this->mutex->getMutex('changeStatusIndustry'.$id)->synchronized(function() use($id,$status){
                $this->industry->changeStatus($id,$status);
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

            $data = $this->industry->getCategory($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 删除行业
     * @route POST /delIndustry
     * @param int $id
     * @type int $type
     * @return mixed
     */
    public function delIndustry($id) {
        try{

            $this->industry->delIndustry($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 同广告主有关联的行业列表
     * @route POST /getIndustryUserList
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @return array
     */
    public function getIndustryUserList($page = 1, $pagesize = 10) {
        try {
            $where = [];
            $where['t_industry_user.status'] = 1;
            $where['t_industry.status'] = 1;
            $order = [];
            $data = $this->industry->getIndustryUserList($page, $pagesize,$where,$order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获得自媒体用户是否选择了一个自媒体行业
     * @route POST /getIndustryUser
     * @param int $uid {@v min:1}
     * @return array
     */
    public function getIndustryUser($uid = 0) {
        try {
            $where = [];
            if($uid>0){
                $where['uid'] = $uid;
            }
            $data = $this->industry->getIndustryUser($where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获得自媒体用户是否选择了一个自媒体行业
     * @route POST /AddUidIndustry
     * @param int $uid {@v min:1}
     * @param int $industry_id {@v min:1}
     * @return array
     */
    public function AddUidIndustry($uid = 0, $industry_id) {
        try {

            $data = $this->industry->AddUidIndustry($uid,$industry_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 添加通用分类
     * @route POST /createItemCategory
     * @param String $itemData
     * @return array
     */
    public function createItemCategory($itemData) {
        try {

            $data = $this->industry->createItemCategory($itemData);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 查询通用分类
     * @route POST /getItemCategory
     * @param int $advert_uid
     * @return array
     */
    public function getItemCategory($advert_uid) {
        try {
            $data = $this->industry->getItemCategory($advert_uid);

            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        }  catch (NullDataException $e) {
            return $this->respone(200, "nodata");
        }catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 删除通用分类
     * @route POST /delItemCategory
     * @param int $advert_uid
     * @return array
     */
    public function delItemCategory($advert_uid) {
        try {
            $data = $this->industry->delItemCategory($advert_uid);

            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }




}