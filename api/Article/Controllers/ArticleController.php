<?php

namespace App\Article\Controllers;

use App\Article\Entities\ArticleCategoryEntity;
use function App\getCurrentTime;
use App\Article\Services\Article;
use App\Article\Entities\ArticleEntity;
use App\Article\Entities\ImgTitleEntity;
use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;

/**
 * @path /article
 */
class ArticleController
{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Article
     */
    public $article;

    /**
     * @inject
     * @var Container
     */
    protected $container;

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
     * 商品列表
     *
     * @route POST /list
     *
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @param string $name
     * @param string $order
     * @param int $status {@v min:1}
     * @return array
     */
    public function list($page = 1, $pagesize = 10, $name='', $order = '', $status = -1) {

        try {
            $where = [];
            if(strlen($name)>0){
                $where = ['name'=>["like"=>'%'.$name.'%']];
            }
            if(strlen($order)>0){
                $order = json_decode($order);
            }
            if($status>0){
                $where['status'] = $status;
            }
            $data = $this->article->getArticleList($page, $pagesize, $where, $order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
            //
        }
    }

    /**
     * 文章排行列表
     *
     * @route POST /getArticleRank
     * @param int $lastweek {@v min:1}
     * @param string $order
     * @return array
     */
    public function getArticleRank($lastweek = -1 ,$order = '') {
        try {
            if(strlen($order)>0){
                $order = json_decode($order,true);
            }
            $data = $this->article->getArticleRank($lastweek,$order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 热点文章排行列表
     *
     * @route POST /getArticleHotRank
     * @param int $lastweek {@v min:1}
     * @param string $order
     * @return array
     */
    public function getArticleHotRank($lastweek = -1 ,$order = '') {
        try {
            if(strlen($order)>0){
                $order = json_decode($order,true);
            }
            $data = $this->article->getArticleHotRank($lastweek,$order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
    /**
     * 商品关联产品分成列表
     *
     * @route POST /getArticleWithProductList
     *
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @param string $name
     * @param string $order
     * @param string $wheres
     * @return array
     */
    public function getArticleWithProductList($page = 1, $pagesize = 10, $name='', $order = '', $wheres = '') {

        try {
            $where = [];
            if(strlen($name)>0){
                $where = ['name'=>["like"=>'%'.$name.'%']];
            }
            if(strlen($wheres)>0){
                $wheres = json_decode($wheres,true);
                $where = array_merge($where, $wheres);
            }
            if(strlen($order)>0){
                $order = json_decode($order,true);
            }
            $data = $this->article->getArticleWithProductList($page, $pagesize, $where, $order);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 文章状态更新
     *
     * @route POST /changeStatus
     * @param int $article_id {@v min:1}
     * @param int $status {@v min:1|max:20}
     * @return array
     */
    public function changeStatus($article_id, $status) {
        try {
            $data = $this->article->changeStatus($article_id, $status);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
    /**
     * 文章添加
     * @route POST /add
     * @param ArticleEntity $entity {@bind request.request}
     * @return array
     */
    public function add(ArticleEntity $entity) {
        try{
            return $this->mutex->getMutex('addArticle')->synchronized(function() use($entity){
               //查询
                $checkExist = $this->article->getarticleList(1, 1,
                    [
                        'name'=>$entity->getName(),
                        'add_time'=>['>='=>getCurrentTime() - $this->container->get("addduplicatetime")]
                    ]);
                if($checkExist && $checkExist['count']>0){
                    throw $this->exception([
                        'code'=>ErrorConst::DUPLICATE_INSERT,
                        'text'=>"文章重复参数为".json_encode($entity->toArray())
                    ]);
                }
                $this->article->add($entity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 文章详情
     * @route POST /getArticle
     * @param int $articleid {@v min:1}
     * @param int $choiceMethod {@v min:1}
     * @return array
     */
    public function getArticle($articleid, $choiceMethod = -1) {
        try {
             $data = $this->article->getArticle($articleid,$choiceMethod);
             return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 文章编辑提交
     * @route POST /editArticle
     * @param ArticleEntity $entity {@bind request.request}
     * @return array
     */
    public function editArticle(ArticleEntity $entity) {
        try{
            return $this->mutex->getMutex('editArticle'.$entity->getId())->synchronized(function() use ($entity){
                $this->article->editArticle($entity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 文章添加分类关联
     * @route POST /addArticleCategory
     * @param $articleID
     * @param $categoryID
     * @return array
     */
    public function addArticleCategory($articleID, $categoryID) {
        try{
            return $this->mutex->getMutex('editArticle'.$articleID)->synchronized(function() use ($articleID, $categoryID){
                $this->article->addArticleCategory($articleID, $categoryID);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /////
    /**
     * 文章分类列表
     * @route POST /getArticleCategoryList
     * @param string $category_name
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     *
     * @return array
     */
    public function getArticleCategoryList($category_name = '', $page = 1, $pagesize = 10) {
        try {
            $where = [];
            if(strlen($category_name) > 0){
                $where['category_name'] = ["like"=>'%'.$category_name.'%'];

            }
            $data = $this->article->getArticleCategoryList($page, $pagesize,$where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 文章分类态更新
     * @route POST /categoryChangeStatus
     * @param int $cid {@v min:1}
     * @param int $status {@v min:1|max:20}
     * @return array
     */
    public function categoryChangeStatus($cid, $status) {
        try {
            $data = $this->article->categoryChangeStatus($cid, $status);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     *
     * @param ArticleCategoryEntity $articleCategoryEntity
     * @return array
     */
    public function categoryAdd(ArticleCategoryEntity $articleCategoryEntity) {
        try {
            $data = $this->article->categoryAdd($articleCategoryEntity);
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
            $data = $this->article->getCategory($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 分类更新
     * @route POST /editCategory
     * @param ArticleCategoryEntity  $articleCategoryEntity {@bind request.request}
     * @return array
     */
    public function editCategory(ArticleCategoryEntity $articleCategoryEntity) {
        try {
            $data = $this->article->editCategory($articleCategoryEntity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, []);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

///

    /**
     * 图文新增
     * @route POST /imgTitleAdd
     * @param ImgTitleEntity $imgTitleEntity {@bind request.request}
     * @return array
     */
    public function imgTitleAdd(ImgTitleEntity $imgTitleEntity) {
        try {
            $data = $this->article->imgTitleAdd($imgTitleEntity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 图文列表
     * @route POST /imgTitleList
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @param string $img_title
     * @param string $img_type
     * @return array
     */
    public function imgTitleList($page = 1, $pagesize = 10, $img_title = '', $img_type = '') {
        try {
            $where = [];

            if(strlen($img_title)>0){
//                $where[] = ['img_title'=>["like"=>'%'.$img_title.'%']];
                $where['img_title'] =["like"=>'%'.$img_title.'%'];
            }
            if(strlen($img_type)>0){
                $where['img_type'] = $img_type;
            }
            $where['status'] = 1;
            $data = $this->article->imgTitleList($page, $pagesize,$where);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 图文删除
     * @route POST /deleteImgTitle
     * @param int $id {@v min:1}
     * @return array
     */
    public function deleteImgTitle($id) {
        try {

            $data = $this->article->deleteImgTitle($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取图文详情
     * @route POST /getImgTitle
     * @param int $id {@v min:1}
     * @return array
     */
    public function getImgTitle($id) {
        try {
            $data = $this->article->getImgTitle($id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $data);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 图文编辑提交
     * @route POST /editImgTitle
     * @param ImgTitleEntity  $entity {@bind request.request}
     * @return array
     */
    public function editImgTitle(ImgTitleEntity $entity) {
        try{
            return $this->mutex->getMutex('editImgTitle'.$entity->getId())->synchronized(function() use ($entity){
                $this->article->editImgTitle($entity);
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            });
        }catch(\Exception $e){
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * 微信文章详情页
     * @route POST /getArticleDetailForWx
     * @param int $article_id {@v min:1}
     * @return array
     */
    public function getArticleDetailForWx($article_id) {
        try {
            $rs = $this->article->getArticleDetailForWx($article_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 文章列表页面Ajax
     * @Post("/getActicleList", as="s_aricle_getActicleList")
     */
    public function getActicleList(Request $request) {
        try {

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $category_id = $request->get("category_id",0);
            $region_id = $request->get("region_id",0);

            $where['t_article.status'] = 1;
            $where['t_product.status'] = 1;
            $arr = [
                'wheres' => json_encode($where),
            ];
            if($category_id > 0){
//                $where['t_article_category_relate.category_id'] = $category_id;
                $where['t_article_category_relate.category_id'] = $category_id;
            }
            if($page >0){
                $arr['page'] = $page;
            }
            if($pagesize >0){
                $arr['pagesize'] = $pagesize;
            }
            if($region_id >0){
                $arr['region_id'] = $region_id;
            }


            $post = getRedisData('getActicleList'.md5(json_encode($request->all())),'/article/getArticleWithProductList',$arr);

            if($post['data']['count']>0){
                foreach ($post['data']['data'] as $key =>$val){
//                    var_dump($val);die;
                    $post['data']['data'][$key]['time_tranx'] = time_tranx($val['add_time']);

//                    $post['data']['data'][$key]['percentKey'] = (isset($val['percent_arr']['mode_2']['percent']) && $val['percent_arr']['mode_2']['percent']>0)?
//                        number_format($val['percent_arr']['mode_2']['percent'],2).'%'  : number_format($val['percent_arr']['mode_2']['account'],2);

                    $post['data']['data'][$key]['percentKey'] = (isset($val['percent_arr']['mode_2']['percent']) && $val['percent_arr']['mode_2']['percent']>0)?
                        number_format($val['percent_arr']['mode_2']['percent'] * $val['selling_price'] /100 ,2)  : number_format($val['percent_arr']['mode_2']['account'],2);

                }
            }
//            foreach ($post['data']['data'] as $key =>$val){
//                $post['data']['data'][$key]['time_tranx'] = time_tranx($val['add_time']);
//                $post['data']['data'][$key]['percentKey'] = isset($val['percent_arr']['mode_2']['percent'])?$val['percent_arr']['mode_2']['percent']:0;
//            }
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     *
     * @Post("/articleAdd", as="s_article_add")
     * @Middleware("auth")
     */
    public function articleAdd(Request $request) {
        try {

            $paramer['id'] = $request->get('id', 0);
            $paramer['name'] = $request->get('name');
            $paramer['content'] = $request->get('content');
            $paramer['article_product_id'] = $request->get('product_id');
            $paramer['spiltway'] = 3;
            $paramer['author'] = $this->getUserId();

            $paramer['article_category_id'] = $request->get('article_category_id',0);

            $status = $request->get('status');


            if($status==1){
                $paramer['status'] = 3;//发布
            }else{
                $paramer['status'] = 5;//草稿
            }

            if($paramer['id']){
                $data = Curl::post('/article/editArticle',
                    $paramer
                );
            }else{
                $data = Curl::post('/article/add',
                    $paramer
                );
            }



            if($data['status']==200){
                $phpPath = config('params.php_path');
                shell_exec("nohup ".$phpPath.' '.public_path('../').'artisan command:ossUploadStript '.$data['data'].' >> /tmp/out.file 2>&1  &');
            }

            return new JsonResponse($data);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获得推广二维码
     * @Post("/createSpreadQRcode", as="s_aricle_createSpreadQRcode")
     */
    public function createSpreadQRcode(Request $request) {
        try {
            $aid = $request->get("aid",-1);
            $aprs = $request->get("aprs",-1);
            if(intval($aid) && $aid >0){
                $post = Curl::post('/user/createSpreadQRcode', [
                    'aprs' => $aprs,
                    'spreadUid' => $this->getUserId()
                ]);
                $data = $post['data'];
                if($post['status']==200){
                    if($post['data']['product_type'] == 3){

                        if(stristr($post['data']['landing_page'],'?')){
                            $post['data']['landing_page'] = $post['data']['landing_page'].'&spreadid='.$post['data']['id'].'&productid='.$post['data']['productId'].'&articleid='.$post['data']['article_id'];
                        }else{
                            $post['data']['landing_page'] = $post['data']['landing_page'].'?spreadid='.$post['data']['id'].'&productid='.$post['data']['productId'].'&articleid='.$post['data']['article_id'];
                        }
                        $post['data']['url'] = $post['data']['landing_page'];
                    }else{
                        $post['data']['url'] = config('params.wx_host').'User/productDetail?spreadid='.$post['data']['id'] .'&nid='.$post['data']['order_no'];
                    }

                    //$post['data']['url'] = file_get_contents('http://suo.im/api.php?url='.urlencode($post['data']['url']));
                }

//               var_dump($data);die;
                return new JsonResponse($post);
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 添加所选分类
     * @Post("/postCategoryChose", as="s_aricle_postCategoryChose")
     */
    public function postCategoryChose(Request $request) {
        try {
            $uid = $this->getUserId();
            $category_name = $request->get("category_name",'');
            if($uid>0 && strlen($category_name)>0 ){
                $post = Curl::post('/article/postCategoryChose', [
                    'uid' => $uid,
                    'category_name' => $category_name
                ]);
                return new JsonResponse($post);
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * @Get("/creationLists", as="s_article_creation_lists")
     * @Post("/creationLists", as="s_article_creation_lists")
     */
    public function creationLists(Request $request){
        if ($request->ajax()) {

            $data = [
                'page' => $request->get('page', 1),
                'pagesize'=>$request->get('pagesize', 10),
                'name'=>$request->get('name', ''),
                'adminlist' => 1
            ];
            if( $request->get('status') > 0){
                $data['status'] = $request->get('status');
            }
            $data = Curl::post('/article/creationList',
                $data
            );

            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }

        return view("Article.creationList");
    }

    /**
     *
     * @Post("/changeStatus", as="s_article_change_status")
     */
    public function changeStatus(Request $request) {

        if ($request->ajax()) {
            $data = Curl::post('/article/changeStatus',
                [
                    'article_id' => $request->get('article_id', 0),
                    'status'=>$request->get('status', 1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }


    /**
     * @Get("/addsecond", as="s_article_addsecond")
     *
     */
    public function addsecond(Request $request){
        try {
//            $articlecategorylist = Curl::post('/article/getArticleCategoryList', ['status' => 1]);
//            $articlecategorylist = $articlecategorylist['data']['data'];
            $product_categorylist = Curl::post('/productCategory/getProductCategoryList',['status'=>1]);
            $product_categorylist = $product_categorylist['data']['data'];

//            $industry_media_list = Curl::post('/industry/getLists', ['status' => 1,'type'=>2]);
            $industry_media_list = Curl::post('/industryCategory/getLists', ['status' => 1,'type'=>2]);
            $industry_media_list = $industry_media_list['data']['data'];
        } catch (Exception $e) {
            $product_categorylist = [];
        }
        try {
            $productList = Curl::post('/product/wrtiergetGoodsList',['pagesize'=>100]);
            $productList = $productList['data']['data'];
        }catch (Exception $e){
            $productList = [];
        }

        foreach ($productList as $k=>$v){
            $percent = -1;
            $account = -1;
            $combine = -1;
            if(strlen($v['display_to_writer'])>0){
                $percents = json_decode($v['percent'],true);
                $display = json_decode($v['display_to_writer'],true);
                foreach ($percents as $item) {
                    if($item['type'] == 1  && $display[$item['type']]>0 ){
                        $percent = $item['contents']['percent'];
                    }
                    if($item['type'] == 2  && $display[$item['type']]>0 ){
                        $account = $item['contents']['account'];
                    }
                    if($item['type'] == 3  && $display[$item['type']]>0 ){
                        $combine = 'percent:'.$item['contents']['percent'].' cash:'.$item['contents']['account'];
                    }
                }
            }
            $productList[$k]['percent'] = $percent;
            $productList[$k]['account'] = $account;
            $productList[$k]['combine'] = $combine;
        }

        return view("Article.add")->with('industry_media_list',$industry_media_list)
            ->with('productList',$productList);
    }

    /**
     * @Post("/save", as="s_article_save")
     */
    public function saveGoods(Request $request) {
        session_start();
        $wxImgList =  isset($_SESSION['wxImgList'])?json_decode($_SESSION['wxImgList'],true):'';

        $this->validate($request, [
            'name' => 'required',
            'content' => 'required',
            'summary' => 'required',
            'article_product_id' => 'required',
            'spiltway' => 'required'
        ]);
        $content = $request->get('content');
        if($wxImgList) {

            foreach ($wxImgList as $value) {
                $content = str_replace(html_entity_decode($value['source']),$value['url'],$content);
            }
        }
        $paramer = $request->all();
        $paramer['content'] = $content;

        $data = Curl::post('/article/add',
            $paramer
        );
        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            return redirect(route('s_article_lists'))->with('addsuccess', 'success');
        }
    }

    /**
     * @Get("/edit", as="s_article_edit")
     */
    public function edit(Request $request){

        try {
//            $articlecategorylist = Curl::post('/article/getArticleCategoryList', ['status' => 1]);
//            $articlecategorylist = $articlecategorylist['data']['data'];

//            $product_categorylist = Curl::post('/productCategory/getProductCategoryList',['status'=>1]);
//            $product_categorylist = $product_categorylist['data']['data'];

//            $industry_media_list = Curl::post('/industry/getLists', ['status' => 1,'type'=>2]);
//            $industry_media_list = $industry_media_list['data']['data'];

            $industry_media_list = Curl::post('/industryCategory/getLists',
                [
                    'status'=>1,
                    'type'=>2,
                    'order'=>'{"status":"ASC","order":"DESC"}'
                ]
            );
            $industry_media_list = $industry_media_list['data']['data'];


        } catch (Exception $e) {
            $articlecategorylist = [];
            $product_categorylist = [];
            $industry_media_list = [];
        }
        $data = Curl::post('/article/getArticle',
            ['articleid'=>$request->get('articleid', 1)]
        );
        $percents = json_decode($data['data']['percent'],true);
        $split_way = -1;
        $percent = 0;

        foreach ($percents as $item) {
            if($item['mode'] == 1){
                if(count($item['contents']) >1){
                    $split_way = 3;
//                    $percent = json_encode($item['contents']);
                    $percent = $item['contents']['percent'].'% + '.$item['contents']['account'].'元';
                }else{
                    if(key_exists('percent',$item['contents'])){
                        $split_way = 1;
                        $percent = $item['contents']['percent'].'%';
                    }else{
                        $split_way = 2;
                        $percent = $item['contents']['account'].'元';
                    }
                }
                break;
            }
        }
//        if($split_way && $percent){
        $data['data']['split_way'] = $split_way;
        $data['data']['percent'] = $percent;
//        }
        $data['data']['content'] = str_replace(chr(10),'<br>',$data['data']['content']);
        $data['data']['content'] = str_replace(chr(13),'<br>',$data['data']['content']);
        return view("Article.edit")
            ->with('res',$data['data'])
            ->with('industry_media_list',$industry_media_list);
    }

    /**
     * @Get("/articleLook", as="s_article_look")
     */
    public function articleLook(Request $request){


        $data = Curl::post('/article/getArticle',
            ['articleid'=>$request->get('articleid', 1)]
        );

        $data2 = Curl::post('/article/articleLook',
            ['article_id'=>$request->get('articleid', 1)]
        );


        $data['data']['content'] = str_replace(chr(10),'<br>',$data['data']['content']);
        $data['data']['content'] = str_replace(chr(13),'<br>',$data['data']['content']);
        return view("Article.articleLook")
            ->with('res',$data['data'])->with('data',$data2['data']);
    }



    function ShengYu_Tian_Shi_Fen($unixEndTime=0)
    {
        if ($unixEndTime <= time()) { // 如果过了活动终止日期
            return '已过期';
        }

        // 使用当前日期时间到活动截至日期时间的毫秒数来计算剩余天时分
        $time = $unixEndTime - time();

        $days = 0;
        if ($time >= 86400) { // 如果大于1天
            $days = (int)($time / 86400);
            $time = $time % 86400; // 计算天后剩余的毫秒数
        }

        $xiaoshi = 0;
        if ($time >= 3600) { // 如果大于1小时
            $xiaoshi = (int)($time / 3600);
            $time = $time % 3600; // 计算小时后剩余的毫秒数
        }

        $fen = (int)($time / 60); // 剩下的毫秒数都算作分

        return $days.'天'.$xiaoshi.'时'.$fen.'分';
    }

    private function getUserId() {
        return \Auth::getUser()->getAuthIdentifier();

    }

    private function getUserName() {
        return \Auth::getUser()->getUserMobile();
    }

    private function getRecommendCode() {
        return \Auth::getUser()->getRecommendCode();
    }
}