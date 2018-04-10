<?php

namespace App\Article\Services;

use App\Article\Entities\ArticleCategoryEntity;
use function App\getCurrentTime;
use App\Article\Entities\ArticleEntity;
use App\Article\Entities\ImgTitleEntity;
use App\Utils\ErrorConst;
use App\Utils\Mutex;
use App\Utils\Pagination;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Psr\Log\LoggerInterface;


class Article
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
     * 获取文章列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getArticleList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_article");
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
            $data = $pagination->get();

            if($data['data']){
                foreach ($data['data'] as $key => $value){
                    $img = $this->db->select("*")->from('t_article_img')->where(['article_id'=>$value['id']])->get();
                    $data['data'][$key]['imgs'] =  $img;
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
     * 获取文章关联产品分成列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getArticleWithProductList($page, $pageszie,  $where = [], $order = [])
    {
        try {
//            $joinRule = $this->db->select("t_article.*", DB::raw("IFNULL(t_product_division.writerpercent,0.00) as percent"),
//                DB::raw("t_article_product_relate.id"))
//                ->from("t_article")
//                ->leftJoin('t_article_category_relate')->on('t_article_category_relate.article_id=t_article.id')
//                ->leftJoin('t_article_product_relate')->on('t_article_product_relate.article_id=t_article.id')
//                ->leftJoin('t_product_division')->on('t_article_product_relate.product_id=t_product_division.product_id');
            ///
//            $joinRule = $this->db->select("t_article.*", DB::raw("IFNULL(t_product_division.writerpercent,0.00) as percent"),
//                DB::raw("t_article_product_relate.id as relateid"))
//                ->from("t_article")
//                ->leftJoin('t_article_category_relate')->on('t_article.id =t_article_category_relate.article_id')
//                ->leftJoin('t_article_product_relate')->on('t_article.id=t_article_product_relate.article_id')
//                ->innerJoin('t_product_division')->on('t_article_product_relate.product_id=t_product_division.product_id');

            $joinRule = $this->db->select("t_article.*",
                DB::raw("t_article_product_relate.id as relateid,t_article_product_relate.product_id,t_article_product_relate.percent"))
                ->from("t_article_product_relate")
                ->innerJoin("t_article")->on('t_article.id=t_article_product_relate.article_id')
                ->leftJoin('t_article_category_relate')->on('t_article.id =t_article_category_relate.article_id')
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
                $joinRule->orderBy('t_article.add_time', DB::ORDER_BY_DESC);
            }
            $pagination = new Pagination($joinRule, $page, $pageszie);
            $data = $pagination->get();
            if($data['data']){
                foreach ($data['data'] as $key => $value){
                    $percent_arr = json_decode($value['percent'],true);
                    $new_arr = [];
                    foreach ($percent_arr as $k=>$val){
                        $new_arr['mode_'.$val['mode']] = $val['contents'];
                    }

                    $data['data'][$key]['percent_arr'] = $new_arr;
                    $img = $this->db->select("*")->from('t_article_img')->where(['article_id'=>$value['id']])->get();
                    $data['data'][$key]['imgs'] =  $img;
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
     * 文章排行列表
     * @param int $lastweek
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getArticleRank($lastweek = -1, $order = [])
    {
        try {
            $now = getCurrentTime();
            $where = [];
            if($lastweek > 0){
                //上周一，上周日 的区间
                $lastweeknow = strtotime('-7 day', $now);

                $lastweeknowday = date('w',$lastweeknow);
                $lastweekday = $lastweeknowday ?  $lastweeknowday : 7;

                $lastweekMonday = date('Y-m-d 00:00:00',$lastweeknow - ($lastweekday - 1) * 86400);
                $lastweekSunday = date('Y-m-d 23:59:59',$lastweeknow + (7-$lastweekday)* 86400);
                $where['t_user_commission.add_time'] = ['BETWEEN'=>[$lastweekMonday,$lastweekSunday]];
            }


            $article = "t_article.name";
            $comission = "ifnull(sum(t_user_commission.account),0) as totalcomission";
            $productOrder = "t_product_order.article_id,t_product_order.spread_id";
            $joinRule = $this->db->select(
                DB::raw($article),DB::raw($comission),DB::raw($productOrder)
                )
                ->from("t_user_commission")
                ->innerJoin("t_product_order")->on('t_product_order.id=t_user_commission.product_order_id')
                ->innerJoin("t_article")->on('t_article.id=t_product_order.article_id')
            ;

            $joinRule->where($where);
            $joinRule->groupBy('t_product_order.article_id');
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $joinRule->orderBy($key, $value);
                    }
                }
            } else {
                $joinRule->orderBy('totalcomission', DB::ORDER_BY_DESC);
            }
            $joinRule->limit(0, 5);

            $data = $joinRule->get();


            if($data){
                foreach ($data as $key => $value){
                    //分成
//                    $percent_arr = json_decode($value['percent'],true);
//                    $new_arr = [];
//                    foreach ($percent_arr as $k=>$val){
//                        $new_arr['mode_'.$val['mode']] = $val['contents'];
//                    }
//                    $data['data'][$key]['percent_arr'] = $new_arr;

                    //一张图片
                    $img = $this->db->select("*")->from('t_article_img')->where(['article_id'=>$value['article_id']])->getFirst();
                    $data[$key]['imgs'] =  $img;

                    //总分享人数
                    $data[$key]['spreadnum'] = 0;
                    $joinRule = $this->db->select(DB::raw('count(*) as spreadnum'))
                        ->from("t_spread_list")
                        ->where(['id'=>$value['spread_id']])->getFirst();
                    if($joinRule){
                        $data[$key]['spreadnum'] = $joinRule['spreadnum'];
                    }
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
     * 热点文章排行列表
     * @param int $lastweek
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getArticleHotRank($lastweek = -1, $order = [])
    {
        try {
            $now = getCurrentTime();
            $where = [];
            if($lastweek > 0){
                //上周一，上周日 的区间
                $now = strtotime('2018-1-29 23:23:23');
//                $now = strtotime('2018-2-4 23:23:23');
                $lastweeknow = strtotime('-7 day', $now);

                $lastweeknowday = date('w',$lastweeknow);
                $lastweekday = $lastweeknowday ?  $lastweeknowday : 7;

                $lastweekMonday = date('Y-m-d 00:00:00',$lastweeknow - ($lastweekday - 1) * 86400);
                $lastweekSunday = date('Y-m-d 23:59:59',$lastweeknow + (7-$lastweekday)* 86400);
                $where['t_user_commission.add_time'] = ['BETWEEN'=>[$lastweekMonday,$lastweekSunday]];
            }

            $article = "t_article.id as tarticleid,t_article.name,t_article.add_time,count(t_article.id) as nums,t_article_product_relate.percent";
//            $comission = "count(t_article.id) as nums";
//            $productOrder = "t_product_order.article_id,t_product_order.spread_id";

            $joinRule = $this->db->select(
                DB::raw($article)
            )
                ->from("t_spread_list")
                ->innerJoin("t_article_product_relate")->on('t_article_product_relate.id=t_spread_list.article_product_id')
                ->innerJoin("t_article")->on('t_article.id=t_article_product_relate.article_id')
            ;

            if(count($where)>0){
                $joinRule->where($where);
            }

            $joinRule->groupBy('t_article.id');
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $joinRule->orderBy($key, $value);
                    }
                }
            } else {
                $joinRule->orderBy('nums', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_article.add_time', DB::ORDER_BY_DESC);
            }
            $joinRule->limit(0, 5);
            $data = $joinRule->get();
            if($data){
                foreach ($data as $key => $value){
//                    分成
                    $percent_arr = json_decode($value['percent'],true);
                    $new_arr = [];
                    foreach ($percent_arr as $k=>$val){
                        $new_arr['mode_'.$val['mode']] = $val['contents'];
                    }
                    $data[$key]['percent_arr'] = $new_arr;

                    //一张图片
                    $img = $this->db->select("*")->from('t_article_img')->where(['article_id'=>$value['tarticleid']])->getFirst();
                    $data[$key]['imgs'] =  $img;

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
     * 更新商品状态
     * @param $articleId
     * @param $status
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function changeStatus($articleId, $status)
    {
        try{
            $execResult = $this->db->update('t_article')->set([
                'status' => $status
            ])->where(['id' => $articleId])->exec();
            if ($execResult->rows != 1) {
                throw $this->exception([
                    'code'=>ErrorConst::ARTICLE_CHANGE_STATUS_ERROR,
                    'text'=>"文章id:".$articleId."状态更新失败"
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
     * 文章添加
     * @param ArticleEntity $articleEntity
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function add(ArticleEntity $articleEntity)
    {
        try{
            return $this->db->transaction(function(DB $db) use ($articleEntity){
                $now = getCurrentTime();


                $lastId = $db->insertInto('t_article')->values([
                    'name'=>$articleEntity->getName(),
                    'content'=>htmlentities($articleEntity->getContent()),
                    'author'=>$articleEntity->getAuthor(),
                    'summary'=> $articleEntity->getSummary(),
                    'add_time' =>$now
                ])->exec()->lastInsertId();
                if($lastId<=0){
                    throw $this->exception([
                        'code'=>ErrorConst::ARTICLE_INSERT_ERROR,
                        'text'=>"文章创建新失败".serialize($articleEntity->toArray())
                    ]);
                }

                $db->deleteFrom('t_article_category_relate')
                    ->where(['article_id'=>$lastId])->exec();
                if($articleEntity->getArticleCategoryId() > 0){
                    $db->insertInto('t_article_category_relate')->values([
                        'article_id'=>$lastId,
                        'category_id'=>$articleEntity->getArticleCategoryId(),
                        'add_time' =>$now
                    ])->exec();
                }


                //insert article_product_relate
                $chose_writer_way = $articleEntity->getSpiltway();
                $division_method = $this->db->select("*")->from('t_product_division_method')
                    ->where(['status'=>1,'product_id'=>$articleEntity->getArticleProductId()])->get();
                if(count($division_method) > 0){
                    $prepare = [];
                    //第一次 获取写手的分成 数据 和内容
                    foreach ($division_method as $key=>$method){
                        if($method['type'] == 1){
                            $percents = json_decode($method['percent'],true);
                            foreach ($percents as $k=>$percent){
                                if($percent['type'] == $chose_writer_way){
                                    if($percent['type'] == 1){
                                        if($percent['contents']['percent'] < 0){
                                            throw $this->exception([
                                                'code'=>ErrorConst::CHANGE_STATUS_ERROR,
                                                'text'=>"选择分成，但没有提供该选择"
                                            ]);
                                            break;
                                        }
                                        $prepare[] = [
                                            'mode'=>1,
                                            'contents'=>[
                                                'percent'=>$percent['contents']['percent']
                                            ]
                                        ];
                                        break;
                                    }
                                    if($percent['type'] == 2){
                                        if($percent['contents']['account'] < 0){
                                            throw $this->exception([
                                                'code'=>ErrorConst::CHANGE_STATUS_ERROR,
                                                'text'=>"选择固定金额，但没有提供该选择"
                                            ]);
                                            break;
                                        }
                                        $prepare[] = [
                                            'mode'=>1,
                                            'contents'=>[
                                                'account'=>$percent['contents']['account']
                                            ]
                                        ];
                                        break;
                                    }
                                    if($percent['type'] == 3){
                                        if($percent['contents']['account'] < 0 || $percent['contents']['percent'] < 0){
                                            throw $this->exception([
                                                'code'=>ErrorConst::CHANGE_STATUS_ERROR,
                                                'text'=>"选择复合模式，但没有提供该选择"
                                            ]);
                                        }
                                        $prepare[] = [
                                            'mode'=>1,
                                            'contents'=>[
                                                'account'=>$percent['contents']['account'],
                                                'percent'=>$percent['contents']['percent']
                                            ]
                                        ];
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                        break;
                    }
                    //第二次 获得另两个的值 完成数组 插入
                    foreach ($division_method as $key=>$method) {
                        if ($method['type'] == 1) {
                            continue;
                        }
                        //PS 目前  除写手外 其他 媒体(渠道)，网站,媒体代理 只有一种方式，故不做特意判断，直接获取
                        $percents = json_decode($method['percent'],true);
                        $contents = [];
                        foreach ($percents as $k=>$percent){
                            if($percent['type'] == 1){
                                $contents['percent'] = $percent['contents']['percent'];
                            }
                            if($percent['type'] == 2){
                                $contents['account'] = $percent['contents']['account'];
                            }
//                            $contents[] = $percent['contents'];
                        }

                        $prepare[] = [
                            'mode'=> $method['type'],
                            'contents' => $contents
                        ];
                        //原本只取大于0的 认为有值的选择
//                        foreach ($percents as $k=>$percent){
//                            if($percent['type'] == 1 && $percent['contents']['percent'] > 0){
//                                $prepare[] = [
//                                    'mode'=> $method['type'],
//                                    'contents'=> $percent['contents']
//
//                                ];
//                            }
//                            if($percent['type'] == 2 && $percent['contents']['account'] > 0){
//                                $prepare[] = [
//                                    'mode'=> $method['type'],
//                                    'contents'=> $percent['contents']
//
//                                ];
//                            }
//                        }
                    }
                }else{
                    //没有产品的分配方案
                    throw $this->exception([
                        'code'=>ErrorConst::NO_SPLIT_METHODS,
                        'text'=>"选择的产品没有分成方案".$articleEntity->getArticleProductId()
                    ]);
                }

                $db->insertInto('t_article_product_relate')->values([
                    'uid' => $this->container->get("adminauthor"),
                    'article_id'=>$lastId,
                    'product_id'=>$articleEntity->getArticleProductId(),
                    'percent' => json_encode($prepare),
                    'add_time' =>$now
                ])->exec();


                $htmlEntityDecode = html_entity_decode($articleEntity->getContent());
                $imgArray = (\App\getimages($htmlEntityDecode));
                $imageArray = [];
                foreach ($imgArray as $key => $value){

                    $imageArray[] = [
                        'null',
                        $lastId,
                        1,
                        $value,
                        $now,
                        date('Y-m-d H:i:s'),
                    ];

                }

                if ($imageArray) {
                    $onDuplicateKeyUpdateRule = $db->insertInto("t_article_img")->batchValues($imageArray);
                   // echo $onDuplicateKeyUpdateRule->context->sql;exit;
                    $onDuplicateKeyUpdateRule->exec();
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
     * 获取单个商品详情
     * @param $articleId
     * @param $choiceMethod
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getArticle($articleId, $choiceMethod = -1)
    {
        $type = 1;
        if($choiceMethod>0){
            $type = $choiceMethod;
        }
        $category = "t_article_category_relate.category_id";
        $product = "t_article_product_relate.product_id,t_article_product_relate.percent,t_article_product_relate.id as article_product_relateId";
        $product_name = "t_product.product_name";
        $data = $this->db->select(
            't_article.*',
            DB::raw($category),
            DB::raw($product),
            DB::raw($product_name)
            )
            ->from('t_article')
            ->leftJoin('t_article_category_relate')->on('t_article_category_relate.article_id=t_article.id')
            ->leftJoin('t_article_product_relate')->on('t_article_product_relate.article_id=t_article.id')
            ->leftJoin('t_product')->on('t_article_product_relate.product_id=t_product.id')
            ->where(['t_article.id' => $articleId])->getFirst();
        return $data;
    }

    /**
     * 文章编辑
     * @param ArticleEntity $articleEntity
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function editArticle(ArticleEntity $articleEntity){
        try{
            return $this->db->transaction(function(DB $db) use ($articleEntity){
                $now = getCurrentTime();

                $value = htmlentities($articleEntity->getContent());
                $value = preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) { return '@E' . base64_encode($r[0]);},$value);

                $countt=substr_count($value,"@");
                for ($i=0; $i < $countt; $i++) {
                    $c = stripos($value,"@");
                    $value=substr($value,0,$c).substr($value,$c+10,strlen($value)-1);
                }
                $value = preg_replace_callback('/@E(.{6}==)/', function($r) {return base64_decode($r[1]);}, $value);

                $db->update('t_article')->set([
                    'name'=>$articleEntity->getName(),
                    'content'=> $value,
                    'summary'=> $articleEntity->getSummary(),
                    'author'=>$articleEntity->getAuthor()
                ])->where(['id'=>$articleEntity->getId()])->exec();



                $db->deleteFrom('t_article_category_relate')
                    ->where(['article_id'=>$articleEntity->getId()])->exec();
                if($articleEntity->getArticleCategoryId() > 0){
                    $db->insertInto('t_article_category_relate')->values([
                        'article_id'=>$articleEntity->getId(),
                        'category_id'=>$articleEntity->getArticleCategoryId(),
                        'add_time' =>$now
                    ])->exec();
                }



                //先查找  原relate纪录，，然后尝试更新
                $old = $this->db->select("*")
                    ->from("t_article_product_relate")
                    ->where([
                        'product_id'=>$articleEntity->getArticleProductId(),
                        'article_id'=>$articleEntity->getId()
                    ])->getFirst();
                if(is_null($old)){
                    throw $this->exception([
                        'code'=>ErrorConst::NOT_FOUND_RELATE_WITH_ARTICLE,
                        'text'=>"没有找到产品文章关联表记录.产品id".$articleEntity->getArticleProductId().'文章id'.$articleEntity->getId()
                    ]);
                }

                //$old['id']   percent

                $chose_writer_way = $articleEntity->getSpiltway();
                $division_method = $this->db->select("*")->from('t_product_division_method')
                    ->where(['status'=>1,'product_id'=>$articleEntity->getArticleProductId()])->get();
                if(count($division_method) > 0){
                    $prepare = [];
                    //第一次 获取写手的分成 数据 和内容
                    foreach ($division_method as $key=>$method){
                        if($method['type'] == 1){
                            $percents = json_decode($method['percent'],true);
                            foreach ($percents as $k=>$percent){
                                if($percent['type'] == $chose_writer_way){
                                    if($percent['type'] == 1){
                                        if($percent['contents']['percent'] < 0){
                                            throw $this->exception([
                                                'code'=>ErrorConst::CHANGE_STATUS_ERROR,
                                                'text'=>"选择分成，但没有提供该选择"
                                            ]);
                                            break;
                                        }
                                        $prepare[] = [
                                            'mode'=>1,
                                            'contents'=>[
                                                'percent'=>$percent['contents']['percent']
                                            ]
                                        ];
                                        break;
                                    }
                                    if($percent['type'] == 2){
                                        if($percent['contents']['account'] < 0){
                                            throw $this->exception([
                                                'code'=>ErrorConst::CHANGE_STATUS_ERROR,
                                                'text'=>"选择固定金额，但没有提供该选择"
                                            ]);
                                            break;
                                        }
                                        $prepare[] = [
                                            'mode'=>1,
                                            'contents'=>[
                                                'account'=>$percent['contents']['account']
                                            ]
                                        ];
                                        break;
                                    }
                                    if($percent['type'] == 3){
                                        if($percent['contents']['account'] < 0 || $percent['contents']['percent'] < 0){
                                            throw $this->exception([
                                                'code'=>ErrorConst::CHANGE_STATUS_ERROR,
                                                'text'=>"选择复合模式，但没有提供该选择"
                                            ]);
                                        }
                                        $prepare[] = [
                                            'mode'=>1,
                                            'contents'=>[
                                                'account'=>$percent['contents']['account'],
                                                'percent'=>$percent['contents']['percent']
                                            ]
                                        ];
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                        break;
                    }
                    //第二次 获得另两个的值 完成数组 插入
                    foreach ($division_method as $key=>$method) {
                        if ($method['type'] == 1) {
                            continue;
                        }
                        //PS 目前  除写手外 其他 渠道，网站只有一种方式，故不做特意判断，直接获取
                        $percents = json_decode($method['percent'],true);
                        $contents = [];
                        foreach ($percents as $k=>$percent){
                            if($percent['type'] == 1){
                                $contents['percent'] = $percent['contents']['percent'];
                            }
                            if($percent['type'] == 2){
                                $contents['account'] = $percent['contents']['account'];
                            }
//                            $contents[] = $percent['contents'];
                        }

                        $prepare[] = [
                            'mode'=> $method['type'],
                            'contents' => $contents
                        ];
//                        foreach ($percents as $k=>$percent){
//                            if($percent['type'] == 1 && $percent['contents']['percent'] > 0){
//                                $prepare[] = [
//                                    'mode'=> $method['type'],
//                                    'contents'=> $percent['contents']
//
//                                ];
//                            }
//                            if($percent['type'] == 2 && $percent['contents']['account'] > 0){
//                                $prepare[] = [
//                                    'mode'=> $method['type'],
//                                    'contents'=> $percent['contents']
//
//                                ];
//                            }
//                        }
                    }
                }else{
                    //没有产品的分配方案
                    throw $this->exception([
                        'code'=>ErrorConst::NO_SPLIT_METHODS,
                        'text'=>"选择的产品没有分成方案".$articleEntity->getArticleProductId()
                    ]);
                }


                $execResult = $this->db->update('t_article_product_relate')->set([
                    'percent' => json_encode($prepare)
                ])->where(['id' => $old['id']])->exec()->rows;
//
//                $db->deleteFrom('t_article_product_relate')
//                    ->where(['article_id'=>$articleEntity->getId()])->exec();
//                if($articleEntity->getArticleProductId() > 0){
//                    $db->insertInto('t_article_product_relate')->values([
//                        'article_id'=>$articleEntity->getId(),
//                        'product_id'=>$articleEntity->getArticleProductId(),
//
//
//
//                        'add_time' =>$now
//                    ])->exec();
//                }


                $db->deleteFrom('t_article_img')->where(['article_id'=>$articleEntity->getId()])->exec();

                $htmlEntityDecode = html_entity_decode($articleEntity->getContent());
                $imgArray = (\App\getimages($htmlEntityDecode));
                $imageArray = [];
                foreach ($imgArray as $key => $value){

                    $imageArray[] = [
                        'null',
                        $articleEntity->getId(),
                        1,
                        $value,
                        $now,
                        date('Y-m-d H:i:s'),
                    ];

                }

                if ($imageArray) {
                    $onDuplicateKeyUpdateRule = $db->insertInto("t_article_img")->batchValues($imageArray);
                    $onDuplicateKeyUpdateRule->exec();
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
     * 文章添加分类关联
     * @param $articleID
     * @param $categoryID
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     * @throws \Exception
     */
    public function addArticleCategory($articleID, $categoryID)
    {
        try{
            return $this->db->transaction(function(DB $db) use ($articleID, $categoryID){
                $now = getCurrentTime();
                $db->deleteFrom('t_article_category')
                    ->where(['article_id'=>$articleID])->exec();
                $db->insertInto('t_article_category')->values([
                    'article_id'=>$articleID,
                    'category_id'=>$categoryID,
                    'add_time' => $now
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
     * 获取文章分类
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getArticleCategoryList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_article_category");
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
            $execResult = $this->db->update('t_article_category')->set([
                'status' => $status
            ])->where(['id' => $cid])->exec();

            if ($execResult->rows != 1) {
                throw $this->exception([
                    'code'=>ErrorConst::ARTICLE_CATEGORY_CHANGE_STATUS_ERROR,
                    'text'=>"文章分类id:".$cid."状态更新失败"
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
     * @param ArticleCategoryEntity $articleCategoryEntity
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function categoryAdd(ArticleCategoryEntity $articleCategoryEntity)
    {
        try{
            return $this->db->transaction(function(DB $db) use ($articleCategoryEntity){
                $db->insertInto('t_article_category')->values([
                    'category_name' => $articleCategoryEntity->getCategoryName(),
                    'parent' => $articleCategoryEntity->getParent(),
                    'status' => $articleCategoryEntity->getStatus(),
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
            ->from('t_article_category')
            ->where(['id' => $id]);
        $data = $nextWhereRule->getFirst();
        return $data;
    }

    /**
     * 分类编辑
     * @param ArticleCategoryEntity $articleCategoryEntity
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function editCategory(ArticleCategoryEntity $articleCategoryEntity){
        try{
            return $this->db->transaction(function(DB $db) use ($articleCategoryEntity){
                $db->update('t_article_category')->set([
                    'category_name' => $articleCategoryEntity->getCategoryName(),
                    'parent' => $articleCategoryEntity->getParent(),
                ])->where(['id'=>$articleCategoryEntity->getId()])->exec();
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
     * 图文添加
     * @param ImgTitleEntity $imgTitleEntity
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function imgTitleAdd(ImgTitleEntity $imgTitleEntity)
    {
        try{
            return $this->db->transaction(function(DB $db) use ($imgTitleEntity){
                $db->insertInto('t_imgtitle')->values([
                    'img_title' => $imgTitleEntity->getImgTitle(),
                    'img_url' => $imgTitleEntity->getImgUrl(),
                    'img_path' => $imgTitleEntity->getImgpath(),
                    'img_type' => $imgTitleEntity->getImgType(),
                    'img_order' => $imgTitleEntity->getImgOrder(),
                    'status' => $imgTitleEntity->getStatus(),
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
     * 获取图文列表
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param array $order
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function imgTitleList($page, $pageszie,  $where = [], $order = [])
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_imgtitle");
//            var_dump($where);die;
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
                $joinRule->orderBy('img_order', DB::ORDER_BY_DESC);
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
     * 删除图文
     * @param int $id
     * @return bool
     * @throws \App\Exceptions\RuntimeException
     */
    public function deleteImgTitle($id) {
        try{
            $rs = $this->db->deleteFrom('t_imgtitle')
                ->where(['id'=>$id])->exec();
            if($rs->rows<=0){
                throw $this->exception([
                    'code'=>ErrorConst::IMGTITLE_DEL_ERROR,
                    'text'=>"图文删除失败id".$id
                ]);
            }
            return true;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }


    /**
     * 获取单个商品详情
     * @param $id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getImgTitle($id)
    {
        try {
            $rs = $this->db->select('*')
                ->from('t_imgtitle')
                ->where(['id' => $id])
                ->getFirst();
            return $rs;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 图文编辑
     * @param ImgTitleEntity $imgTitleEntity
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function editImgTitle(ImgTitleEntity $imgTitleEntity){
        try{
            return $this->db->transaction(function(DB $db) use ($imgTitleEntity){
                $db->update('t_imgtitle')->set([
                    'img_title' => $imgTitleEntity->getImgTitle(),
                    'img_url' => $imgTitleEntity->getImgUrl(),
                    'img_path' => $imgTitleEntity->getImgpath(),
                    'img_type' => $imgTitleEntity->getImgType(),
                    'img_order' => $imgTitleEntity->getImgOrder(),
                    'status' => $imgTitleEntity->getStatus(),
                ])->where(['id'=>$imgTitleEntity->getId()])->exec();
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
     * 微信文章详情页
     * @param int $article_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function getArticleDetailForWx($article_id)
    {
        try {
            $joinRule = $this->db->select(
                't_article.*',
                DB::raw('t_product_img.img_path,t_product_img.lengths,t_product_img.width'),
                DB::raw('t_goods.selling_price,t_goods.num,t_goods.goods_name,t_goods.numlimit'),
                DB::raw('t_product.status as product_status,t_product.product_name')
                )
                ->from("t_article")
                ->leftJoin('t_article_product_relate')->on('t_article_product_relate.article_id=t_article.id')
                ->leftJoin('t_product')->on('t_product.id=t_article_product_relate.product_id')
                ->leftJoin('t_product_img')->on('t_product.id=t_product_img.product_id')
                ->leftJoin('t_goods')->on('t_product.id=t_goods.product_id')
                ->where([
                    't_article.id'=>  $article_id
                ])->getFirst();

            return $joinRule;

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }
}