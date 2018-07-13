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

    /**
     * 文章栏目 下的 文章列表
     * @param $columnID
     * @param $pageszie
     * @param $page
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function columnArticleList($columnID,$page, $pageszie)
    {
        try {
            $where = ['t_column_relate.column_id'=>$columnID];
            $order = [];
            $joinRule = $this->db->select(DB::raw('t_article.name,t_article_column_relate.id as relateID'))
                ->from('t_article_column_relate')
                ->leftJoin("t_article")->on('t_article.id = t_article_column_relate.article_id');
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
                $joinRule->orderBy('t_article_column_relate.add_time', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_article_column_relate.status', DB::ORDER_BY_DESC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);
            $pagination = new Pagination($joinRule, $page, $pageszie,$this->db);
            $data = $pagination->get();
            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 添加文章栏目 关联 文章
     * @param $columnId
     * @param $articleId
     * @return int
     * @throws \App\Exceptions\RuntimeException
     */
    public function columnAddArticle($columnId, $articleId)
    {
//        $exist = $this->columnCheckExist($name);
//        if(!is_null($exist)){
//            throw $this->exception([
//                'code'=>ErrorConst::DUPLICATE_INSERT,
//                'text'=>'文章栏目已经存在'.$name
//            ]);
//        }
        try {
            $lastId = $this->db->insertInto('t_article_column_relate')->values([
                'column_id'=>$columnId,
                'article_id'=>$articleId,
                'add_time' =>getCurrentTime()
            ])->exec()->lastInsertId();
            return $lastId;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }
    /**
     * 删除文章栏目 下属 文章
     * @param $id
     * @return int
     * @throws \App\Exceptions\RuntimeException
     */
    public function columnArticleDel($id)
    {
        try {
            $res = $this->db->deleteFrom('t_column_relate')->where(['id'=>$id])->exec()->rows;
            return $res;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 点击栏目more查询对应 文章分类下的 文章列表
     * @param $columnID
     * @param $categoryId
     * @param $pageszie
     * @param $page
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function columnCatgoryArticleList($categoryId = 0,$page, $pageszie)
    {
        try {
            $where = ['t_article.status'=>1,'t_product.status'=>1];
            $where['t_article.is_oss'] = 2;
//            if($columnID>0){
//                $where['t_article_column_relate.column_id'] = $columnID;
//
//            }
            $order = [];

            $joinRule = $this->db->select(DB::raw('t_article.name,
            t_article_product_relate.percent,t_article.id as articleId,t_product.id as productId'))
                ->from('t_article_product_relate')
                ->leftJoin("t_article")->on('t_article.id=t_article_product_relate.article_id')
                ->leftJoin("t_product")->on('t_product.id = t_article_product_relate.product_id')
            ;
            if($categoryId >0){
                $where['t_article_category_relate.category_id'] = $categoryId;
                $joinRule = $joinRule->leftJoin('t_article_category_relate')->on('t_article.id =t_article_category_relate.article_id');
            }

            if ($where) {
                $joinRule->where($where);
            }
//            if($columnID == 0){
//                $joinRule->orderBy('t_article_column_relate.id', DB::ORDER_BY_ASC);
//            }
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $joinRule->orderBy($key, $value);
                    }
                }
            } else {
                $joinRule->orderBy('t_article.sort', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_article.add_time', DB::ORDER_BY_DESC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);
            $pagination = new Pagination($joinRule, $page, $pageszie,$this->db);
            $data = $pagination->get();
            foreach ($data['data'] as $key=>$value){
                $percent = json_decode($value['percent'],true);
                $spread_percent = 0;
                $spread_account = 0;
                foreach ($percent as $k=>$v){
                    if($v['mode'] == 2){//自媒体推广
                        $spread_percent = $v['contents']['percent'];
                        $spread_account = $v['contents']['account'];
                        break;
                    }
                }

                $specifications = $this->db->select('t_goods.id,t_goods.num,t_goods.selling_price')
                    ->from("t_goods")
                    ->where([
                        't_goods.status'=>  1,
                        't_goods.product_id' => $value['productId']
                    ])->orderBy('t_goods.selling_price',DB::ORDER_BY_ASC)->get();
                $price_arr = [];
                foreach ($specifications as $k=>$v){
                    $price_arr[] = $v['selling_price'];
                }
                if(count($price_arr) == 1){
                    $data['data'][$key]['min_price'] = $data['data'][$key]['max_price'] = $price_arr[0];
                }else{
                    $data['data'][$key]['min_price'] = $price_arr[0];
                    $data['data'][$key]['max_price'] = $price_arr[count($price_arr)-1];
                }

                //最低价 * （自媒体推广） 佣金百分比   + 固定金额
                $data['data'][$key]['min_commission'] = bcadd(bcmul($data['data'][$key]['min_price'],$spread_percent/100),$spread_account);




                $data['data'][$key]['img_path285'] = '';
                $img = $this->db->select("*")->from('t_article_img')->where(['article_id'=>$value['articleId'],'status'=>1])->orderBy('t_article_img.orderby','asc')->getFirst();
                if(!is_null($img)){
                    $ori_path = $img['img_path'];

                    if(strstr('jianzhiwangluo.oss',$ori_path)){
                        $extension = explode('/',$ori_path);
                        $name = $extension[count($extension) -1];

                        $new_path285 = md5($name.'_285_285');
                        $new_path86 = md5($name.'_86_86');
                        $newpath285 = str_replace($name,$new_path285,$ori_path);
                    }else{
                        $newpath285 = $ori_path;
                    }
                    $data['data'][$key]['img_path285'] = $newpath285;
                }

                $data['data'][$key]['viewNums'] = 0;
                $viewNums = $this->db->select(DB::raw('ifnull(count(article_id),0) as viewNums'))
                    ->from("t_wx_article_info_view_quantity")
                    ->where([
                        'article_id' => $value['articleId']
                    ])->groupBy('article_id')->getFirst();
                if(!is_null($viewNums)){
                    $data['data'][$key]['viewNums'] = $viewNums['viewNums'];
                }


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
     * 最热/最新 文章栏目 下的 文章列表 前台展示
     * @param $categoryId
     * @param $pageszie
     * @param $page
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function columnListwithArticleList($categoryId = 0,$page, $pageszie)
    {
        try {
            $where = [];
//            $where = ['t_article.status'=>1,'t_product.status'=>1];


            $order = [];

            $joinRule = $this->db->select(DB::raw('t_article_column.id as ColumnId,t_article_column.name,t_article_column.article_category_id,t_industry.name as CategoryName'))
                ->from('t_article_column')
                ->leftJoin("t_industry")->on('t_industry.id = t_article_column.article_category_id')
            ;
//
//            $joinRule = $this->db->select(DB::raw('t_article.name,t_article_column_relate.id as relateID,
//            t_article_product_relate.percent,t_article.id as articleId,t_product.id as productId'))
//                ->from('t_article_column_relate')
//                ->leftJoin("t_article")->on('t_article.id = t_article_column_relate.article_id')
//                ->leftJoin("t_article_product_relate")->on('t_article.id = t_article_product_relate.article_id')
//                ->leftJoin("t_product")->on('t_product.id = t_article_product_relate.product_id')
////            ;
            if($categoryId >0){
                $where['t_article_column.article_category_id'] = $categoryId;
            }

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
                $joinRule->orderBy('t_article_column.id', DB::ORDER_BY_ASC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);
            $pagination = new Pagination($joinRule, $page, $pageszie,$this->db);
            $data = $pagination->get();
            foreach ($data['data'] as $key=>$value){

                $articleList = $this->db->select(DB::raw('t_article.name,t_article_column_relate.id as relateID,
                t_article_product_relate.percent,t_article.id as articleId,t_product.id as productId'))
                    ->from("t_article_column_relate")
                    ->leftJoin("t_article")->on('t_article.id = t_article_column_relate.article_id')
                    ->leftJoin("t_article_product_relate")->on('t_article.id = t_article_product_relate.article_id')
                    ->leftJoin("t_product")->on('t_product.id = t_article_product_relate.product_id')
                    ->where([
                        't_article_column_relate.column_id' => $value['ColumnId']
                    ])->get();

                if($value['ColumnId'] == 1 && count($articleList) == 0){
//                    var_dump($articleList);die;
                    $articleList = $this->getNewList()['data'];
                }

//                var_dump($articleList);die;
                $data['data'][$key]['list'] = $articleList;
                foreach ($articleList as $kk=>$vv){
                    $percent = json_decode($vv['percent'],true);
                    $spread_percent = 0;
                    $spread_account = 0;
                    foreach ($percent as $k=>$v){
                        if($v['mode'] == 2){//自媒体推广
                            $spread_percent = $v['contents']['percent'];
                            $spread_account = $v['contents']['account'];
                            break;
                        }
                    }

                    $specifications = $this->db->select('t_goods.id,t_goods.num,t_goods.selling_price')
                        ->from("t_goods")
                        ->where([
                            't_goods.status'=>  1,
                            't_goods.product_id' => $vv['productId']
                        ])->orderBy('t_goods.selling_price',DB::ORDER_BY_ASC)->get();
                    $price_arr = [];
                    foreach ($specifications as $k=>$v){
                        $price_arr[] = $v['selling_price'];
                    }
                    if(count($price_arr) == 1){
                        $data['data'][$key]['list'][$kk]['min_price'] = $data['data'][$key]['list'][$kk]['max_price'] = $price_arr[0];
//                        $data['data'][$key]['min_price'] = $data['data'][$key]['max_price'] = $price_arr[0];
                    }else{
                        $data['data'][$key]['list'][$kk]['min_price'] = $price_arr[0];
                        $data['data'][$key]['list'][$kk]['max_price'] = $price_arr[count($price_arr)-1];
//                        $data['data'][$key]['min_price'] = $price_arr[0];
//                        $data['data'][$key]['max_price'] = $price_arr[count($price_arr)-1];
                    }

                    //最低价 * （自媒体推广） 佣金百分比   + 固定金额
//                    $data['data'][$key]['min_commission'] = bcadd(bcmul($data['data'][$key]['min_price'],$spread_percent/100),$spread_account);
                    $data['data'][$key]['list'][$kk]['min_commission'] = bcadd(bcmul($data['data'][$key]['list'][$kk]['min_price'],$spread_percent/100),$spread_account);

//                    $data['data'][$key]['img_path285'] = '';
                    $data['data'][$key]['list'][$kk]['img_path285'] = '';
                    $img = $this->db->select("*")->from('t_article_img')->where(['article_id'=>$vv['articleId'],'status'=>1])->orderBy('t_article_img.orderby','asc')->getFirst();
                    if(!is_null($img)){
                        $ori_path = $img['img_path'];
                        if(strstr('jianzhiwangluo.oss',$ori_path)){
                            $extension = explode('/',$ori_path);
                            $name = $extension[count($extension) -1];

                            $new_path285 = md5($name.'_285_285');
                            $new_path86 = md5($name.'_86_86');
                            $newpath285 = str_replace($name,$new_path285,$ori_path);
                        }else{
                            $newpath285 = $ori_path;
                        }

//                        $data['data'][$key]['img_path285'] = $newpath285;
                        $data['data'][$key]['list'][$kk]['img_path285'] = $newpath285;
                    }

                    $data['data'][$key]['list'][$kk]['viewNums'] = 0;
//                    $data['data'][$key]['viewNums'] = 0;
                    $viewNums = $this->db->select(DB::raw('ifnull(count(article_id),0) as viewNums'))
                        ->from("t_wx_article_info_view_quantity")
                        ->where([
                            'article_id' => $vv['articleId']
                        ])->groupBy('article_id')->getFirst();
                    if(!is_null($viewNums)){
//                        $data['data'][$key]['viewNums'] = $viewNums['viewNums'];
                        $data['data'][$key]['list'][$kk]['viewNums'] = $viewNums['viewNums'];
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
    }

    /**
     * 单个栏目下 对应 文章列表
     * @param $columnID
     * @param $page
     * @param $pageszie
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function columnWithArticleList($columnID = 0,$page, $pageszie)
    {
        try {
            $where = ['t_article.status'=>1,'t_product.status'=>1];
            $where['t_article.is_oss'] = 2;
            if($columnID>0){
                $where['t_article_column_relate.column_id'] = $columnID;

            }
            $order = [];

            $joinRule = $this->db->select(DB::raw('t_article.name,t_article_column_relate.column_id as columnId,
            t_article_product_relate.percent,t_article.id as articleId,t_product.id as productId'))
                ->from('t_article_column_relate')
                ->leftJoin('t_article_product_relate')->on('t_article_column_relate.article_id=t_article_product_relate.article_id')
                ->leftJoin("t_article")->on('t_article.id=t_article_product_relate.article_id')
                ->leftJoin("t_product")->on('t_product.id = t_article_product_relate.product_id')
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
                $joinRule->orderBy('t_article.sort', DB::ORDER_BY_DESC);
                $joinRule->orderBy('t_article.add_time', DB::ORDER_BY_DESC);
            }
//            var_dump($joinRule->context->sql,$joinRule->context->params);
            $pagination = new Pagination($joinRule, $page, $pageszie,$this->db);
            $data = $pagination->get();
            if($data['count'] == 0 && $columnID == 1){
                $data = $this->getNewList();
            }

            foreach ($data['data'] as $key=>$value){
                $percent = json_decode($value['percent'],true);
                $spread_percent = 0;
                $spread_account = 0;
                foreach ($percent as $k=>$v){
                    if($v['mode'] == 2){//自媒体推广
                        $spread_percent = $v['contents']['percent'];
                        $spread_account = $v['contents']['account'];
                        break;
                    }
                }

                $specifications = $this->db->select('t_goods.id,t_goods.num,t_goods.selling_price')
                    ->from("t_goods")
                    ->where([
                        't_goods.status'=>  1,
                        't_goods.product_id' => $value['productId']
                    ])->orderBy('t_goods.selling_price',DB::ORDER_BY_ASC)->get();
                $price_arr = [];
                foreach ($specifications as $k=>$v){
                    $price_arr[] = $v['selling_price'];
                }
                if(count($price_arr) == 1){
                    $data['data'][$key]['min_price'] = $data['data'][$key]['max_price'] = $price_arr[0];
                }else{
                    $data['data'][$key]['min_price'] = $price_arr[0];
                    $data['data'][$key]['max_price'] = $price_arr[count($price_arr)-1];
                }

                //最低价 * （自媒体推广） 佣金百分比   + 固定金额
                $data['data'][$key]['min_commission'] = bcadd(bcmul($data['data'][$key]['min_price'],$spread_percent/100),$spread_account);




                $data['data'][$key]['img_path285'] = '';
                $img = $this->db->select("*")->from('t_article_img')->where(['article_id'=>$value['articleId'],'status'=>1])->orderBy('t_article_img.orderby','asc')->getFirst();
                if(!is_null($img)){
                    $ori_path = $img['img_path'];

                    if(strstr('jianzhiwangluo.oss',$ori_path)){
                        $extension = explode('/',$ori_path);
                        $name = $extension[count($extension) -1];

                        $new_path285 = md5($name.'_285_285');
                        $new_path86 = md5($name.'_86_86');
                        $newpath285 = str_replace($name,$new_path285,$ori_path);
                    }else{
                        $newpath285 = $ori_path;
                    }

                    $data['data'][$key]['img_path285'] = $newpath285;
                }

                $data['data'][$key]['viewNums'] = 0;
                $viewNums = $this->db->select(DB::raw('ifnull(count(article_id),0) as viewNums'))
                    ->from("t_wx_article_info_view_quantity")
                    ->where([
                        'article_id' => $value['articleId']
                    ])->groupBy('article_id')->getFirst();
                if(!is_null($viewNums)){
                    $data['data'][$key]['viewNums'] = $viewNums['viewNums'];
                }


            }
            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }
    public function getNewList(){
        $where = ['t_article.status'=>1,'t_product.status'=>1];
        $where['t_article.is_oss'] = 2;

        $joinRule = $this->db->select(DB::raw('t_article.name,
            t_article_product_relate.percent,t_article.id as articleId,t_product.id as productId'))
            ->from('t_article_product_relate')
            ->leftJoin("t_article")->on('t_article.id=t_article_product_relate.article_id')
            ->leftJoin("t_product")->on('t_product.id = t_article_product_relate.product_id')
        ;

        if ($where) {
            $joinRule->where($where);
        }
        $joinRule->orderBy('t_article.add_time', DB::ORDER_BY_DESC);
        $joinRule->orderBy('t_article.sort', DB::ORDER_BY_DESC);

        $pagination = new Pagination($joinRule, 1, 6,$this->db);
        $data = $pagination->get();
        return $data;
    }

    /**
     * 后台用户列表页面
     * @Get("/adminUserLists", as="s_user_adminUserLists")
     * @Post("/adminUserLists", as="s_user_adminUserLists")
     */
    public function adminUserLists(Request $request) {

        if ($request->ajax()) {

            $page = $request->get('page',1);
            $pageszie = $request->get('pageszie',10);
            $mobile = $request->get('mobile','');

            $joinRule = DB::table('t_admin_user')
                ->select('t_admin_user.*','t_admin_group.name')
                ->leftJoin('t_admin_group_user', 't_admin_group_user.user_id', '=', 't_admin_user.id')
                ->leftJoin('t_admin_group', 't_admin_group_user.group_id', '=', 't_admin_group.id');

            if(strlen($mobile)>0){
                $joinRule = $joinRule->where('mobile',$mobile);
            }
            $joinRule = $joinRule->orderBy('status','DESC');
            $joinRule = $joinRule->orderBy('add_time', 'DESC');
            $joinRule = $joinRule->paginate($pageszie,['*'],'page',$page);

            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $joinRule->total(),
                'iTotalDisplayRecords' => $joinRule->total(),
                'aaData' => $joinRule->items(),
            ];
            return new JsonResponse($return);
        }
        return view("Users.admimUserList");
    }

    /**
     * 删除后台用户提交
     * @Post("/delAdminUser", as="s_user_delAdminUser")
     */
    public function delAdminUser(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $rs = DB::table('t_admin_user')->where('id',$request->get('id'))->delete();
            if($rs){
                return new JsonResponse(['msg'=>'ok','status'=>200]);
            }else{
                return new JsonResponse(['msg'=>'删除失败','status'=>201]);
            }
        }
        return false;
    }

    /**
     * 添加后台用户页面
     * @Get("/adminUserAdd", as="s_user_adminUserAdd")
     */
    public function adminUserAdd(Request $request) {
        return view("Users.admimUserAdd");
    }

    /**
     * 添加后台用户提交
     * @Post("/adminUserAddPost", as="s_user_adminUserAddPost")
     */
    public function adminUserAddPost(Request $request) {

        $this->validate($request, [
            'mobile' => 'required|unique:t_admin_user',
            'password' => 'required'
        ]);
        $now = getCurrentTime();
        $rs = DB::table('t_admin_user')->insert([
            'mobile'=> $request->get('mobile'),
            'password' => Hash::make($request->get('password')),
            'register_time' =>$now,
            'add_time' => $now
        ]);

        if($rs){
            return redirect(route('s_user_adminUserLists'))->with('addsuccess', 'success');
        }else{
            return back()->withErrors($rs['message']);
        }
    }
    /**
     * 修改后台用户密码提交
     * @Post("/adminUserChangePwd", as="s_user_adminUserChangePwd")
     */
    public function adminUserChangePwd(Request $request) {
        $this->validate($request, [
            'id' => 'required',
            'password'=> 'required'
        ]);
        $now = getCurrentTime();
        $rs = DB::table('t_admin_user')->where('id',$request->get('id'))->update([
            'password' => Hash::make($request->get('password')),
        ]);

        if($rs){
            return redirect(route('s_user_adminUserLists'))->with('addsuccess', 'success');
        }else{
            return back()->withErrors($rs['message']);
        }
    }
    /**
     * 编辑后台用户页面
     * @Get("/adminUserEdit", as="s_user_adminUserEdit")
     */
    public function adminUserEdit(Request $request) {
        $user = \Auth::user();

        $adminself = $request->get('admin',false);
        if($adminself && $user->id == 1){
            $id = 1;
        }else{
            $id = $request->get('uid',-1);
        }
//        $res = DB::table('t_admin_user')->select('*')->where('id',$id)->first();
        return view("Users.admimUserEdit")->with('id',$id);
    }

    /**
     * 添加后台用户提交
     * @Post("/adminUserEditPost", as="s_user_adminUserEditPost")
     */
    public function adminUserEditPost(Request $request) {
        $this->validate($request, [
            'password_confirmation' => 'required',
            'password' => 'required|confirmed',
            'id'=> 'required'
        ]);
//        $res = DB::table('t_admin_user')->select('*')->where('id',$request->get('id'))->first();
//        if(!Hash::check($request->get('oldpassword'),$res->password)){
//            return back()->withInput()->withErrors(['原密码输入错误']);
//        }

        $rs = DB::table('t_admin_user')
            ->where('id',$request->get('id'))
            ->update([
                'password' => Hash::make($request->get('password')),
            ]);
        if($rs){
            return redirect(route('s_user_adminUserLists'))->with('addsuccess', 'success');
        }else{
            return back()->withInput()->withErrors(['密码更新失败']);
        }
    }

    /**
     * 组权限添加页面
     * @Get("/adminGroupAdd", as="s_user_adminGroupAdd")
     */
    public function adminGroupAdd(Request $request) {
        $powerList = DB::table('t_admin_power')->select('*')->where('status',1)->get();
        return view("Powrer.powerUserGroupAdd")->with('powerList',$powerList);
    }

    /**
     * 组权限添加提交
     * @Post("/adminGroupAddPost", as="s_user_adminGroupAddPost")
     */
    public function adminGroupAddPost(Request $request) {
        $this->validate($request, [
            'name' => 'required|unique:t_admin_group',
            'powerlist' => 'required',

        ]);
        $now = getCurrentTime();
        $name = $request->get('name');
        $powerlist = $request->get('powerlist');

        DB::beginTransaction();
        $groupAddId = DB::table('t_admin_group')->insertGetId([
            'name'=> $name,
            'add_time' =>$now
        ]);

        foreach ($powerlist as $power) {
            $rs = DB::table('t_admin_group_power')->insertGetId([
                'power_id'=> $power,
                'group_id' => $groupAddId,
                'add_time' =>$now
            ]);
            if($rs<=0){
                DB::rollBack();
                return back()->withInput()->withErrors(['权限组创建失败']);
            }
        }

        DB::commit();

        if($rs){
            return redirect(route('s_user_adminGroupLists'))->with('addsuccess', 'success');
        }else{
            return back()->withInput()->withErrors(['权限组创建失败']);
        }
    }

    /**
     * 权限用户所属组编辑页面
     * @Get("/adminUserGroupEdit", as="s_user_adminUserGroupEdit")
     */
    public function adminUserGroupEdit(Request $request) {
        $uid = $request->get('uid',-1);
        if(!$uid){
            return redirect(route('s_user_adminUserLists'));
        }
        $groupList = DB::table('t_admin_group')->select('*')->where('status',1)->get();
        $groupuser = DB::table('t_admin_group_user')->where('status',1)->where('user_id',$uid)->first();
        $gid = -1;
        if($groupuser){
            $gid = $groupuser->group_id;
        }
        return view("Powrer.powerUserGroupEdit")->with('groupList',$groupList)->with('uid',$uid)->with('gid',$gid);
    }

    /**
     * 权限用户所属组编辑提交
     * @Post("/adminUserGroupEditPost", as="s_user_adminUserGroupEditPost")
     */
    public function adminUserGroupEditPost(Request $request) {
        $this->validate($request, [
            'group' => 'required',
            'uid' => 'required'
        ]);
        $group = $request->get('group');
        if($group){
            $now = getCurrentTime();
            $rs = DB::table('t_admin_group_user')->where('user_id',$request->get('uid'))->delete();
            $rs = DB::table('t_admin_group_user')->insertGetId([
                'user_id'=> $request->get('uid'),
                'group_id' => $group,
                'add_time' =>$now
            ]);
        }else{
            //del
            $rs = DB::table('t_admin_group_user')->where('user_id',$request->get('uid'))->delete();
        }
        if($rs){
            return redirect(route('s_user_adminUserLists'))->with('addsuccess', 'success');
        }
        return false;
    }

    /**
     * 权限组列表页面
     * @Get("/adminGroupLists", as="s_user_adminGroupLists")
     * @Post("/adminGroupLists", as="s_user_adminGroupLists")
     */
    public function adminGroupLists(Request $request) {
        if ($request->ajax()) {

            $page = $request->get('page',1);
            $pageszie = $request->get('pageszie',10);
            $name = $request->get('name','');

            $joinRule = DB::table('t_admin_group');

            if(strlen($name)>0){
                $joinRule = $joinRule->where('name','like','%'.$name.'%');
            }
            $joinRule = $joinRule->orderBy('status','DESC');
            $joinRule = $joinRule->orderBy('add_time', 'DESC');
            $joinRule = $joinRule->paginate($pageszie,['*'],'page',$page);

            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $joinRule->total(),
                'iTotalDisplayRecords' => $joinRule->total(),
                'aaData' => $joinRule->items(),
            ];
            return new JsonResponse($return);
        }
        return view("Powrer.powerGroupList");
    }

    /**
     * 删除权限组提交
     * @Post("/delAdminGroupPost", as="s_user_delAdminGroupPost")
     */
    public function delAdminGroupPost(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            DB::beginTransaction();
            $rs = DB::table('t_admin_group')->where('id',$request->get('id'))->delete();
            $rs = DB::table('t_admin_group_user')->where('group_id',$request->get('id'))->delete();
            $rs = DB::table('t_admin_group_power')->where('group_id',$request->get('id'))->delete();
            DB::commit();
            if($rs){
                return new JsonResponse(['msg'=>'ok','status'=>200]);
            }else{
                return new JsonResponse(['msg'=>'删除失败','status'=>201]);
            }
        }
        return false;
    }

    /**
     * 组编辑页面
     * @Get("/adminGroupEdit", as="s_user_adminGroupEdit")
     */
    public function adminGroupEdit(Request $request) {
        $gid = $request->get('gid',-1);
        if(!$gid){
            return redirect(route('s_user_adminUserLists'));
        }
        $choosedPowerList = DB::table('t_admin_group_power')->where('status',1)->where('group_id',$gid)->pluck('power_id')->Toarray();
        $res = DB::table('t_admin_group')->where('status',1)->where('id',$gid)->first();

        $powerList = DB::table('t_admin_power')->select('*')->where('status',1)->get();
        return view("Powrer.powerGroupEdit")->with('res',$res)->with('choosedPowerList',$choosedPowerList)
            ->with('gid',$gid)->with('powerList',$powerList);
    }
    /**
     * 组编辑页面提交
     * @Post("/adminGroupEditPost", as="s_user_adminGroupEditPost")
     */
    public function adminGroupEditPost(Request $request) {
        $this->validate($request, [
            'id' => 'required',
            'name' => 'required',
            'powerlist' => 'required',
        ]);
        $id = $request->get('id');
        $name = $request->get('name');
        $powerList = $request->get('powerlist');

        DB::beginTransaction();
        $now = getCurrentTime();

        $rs = DB::table('t_admin_group')
            ->where('id',$id)
            ->update([
                'name' => $name,
            ]);
//        if($rs<=0){
//            DB::rollBack();
//            return back()->withInput()->withErrors(['名称更新失败']);
//        }
        $res = DB::table('t_admin_group_power')->where('group_id',$id)->delete();

        foreach ($powerList as $power) {
            $rs = DB::table('t_admin_group_power')->insertGetId([
                'power_id'=> $power,
                'group_id' => $id,
                'add_time' =>$now
            ]);
            if($rs<=0){
                DB::rollBack();
                return back()->withInput()->withErrors(['权限更新失败']);
            }
        }

        DB::commit();

        return redirect(route('s_user_adminGroupLists'))->with('addsuccess', 'success');
    }

    /**
     * 绑定关联
     * @Get("/innerBindRel", as="s_user_innerBindRel")
     * @Post("/innerBindRel", as="s_user_innerBindRel")
     */
    public function innerBindRel(Request $request) {

        if ($request->ajax()) {

            $invitedUid = $request->get('invitedUid',0);
            $inviteUid = $request->get('inviteUid',0);
            if(!intval($invitedUid) || $invitedUid<=0 || !intval($inviteUid) || $inviteUid<=0){
                return new JsonResponse(['status' => 404, 'msg' => '参数传递非法']);
            }
            try {
                $post = Curl::post('/user/bindInviteUser', ['uid' => $invitedUid, 'invite_uid' => $inviteUid]);
            }catch (\Exception $e) {
                return new JsonResponse(['status' => 403, 'msg' => '请求失败请重试']);
            }
            return new JsonResponse($post);
        }
        return view("Users.innerBindRel");
    }

    /**
     * 昵称搜索用户
     * @Post("/nickSearchUser", as="s_user_nickSearchUser")
     *
     */
    public function nickSearchUser(Request $request){

        $nickname = $request->get('nickname','');
        if(strlen($nickname)>0){
            $arr = [
                'nickname' =>   $nickname,
            ];
            $userList = Curl::post('/user/nickSearchUser', $arr);
            foreach ($userList['data']['data'] as $key =>$val){
                $userList['data']['data'][$key]['id'] = $val['uid'];
            }
            return new JsonResponse($userList);
        }
    }

    /**
     * 内部员工信息
     * @Get("/innerUserList", as="s_user_innerUserList")
     * @Post("/innerUserList", as="s_user_innerUserList")
     */
    public function innerUserList(Request $request) {

        if ($request->ajax()) {
            $data = Curl::post('/user/employeeList',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'name'=>$request->get('name', 0),
                ]
            );
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }
        return view("Users.innerUserList");
    }

    /**
     * 添加内部员工信息
     * @Get("/employeeAdd", as="s_user_employeeAdd")
     */
    public function employeeAdd(Request $request) {
        return view("Users.employeeAdd");
    }

    /**
     * @Post("/addEmployee", as="s_user_addEmployee")
     */
    public function addEmployee(Request $request) {

        $this->validate($request, [
            'name' => 'required',
        ]);
        $name = $request->get('name');
        $mobile = $request->get('mobile','');
        $uid = $request->get('uid','');

        if(is_null($uid)){
            $uid = 0;
        }
        if(is_null($mobile)){
            $mobile = '';
        }
//            dd([
//                'name' => $name,
//                'mobile'=>$mobile,
//                'uid'=>$uid
//            ]);
        $data = Curl::post('/user/addEmployee',
            [
                'name' => $name,
                'mobile'=>$mobile,
                'uid'=>$uid
            ]
        );
        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{

            return redirect(route('s_user_innerUserList'))->with('addsuccess', 'success');
        }

    }

    /**
     * 被邀请用户列表
     * @Get("/employeeInviteUserList", as="s_user_employeeInviteUserList")
     * @Post("/employeeInviteUserList", as="s_user_employeeInviteUserList")
     */
    public function innerInviteList(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/user/employeeInviteUserList',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'uid'=>$request->get('uid', -1),
                ]
            );
//            dd($data);
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }

        return view("Users.employeeInviteUserList")->with('uid',$request->get('uid', 0));
    }

    /**
     * 被邀请用户列表
     * @Get("/cooperateList", as="s_user_cooperateList")
     * @Post("/cooperateList", as="s_user_cooperateList")
     */
    public function cooperateList(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/user/getbusinessCooperate',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'name'=>$request->get('name', ''),
                ]
            );
//            dd($data);
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }

        return view("Users.cooperate");
    }

}