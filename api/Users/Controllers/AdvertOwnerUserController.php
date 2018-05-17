<?php

namespace App\Users\Controllers;

use App\Users\Entities\AdvertOwnerEntity;
use App\Users\Services\AdvertOwner;
use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Symfony\Component\HttpFoundation\Request;

/**
 * @path /advertOwner
 */
class AdvertOwnerUserController{
    use EnableDIAnnotations,HttpResponseTrait,ThrowResponseParamerTrait;
    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Message
     */

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
     * @var Request
     */
    public $request;

    /**
     * @inject
     * @var AdvertOwner
     */
    public $advertOwner;


    /**
     * 注册
     *
     * @route POST /register
     * @param AdvertOwnerEntity $entity {@bind request.request}
     *
     * @return array
     */
    public function register(AdvertOwnerEntity $entity) {
        try {

            $rs = $this->advertOwner->register($entity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 手动注册
     * @route POST /autoRegister
     * @param AdvertOwnerEntity $entity {@bind request.request}
     *
     * @return array
     */
    public function autoRegister(AdvertOwnerEntity $entity) {
        try {
            $rs = $this->advertOwner->autoRegister($entity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 后台添加用户
     *
     * @route POST /updateAdvertUser
     * @param int $uid
     * @param string $realname
     * @return array
     */
    public function updateAdvertUser($uid, $realname) {
        try {
            $rs = $this->advertOwner->updateAdvertUser($uid, $realname);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 后台添加用户
     *
     * @route POST /getAdvertUser
     * @param int $uid
     * @return array
     */
    public function getAdvertUser($uid) {
        try {
            $rs = $this->advertOwner->getAdvertUser($uid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }


    /**
     * 用户登录
     * @route POST /login
     * @param string $mobile
     * @param string $passwd
     * @return array
     */
    public function login($mobile, $passwd) {
        try {
            $rs = $this->advertOwner->login($mobile, $passwd);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 账户总览
     * @route POST /getAccountInfo
     * @param string $uid
     * @param int $type
     * @param int $showBy
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getAccountInfo($uid, $type = 1, $showBy = 1, $page=1, $pagesize=10) {
        try {
            $rs = $this->advertOwner->getAccountInfo($uid,$type,$showBy,$page,$pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 商品列表 商品数据页面1
     * @route POST /getAdvertRelativeProList
     * @param string $advert_relative_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @param string $name
     * @param string $order
     * @return array
     */
    public function getAdvertRelativeProList($advert_relative_id, $starttime = 0, $endtime = 0, $page = 1, $pagesize =10 , $name = '', $order = '') {
        try {
            $rs = $this->advertOwner->getAdvertRelativeProList($advert_relative_id, $starttime, $endtime, $page, $pagesize , $name, $order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 商品的文章列表 商品数据页面2
     * @route POST /getAdvertRelativeProDetail
     * @param string $advert_relative_id
     * @param int $pro_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @param string $name
     * @param string $order
     * @return array
     */
    public function getAdvertRelativeProDetail($advert_relative_id, $pro_id, $starttime = 0, $endtime = 0, $page = 1, $pagesize =10 , $name = '', $order = '') {
        try {
            $rs = $this->advertOwner->getAdvertRelativeProDetail($advert_relative_id,$pro_id, $starttime, $endtime, $page, $pagesize , $name, $order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 商品的分享的文章的产品 数据详情 商品数据页面3
     * @route POST /getAdvertRelativeProOrderDetail
     * @param string $advert_relative_id
     * @param int $spread_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @param string $name
     * @param string $order
     * @return array
     */
    public function getAdvertRelativeProOrderDetail($advert_relative_id, $spread_id, $starttime = 0, $endtime = 0, $page = 1, $pagesize =10 , $name = '', $order = '') {
        try {
            $rs = $this->advertOwner->getAdvertRelativeProOrderDetail($advert_relative_id,$spread_id, $starttime, $endtime, $page, $pagesize , $name, $order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 检测手机号是否已经存在
     * @route POST /checkMobile
     * @param string $mobile
     * @return array
     */
    public function checkMobile($mobile) {
        try {
            $rs = $this->advertOwner->checkMobile($mobile);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 结算明细页面
     * @route POST /settledDetailFlow
     * @param string $advert_relative_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @param string $name
     * @param string $order
     * @return array
     */
    public function settledDetailFlow($advert_relative_id, $starttime = 0, $endtime = 0, $page = 1, $pagesize =10 , $name = '', $order = '') {
        try {
            $rs = $this->advertOwner->settledDetailFlow($advert_relative_id, $starttime, $endtime, $page, $pagesize , $name, $order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 广告主订单查询
     * @route POST /getAdvertRelativeOrderList
     * @param string $advert_relative_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @param string $procname
     * @param int $status
     * @param string $order
     * @return array
     */
    public function getAdvertRelativeOrderList($advert_relative_id, $starttime=0, $endtime=0, $page=1, $pagesize=10, $procname='', $status=0, $order='') {
        try {
            $rs = $this->advertOwner->getAdvertRelativeOrderList($advert_relative_id, $starttime, $endtime, $page, $pagesize, $procname,$status, $order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 广告主订单 状态变更
     * @route POST /changeAdvertRelativeOrderStatus
     * @param int $advert_relative_id
     * @param int $product_order_id
     * @param string $product_order_id_arr
     * @param string $status
     * @return array
     */
    public function changeAdvertRelativeOrderStatus($advert_relative_id,$product_order_id = 0, $product_order_id_arr = '', $status = 0) {
        try {
            $rs = $this->advertOwner->changeAdvertRelativeOrderStatus($advert_relative_id,$product_order_id,$product_order_id_arr, $status);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 广告主曝光数据页面
     * @route POST /getAdvertRelativeExposeList
     * @param string $advert_relative_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @param string $procname
     * @param int $status
     * @param string $order
     * @return array
     */
    public function getAdvertRelativeExposeList($advert_relative_id, $starttime=0, $endtime=0, $page=1, $pagesize=10, $procname='', $status=0, $order='') {
        try {
            $rs = $this->advertOwner->getAdvertRelativeExposeList($advert_relative_id, $starttime, $endtime, $page, $pagesize, $procname,$status, $order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 广告主showCPA 状态变更
     * @route POST /changeAdvertShowCPA
     * @param int $id
     * @param string $status
     * @return array
     */
    public function changeAdvertShowCPA($id ,$status = 0) {
        try {
            $rs = $this->advertOwner->changeAdvertShowCPA($id, $status);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * admin 获得广告主下的数据 包含cpa
     * @route POST /getOwnerProListWithCPA
     * @param string $advert_relative_id
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getOwnerProListWithCPA($advert_relative_id, $product_type = 0, $page = 1, $pagesize =10 ) {
        try {
            $rs = $this->advertOwner->getOwnerProListWithCPA($advert_relative_id, $product_type, $page, $pagesize );
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * admin 获得广告主下的数据 包含cpa
     * @route POST /getOwnerCPAOrderList
     * @param string $advert_relative_id
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getOwnerCPAOrderList($advert_relative_id, $page = 1, $pagesize =10 ) {
        try {
            $rs = $this->advertOwner->getOwnerCPAOrderList($advert_relative_id, $page, $pagesize );
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 广告主下 结算记录列表
     * @route POST /getOwnerSettlement
     * @param string $advert_relative_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getOwnerSettlement($advert_relative_id, $starttime = 0, $endtime = 0, $page = 1, $pagesize =10 ) {
        try {
            $rs = $this->advertOwner->getOwnerSettlement($advert_relative_id, $starttime, $endtime, $page, $pagesize );
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 广告主下 收入记录列表
     * @route POST /getOwnerIncomeFlow
     * @param string $advert_relative_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getOwnerIncomeFlow($advert_relative_id, $starttime, $endtime, $page = 1, $pagesize =10 ) {
        try {
            $rs = $this->advertOwner->getOwnerIncomeFlow($advert_relative_id, $starttime, $endtime, $page, $pagesize );
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 商品分享页面
     * @Get("/articleInfo", as="s_article_articleInfo")
     */
    public function articleInfo(Request $request) {

        $spreadid = $request->get('spreadid');
        if($spreadid){
            $nid = $request->get('nid');
            try{
                $info = Curl::post('/user/getInfoBySpreadId',['spreadid'=>$spreadid])['data'];
                $article_id = $info['article_id'];

                if($info['order_no'] != $nid) {
                    $spreadid = '';
                }
            }catch (ApiException $e){
                return redirect(route('s_order_orderHistoryList'));
                //var_dump('没有找推广id');die;
            }
            $url = config('params.app_host').'User/productDetail?spreadid='.$spreadid .'&nid='.$nid;


        }else{
            $article_id = $request->get('article_id');
            $url = '';
        }
        try{
            $info = Curl::post('/article/getArticleDetailForWx',['article_id'=>$article_id])['data'];
//            var_dump($info);
            if($spreadid && $info['product_type'] == 3){
                $info['landing_page'];
                if(stristr($info['landing_page'],'?')){
                    $url = $info['landing_page'].'&spreadid='.$spreadid.'&productid='.$info['productId'].'&articleid='.$article_id;
                }else{
                    $url = $info['landing_page'].'?spreadid='.$spreadid.'&productid='.$info['productId'].'&articleid='.$article_id;
                }

            }


        }catch (ApiException $e){
            return redirect(route('s_order_orderHistoryList'));
            //var_dump('没有找到该成品');die;
        }
        $img_path = '';
        $img = Curl::post('/article/getArticleImgList',['article_id'=>$article_id,'pagesize'=>10]);
        foreach ($img['data']['data'] as $key => $val){
            $fileType = getFileType($val['img_path']);
            if($fileType != 'gif'){
                $img_path = $val['img_path'];
            }

        }


        $app = app('wechat.official_account');

        //print_r($info['article_img_path']);exit;

        return view("Article.articleInfo")->with('info',$info)->with('url',$url)->with('spreadid', $spreadid)->with('app', $app)->with('img_path',$img_path);
    }

    /**
     * 商品分享页面
     * @Get("/success", as="s_article_success")
     */
    public function success(Request $request) {
        $app = app('wechat.official_account');
        return view("Auth.success")->with('app', $app);

    }


}