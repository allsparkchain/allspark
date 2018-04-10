<?php

namespace App\Goods\Services;

use function App\getCurrentTime;
use App\Goods\Entities\ImgTitleEntity;
use App\Goods\Entities\ProductCategoryEntity;
use App\Goods\Entities\ProductEntity;
use App\Utils\ErrorConst;
use App\Utils\Mutex;
use App\Utils\Pagination;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Psr\Log\LoggerInterface;


class Goods
{
    use EnableDIAnnotations, ThrowResponseParamerTrait;
    /**
     * @inject
     * @var DB
     */
    private $db;

    /**
     * @inject
     * @var LoggerInterface
     */
    public $logger;

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
     * @inject
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;


    /**
     * 获取商品列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getGoodsList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_product");
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
                $joinRule->orderBy('status', DB::ORDER_BY_ASC);
                $joinRule->orderBy('add_time', DB::ORDER_BY_DESC);
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
     * 商品列表包含分成
     * @param $pageszie
     * @param $page
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getGoodsListWithPercent($page, $pageszie)
    {
        try {
            $data = [];
            $joinRule = $this->db->select(
                "*")
                ->from("t_product")
                ->where(['status'=>1])->orderBy('add_time',DB::ORDER_BY_DESC);
            ;
            $pagination = new Pagination($joinRule, $page, $pageszie);
            $data = $pagination->get();
            if($data && $data['count']>0){
                foreach ($data['data'] as $key=>$value){
                    $division_method = $this->db->select("*")->from('t_product_division_method')
                        ->where(['status'=>1,'type'=>1,'product_id'=>$value['id']])->getFirst();
                    $data['data'][$key]['percentmethod'] = json_decode($division_method['percent'],true);
                }
            }

            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
        return [];
    }

    /**
     * 获取品牌列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getBrandList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_brand");
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
                $joinRule->orderBy('status', DB::ORDER_BY_ASC);
                $joinRule->orderBy('add_time', DB::ORDER_BY_DESC);
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
     * 更新商品状态
     * @param $producId
     * @param $status
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function changeStatus($productId, $status)
    {
        try{
            $execResult = $this->db->update('t_product')->set([
                'status' => $status
            ])->where(['id' => $productId])->exec();

            if ($execResult->rows != 1) {
                throw $this->exception([
                    'code'=>ErrorConst::CHANGE_STATUS_ERROR,
                    'text'=>"商品id:".$productId."状态更新失败"
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
     * 商品添加
     * @param ProductEntity $productEntity
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function add(ProductEntity $productEntity)
    {
        try{
            return $this->db->transaction(function(DB $db) use ($productEntity){
                $now = getCurrentTime();
                $procId = $db->insertInto('t_product')->values([
                    'product_name'=>$productEntity->getProductName(),
                    'category_id'=>$productEntity->getCategoryId(),
                    'brand_id'=>$productEntity->getBrandId(),
                    'oriurl'=>"aa",
                    'add_time' =>getCurrentTime()
                ])->exec()->lastInsertId();
                //goods
                $db->insertInto('t_goods')->values([
                    'goods_name'=>$productEntity->getProductName(),
                    'product_id'=>$procId,
                    'selling_price'=>$productEntity->getSellingPrice(),
                    'num'=>$productEntity->getStock(),//    如果传递-1 则numlimit 不限制0 反之填写了数量
                    'numlimit'=> $productEntity->getStock() == -1 ? 0:1,
                    'add_time'=>$now,
                ])->exec();

                $arrs = [];
                $type = 1;
                //product_division_method 4条记录  1，writer写手 2，channel 渠道（改为媒体分成）3，site 网站 4,媒体代理分成
                for ($i=1;$i<5;$i++){
                    if($i==1){//写手
                        $type = 1;
                        $method = [];
                        $writerpercent = ['type'=>1,'contents'=>['percent'=>0]];
                        $writermoney = ['type'=>2,'contents'=>['account'=>0]];
                        $writerCombo = ['type'=>3,'contents'=>['percent'=>0,'account'=>0]];
                        if($productEntity->getWriterpercent() >= 0){
                            $writerpercent = [
                                'type' =>1,
                                'contents' => [
                                    'percent' => $productEntity->getWriterpercent(),
                                ]
                            ];
                        }
                        if($productEntity->getWriterMoney() >= 0){
                            $writermoney = [
                                'type' =>2,
                                'contents' => [
                                    'account' => $productEntity->getWriterMoney(),
                                ]
                            ];

                        }
                        if($productEntity->getWriterCombinepercent() >= 0 && $productEntity->getWriterCombineaccount() >= 0){
                            $writerCombo = [
                                'type' =>3,
                                'contents' => [
                                    'percent' => $productEntity->getWriterCombinepercent(),
                                    'account' => $productEntity->getWriterCombineaccount(),
                                ]
                            ];

                        }
                        $method = [
                            $writerpercent,
                            $writermoney,
                            $writerCombo
                        ];
//                      var_dump(json_encode($method));die;
//                        if($productEntity->getWriterSettleMethod() == 1 ){
//                            if($productEntity->getWriterpercent()>0){
//                                $writerpercent = [
//                                    [
//                                        'type' =>1,
//                                        'contents' => [
//                                            'percent' => $productEntity->getWriterpercent(),
//                                        ]
//                                    ],
//                                    [
//                                        'type' =>2,
//                                        'contents' => [
//                                            'account' => 0
//                                        ]
//                                    ]
//                                ];
//                            }else{
//                                throw $this->exception([
//                                    'code'=>ErrorConst::WRITER_CHOSE_PERCENT_PERCENT_NEEDED,
//                                    'text'=>"选择写手分成则分成百分比必须大于0"
//                                ]);
//                            }
//
//                        }
//                        if($productEntity->getWriterSettleMethod() == 2 ){
//                            if($productEntity->getWriterMoney() > 0){
//                                $writerpercent = [
//                                    [
//                                        'type' =>1,
//                                        'contents' => [
//                                            'percent' => 0,
//                                        ]
//                                    ],
//                                    [
//                                        'type' =>2,
//                                        'contents' => [
//                                            'account' => $productEntity->getWriterMoney()
//                                        ]
//                                    ]
//                                ];
//                            }else{
//                                throw $this->exception([
//                                    'code'=>ErrorConst::WRITER_CHOSE_ACCOUNT_ACCOUNT_NEEDED,
//                                    'text'=>"选择写手固定额度则固定额度必须大于0"
//                                ]);
//                            }
//
//                        }
                        $percent = json_encode($method);
                    }
                    if($i==2){//渠道=》媒体
                        $type = 2;
                        $arr = [
                            [
                                'type' => 1,
                                'contents' => [
                                    'percent' => $productEntity->getChannelpercent()
                                ]
                            ],
                            [
                                'type' =>2,
                                'contents' => [
                                    'account' => 0
                                ]
                            ]

                        ];
                        $percent = json_encode($arr);
                    }
                    if($i==3){//网站
                        $type = 3;
                        $arr = [
                            [
                                'type' => 1,
                                'contents' => [
                                    'percent' => $productEntity->getSitepercent()
                                ]
                            ],
                            [
                                'type' =>2,
                                'contents' => [
                                    'account' => 0
                                ]
                            ]

                        ];
                        $percent = json_encode($arr);
                    }
                    if($i==4){//自媒体推广渠道
                        $type = 4;
                        $arr = [
                            [
                                'type' => 1,
                                'contents' => [
                                    'percent' => $productEntity->getMeiaAgent()
                                ]
                            ],
                            [
                                'type' =>2,
                                'contents' => [
                                    'account' => 0
                                ]
                            ]

                        ];
                        $percent = json_encode($arr);
                    }
                    $arrs[] = [
                        null,
                        $procId,
                        0,
                        $type,
                        $percent,
                        1,
                        $now,
                        date('Y-m-d H:i:s',$now)
                    ];
                }
                if ($arrs) {
                    $onDuplicateKeyUpdateRule = $db->insertInto("t_product_division_method")->batchValues($arrs);
                    $onDuplicateKeyUpdateRule->exec();
                }

                //product_info
                $db->insertInto('t_product_info')->values([
                    'product_id'=>$procId,
                    'contents'=>$productEntity->getProductInfo(),
                    'add_time'=>$now
                ])->exec();

                //product_source
                $db->insertInto('t_product_source')->values([
                    'product_source_type'=>1,
                    'source_id'=>1,
                    'product_id'=>$procId,
                    'add_time'=>$now
                ])->exec();

                //product_source
                $db->insertInto('t_product_img')->values([
                    'img_path' => $productEntity->getImgPath(),
                    'type' =>1,
                    'product_id'=>$procId,
                ])->exec();
                $advert_uid = 0;
                if($productEntity->getAdvertId()){
                    $advert_uid = $productEntity->getAdvertId();
                }
                //t_advert_product_relate
                $db->insertInto('t_advert_product_relate')->values([
                    'advert_uid' => $advert_uid,
                    'add_time'=>$now,
                    'product_id'=>$procId,
                ])->exec();

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
     * 获取单个商品详情
     * @param $productId
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getProduct($productId)
    {

        $productInfo = "t_product_info.contents";
        $goods = "t_goods.selling_price,t_goods.num";
        $product_region = "IFNULL(t_product_region.region_id,-1) as region_id";
        $proc_img = "t_product_img.img_path";
        $advert_id = 't_advert_product_relate.advert_uid';
        $nextWhereRule = $this->db->select('t_product.*',
            DB::raw($productInfo),
            DB::raw($goods),
            DB::raw($product_region),
            DB::raw($advert_id),
            DB::raw($proc_img))
            ->from('t_product')
            ->innerJoin('t_product_info')->on('t_product.id=t_product_info.product_id')
            ->innerJoin('t_goods')->on('t_product.id=t_goods.product_id')
            ->leftJoin('t_product_region')->on('t_product.id=t_product_region.product_id')
            ->leftJoin('t_product_img')->on('t_product.id=t_product_img.product_id')
            ->leftJoin('t_advert_product_relate')->on('t_advert_product_relate.product_id=t_product.id')

            ->where(['t_product.id' => $productId]);
        $data = $nextWhereRule->getFirst();
        if($data){
            $division_method = $this->db->select("*")->from('t_product_division_method')->where(['product_id'=>$data['id']])->get();
            $data['product_division_methods'] = $division_method;
        }
        return $data;
    }

    /**
     * 商品编辑
     * @param ProductEntity $productEntity
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function editProduct(ProductEntity $productEntity){
        try{
            return $this->db->transaction(function(DB $db) use ($productEntity) {
                $db->update('t_product')->set([
                    'product_name' => $productEntity->getProductName(),
                    'category_id' => $productEntity->getCategoryId(),
                    'brand_id' => $productEntity->getBrandId(),
                    'oriurl' => "aa",
                ])->where(['id' => $productEntity->getId()])->exec();

                //goods
                $db->update('t_goods')->set([
                    'goods_name' => $productEntity->getProductName(),
                    'selling_price' => $productEntity->getSellingPrice(),
                    'num' => $productEntity->getStock(),
                ])->where(['product_id' => $productEntity->getId()])->exec();

                //prodcut_division
//                $db->update('t_product_division')->set([
//                    'totalpercent'=>$productEntity->getWriterpercent() + $productEntity->getSitepercent() + $productEntity->getChannelpercent(),
//                    'writerpercent'=>$productEntity->getWriterpercent(),
//                    'sitepercent'=>$productEntity->getSitepercent(),
//                    'channelpercent'=>$productEntity->getChannelpercent(),
//                ])->where(['product_id'=>$productEntity->getId()])->exec();

                //product_img
                if (strlen($productEntity->getImgPath()) > 0) {
                    $db->update('t_product_img')->set([
                        'img_path' => $productEntity->getImgPath(),
                    ])->where(['product_id' => $productEntity->getId()])->exec();
                }

                //product_info
                $db->update('t_product_info')->set([
                    'contents' => $productEntity->getProductInfo(),
                ])->where(['product_id' => $productEntity->getId()])->exec();

                //product_source
                $db->update('t_product_source')->set([
                    'product_source_type' => 1,
                    'source_id' => 1,
                ])->where(['product_id' => $productEntity->getId()])->exec();

                $advert_uid = 0;
                if ($productEntity->getAdvertId()) {
                    $advert_uid = $productEntity->getAdvertId();
                }

                $advert = $db->select('*')->from('t_advert_product_relate')->where(['product_id' => $productEntity->getId()])->getFirst();
                //t_advert_product_relate
                if ($advert) {
                    $db->update('t_advert_product_relate')->set([
                        'advert_uid' => $advert_uid,
                    ])->where(['product_id' => $productEntity->getId()])->exec();
                }else{
                    $db->insertInto('t_advert_product_relate')->values([
                        'advert_uid' => $advert_uid,
                        'add_time'=>getCurrentTime(),
                        'product_id'=>$productEntity->getId(),
                    ])->exec();
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
     * 根据用户id获得其所有的订单列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getProcOrderListByUid($uid, $page, $pageszie,  $where = [], $order = [])
    {
        try {

            $joinRule = $this->db->select(
                "t_order.*",
                DB::raw("t_product_order.contents,t_product_order.number"),
                DB::raw("t_article.name as ArticleName,t_article.summary")
            )
                ->from("t_order")
                ->innerJoin('t_product_order')->on('t_order.id = t_product_order.order_id')
                ->innerJoin('t_article')->on('t_article.id = t_product_order.article_id')
            ;

            if ($where) {
                $joinRule->where($where);
            }
//            var_dump($joinRule->context->sql);die;
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $joinRule->orderBy($key, $value);
                    }
                }
            } else {
                $joinRule->orderBy('t_order.add_time', DB::ORDER_BY_DESC);
            }
            $pagination = new Pagination($joinRule, $page, $pageszie);
            $data = $pagination->get();

            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
        return [];
    }

    /**
     * 根据用户id获得其所有的订单列表
     * @param array $where
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getProcOrderDetailByOid($where = [])
    {
        try {

            $joinRule = $this->db->select(
                "t_order.*",
                DB::raw("t_product_order.contents,t_product_order.number")
            )
                ->from("t_order")
                ->innerJoin('t_product_order')->on('t_order.id = t_product_order.order_id')
               ;

            if ($where) {
                $joinRule->where($where);
            }
            $data = $joinRule->getFirst();

            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
        return [];
    }

     /**
     * 购买商品
     * @param int $id
     * @return bool
     * @throws \App\Exceptions\RuntimeException
     */
    public function buyGoods($spread_id, $uid, $number, $address_id, $mode) {

        try{
            $spreadList = $this->db->select('t_spread_list.channel_user_id,t_article_product_relate.article_id')->from('t_spread_list')
                ->leftJoin('t_article_product_relate')->on('t_spread_list.article_product_id=t_article_product_relate.id')
                ->where(['t_spread_list.id'=>$spread_id])->getFirst();
            $articleId = $spreadList['article_id'];
            $countingId = $spreadList['channel_user_id'];

            //查询商品是否存在
            $goodsRule = $this->db->select('t_article_product_relate.id',DB::raw("t_goods.goods_name,t_goods.product_id,t_goods.selling_price,t_goods.num,t_goods.numlimit,t_product.product_name,t_product_img.img_path"))
                ->from("t_article_product_relate")
                ->leftJoin('t_goods')->on('t_goods.product_id=t_article_product_relate.product_id')
                ->leftJoin('t_product')->on('t_product.id=t_article_product_relate.product_id')
                ->leftJoin('t_product_img')->on('t_product_img.product_id=t_article_product_relate.product_id')
                ->where([
                    't_article_product_relate.article_id'=>  $articleId
                ])->getFirst();

            if(!$goodsRule['product_id']){
                throw $this->exception([
                    'code'=>ErrorConst::PRODUCT_NOT_EXIST,
                    'text'=>"商品不存在".json_encode([$uid, $articleId, $number])
                ]);
            }

            return $this->mutex->getMutex('buyGoods'.$goodsRule['product_id'])->synchronized(function() use($articleId, $uid, $number, $address_id, $countingId, $goodsRule, $mode, $spread_id){
                //查询未支付成功订单数
                $count = $this->db->select(DB::raw("sum(t_product_order.number) as count"))->from('t_product_order')
                    ->leftJoin('t_order')->on('t_order.id=t_product_order.order_id')
                    ->where([
                        't_product_order.product_id' => $goodsRule['product_id'],
                        't_order.status' => 2,
                        't_product_order.failure_time' => ['>'=>getCurrentTime()],
                    ])->getFirst();
                //判断库存

                if($goodsRule['num'] !='-1'){
                    if( bcsub($goodsRule['num'],$count['count']) < $number){
                        throw $this->exception([
                            'code'=>ErrorConst::INSUFFICIENT_STOCK,
                            'text'=>"商品库存不足".json_encode([$uid, $articleId, $number])
                        ]);
                    }
                }
                //取地址信息
                $userAddress = $this->db->select('t_user_address.mobile,t_user_address.realname,t_user_address.address')->from('t_user_address')
                    ->where(['id' => $address_id])
                    ->getFirst();

                //存t_order表
                $lastInsertId = $this->db->insertInto('t_order')->values(
                    [
                        'uid' => $uid,
                        'order_number'=> $this->getOrderNumber(),
                        'status' => 2,
                        'type' => 2,
                        'account' => bcmul($number, $goodsRule['selling_price'],4),
                        'add_time' => getCurrentTime(),

                    ]
                )->exec()->lastInsertId();
                $userContent = array();
                $userContent['userAddress'] = $userAddress;
                $userContent['goods']  = $goodsRule;
                //存t_product_order
                $productOrderId = $this->db->insertInto('t_product_order')->values(
                    [
                        'product_id' => $goodsRule['product_id'],
                        'order_id' => $lastInsertId,
                        'number' => $number,
                        'article_id' => $articleId,
                        'counting_id' => $countingId,
                        'spread_id' => $spread_id,
                        'contents' => json_encode($userContent),
                        'failure_time' => bcadd(getCurrentTime(), $this->container->get('failure_time'), 2),
                        'add_time' => getCurrentTime(),
                    ]
                )->exec()->lastInsertId();

                //生成支付订单order
                $pOrderId = $this->db->insertInto('t_order')->values(
                    [
                        'uid' => $uid,
                        'order_number' => $this->getOrderNumber(),
                        'status' => 2,
                        'type' => 3,
                        'account' => bcmul($number, $goodsRule['selling_price'],4),
                        'add_time' => getCurrentTime(),

                    ]
                )->exec()->lastInsertId();
                //生成支付订单
                $payData = [
                    'uid' => $uid,
                    'order_number'=> $this->getOrderNumber('PAY'),
                    'mode' => $mode,
                    'account' => bcmul($number, $goodsRule['selling_price'],4),
                    'order_id'=>$pOrderId,
                    'product_order_id' => $productOrderId,
                    'token'=>'abcd1234',
                    'add_time' => getCurrentTime(),

                ];
                $this->db->insertInto('t_pay_order')->values(
                    $payData
                )->exec();

                //调取支付接口
                //存支付日志
                $this->db->insertInto('t_pay_log')->values(
                    [
                        'uid' => $uid,
                        'pay_order_number'=> $payData['order_number'],
                        'status' => 1,
                        'response_content'=> json_encode($payData),
                        'add_time' => getCurrentTime(),
                    ]
                )->exec();

                return ['pay_order_number'=>$payData['order_number'],
                    'account'=>$payData['account'],
                    'goods_name'=>$goodsRule['goods_name']];
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 购买商品
     * @param string $pay_order_number
     * @return bool
     * @throws \App\Exceptions\RuntimeException
     */
    public function buyReturn($pay_order_number, $return_code, $message) {
        try{

            return $this->mutex->getMutex('buyReturn'.$pay_order_number)->synchronized(function() use($pay_order_number, $return_code, $message) {
                $payOrder = $this->db->select('*')->from('t_pay_order')->where(['order_number' => $pay_order_number])->getFirst();
                $productOrder = $this->db->select('*')->from('t_product_order')->where(['id' => $payOrder['product_order_id']])->getFirst();

                if ($return_code == 'SUCCESS') {

                    //成功 更新支付订单回调日志
                    $execResult = $this->db->update('t_pay_log')->set([
                        'status' => 2,
                        'request_content' => $message
                    ])->where(['pay_order_number' => $pay_order_number, 'status' => 1])->exec()->rows;

                    if ($execResult < 1) {
                        throw $this->exception([
                            'code' => ErrorConst::ORDER_CHACK_ERROR,
                            'text' => "订单更新失败" . json_encode([$pay_order_number])
                        ]);
                    }

                    //更新支付订单状态
                    $execResult = $this->db->update('t_order')->set([
                        'status' => 3
                    ])->where(['id' => $payOrder['order_id'], 'status' => 2])->exec()->rows;
                    if ($execResult < 1) {
                        throw $this->exception([
                            'code' => ErrorConst::ORDER_CHACK_ERROR,
                            'text' => "订单更新失败" . json_encode([$pay_order_number])
                        ]);
                    }
                    //更新商品订单状态
                    $execResult = $this->db->update('t_order')->set([
                        'status' => 3
                    ])->where(['id' => $productOrder['order_id'], 'status' => 2])->exec()->rows;
                    if ($execResult < 1) {
                        throw $this->exception([
                            'code' => ErrorConst::ORDER_CHACK_ERROR,
                            'text' => "订单更新失败" . json_encode([$pay_order_number])
                        ]);
                    }

                    //佣金分成
                    $this->userCommission($productOrder['id']);

                } else {
                    //失败 更新支付订单回调日志
                    $execResult = $this->db->update('t_pay_log')->set([
                        'status' => 3,
                        'request_content' => $message
                    ])->where(['pay_order_number' => $pay_order_number, 'status' => 1])->exec()->rows;
                    if ($execResult < 1) {
                        throw $this->exception([
                            'code' => ErrorConst::ORDER_CHACK_ERROR,
                            'text' => "订单更新失败" . json_encode([$pay_order_number])
                        ]);
                    }

                    //更新支付订单状态
                    $execResult = $this->db->update('t_order')->set([
                        'status' => 4
                    ])->where(['id' => $payOrder['order_id'], 'status' => 2])->exec()->rows;
                    if ($execResult < 1) {
                        throw $this->exception([
                            'code' => ErrorConst::ORDER_CHACK_ERROR,
                            'text' => "订单更新失败" . json_encode([$pay_order_number])
                        ]);
                    }

                    //更新商品订单状态
                    $execResult = $this->db->update('t_order')->set([
                        'status' => 4
                    ])->where(['id' => $productOrder['order_id'], 'status' => 2])->exec()->rows;
                    if ($execResult < 1) {
                        throw $this->exception([
                            'code' => ErrorConst::ORDER_CHACK_ERROR,
                            'text' => "订单更新失败" . json_encode([$pay_order_number])
                        ]);
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
     * 结算脚本
     * @return bool
     */
    public function settlementScript(){

        $start_time = strtotime(date('Y-m-d 00:00:00'));
        $end_time   = strtotime(date('Y-m-d 23:59:59'));
        $arr = $this->db->select('*')->from('t_user_commission')
            ->where([
                'status' => 1,
                'unlock_time' => ['>=' => DB::raw($start_time.' and  unlock_time<='.$end_time)]
            ])->get();

        foreach($arr as $key =>$value){

            $this->db->update('t_user_commission')->set([
                'status' => 2,
            ])->where([ 'id' => $value['id'] ])->exec();
            //t_user_account表  未结算佣金+  本月未结算+
            $this->db->update('t_user_account')->set([
                'available_amount' => DB::raw('available_amount + ' . $value['account']),
                'unsettled_amount' => DB::raw('unsettled_amount - ' . $value['account'])
            ])->where([ 'uid' => $value['uid'] ])->exec();

            if($value['add_time']>=strtotime(date('Y-m-01'))){ //当前时间大于本月，扣本月未结算金额
                $this->db->update('t_user_account')->set([
                    'unsettled_amount_month' => DB::raw('unsettled_amount_month - ' . $value['account'])
                ])->where([ 'uid' => $value['uid'] ])->exec();
            }else{ //否则扣上月未结算金额
                $this->db->update('t_user_account')->set([
                    'non_settlement_month' => DB::raw('non_settlement_month - ' . $value['account'])
                ])->where([ 'uid' => $value['uid'] ])->exec();
            }

            $this->db->update('t_order')->set([
                'status' => 3
            ])->where(['id' => $value['order_id'],'status'=>2])->exec();

            $userAccount = $this->db->select('*')->from('t_user_account')->where(['uid' => $value['uid']])->getFirst();
            $this->db->insertInto('t_user_commission_record')->values(
                [
                    'uid' => $value['uid'],
                    'order_id' => $value['order_id'],
                    'type' => 2,
                    'account' => $value['account'],
                    'available_amount'=>$userAccount['available_amount'],
                    'add_time' => getCurrentTime(),
                ]
            )->exec();

        }
        return true;
    }

    /**
     * 月初未结算清零脚本
     * @return bool
     */
    public function settledMonthScript(){

        $userAccountList = $this->db->select('*')->from('t_user_account')
            ->where([
                'unsettled_amount_month' => [ '>'=>0 ],
            ])->get();

        foreach ($userAccountList as $value){
            $this->db->update('t_user_account')->set([
                'non_settlement_month' => $value['unsettled_amount_month'],
                'unsettled_amount_month' => 0 ,
            ])->where([ 'uid' => $value['uid'] ])->exec();
        }

        return true;
    }

    /**
     * 佣金分成
     */
    private function userCommission($product_order_id){

        $productOrder = $this->db->select('t_product_order.*,t_order.account,t_article.author,t_spread_list.article_product_id,t_article_product_relate.percent')->from('t_product_order')
            ->leftJoin('t_order')->on('t_product_order.order_id=t_order.id')
            ->leftJoin('t_article')->on('t_product_order.article_id=t_article.id')
            ->leftJoin('t_spread_list')->on('t_spread_list.id=t_product_order.spread_id')
            ->leftJoin('t_article_product_relate')->on('t_spread_list.article_product_id=t_article_product_relate.id')
            ->where(['t_product_order.id' => $product_order_id])->getFirst();
        //获得分成比例
        $method = json_decode($productOrder['percent'],true);

        foreach ($method as $key =>$value){

            if($value['mode'] == 1){//写手
                $uid = $productOrder['author'];
            }elseif ($value['mode'] == 2){//媒体
                $uid = $productOrder['counting_id'];
            }elseif ($value['mode'] == 3){//平台
                $uid = 1;
            }elseif ($value['mode'] == 4){//媒体代理
                //查询媒体的推荐人
                $inviteUid = $this->db->select('*')->from('t_user_invite_relate')
                    ->where(['t_user_invite_relate.uid' => $productOrder['counting_id']])->getFirst();
                $uid = $inviteUid?$inviteUid['invite_uid']:0;
            }

            if($uid) {
                $percent_num = 0;
                if (!empty($value['contents']['percent'])) {
                    $percent_num = $value['contents']['percent'];
                }

                $account = bcdiv(bcmul($productOrder['account'], $percent_num, 4), 100, 4);

                //生成支付订单order
                $orderId = $this->db->insertInto('t_order')->values(
                    [
                        'uid' => $uid,
                        'order_number' => $this->getOrderNumber(),
                        'status' => 2,
                        'type' => 4,
                        'account' => $account,
                        'add_time' => getCurrentTime(),

                    ]
                )->exec()->lastInsertId();

                $this->db->insertInto('t_order_funds')->values(
                    [
                        'order_id' => $orderId,
                        'contents' => json_encode(array_merge($productOrder, $value)),
                        'add_time' => getCurrentTime(),

                    ]
                )->exec();

                //最后生成t_user_commission  (`order_id`, `product_order_id`, `type`, `product_account`, `percent`, `account`, `after_account`, `status`, `unlock_time`, `add_time`)
                $sum_account = $this->db->select(DB::raw('sum(account) as account'))->from('t_user_commission')->where(['uid' => $uid])->getFirst();

                $sum_account = $sum_account['account'] ? $sum_account['account'] : 0;


                //结算时间
                $settlementday = $this->container->get('settlementday');
                $unlock_time = strtotime(date('Y-m-15', strtotime('+1 month')));
                if (!$settlementday) {
                    $unlock_time = getCurrentTime();
                }


                $this->db->insertInto('t_user_commission')->values(
                    [
                        'uid' => $uid,
                        'order_id' => $orderId,
                        'product_order_id' => $productOrder['id'],
                        'type' => $value['mode'],
                        'product_account' => $productOrder['account'],
                        'percent' => $percent_num,
                        'account' => $account,
                        'after_account' => bcadd($sum_account, $account),
                        'status' => 1,
                        'unlock_time' => $unlock_time,
                        'add_time' => getCurrentTime(),
                    ]
                )->exec();

                if ($value['mode'] == 1) {//写手
                    $this->db->update('t_article_product_relate')->set([
                        'number' => DB::raw('number + ' . $productOrder['number']),
                        'commission_account' => DB::raw('commission_account + ' . $account)
                    ])->where(['id' => $productOrder['article_product_id']])->exec();
                } elseif ($value['mode'] == 2) {//渠道
                    $this->db->update('t_spread_list')->set([
                        'number' => DB::raw('number + ' . $productOrder['number']),
                        'commission_account' => DB::raw('commission_account + ' . $account)
                    ])->where(['id' => $productOrder['spread_id']])->exec();
                }

                //t_user_account表  未结算佣金+  本月未结算+
                $this->db->update('t_user_account')->set([
                    'unsettled_amount' => DB::raw('unsettled_amount + ' . $account),
                    'unsettled_amount_month' => DB::raw('unsettled_amount_month + ' . $account)
                ])->where(['uid' => $uid])->exec();
            }

        }

    }

    /////

    /**
     * 获取产品分类
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getProductCategoryList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_category");
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
        return [];
    }

    /**
     * 更新文章分类状态
     * @param $cid
     * @param $status
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function categoryChangeStatus($cid, $status)
    {
        try{
            $execResult = $this->db->update('t_category')->set([
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
     * 分类添加
     * @param ProductCategoryEntity $articleCategoryEntity
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function categoryAdd(ProductCategoryEntity $productCategoryEntity)
    {
        try{
            return $this->db->transaction(function(DB $db) use ($productCategoryEntity){
                $db->insertInto('t_category')->values([
                    'category_name' => $productCategoryEntity->getCategoryName(),
                    'parent' => $productCategoryEntity->getParent(),
                    'status' => $productCategoryEntity->getStatus(),
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
            ->from('t_category')
            ->where(['id' => $id]);
        $data = $nextWhereRule->getFirst();
        return $data;
    }

    /**
     * 分类编辑
     * @param ProductCategoryEntity $productCategoryEntity
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function editCategory(ProductCategoryEntity $productCategoryEntity){
        try{
            return $this->db->transaction(function(DB $db) use ($productCategoryEntity){
                $db->update('t_category')->set([
                    'category_name' => $productCategoryEntity->getCategoryName(),
                    'parent' => $productCategoryEntity->getParent(),
                ])->where(['id'=>$productCategoryEntity->getId()])->exec();
                return true;
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    ////
    private function getOrderNumber($type = 'ORD') {
        return $type.time().rand(10000, 99999);
    }
    
}