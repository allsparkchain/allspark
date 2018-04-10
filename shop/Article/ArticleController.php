<?php

namespace App\Http\Controllers\Article;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use App\Services\OSS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductController
 * @Controller(prefix="/Article")
 * @package App\Http\Controllers
 */
class ArticleController extends Controller
{

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
                var_dump('没有找推广id');die;
            }
            $url = config('params.app_host').'User/productDetail?spreadid='.$spreadid .'&nid='.$nid;
        }else{
            $article_id = $request->get('article_id');
            $url = '';
        }
        try{
            $info = Curl::post('/article/getArticleDetailForWx',['article_id'=>$article_id])['data'];
//            var_dump($info);
        }catch (ApiException $e){
            return redirect(route('s_order_orderHistoryList'));
            var_dump('没有找到该成品');die;
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