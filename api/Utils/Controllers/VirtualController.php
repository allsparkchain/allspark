<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4 0004
 * Time: 10:48
 */

namespace App\Utils\Controllers;

use PhpBoot\DI\Traits\EnableDIAnnotations;
use App\Utils\ThrowResponseParamerTrait;
use App\Utils\HttpResponseTrait;
use PhpBoot\DB\DB;
use App\Utils\Services\VirtualService;
use App\Utils\Defines;

/**
 * @path /utils/virtual
 */
class VirtualController
{
    use EnableDIAnnotations,ThrowResponseParamerTrait,HttpResponseTrait;//启动依赖注入@inject

    /**
     * @inject
     * @var DB
     */
    protected $db;

    /**
     * @inject
     * @var VirtualService
     */
    protected $virtual;



    /**
     * 更新虚拟码
     * @route POST /update_virtual
     * @param int $good_id
     * @param int $number
     * @return mixed
     */
    public function updateVirtual($good_id, $number)
    {
        try{
            $rs = $this->virtual->updateVirtual($good_id, $number);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * 绑定虚拟码与商品
     * @route POST /bind_virtual
     *
     * @param string $name //主虚拟码表 name
     * @param int $good_id
     * @param int $number
     * @param string $remark
     *
     * @return mixed
     */
    public function bindVirtual($name, $good_id, $number, $remark = '')
    {
        try{
            $rs = $this->virtual->bindVirtual($name, $good_id, $number, $remark);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }



    /**
     * 验证并使用虚拟码
     * @route POST /verify_virtual
     *
     * @param string $code_group
     * @param string $advert_id
     *
     * @return mixed
     */
    public function verifyVirtual($code_group, $advert_id)
    {
        try{
            $rs = $this->virtual->verifyVirtual($code_group, $advert_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * 添加条目统计
     * @route POST /add_item_statistics
     * @param string $advert_id
     * @param string $first
     * @param int $second
     *
     * @return mixed
     */
    public function addItemStatistics($advert_id, $first, $second)
    {
        try{
            $rs = $this->virtual->addItemStatistics($advert_id, $first, $second);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取激活总数
     * @route POST /total_statistics
     * @param string $avert_id
     * @param string $first
     * @param int $second
     * @return mixed
     */
    public function totalStatistics($avert_id, $first, $second)
    {
        try{
            $rs = $this->virtual->totalStatistics($avert_id, $first, $second);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取条目统计
     * @route POST /get_item_statistics
     * @param string $advert_id
     * @param string $first
     * @param int $page
     * @param int $pagesize
     * @param int $second
     * @return mixed
     */
    public function getItemStatistics($advert_id, $first, $page, $pagesize, $second)
    {
        try{
            $rs = $this->virtual->getItemStatistics($advert_id, $first, $page, $pagesize, $second);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * 获取虚拟码
     *
     * @route POST /get_virtual
     * @param  string $good_id
     * @param string $buyer_id
     * @param string $order_id
     * @return mixed
     */
    public function getVirtual($good_id, $buyer_id, $order_id)
    {
        try{
            $rs = $this->virtual->getVirtual($good_id, $buyer_id, $order_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取商品的所有虚拟码
     *
     * @route POST /getVirtualList
     * @param int $good_id
     * @param string $code
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @return mixed
     */
    public function getVirtualList($good_id, $code = '', $page =1, $pagesize = 10)
    {
        try{
            $rs = $this->virtual->getVirtualList($good_id,$code, $page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 通过bind 用户id与code_id获取对应的发送信息
     *
     * @route POST /get_info
     * @param int $id
     * @return mixed
     */
    public function getInfo($id)
    {
        try{
            $rs = $this->virtual->getInfo($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * 添加虚拟码
     * @route POST /addVirtual
     * @param string $name {$v required|lengthMin:1}
     * @param int $good_id {$v required|min:1}
     * @param int $number {$v min:1}
     * @param string $remark
     * @param int $generateType {$v min:1}
     * @return array
     */
    public function addVirtual($name, $good_id, $number,$remark='',$generateType = 1)
    {
        try{
            $rs = $this->virtual->addVirtual($name,$good_id,$number,$generateType,$remark);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * excel导入虚拟码
     * @route POST /importCode
     * @param int $good_id {$v required|min:1}
     * @param array $list {$v required}
     * @return array
     */
    public function importCode($good_id, $list)
    {
        try{
            $rs = $this->virtual->importCode($good_id,$list);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
}