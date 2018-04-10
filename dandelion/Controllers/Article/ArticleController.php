<?php

namespace App\Http\Controllers\Article;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ProductController
 * @Controller(prefix="/Article")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class ArticleController extends Controller
{

    /**
     * @Get("/lists", as="s_article_lists")
     */
    public function lists(Request $request) {
        $current_page = $request->get('page',1);
        $key = "Article.newLists.".md5(json_encode($request->all()));
        if(!intval($current_page)){
            $current_page = 1;
        }

        if (! $cacheData = \Cache::get($key)) {
            try {
                $articlecategorylist = Curl::post('/article/getArticleCategoryList', ['status' => 1]);
                $articlecategorylist = $articlecategorylist['data']['data'];
            } catch (\Exception $e) {
                $articlecategorylist = [];
            }

            $category_id = $request->get('cate', false);
            $where['t_article.status'] = 1;//只显示上架的文章

            if ($category_id && intval($category_id) && $category_id > 0) {
                $where['t_article_category_relate.category_id'] = $category_id;
            } else {
                $category_id = -1;
            }

            $where = json_encode($where);
            $prepear = ['pagesize' => 10, 'page' => $current_page, 'wheres' => $where, 'order' => json_encode(['t_article.add_time' => "DESC"])];
            $articlelist = Curl::post('/article/getArticleWithProductList', $prepear);
            $pageList = false;

            if (isset($articlelist['data']['page_count']) && $articlelist['data']['count'] > 0) {
                $pageList = getPageList($articlelist['data']['page_count'], $current_page);
                $articlelist = $articlelist['data']['data'];
            } else {
                $articlelist = [];
            }

            //上周排行
            try {
                $lastweekrank = Curl::post('/article/getArticleRank');
//            $lastweekrank = Curl::post('/article/getArticleRank',['lastweek'=>1]);
                $lastweekrank = $lastweekrank['data'];

            } catch (ApiException $e) {
                $lastweekrank = [];

            }
            //扫码降序 热门文章
            try {
                $lastweekhotrank = Curl::post('/article/getArticleHotRank');
                $lastweekhotrank = $lastweekhotrank['data'];

            } catch (ApiException $e) {
                $lastweekhotrank = [];

            }
            \Cache::put("Article.newLists.".md5(json_encode($request->all())), json_encode([
                $lastweekrank,
                $lastweekhotrank,
                $current_page,
                $articlelist,
                $pageList,
                $category_id,
                $articlecategorylist
            ]), 20);
        } else {
            $cacheData = json_decode($cacheData, true);
            $lastweekrank = $cacheData[0];
            $lastweekhotrank = $cacheData[1];
            $current_page = $cacheData[2];
            $articlelist = $cacheData[3];
            $pageList = $cacheData[4];
            $category_id = $cacheData[5];
            $articlecategorylist = $cacheData[6];
        }
        $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';


        return view("Article.newLists")
            ->with('lastweekrank',$lastweekrank)
            ->with('lastweekhotrank',$lastweekhotrank)
            ->with('current_page', $current_page)
            ->with('articlelist', $articlelist)
            ->with('pageList', $pageList)
            ->with('category_id', $category_id)
            ->with('articlecategorylist', $articlecategorylist)
            ->with('user', $user);
    }

    /**
     * @Get("/listdetail", as="s_article_listdetail")
     */
    public function listdetail(Request $request) {
        $id = $request->get('id',-1);
        if(!$id || is_null($id) || !intval($id)){
            return redirect(route('s_article_lists'));
        }
        $article = Curl::post('/article/getArticle',['articleid'=>$id]);
        if(is_null($article['data'])){
            return redirect(route('s_article_lists'));
        }
        $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';
        $percents = json_decode($article['data']['percent'],true);
        $percent = 0;
        foreach ($percents as $item) {
            if($item['mode'] == 2){

                if(count($item['contents']) >1){
//                    $percent = json_encode($item['contents']);
                    if($item['contents']['percent']>0){
                        $percent = $item['contents']['percent'].'%';
                    }elseif($item['contents']['account'] >0){
                        $percent = $item['contents']['account'].'元';
                    }else{
                        $percent = '0元';
                    }
                }else{
                    if(key_exists('percent',$item['contents'])){
                        $percent = $item['contents']['percent'].'%';
                    }else{
                        $percent = $item['contents']['account'].'元';
                    }
                }
                break;
            }
        }
        $article['data']['channlepercent'] = $percent;
        $article['data']['content'] = html_entity_decode($article['data']['content']);

        //扫码降序 热门文章
        try {
            $lastweekhotrank = Curl::post('/article/getArticleHotRank');
            $lastweekhotrank = $lastweekhotrank['data'];

        } catch (ApiException $e) {
            $lastweekhotrank = [];

        }

        $jzstate = \Session:: get('pc_jzstate');
        if(!$jzstate){
            $code = Curl::post('/weixin/createCode');
            $jzstate  = ($code['data']);
            \Session::put("pc_jzstate", $jzstate);
        }
        
        if( $user){

            try {
                $post = Curl::post('/user/createSpreadQRcode', [
                    'aprs' => $article['data']['article_product_relateId'],
                    'spreadUid' => $user
                ]);
                $url = config('params.wx_host') . 'Article/articleInfo?spreadid=' . $post['data']['id'] .'&nid='.$post['data']['order_no'];
            } catch (ApiException $e) {
                $url = config('params.wx_host') . 'Article/articleInfo?article_id=$id';
            }

        }else{
            $url = config('params.wx_host') . 'Article/articleInfo?article_id=$id';
        }


        return view("Article.newListdetail")
            ->with('lastweekhotrank',$lastweekhotrank)
            ->with('article',$article['data'])
            ->with('user', $user)->with('pc_jzstate', $jzstate)->with('url', $url);
    }

}