<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4 0004
 * Time: 13:13
 */

namespace App\Utils\Services;
use App\Goods\Services\Goods;
use App\Users\Services\User;
use App\Utils\ErrorConst;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use App\Utils\ThrowResponseParamerTrait;
use App\Utils\HttpResponseTrait;
use PhpBoot\DB\DB;
use App\Utils\Mutex;
use App\Utils\Lib\Virtual\VirtualInterface;
use App\Utils\Lib\Item\ItemInterface;
use App\Utils\Paramers;
use DI\Container;
use App\Utils\Pagination;


class VirtualService
{
    use EnableDIAnnotations,ThrowResponseParamerTrait,HttpResponseTrait;
    /**
     * @inject
     * @var DB
     */
    protected $db;

    /**
     * @inject
     * @var Mutex
     */
    protected $mutex;

    /**
     * @inject
     * @var Goods
     */
    protected $goods;

    /**
     * @inject
     * @var Message
     */
    protected $sms;

    /**
     * @inject
     * @var VirtualInterface
     */
    protected $virtualTool;

    /**
     * @inject
     * @var ItemInterface
     */
    protected $item;

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
     * @inject
     * @var User
     */
    protected $user;

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

                $rs = $this->virtualTool->addVirtual($name, $number);
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
     * 获取商品的所有虚拟码
     * @param $goodId
     * @param $code
     * @param $page
     * @param $pagesize
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function getVirtualList($goodId,$code = '',$page =1,$pagesize = 10)
    {
        try {
            $where = ['t_virtual_bind.good_id' => $goodId];
            if(strlen($code)>0){
                $where['t_virtual_detail.code_group'] = ['like'=>"%$code%"];
            }
            $rs = $this->db->select('t_virtual_bind.id,t_virtual_bind.bind_number,t_virtual_bind.used_number,t_virtual_detail.code_group',
                    DB::raw('t_virtual_detail.id as virtualDetailId,t_virtual_detail.is_used,t_virtual_bind.generate_type')
                )
                ->from('t_virtual_detail')
                ->leftJoin('t_virtual_bind')->on('t_virtual_detail.base_id = t_virtual_bind.base_id')
                ->where($where)
                ;
            $rs = $rs->orderBy('t_virtual_detail.id',DB::ORDER_BY_DESC);
            $pagination = new Pagination($rs, $page, $pagesize,$this->db);

            $data = $pagination->get();
            if($data['count']>0){
                $data['generate_type'] =  $data['data'][0]['generate_type'];
                $data['total_used'] = $data['data'][0]['used_number'];
            }else{
                $virtualBind = $this->virtualTool->getVirtualBind(0,$goodId);
                $data['total_used'] = 0;
                $data['generate_type'] = $virtualBind['generate_type'];
            }
            return $data;

        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 获取商品绑定的虚拟码
     * @param $good_id
     * @param $buyer_id
     * @param $order_id
     * @return string
     * @throws \App\Exceptions\RuntimeException
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

                if (!$rd) {
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
     * @param $good_id
     * @param $number
     * @param string $remark
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function updateVirtual($good_id, $number, $remark = '')
    {
        try {
            return $this->mutex->getMutex('updateVirtual' . $good_id)->synchronized(function () use ($good_id, $number, $remark) {
                //通过good_id查找base_id
                $rm = $this->db->select('base_id','generate_type','id')
                    ->from('t_virtual_bind')
                    ->where([
                        'good_id' => $good_id
                    ])->getFirst();
                if(!$rm) {
                    throw new \Exception("t_virtual_bind记录{$good_id}未找到", 500);
                }
                $vBaseData = $this->db->select('*')->from('t_virtual_base')->where(['id'=>$rm['base_id']])->getFirst();
                if($rm['generate_type'] == 2) {
                    //导入型 无须直接修改库存数
                    return true;
                }

                //自动生成型 库存增加修改库存数、并生成码，库存变小直接修改库存数
                $diffNumber = $number - $vBaseData['number'];
                if($diffNumber > 0) {
                    $this->virtualTool->addVirtualDetail($vBaseData['id'],[],$diffNumber);
                }
                $this->virtualTool->addVirtualBaseNumber($vBaseData['id'], $diffNumber);
                $this->virtualTool->addVirtualBindNumber($rm['id'], $diffNumber);

                //更新 产品数量
                $this->goods->updateStock($good_id,$diffNumber);

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
                ->leftJoin('t_virtual_base')->on('t_virtual_detail.base_id = t_virtual_base.id')
                ->leftJoin('t_virtual_bind')->on('t_virtual_base.id = t_virtual_bind.base_id')
                ->leftJoin('t_product')->on('t_product.id = t_virtual_bind.good_id')
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
                    ], $config))
                    ->getFirst();
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
     * @param string $advert_id
     * @param string $first
     * @param int $page
     * @param int $pagesize
     * @param int $second
     * @return mixed
     */
    public function getItemStatistics($advert_id, $first, $page, $pagesize, $second)
    {
        try {
            return $this->mutex->getMutex('getItemStatistics' . $advert_id)->synchronized(function () use ($advert_id, $first, $page, $pagesize, $second) {

//                $rd['detail'] = [];
//                $ru['page_count'] = 0;
//                $ru['page'] = 1;
//                if(!empty($second)){
//                    $sArray = ['item_id' => $second];
//                }else{
//                    $sArray = [];
//                }
//
//                //first  目前只有一个一级分类，所以first未使用，只判断了广告主id？？
//
//                //second 目前使用绑定  必须选二级分类
//
//                if(empty($first) && empty($second)){
//                    $rd['detail'] = $this->db->select('activity_num', 'date')->from('t_good_statistics')
//                        ->where([
//                            'relation_id' => $advert_id,
//                            'data_status' => 1,
//                        ])->orderBy('t_good_statistics.date', 'DESC')
//
//                        ->get();
//                }else {
//                    $rd['detail'] = $this->db->select('t_item_category.name', 't_good_statistics.activity_num', 't_good_statistics.date')
//                        ->from('t_good_statistics')
//                        ->leftJoin('t_item_category')
//                        ->on('t_good_statistics.item_id = t_item_category.id')
//                        ->where(array_merge([
//                            't_good_statistics.relation_id' => $advert_id,
//                            't_good_statistics.data_status' => 1,
//                            't_item_category.status' => 1
//                        ], $sArray))->orderBy('t_good_statistics.date', 'DESC')
//
//                        ->get();
//                }
//                $ru['page_count'] = $this->db->select('t_item_category.name', 'activity_num', 'date')
//                    ->from('t_good_statistics')
//                    ->leftJoin('t_item_category')
//                    ->on('t_good_statistics.item_id = t_item_category.id')
//                    ->where(array_merge([
//                        't_good_statistics.relation_id' => $advert_id,
//                        't_good_statistics.data_status' => 1,
//                        't_item_category.status' => 1
//                    ], $sArray))->orderBy('date', 'DESC')
//                    ->count();
//                $ru['page'] = $page;

                $where = [
                    't_item_category.advert_uid' => $advert_id,
                    't_item_category.status' => 1,
                    't_item_category.pid'=>['>'=>0]
                ];
                if($second>0){
                  $where['t_item_category.id'] = $second;
                }
                $second_categorys = $this->db->select('t_item_category.name,t_item_category.id')
                    ->from('t_item_category')
                    ->where($where);
                $pagination = new Pagination($second_categorys, $page, $pagesize, $this->db);
                $ru =  $pagination->get($this->db);
//                var_dump($ru);die;
                foreach ($ru['data'] as $key=>$value){

                    $good_statistics_detail = $this->db->select('t_good_statistics.activity_num', 't_good_statistics.date')
                        ->from('t_good_statistics')
                        ->where([
                            't_good_statistics.relation_id' => $advert_id,
                            't_good_statistics.data_status' => 1,
                            't_good_statistics.item_id'=>$value['id']
                        ])
                        ->orderBy('update_time',DB::ORDER_BY_DESC)
                        ->getFirst();
                    if(!is_null($good_statistics_detail)){
                        $ru['data'][$key]['date'] = $good_statistics_detail['date'];
                        $ru['data'][$key]['activity_num'] = $good_statistics_detail['activity_num'];
                    }else{
                        $ru['data'][$key]['date'] = '';
                        $ru['data'][$key]['activity_num'] = 0;
                    }
                }
                return $ru;

//                var_dump($ru,$rd);die;
//                return array_merge($ru, $rd);
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
//            $ru = $this->db->select('mobile')
//                ->from('t_user_address')
//                ->where([
//                    'status' => 1,
//                    'uid' => $rs['buyer_id']
//                ])->orderBy('')->getFirst();

            $ru = $this->user->getUserAddress(0,$rs['buyer_id'],1);

            //获取对应的微信openid
            $rt = $this->db->select('t_weixin_user.openid')
                ->from('t_weixin_user_relate')
                ->leftJoin('t_weixin_user')
                ->on('t_weixin_user_relate.wx_id = t_weixin_user.id')
                ->where([
                    't_weixin_user_relate.uid' => $rs['buyer_id'],
                ])->getFirst();
            return ['code' => $rd['code_group'], 'contents' => $rs['contents'], 'mobile' => $ru[0]['mobile']??0, 'openid' => $rt['openid']];
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 导入产品虚拟码
     * @param $goodId
     * @param $list
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function addCodeList($goodId,$list)
    {
        try {
            $check_bind = $this->virtualTool->getVirtualBind(0,$goodId);
            if(!is_null($check_bind)){
                $this->virtualTool->addVirtualDetail($check_bind['base_id'],$list);
            }else{
                throw $this->exception([
                    'code' => ErrorConst::VIRTUAL_BIND_NOT_EXIST,
                    'text' => '不存在bind,产品id'.$goodId,
                ]);
            }
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 绑定虚拟码与商品
     * @param $name
     * @param $good_id
     * @param $number
     * @param int $generate_type
     * @param string $remark
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function addVirtual($name, $good_id, $number,$generate_type = 1 ,$remark = '')
    {
        try {
            return $this->mutex->getMutex('addVirtual' . $name)->synchronized(function () use ($name, $good_id, $number, $generate_type, $remark) {
                $check_bind = $this->virtualTool->getVirtualBind(0,$good_id);
                if(!is_null($check_bind)){
                    throw $this->exception([
                        'code' => ErrorConst::VIRTUAL_BIND_EXIST,
                        'text' => '已存在bind'.serialize($check_bind),
                    ]);
                }
                //insert base
                $base_id = $this->virtualTool->addVirtual($name,$number);
                //insert bind
                $bind_id = $this->virtualTool->addVirtualBind($base_id,$good_id,$number, $generate_type);

                if($generate_type == 1){
                    //自动生成
                    $this->virtualTool->addVirtualDetail($base_id,[],$number);
                }

                return true;
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
     * excel导入code
     * @param $good_id
     * @param $list
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function importCode($good_id, $list)
    {
        try {
            return $this->mutex->getMutex('bindVirtual' . $good_id)->synchronized(function () use ($good_id, $list) {
                $bindInfo = $this->virtualTool->getVirtualBind(0,$good_id);
                if(is_null($bindInfo)){
                    throw $this->exception([
                        'code' => ErrorConst::VIRTUAL_BIND_NOT_EXIST,
                        'text' => '该产品不存在bind'.$good_id,
                    ]);
                }
//                $baseInfo =  $this->virtualTool->getVirtualBase($bindInfo['base_id']);


                //去除 excel 重复的数据
                $list = array_unique($list);


                //根据产品id寻找广告主下的所有code，后同 交集重复的数组
                $intersect =  $this->virtualTool->getAdvertAllVirtualCode($good_id,$list);

                //再取差集 从而 获得 需要插入的 不重复的 数组
                $list = array_diff($list,$intersect);

                if(!empty($list)){
                    //添加code记录
                    $this->virtualTool->addVirtualDetail($bindInfo['base_id'],$list);

                    //更新base数量
                    $this->virtualTool->addVirtualBaseNumber($bindInfo['base_id'],count($list));
                    //更新base数量
                    $rs = $this->virtualTool->addVirtualBindNumber($bindInfo['id'],count($list));
                    $num = count($list);

                    //更新 产品数量
                    $this->goods->updateStock($good_id,$num);
                }else{
                    $num = 0;
                }


                return $num;
            });
        }catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'text'=>$e->getMessage()
            ]);
        }
    }


    /**
     * 设置为默认收货地址
     * @param $product_order_id
     * @param $buyer_id
     * @param $product_id
     * @param $num
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function getVirtualCode($product_order_id,$buyer_id,$product_id,$num)
    {
        try {
            $datas = [];
            for($i = 0; $i < $num; $i++) {
                $id = $this->getVirtual($product_id,$buyer_id,$product_order_id);
//                //进行三方发送 短信、微信
//                $info = Curl::post('/utils/virtual/get_info', [
//                    'id' =>  array_get($id, 'data'),
//                ]);

                $info = $this->getInfo($id);
                $datas[] = $info['code'];
            }

            $message = implode(',',$datas);
            $contents = "你获得的激活码是{$message}请妥善保存";
            if(isset($info['mobile']) && strlen($info['mobile'])>0){
                $mobile = $info['mobile'];
                //短信发送-获取激活码
//            if (!empty($mobile)) {
//                $post = Curl::post('/utils/message/createMsg', [
//                    'mobile' => $mobile,
//                    'type' => 10,
//                    'param' => ['code' => $message]
////                    'param' => ['code' => $info['data']['code']]
//                ]);
//                \Log::info("发送短信结果", [$post]);
//            }
                $msg = $this->sms->createMsg($mobile, 10, $contents,['code' => $message]);
            }


            return $message;

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

}