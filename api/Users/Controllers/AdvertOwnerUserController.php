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


    /**
     * @Get("/specifications_edit", as="s_goods_specifications_edit")
     * @Post("/specifications_edit", as="s_goods_specifications_edit")
     */
    public function specifications_edit(Request $request) {


        $data = Curl::post('/product/getGoodsSpecifications',
            ['goods_id'=>$request->get('id', '')]
        );


        $product = Curl::post('/product/getProduct',
            ['product_id'=>$data['data']['product_id']]
        );


        if($product['data']['specifications']){
            $specifications = json_decode($product['data']['specifications'],true);
        }else{
            $specifications = '';
        }

        if($data['data']['specifications']){
            $specifications2 = json_decode($data['data']['specifications'],true);
        }else{
            $specifications2 = '';
        }


        return view("Goods.specifications_edit")->with('data',$data['data'])->with('specifications', $specifications)->with('specifications2',$specifications2);
    }

    /**
     * @Get("/specifications_add", as="s_goods_specifications_add")
     * @Post("/specifications_add", as="s_goods_specifications_add")
     */
    public function specifications_add(Request $request) {


        $data = Curl::post('/product/getProduct',
            ['product_id'=>$request->get('product_id', '')]
        );

        if($data['data']['specifications']){
            $specifications = json_decode($data['data']['specifications'],true);
        }else{
            $specifications = '';
        }


        return view("Goods.specifications_add")->with('specifications', $specifications)->with('product_id',$request->get('product_id', ''));
    }

    /**
     * @Get("/specifications_del", as="s_goods_specifications_del")
     * @Post("/specifications_del", as="s_goods_specifications_del")
     */
    public function specifications_del(Request $request) {



        $data = Curl::post('/product/delSpecifications',
            [
                'goods_id' => $request->get('id', ''),
            ]
        );
        return new JsonResponse($data);

    }



    /**
     * @Get("/specifications_save", as="s_specifications_save")
     * @Post("/specifications_save", as="s_specifications_save")
     */
    public function specifications_save(Request $request) {


        $validator = \Validator::make($request->all(), [
            'specifications_key' => 'required',
            'selling_price' => 'required|Numeric|min:1',
        ]);



        $paramer = $request->all();

        if ($validator->fails()) {
            return redirect(route('s_goods_specifications_add',['product_id'=>$paramer['product_id']]))
                ->withErrors($validator)
                ->withInput();
        }

        $array = [];
        $is_null = 0;
        foreach ($paramer['specifications'] as $key => $val){
            if($paramer['specifications_key'][$key]){
                $is_null++;
                $array[$val] = $paramer['specifications_key'][$key];
            }
        }

        if($is_null==0){
            return back()->withErrors('至少选择1个规格属性');
        }
        if(isset($paramer['goods_id'])){
            $data = Curl::post('/product/editSpecifications',
                [
                    'goods_id' => $paramer['goods_id'],
                    'product_id' => $paramer['product_id'],
                    'num'   => $paramer['stock'],
                    'specifications'   => $array?json_encode($array):'',
                    'selling_price'=>$paramer['selling_price'],
                ]
            );
        }else{
            $data = Curl::post('/product/addSpecifications',
                [   'product_id' => $paramer['product_id'],
                    'num'   => $paramer['stock'],
                    'specifications'   => $array?json_encode($array):'',
                    'selling_price'=>$paramer['selling_price'],
                ]
            );
        }


        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            return redirect(route('s_goods_specificationslists',['product_id'=>$paramer['product_id']]));
        }


    }



    /**
     *
     * @Post("/changeStatus", as="s_goods_change_status")
     */
    public function changeStatus(Request $request) {

        if ($request->ajax()) {
            $data = Curl::post('/product/changeStatus',
                [
                    'product_id' => $request->get('product_id', 0),
                    'status'=>$request->get('status', 1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }


    /**
     * @Get("/add", as="s_goods_add")
     *
     */
    public function add(Request $request){
//        $categorylist = Curl::post('/productCategory/getProductCategoryList',['status'=>1]);
        $brandlist = Curl::post('/product/brandList');
//      $regionlist = Curl::post('/product/regionList',['parent_id'=>1]);
//        $categorylist = Curl::post('/industry/getIndustryUserList');
//        $categorylist = Curl::post('/industry/getLists',['status'=>1,'type'=>1]);
        $categorylist = Curl::post('/industryCategory/getLists',['status'=>1,'type'=>1]);
        $advertList = Curl::post('/advert/advertRelativeList');

//var_dump($advertList['data']);die;
        $product_type = $request->get('product_type', 1);

        return view("Goods.add")
            ->with('categorylist',$categorylist['data']['data'])
            ->with('brandlist',$brandlist['data']['data'])
            ->with('product_type',$product_type)
            ->with('advertList',$advertList['data']);
    }


    /**
     * @Post("/auth/logintest", as="s_auth_login")
     */
    public function logintest(Request $request)
    {
        if($request->ajax()){
            try {
                $result = parent::login($request);
                if($result instanceof JsonResponse){
                    return new JsonResponse(['msg'=>'用户名或密码错误','status'=>208]);
                }
                return new JsonResponse(['msg'=>'ok','status'=>200]);
            } catch (\Exception $e) {
                return new JsonResponse(['msg'=>'用户名或密码错误','status'=>209]);
            }
        }
        //api接口 登录
        return parent::login($request);
    }

    /**
     * 邮箱登录
     * @Post("/emailLogin", as="s_login_emailLogin")
     */
    public function emailLogin(Request $request) {
        if($request->ajax()){
            try {
                $email = $request->get("email",'');
                $passwd = $request->get("passwd",'');
                if(strlen($email)<7 || mb_strlen($passwd)<7){
                    return new JsonResponse([
                        "status"=>'333',
                        "message"=>'传递参数非法或者缺少参数',
                    ]);
                }
                $res = \Auth::attempt(['email_login' => 1, 'email' => $email, 'password'=>$passwd ],true);
                if($res){
                    return new JsonResponse([
                        "status"=>'200',
                        "message"=>'成功',
                    ]);
                }else{
                    return new JsonResponse([
                        "status"=>'208',
                        "message"=>'邮箱地址或密码错误',
                    ]);
                }
            } catch (\ApiException $e) {

                return new JsonResponse([
                    "status"=>$e->getCode(),
                    "message"=>$e->getMessage(),
                ]);
            }
        }
    }

    protected function authenticated(Request $request, $user)
    {
        $url = $request->get('url');
        if($url){
            return redirect($url);
        }
        return redirect($this->redirectPath());
    }


}