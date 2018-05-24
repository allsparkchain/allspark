<?php

namespace App\Http\Controllers\Article;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use App\Services\OSS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\Image;

/**
 * Class ProductController
 * @Controller(prefix="/Article")
 * @Middleware("web")
 * @Middleware("auth")
 * @package App\Http\Controllers
 */
class ArticleController extends Controller
{
    /**
     * @Get("/lists", as="s_article_lists")
     * @Post("/lists", as="s_article_lists")
     */
    public function lists(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/article/list',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'name'=>$request->get('name', ''),
                    'adminlist' => 1
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
        return view("Article.list");
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
     * @Get("/add", as="s_article_add")
     *
     */
    public function add(Request $request){
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

    /**
     *
     * @Post("/auditStatus", as="s_article_audit_status")
     */
    public function auditStatus(Request $request) {

        if ($request->ajax()) {
            $data = Curl::post('/article/auditArticle',
                [
                    'article_id' => $request->get('article_id'),
                    'status'     => $request->get('status'),
                    'remark'     => $request->get('remark'),
                    'admin_id'   => 1,
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }



    /**
     * @Post("/editpost", as="s_article_editpost")
     */
    public function editpost(Request $request){
        session_start();
        $wxImgList =  isset($_SESSION['wxImgList'])?json_decode($_SESSION['wxImgList'],true):'';
        $this->validate($request, [
            'name' => 'required',
            'content' => 'required',
            'id' => 'required',
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

        $data = Curl::post('/article/editArticle',
            $paramer
        );
        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            return redirect(route('s_article_lists'))->with('addsuccess', 'success');
        }
    }

    /**
     * @Get("/imgTitleAdd", as="s_article_imgTitleAdd")
     */
    public function imgTitleAdd(Request $request) {
        $re = '';
        if($request->get('img_type')){
            $re = $request->get('img_type');
        }
        return view("Article.imgtitleadd")->with('re',$re);
    }
    /**
     * @Post("/imgTitleAddPost", as="s_article_imgTitleAddPost")
     */
    public function imgTitleAddPost(Request $request) {
        $re = $request->get('img_type',0);
        if($re == 1){
            $this->validate($request, [
                'img_title' => 'required',
                'images' => 'required',
                'img_url' => 'required',
                'imagesHead' => 'required',
                'article_title' => 'required',
                'article_desc' => 'required',
                'article_addtime' => 'required',
                'article_author' => 'required'
            ]);


            if($request->hasFile('images') && $request->file('images')->isValid() &&
                $request->hasFile('imagesHead') && $request->file('imagesHead')->isValid()
            ){
                $extension = $request->file('images')->extension();
                $newname = time().'images.'.$extension;
                $res = $request->file('images')->storeAs('images',$newname,'local2');
                //$request['img_path']= $newname;
                $ossKey =  md5(time().rand().rand());
                OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
                $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);



                $request['img_path'] = $publicObjectURL;



                //
                $extension = $request->file('imagesHead')->extension();
                $newname = time().'images.'.$extension;
                $res = $request->file('imagesHead')->storeAs('images',$newname,'local2');
                $ossKey =  md5(time().rand().rand());
                OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
                $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);

                $request['img_path2'] = $publicObjectURL;
//                $img_path['oss'] = $publicObjectURL;
//                $publicObjectURL = fastdfs_storage_upload_by_filename(public_path('app/').$res);
//                $url = \Config::get('params.img_url').$publicObjectURL['group_name'].'/'.$publicObjectURL['filename'];
//                $img_path['fastdfs'] = $url;
//                $request['img_path2'] = json_encode($img_path);


//var_dump($request->all());die;





                $data = Curl::post('/article/imgTitleAdd',
                    $request->all()
                );
                if($data['status'] != 200){
                    return back()->withErrors($data['message']);
                }else{
                    return redirect(route('s_article_imgTitleList')."?img_type=".$re)->with('addsuccess', 'success');
                }
            }


        }else{
            $this->validate($request, [
                'img_title' => 'required',
                'images' => 'required'
            ]);


            if($request->hasFile('images') && $request->file('images')->isValid()){
                $extension = $request->file('images')->extension();
                $newname = time().'images.'.$extension;
                $res = $request->file('images')->storeAs('images',$newname,'local2');
                //$request['img_path']= $newname;
                $ossKey =  md5(time().rand().rand());

                OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
                $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);
                $request['img_path'] = $publicObjectURL;



                $data = Curl::post('/article/imgTitleAdd',
                    $request->all()
                );
                if($data['status'] != 200){
                    return back()->withErrors($data['message']);
                }else{
                    return redirect(route('s_article_imgTitleList')."?img_type=".$re)->with('addsuccess', 'success');
                }
            }
        }




        return false;
    }
    /**
     * @Get("/imgTitleList", as="s_article_imgTitleList")
     * @Post("/imgTitleList", as="s_article_imgTitleList")
     */
    public function imgTitleList(Request $request) {
        if ($request->ajax()) {
            $array = [
                'page' => $request->get('page', 1),
                'pagesize'=>$request->get('pagesize', 10),
                'img_title'=>$request->get('img_title', ''),
            ];
            if($request->get('img_type')){
                $array['img_type'] = $request->get('img_type');
            }

            $data = Curl::post('/article/imgTitleList',
                $array
            );
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }
        $re = '';
        if($request->get('img_type')){
            $re = $request->get('img_type');
        }
        return view("Article.imgtitleist")->with('re',$re);
    }

    /**
     * @Get("/imgTitleEdit", as="s_article_imgTitleEdit")
     */
    public function imgTitleEdit(Request $request) {
        $re = '';
        if($request->get('img_type')){
            $re = $request->get('img_type');
        }
        $data = Curl::post('/article/getImgTitle',
            [
                'id' => $request->get('id', 1),
            ]
        );
        return view("Article.imgtitleedit")->with('res',$data['data'])->with('re',$re);
    }
    /**
     * 图文编辑提交
     * @Post("/imgTitleEditPost", as="s_article_imgTitleEditPost")
     */
    public function imgTitleEditPost(Request $request){
        $this->validate($request, [
            'id' => 'required',
        ]);

        if($request->hasFile('images') && $request->file('images')->isValid()) {
            $extension = $request->file('images')->extension();
            $newname = time() . 'images.' . $extension;
            $res = $request->file('images')->storeAs('images', $newname, 'local2');
            $ossKey =  md5(time().rand().rand());
            OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
            $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);
            $request['img_path'] = $publicObjectURL;


        }else{
            $request['img_path'] = $request['img_path_old'];
        }

        if($request->hasFile('imagesHead') && $request->file('imagesHead')->isValid()) {
            $extension = $request->file('imagesHead')->extension();
            $newname = time() . 'images.' . $extension;
            $res = $request->file('imagesHead')->storeAs('images', $newname, 'local2');
            $ossKey =  md5(time().rand().rand());
            OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
            $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);
            $request['img_path2'] = $publicObjectURL;



        }else{
            $request['img_path2'] = $request['imagesHead_old'];
        }



        $data = Curl::post('/article/editImgTitle',
            $request->all()
        );
        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            $re = '';
            if($request->get('img_type')){
                $re = $request->get('img_type');
            }
            return redirect(route('s_article_imgTitleList').'?img_type='.$re)->with('addsuccess', 'success');
        }
    }

    /**
     * @Post("/delImgTitle", as="s_article_delImgTitle")
     */
    public function delImgTitle(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $data = Curl::post('/article/deleteImgTitle',
                [
                    'id' => $request->get('id', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }


    /**
     * @Get("/getArticleImgList", as="s_article_getArticleImgList")
     * @Post("/getArticleImgList", as="s_article_getArticleImgList")
     */
    public function getArticleImgList(Request $request) {
        if ($request->ajax()) {
            $array = [
                'page' => $request->get('page', 1),
                'pagesize'=>$request->get('pagesize', 10),
                'article_id'=>$request->get('article_id', ''),
            ];


            $data = Curl::post('/article/getArticleImgList',
                $array
            );
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }
        $re = '-1';
        if($request->get('article_id')){
            $re = $request->get('article_id');
        }
        return view("Article.articleImgList")->with('article_id',$re);
    }

    /**
     * @Post("/getArticleImgDel", as="s_article_getArticleImgDel")
     */
    public function getArticleImgDel(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $data = Curl::post('/article/getArticleImgDel',
                [
                    'id' => $request->get('id', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

    /**
     * @Post("/getArticleImgOrder", as="s_article_getArticleImgOrder")
     */
    public function getArticleImgOrder(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $data = Curl::post('/article/getArticleImgOrder',
                [
                    'id' => $request->get('id'),
                    'orderby' => $request->get('orderby'),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

}