<?php

namespace App\Http\Controllers\Article;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use App\Services\OSS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use SocialiteProviders\Weixin\WeixinExtendSocialite;

/**
 * Class ArticleController
 * @Controller(prefix="/Article")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class ArticleController extends Controller
{
    /**
     * 文章详情信息
     * @Get("/detailData", as="s_article_detailData")
     */
    public function detailData(Request $request) {
        $id = $request->get('id');
        
        $errorResponseArr = [
            'status' => '404',
            'message' => ''
        ];

        if($id && (int)$id <= 0 ){
            return new JsonResponse($errorResponseArr);
        }

        try {
            $article = Curl::post('/article/getArticle',['articleid'=>(int)$id,'status'=>'1,3,5']);
        } catch (ApiException $ex) {
            return new JsonResponse([
                "status"=>$ex->getCode(),
                "message"=>$ex->getMessage(),
            ]);
        }
        
        if(empty($article['data'])){
            return new JsonResponse($errorResponseArr);
        }

        if($article['data']['img_path']){
            $article['data']['img_path'] = $this->imageShow($article['data']['img_path'], 100, 75);
        }

        //140
        try {
            $goods = Curl::post('/product/getGoodsShow', $arr = [
                'product_id' => $article['data']['product_id']
            ]);
            
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
        if(empty($goods['data'])){
            return new JsonResponse($errorResponseArr);
        }

        $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';
        
        $commissionPercent = $article['data']['commissionPercent'];



        //计算推广获得奖励
        $article['data']['min_commission'] = isset($goods['data']['zmt_min_commission'])?$goods['data']['zmt_min_commission']:'0.00';

        $article['data']['max_commission'] = isset($goods['data']['zmt_max_commission'])?$goods['data']['zmt_max_commission']:'0.00';

        //$article['data']['min_commission'] = number_format(($commissionPercent['2']['percent'] * $goods['data']['min_price'] / 100) + $commissionPercent['2']['account'], 2);
       // $article['data']['max_commission'] = number_format(($commissionPercent['2']['percent'] * $goods['data']['max_price'] / 100) + $commissionPercent['2']['account'], 2);



        $article['data']['content'] = html_entity_decode($article['data']['content']);

        if($goods['data']['expiration_time']==0){
            $goods['data']['syts'] = "不限时";
        }else{
            $goods['data']['syts'] = $this->ShengYu_Tian_Shi_Fen($goods['data']['expiration_time']);
        }

//        try {
//            //统计 查看页面次数
//            Curl::post('/weixin/addArticleQuantity', $arr = [
//                'article_id' => $id,
//                'uid' => $user ? $user : 0
//            ]);
//        } catch (ApiException $e) {
//
//        }

        //查看页面 次数统计
        try{
            Curl::post('/weixin/addArticleInfoViewQuantity',['article_id'=>$id,'uid'=>$user ? $user : 0,'open_id'=>0]);

        }catch (ApiException $e){

        }



        $ori_path = $goods['data']['img_path'];
        $extension = explode('/',$ori_path);
        $name = $extension[count($extension) -1];

        $new_path325 = md5($name.'_325_325');
        $goods['data']['new_path325'] = str_replace($name,$new_path325,$ori_path);

        $rand = $request->get('rand','');

        $goods['data']['img_path'] = $this->imageShow($goods['data']['img_path'], 330, 230);
        $respData = $article;
        $respData['data'] = [];
        $respData['data']['article'] = $article['data'];
        $respData['data']['goods'] = $goods['data'];
        return new JsonResponse($respData);
    }



    /**
     * 推广码需要的数据
     * @Get("/getSpreadQRcodeData", as="s_article_getSpreadQRcodeData")
     */
    public function getSpreadQRcodeData(Request $request) {
        $errorResponseArr = [
            'status' => '404',
            'message' => ''
        ];

        $aprs = $request->get("aprs");

        if(!$aprs){
            $post = array('status'=>400);
            return new JsonResponse($post);
        }
        
//        $token = time().rand(10000, 90000);
//        $key = "weChatAjax" . \Session::getId();
//        \Cache::forget($key);
//        \Cache::add($key, $token, 60);


        //没登录时 点击购买 用uid=1
        if($request->get('default_user') == 1){
            $uid = 1;
        }else{
            $isLogin = \Auth::getUser();
            if(!$isLogin) {
                return new JsonResponse($errorResponseArr);
            }
            $uid = $this->getUserId();
        }

        
        //create
        try {
            $aid = $request->get("aid",-1);
            $aprs = $request->get("aprs",-1)*1;
            $article_id = $request->get("article_id",-1);
            if (DB::table('t_article')->where(['id' => $article_id])->value('status') == 5) return new JsonResponse(['status' => 400]);
            if(!is_int($aprs)){
                $post = array('status'=>400);
                return new JsonResponse($post);
            }
            if(intval($aid) && $aid >0){
                $post = Curl::post('/user/createSpreadQRcode', [
                    'aprs' => intval($aprs),
                    'spreadUid' => $uid
                ]);
                $buyUrl = '';
//                $data = $post['data'];
                if($post['status']==200){
                    if ($post['data']['stauts'] == 5) {
                        return new JsonResponse(['status' => 400, 'message' => '']);
                    }
                    $buyUrl = config('params.wx_host').'User/productDetail?spreadid='.$post['data']['id'] .'&nid='.$post['data']['order_no'];

                    $pc_spread_scan = $aid.$aprs.$uid.time();
                    \Cache::put($pc_spread_scan,'wait',10);
                    $post['data']['articleurl'] = config('params.wx_host') . 'Article/articleInfo?spreadid=' . $post['data']['id'] . '&nid=' . $post['data']['order_no'].'&pc_spread_scan='.$pc_spread_scan;
                    $post['data']['pc_spread_scan'] = $pc_spread_scan;
                    //$post['data']['url'] = file_get_contents('http://suo.im/api.php?url='.urlencode($post['data']['url']));
                    $post['data']['articleurl'] = shortUrl($post['data']['articleurl']);
                }


                if(config('params.is_share')){
                    $openPlatform = \Wechat::openPlatform('default');
                    $html = config('params.weixin_callback');//
                    $openUrl = $openPlatform->getPreAuthorizationUrl($html.'?id='.$aprs.'&url='.urlencode($buyUrl)); // 传入回调URI即可
                }else{
                    $openUrl = 'javascript:;';
                }

                $post['data']['openUrl'] = $openUrl;
                $post['data']['buyUrl'] = $buyUrl;
                if($post['data']['product_type'] == 3) {
                    $landing_page = $post['data']['landing_page'];
                    if (strstr($landing_page, '?')) $landing_page .= '&spreadid=' . $post['data']['id']. '&nid=' . $post['data']['order_no'].'&pc_spread_scan='.$pc_spread_scan.'&article_id='.$aid;
                    else $landing_page .= '?spreadid=' . $post['data']['id']. '&nid=' . $post['data']['order_no'].'&pc_spread_scan='.$pc_spread_scan.'&article_id='.$aid;
                    $post['data']['buyUrl'] = shortUrl($landing_page . '&product_id=' . $post['data']['productId']);
                }
    

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
     * 轮询查看是否扫码
     * @Post("/getWxsacn", as="s_article_getWxsacn")
     */
    public function getWxsacn(Request $request) {
        try {
            $pc_spread_scan = $request->get('pc_spread_scan');
            if($val = \Cache::get($pc_spread_scan)){
               if($val == 'success'){
                   return new JsonResponse([
                       "status"=>200,
                       "message"=>'success',
                   ]);
               }
            }
            return new JsonResponse([
                "status"=>201,
                "message"=>'wait',
            ]);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 文章列表页面
     * @Get("/lists", as="s_aricle_lists")
     * @param Request $request
     * @return mixed
     */
    public function lists(Request $request) {
        return view("Article.lists");
    }
    
    /**
     * 文章more页面
     * @Get("/moreLists", as="s_article_moreLists")
     * @param Request $request
     * @return mixed
     */
    public function moreLists(Request $request) {

        return view("Article.moreLists");
    }

    /**
     * 文章search页面
     * @Get("/search", as="s_article_search")
     * @param Request $request
     * @return mixed
     */
    public function search(Request $request) {

        return view("Article.search");
    }

    /**
     * 文章详情页面
     * @Get("/detail", as="s_aricle_detail")
     */
    public function detail0521(Request $request) {
        return view("Article.detail");
    }

    /**
     * 文章搜索页面
     * @Get("/searchArticle", as="s_article_searchArticle")
     * @param Request $request
     * @return mixed
     */
    public function searchArticle(Request $request) {
        return view("Article.searchArticle");
    }

    /**
     * 文章详情页面(旧)
     * @param Request $request
     * @return mixed
     * @throws ApiException
     */
    public function detail(Request $request) {
        $id = $request->get('id');

        if(!$id || is_null($id) || !intval($id)){
            return redirect(route('s_aricle_lists'));
        }
        $article = Curl::post('/article/getArticle',['articleid'=>$id,'status'=>1]);

        if(is_null($article['data'])){
            $status = 404;
            if (view()->exists("errors.{$status}")){
                return response()->view("errors.{$status}", [], $status);
            }

            return redirect(route('s_article_lists'));
        }

        //140
        try {
            $goods = Curl::post('/product/getGoodsShow', $arr = [
                'product_id' => $article['data']['product_id']
            ]);
            if(empty($goods['data'])){
                return response()->view("errors.404", [], 404);
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }


        $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';
        $percents = json_decode($article['data']['percent'],true);
        $percent = 0;
        $way = 'percent';

        //广告主
        $adverOwnerTake = 0;
        foreach ($percents as $item) {
            if($item['mode'] == 2){
                if(count($item['contents']) >1){
//                    $percent = json_encode($item['contents']);
                    if($item['contents']['percent']>0){
                        $percent = $item['contents']['percent'];
                    }elseif($item['contents']['account'] >0){
                        $percent = $item['contents']['account'];
                        $way = 'account';
                    }else{
                        $percent = '0';
                    }
                }else{
                    if(key_exists('percent',$item['contents'])){
                        $percent = $item['contents']['percent'];
                    }else{
                        $percent = $item['contents']['account'];
                        $way = 'account';
                    }
                }
            }

            //mode ==3  网站为0  7为广告主分成
            if($item['mode'] == 7){
                if($item['contents']['percent']>0){
                    $adverOwnerTake = $item['contents']['percent'] * $goods['data']['selling_price'] /100;
                }elseif($item['contents']['account'] >0){
                    $adverOwnerTake = $item['contents']['account'];

                }else{
                    $adverOwnerTake = 0;
                }
            }
        }

        $article['data']['channlepercent'] = $percent;
        $article['data']['channleway'] = $way;
        $article['data']['afterchannlepercent'] = 0;
        $article['data']['content'] = html_entity_decode($article['data']['content']);



        //是百分比分成 则计算推广后获得的额度
        if($way == 'percent' && $percent > 0){
            $siteTake = $goods['data']['selling_price'] - $adverOwnerTake;

            $article['data']['afterchannlepercent'] = $article['data']['channlepercent'] * $siteTake /100;
        }



        //扫码降序 热门文章
        try {
            $lastweekhotrank = Curl::post('/article/getArticleHotRank');
            $lastweekhotrank = $lastweekhotrank['data'];

            foreach ($lastweekhotrank['data'] as $key => $val){
//                var_dump($val);die;
                $lastweekhotrank['data'][$key]['percentKey'] = (isset($val['percent_arr']['mode_2']['percent']) && $val['percent_arr']['mode_2']['percent']>0)?
                    number_format($val['selling_price'] * $val['percent_arr']['mode_2']['percent']/100,2)  : number_format($val['percent_arr']['mode_2']['account'],2);
            }
            $lastweekhotrank = $lastweekhotrank['data'];
        } catch (ApiException $e) {
            $lastweekhotrank = [];
        }

        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);
        \Session::put("pc_jzstate", $jzstate);
        \Cache::put("wxdl_".$jzstate, $jzstate,30);

        $qRcode = 2;

        $need_select = 0;
        if(\Auth::getUser()){
            $uid = $this->getUserId();
            try {
                $post = Curl::post('/user/selectSpreadQRcode', [
                    'aprs' => $article['data']['article_product_relateId'],
                    'spreadUid' => $uid,
                ]);
                $qRcode = $post['data'];//该用户已推广过 return 1 else 2
            } catch (ApiException $e) {
                $qRcode = 2;
            }

            $chooseinfo = Curl::post('/article/getCategoryChose', [
                'uid' => $uid,
            ]);
//            $info['data']['id'];
            if(empty($chooseinfo['data'])){
                $need_select = 1;
            }else{

            }
        }

        $buyUrl = '';
        if( $user){
            if($qRcode==2){
                $url = config('params.wx_host') . 'Article/articleInfo?article_id='.$id;
            }else {
                try {
//                    var_dump(1);
                    $post = Curl::post('/user/createSpreadQRcode', [
                        'aprs' => $article['data']['article_product_relateId'],
                        'spreadUid' => $user
                    ]);
                    $url = config('params.wx_host') . 'Article/articleInfo?spreadid=' . $post['data']['id'] . '&nid=' . $post['data']['order_no'];
                    $buyUrl = config('params.wx_host') . 'User/productDetail?spreadid=' . $post['data']['id'] . '&nid=' . $post['data']['order_no'];
                } catch (ApiException $e) {
                    $url = config('params.wx_host') . 'Article/articleInfo?article_id='.$id;
                }
            }

        }else{
            $url = config('params.wx_host') . 'Article/articleInfo?article_id='.$id;
        }

        if(config('params.is_share')){
            $openPlatform = \Wechat::openPlatform('default');
            $html = config('params.weixin_callback');//
            $openUrl = $openPlatform->getPreAuthorizationUrl($html.'?id='.$id.'&url='.urlencode($buyUrl)); // 传入回调URI即可
        }else{
            $openUrl = 'javascript:;';
        }

        $token = time().rand(10000, 90000);
        $key = "weChatAjax" . \Session::getId();
        \Cache::forget($key);
        \Cache::add($key, $token, 60);

        $wxHost = config('params.wx_host');

        if($goods['data']['expiration_time']==0){
            $goods['data']['syts'] = "不限时";
        }else{
            $goods['data']['syts'] = $this->ShengYu_Tian_Shi_Fen($goods['data']['expiration_time']);
        }

        $article_category = [];
        if(\Auth::getUser()) {
            $uid = $this->getUserId();
            try {
                $checkChose = $post = Curl::post('/industry/getIndustryUser',
                    [
                        'uid' => $uid,//74
                    ]
                );
                if(empty($checkChose['data'])){
                    $article_category = getRedisData('articleCategoryWrite'.$id,'/industryCategory/getIndustryCategoryListWithIndustry',['type'=>2]);
                    $article_category = $article_category['data']['data'];
                }else{
                    $article_category = [];
                }

            } catch (ApiException $e) {
                $article_category = [];
            }
        }


        try {
            //统计 查看页面次数
            Curl::post('/weixin/addArticleQuantity', $arr = [
                'article_id' => $id,
                'uid' => $user ? $user : 0
            ]);
        } catch (ApiException $e) {

        }


        $ori_path = $goods['data']['img_path'];
        $extension = explode('/',$ori_path);
        $name = $extension[count($extension) -1];

        $new_path325 = md5($name.'_325_325');
        $goods['data']['new_path325'] = str_replace($name,$new_path325,$ori_path);

        $rand = $request->get('rand','');
        return view("Article.detail")->with('lastweekhotrank',$lastweekhotrank)
            ->with('article',$article['data'])
            ->with('user', $user)
            ->with('pc_jzstate', $jzstate)
            ->with('url', $url)
            ->with('qRcode',$qRcode)
            ->with('wxHost',$wxHost)
            ->with('openUrl', $openUrl )
            ->with('rand', $rand)
            ->with('article_category',$article_category)
            ->with('need_select',$need_select)
            ->with('goods',$goods['data']);
    }


    /**
     * 文章详情页面
     * @Get("/previewDetail", as="s_aricle_previewDetail")
     * @param Request $request
     * @return mixed
     * @throws ApiException
     */
    public function previewDetail(Request $request) {
        $code = $request->get('code');

        try {
        $codeData = Curl::post('/article/getArticlePreview',['code'=>$code]);
            if($codeData['status'] != 200){
                return response()->view("errors.404", [], 404);
            }
            $id = $codeData['data']['article_id'];
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }


        if(!$id || is_null($id) || !intval($id)){
            return redirect(route('s_aricle_lists'));
        }
        $article = Curl::post('/article/getArticle',['articleid'=>$id]);

        if(is_null($article['data'])){
            $status = 404;
            if (view()->exists("errors.{$status}")){
                return response()->view("errors.{$status}", [], $status);
            }

            return redirect(route('s_article_lists'));
        }

        //140
        try {
            $goods = Curl::post('/product/getGoodsShow', $arr = [
                'product_id'    =>  $article['data']['product_id'],
                'status'        =>  '1,2'
            ]);
            if(empty($goods['data'])){
                return response()->view("errors.404", [], 404);
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }

        $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';
        $percents = json_decode($article['data']['percent'],true);
        $percent = 0;
        $way = 'percent';

        //广告主
        $adverOwnerTake = 0;
        foreach ($percents as $item) {
            if($item['mode'] == 2){
                if(count($item['contents']) >1){
//                    $percent = json_encode($item['contents']);
                    if($item['contents']['percent']>0){
                        $percent = $item['contents']['percent'];
                    }elseif($item['contents']['account'] >0){
                        $percent = $item['contents']['account'];
                        $way = 'account';
                    }else{
                        $percent = '0';
                    }
                }else{
                    if(key_exists('percent',$item['contents'])){
                        $percent = $item['contents']['percent'];
                    }else{
                        $percent = $item['contents']['account'];
                        $way = 'account';
                    }
                }
            }

            //mode ==3  网站为0  7为广告主分成
            if($item['mode'] == 7){
                if($item['contents']['percent']>0){
                    $adverOwnerTake = $item['contents']['percent'] * $goods['data']['selling_price'] /100;
                }elseif($item['contents']['account'] >0){
                    $adverOwnerTake = $item['contents']['account'];

                }else{
                    $adverOwnerTake = 0;
                }
            }
        }

        $article['data']['channlepercent'] = $percent;
        $article['data']['channleway'] = $way;

        $article['data']['content'] = html_entity_decode($article['data']['content']);



        //是百分比分成 则计算推广后获得的额度
        if($way == 'percent' && $percent > 0){
            $siteTake = $goods['data']['selling_price'] - $adverOwnerTake;

            $article['data']['afterchannlepercent'] = $article['data']['channlepercent'] * $siteTake /100;
        }



        //扫码降序 热门文章
        try {
            $lastweekhotrank = Curl::post('/article/getArticleHotRank');
            $lastweekhotrank = $lastweekhotrank['data'];

            foreach ($lastweekhotrank['data'] as $key => $val){
//                var_dump($val);die;
                $lastweekhotrank['data'][$key]['percentKey'] = (isset($val['percent_arr']['mode_2']['percent']) && $val['percent_arr']['mode_2']['percent']>0)?
                    number_format($val['selling_price'] * $val['percent_arr']['mode_2']['percent']/100,2)  : number_format($val['percent_arr']['mode_2']['account'],2);
            }
            $lastweekhotrank = $lastweekhotrank['data'];
        } catch (ApiException $e) {
            $lastweekhotrank = [];
        }

        $code = Curl::post('/weixin/createCode');
        $jzstate  = ($code['data']);
        \Session::put("pc_jzstate", $jzstate);
        \Cache::put("wxdl_".$jzstate, $jzstate,30);

        $qRcode = 2;

        $need_select = 0;
        if(\Auth::getUser()){
            $uid = $this->getUserId();
            try {
                $post = Curl::post('/user/selectSpreadQRcode', [
                    'aprs' => $article['data']['article_product_relateId'],
                    'spreadUid' => $uid,
                ]);
                $qRcode = $post['data'];//该用户已推广过 return 1 else 2
            } catch (ApiException $e) {
                $qRcode = 2;
            }

            $chooseinfo = Curl::post('/article/getCategoryChose', [
                'uid' => $uid,
            ]);
//            $info['data']['id'];
            if(empty($chooseinfo['data'])){
                $need_select = 1;
            }else{

            }
        }

        $buyUrl = '';
        if( $user){
            if($qRcode==2){
                $url = config('params.wx_host') . 'Article/articleInfo?article_id='.$id;
            }else {
                try {
//                    var_dump(1);
                    $post = Curl::post('/user/createSpreadQRcode', [
                        'aprs' => $article['data']['article_product_relateId'],
                        'spreadUid' => $user
                    ]);
                    $url = config('params.wx_host') . 'Article/articleInfo?spreadid=' . $post['data']['id'] . '&nid=' . $post['data']['order_no'];
                    $buyUrl = config('params.wx_host') . 'User/productDetail?spreadid=' . $post['data']['id'] . '&nid=' . $post['data']['order_no'];
                } catch (ApiException $e) {
                    $url = config('params.wx_host') . 'Article/articleInfo?article_id='.$id;
                }
            }

        }else{
            $url = config('params.wx_host') . 'Article/articleInfo?article_id='.$id;
        }

        if(config('params.is_share')){
            $openPlatform = \Wechat::openPlatform('default');
            $html = config('params.weixin_callback');//
            $openUrl = $openPlatform->getPreAuthorizationUrl($html.'?id='.$id.'&url='.urlencode($buyUrl)); // 传入回调URI即可
        }else{
            $openUrl = 'javascript:;';
        }

        $token = time().rand(10000, 90000);
        $key = "weChatAjax" . \Session::getId();
        \Cache::forget($key);
        \Cache::add($key, $token, 60);

        $wxHost = config('params.wx_host');

        if($goods['data']['expiration_time']==0){
            $goods['data']['syts'] = "不限时";
        }else{
            $goods['data']['syts'] = $this->ShengYu_Tian_Shi_Fen($goods['data']['expiration_time']);
        }

        $article_category = [];
        if(\Auth::getUser()) {
            $uid = $this->getUserId();
            try {
                $checkChose = $post = Curl::post('/industry/getIndustryUser',
                    [
                        'uid' => $uid,//74
                    ]
                );
                if(empty($checkChose['data'])){
                    $article_category = getRedisData('articleCategoryWrite'.$id,'/industryCategory/getIndustryCategoryListWithIndustry',['type'=>2]);
                    $article_category = $article_category['data']['data'];
                }else{
                    $article_category = [];
                }

            } catch (ApiException $e) {
                $article_category = [];
            }
        }


        try {
            //统计 查看页面次数
            Curl::post('/weixin/addArticleQuantity', $arr = [
                'article_id' => $id,
                'uid' => $user ? $user : 0
            ]);
        } catch (ApiException $e) {

        }


        $ori_path = $goods['data']['img_path'];
        $extension = explode('/',$ori_path);
        $name = $extension[count($extension) -1];

        $new_path325 = md5($name.'_325_325');
        $goods['data']['new_path325'] = str_replace($name,$new_path325,$ori_path);

        $rand = $request->get('rand','');
        return view("Article.previewDetail")->with('lastweekhotrank',$lastweekhotrank)
            ->with('article',$article['data'])
            ->with('user', $user)
            ->with('pc_jzstate', $jzstate)
            ->with('url', $url)
            ->with('qRcode',$qRcode)
            ->with('wxHost',$wxHost)
            ->with('openUrl', $openUrl )
            ->with('rand', $rand)
            ->with('article_category',$article_category)
            ->with('need_select',$need_select)
            ->with('goods',$goods['data']);
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
            if($category_id > 0){
//                $where['t_article_category_relate.category_id'] = $category_id;
                $where['t_article_category_relate.category_id'] = $category_id;
            }

            $arr = [
                'wheres' => json_encode($where),
                'page' => $page,
                'pagesize' => $pagesize,
            ];
            if ($category_id > 0) {
                $arr['order'] = json_encode(['t_article.sort' => 'DESC', 't_article.add_time' => 'DESC']);
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
     * 上周排行
     * @Post("/getArticleRank", as="s_aricle_getArticleRank")
     */
    public function getArticleRank(Request $request) {
        try {
            $arr = [
                'lastweek'=>1
            ];
            $post = getRedisData('getArticleRank'.md5(json_encode($request->all())),'/article/getArticleRank',$arr);
//            $post = Curl::post('/article/getArticleRank', $arr = ['lastweek'=>1]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 热门文章
     * @Post("/getArticleHotRank", as="s_aricle_getArticleHotRank")
     */
    public function getArticleHotRank(Request $request) {
        try {
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",2);

            $arr = ['page'=>$page,'pagesize'=>$pagesize];
//            $post = getRedisData('getArticleHotRank'.md5(json_encode($request->all())),'/article/getArticleHotRank',$arr);

            $post = Curl::post('/article/getArticleHotRank', $arr = ['page'=>$page,'pagesize'=>$pagesize]);

//            var_dump($post);die;

            foreach ($post['data']['data'] as $key =>$val){
//                $post['data']['data'][$key]['time_tranx'] = time_tranx($val['add_time']);

                $post['data']['data'][$key]['percentKey'] = (isset($val['percent_arr']['mode_2']['percent']) && $val['percent_arr']['mode_2']['percent']>0)?
                    number_format($val['percent_arr']['mode_2']['percent'],2).'%'  : number_format($val['percent_arr']['mode_2']['account'],2);
            }
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 首页banner
     * @Post("/getBanner", as="s_aricle_getBanner")
     */
    public function getBanner(Request $request) {
        try {
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",5);
            $arr = ['page'=>$page,'pagesize'=>$pagesize,'img_type'=>1];
            $post = Curl::post('/article/imgTitleList', $arr);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 文章分类列表页面Ajax
     * @Post("/getCategoryList", as="s_aricle_getCategoryList")
     */
    public function getCategoryList(Request $request) {
        try {
            $arr = ['status'=>1,'type'=>2];
//            $post = Curl::post('/article/getArticleCategoryList', ['status'=>1]);
//            $post = Curl::post('/productCategory/getProductCategoryList', ['status'=>1]);

//            $post = Curl::post('/industry/getLists', $arr);

//            $post = getRedisData('ArticlegetCategoryList'.md5(json_encode($request->all())),'/industry/getLists',$arr);

            $arr = ['type'=>2,'status'=>1,'pagesize'=>100,'order' =>'{"order":"DESC"}'];
            $post = getRedisData('ArticlegetCategoryList'.md5(json_encode($request->all())),'/industryCategory/getLists',$arr);


            return new JsonResponse($post);
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
                        $post['data']['url'] = shortUrl($post['data']['landing_page']);
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

//    /**
//     * 添加用户行业分类关联
//     * @Post("/addUidIndustry", as="s_aricle_addUidIndustry")
//     */
//    public function addUidIndustry(Request $request) {
//        try {
//            $uid = $this->getUserId();
//            $industry_id = $request->get("industry_id",-1);
//            if(intval($industry_id) && $industry_id >0){
//                $post = Curl::post('/industry/AddUidIndustry', [
//                    'uid' => $uid,
//                    'industry_id' => $industry_id
//                ]);
//
//                return new JsonResponse($post);
//            }
//        } catch (ApiException $e) {
//
//            return new JsonResponse([
//                "status"=>$e->getCode(),
//                "message"=>$e->getMessage(),
//            ]);
//        }
//    }

    /**
     * 点击一键转公微 统计
     * @Post("/addZhuanQuantity", as="s_article_addZhuanQuantity")
     */
    public function getCategorList(Request $request) {
        try {
            $spread_id = $request->get("spread_id",-1);
            if($spread_id >0){
                $user = \Auth::getUser()?\Auth::getUser()->getAuthIdentifier(): '';
                Curl::post('/weixin/addZhuanQuantity', $arr = [
                    'spread_id' => $spread_id,
                    'uid' => $user ? $user : 0
                ]);
            }

        } catch (ApiException $e) {

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
     * 文章首页ajax
     * @Post("/getArticleIndex", as="s_aricle_getArticleIndex")
     */
    public function getArticleIndex(Request $request) {
        try {
            //old getArticleWithProductList

            $category_id = $request->get("category_id",0);
            $region_id = $request->get("region_id",0);

            $arr = [];
//            if ($category_id > 0) {
//                $arr['order'] = json_encode(['t_article.sort' => 'DESC', 't_article.add_time' => 'DESC']);
//            }
            if($category_id > 0){
                $where['category_id'] = $category_id;
            }
            if($region_id >0){
                $arr['region_id'] = $region_id;
            }
            $post = getRedisData('getArticleIndex'.md5(json_encode($request->all())),'/article/columnListwithArticleList',$arr);

            if($post['status']==200){
                foreach($post['data']['data'] as $key =>$val){
                    if($val['list']){
                        foreach ($val['list'] as $k => $v){
                            if(isset($v['img_path']) && is_json($v['img_path'])) {
                                $post['data']['data'][$key]['list'][$k]['img_path285'] = $this->imageShow(json_decode($v['img_path'], true)['oss'], 458, 350);
                            }else{
                                $post['data']['data'][$key]['list'][$k]['img_path285'] = $this->imageShow($v['img_path'], 458, 350);
                            }

                        }
                    }

                }
            }

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 文章more、search列表页面ajax
     * @Post("/getArticleList", as="s_aricle_getArticleList")
     */
    public function getArticleList(Request $request) {
        try {
            //old getArticleWithProductList

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $category_id = $request->get("category_id",0);
            $region_id = $request->get("region_id",0);
            $name = $request->get("name",'');
            $arr = [
                'page'=>$page,
                'pagesize'=>$pagesize
            ];
//            if ($category_id > 0) {
//                $arr['order'] = json_encode(['t_article.sort' => 'DESC', 't_article.add_time' => 'DESC']);
//            }
            if($category_id > 0){
                $arr['category_id'] = $category_id;
            }
            if($region_id >0){
                $arr['region_id'] = $region_id;
            }
            if(strlen($name) > 0){
                $arr['name'] = $name;
            }
            $post = getRedisData('getArticleList'.md5(json_encode($request->all())),'/article/columnCatgoryArticleList',$arr);

            if($post['status']==200){
                foreach($post['data']['data'] as $key =>$val){

                            if(isset($val['img_path']) && is_json($val['img_path'])) {
                                $post['data']['data'][$key]['img_path285'] = $this->imageShow(json_decode($val['img_path'], true)['oss'], 458, 350);
                            }else{
                                $post['data']['data'][$key]['img_path285'] = $this->imageShow($val['img_path'], 458, 350);
                            }



                }
            }

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 相关文章列表
     * @Post("/getRelateArticleList", as="s_aricle_getRelateArticleList")
     */
    public function getRelateArticleList(Request $request) {

        $page = $request->get("page",1);
        $pagesize = $request->get("pagesize",10);
        $article_id = $request->get("article_id",0);
        $product_id = $request->get("product_id",0);
        if(!intval($product_id) || $product_id<=0){
            return new JsonResponse([
                "status"=>405,
                "message"=>"参数错误",
            ]);
        }

        if(is_null($article_id)){
            $article_id = 0;
        }
        if (DB::table('t_article')->where(['id' => $article_id])->value('status') == 5) return new JsonResponse(['status' => 400]);

        try {
            $arr = [
                'page'=>$page,
                'pagesize'=>$pagesize,
                'article_id'=>$article_id,
                'product_id'=>$product_id
            ];

            $post = getRedisData('getRelateArticleList'.md5(json_encode($request->all())),'/article/getRelateArticleList',$arr);
//            $post = Curl::post('/article/getRelateArticleList',$arr);


            foreach ($post['data']['data'] as $key=>$value){
                $post['data']['data'][$key]['img_252'] = $this->imageShow($value['img_path'], 252, 192);
//                $post['data']['data'][$key]['img_252'] = $value['img_path'];
            }
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
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

    private function imageShow($ori_path, $width, $height) {


        if(!$ori_path || !$width || !$height){
            //echo $ori_path;exit;
            return $ori_path;
        }

        if(!strstr($ori_path,'jianzhiwangluo')){
            return $ori_path;
        }

        $extension = explode('/',$ori_path);
        $name = $extension[count($extension) -1];

        $new_path325 = md5($name.'_'.$width.'_'.$height);
        $url =  str_replace($name,$new_path325,$ori_path);

        try {
            getFileType($url);
            //echo $url;exit;
            return $url;
        } catch (\Exception $e) {
            $type = getFileType($ori_path);
            $local_path = public_path().'/'.$name.'.'.$type;
            file_put_contents($local_path , file_get_contents($ori_path));
        }
        $pathName = $name.'_'.$width.'_'.$height;

        $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img285->resizeCanvas($width, $height)->save( $pathName,100 );

        OSS::publicUpload(\Config::get('alioss.BucketName'), md5($pathName), public_path().'/'.$pathName);
        $publicSimgURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), md5($pathName));

        unlink($local_path);
        unlink(public_path().'/'.$pathName);
        //echo $publicSimgURL;exit;
        return $publicSimgURL;

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