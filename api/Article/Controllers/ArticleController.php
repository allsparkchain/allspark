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
}