<?php

namespace App\Http\Controllers\Goods;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use App\Services\OSS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
/**
 * Class ProductController
 * @Controller(prefix="/Goods")
 * @Middleware("web")
 * @Middleware("auth")
 * @package App\Http\Controllers
 */
class GoodsController extends Controller
{
 
    /**
     * @Get("/lists", as="s_goods_lists")
     * @Post("/lists", as="s_goods_lists")
     */
    public function lists(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/product/list',
                [
                    'adminlist'=>1,
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'product_name'=>$request->get('product_name', ''),
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

        return view("Goods.list");
    }

    /**
     * @Get("/prviewDetail", as="s_goods_prviewDetail")
     */
    public function prviewDetail(Request $request) {

            $proid = $request->get('proid', '');
//        $proid = 64;
//        public_path()
        $url = config('params.wx_host').'User/previewProductDetail?product_id='.$proid;
        return view("Goods.prviewDetail")->with('url',$url);
    }

    /**
     * @Get("/prviewGoodsDetail", as="s_goods_prviewGoodsDetail")
     */
    public function prviewGoodsDetail(Request $request) {

        $proid = $request->get('proid', '');
        $url = config('params.pc_host').'Goods/previewDetail?id='.$proid;
        return redirect($url);
    }

    /**
     * @Get("/specificationslists", as="s_goods_specificationslists")
     * @Post("/specificationslists", as="s_goods_specificationslists")
     */
    public function specificationslists(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/product/getSpecificationsList',
                [
                    'product_id'=>$request->get('product_id', ''),
                    'type'=>2,
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 60),

                ]
            );

            foreach ($data['data']['data'] as $k =>$v){
                $data['data']['data'][$k]['specifications'] =  implode(" ",json_decode($v['specifications'],true));
            }
            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $data['data']['count'],
                'iTotalDisplayRecords' => $data['data']['count'],
                'aaData' => $data['data']['data'],
            ];
            return new JsonResponse($return);
        }
        $id = $request->get('product_id', '');
        return view("Goods.specificationsLists")->with('id', $id);
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
     * @Post("/save", as="s_goods_save")
     */
    public function saveGoods(Request $request) {
        session_start();
        $wxImgList =  isset($_SESSION['wxImgList'])?json_decode($_SESSION['wxImgList'],true):'';


        $product_type = $request->get('product_type',1);
        if($product_type == 3){
            $validator = \Validator::make($request->all(), [
                'product_name' => 'required',
                'synopsis' => 'required',
                'product_info' => 'required',
//            'category_id' => 'required|Integer|min:0',
                'channelpercent' => 'required|Numeric|min:0',
                'mediaAgent' => 'required|Numeric|min:0',
                'sitepercent' => 'required|Numeric|min:0',
                'selling_price' => 'required|Numeric|min:1',
                'writerpercent' => 'required|Numeric|min:0',
                'advertpercent' => 'required|Numeric|min:0',
                'images' => 'required',
                'advert_id' => 'required|Integer|min:0',
                'landing_page'=> 'required',
                'stock' => 'required|Integer|min:-1',
            ]);
        }else{
            $validator = \Validator::make($request->all(), [
                'product_name' => 'required',
                'synopsis' => 'required',
                'product_info' => 'required',
                'stock' => 'required|Integer|min:-1',
//            'category_id' => 'required|Integer|min:0',
                'channelpercent' => 'required|Numeric|min:0',
                'mediaAgent' => 'required|Numeric|min:0',
                'sitepercent' => 'required|Numeric|min:0',
                'selling_price' => 'required|Numeric|min:1',
                'writerpercent' => 'required|Numeric|min:0',
                'advertpercent' => 'required|Numeric|min:0',
                'images' => 'required',
                'advert_id' => 'required|Integer|min:0',
            ]);
        }


        if ($validator->fails()) {
            return redirect(route('s_goods_add').'?product_type='.$product_type

            )
                ->withErrors($validator)
                ->withInput();
        }


//        $paramer = $request->all();
//        var_dump($paramer);die;

        $writerpercentDisplay = $request->get('writerpercentDisplay',0);
        $writerMoneyDisplay = $request->get('writerMoneyDisplay',0);
        $writerCombineDisplay = $request->get('writerCombineDisplay',0);
        if(!$writerpercentDisplay && !$writerMoneyDisplay && !$writerCombineDisplay){
            return back()->withErrors('写手 三个 分成模式 至少一个 可见');
        }
        //选择可见，也可以为0
//        if($writerpercentDisplay){
//            $writerPErcent = $request->get('writerpercent',0);
//            if($writerPErcent<=0){
//                return back()->withErrors('写手百分比可见 百分比值<=0');
//            }
//        }
//        if($writerMoneyDisplay){
//            $writerMoney = $request->get('writerMoney',0);
//            if($writerMoney<=0){
//                return back()->withErrors('写手固定金额可见 金额值<=0');
//            }
//        }
//        if($writerCombineDisplay){
//            $writerCombinepercent = $request->get('writerCombinepercent',0);
//            $writerCombineaccount = $request->get('writerCombineaccount',0);
//            if($writerCombinepercent<=0 || $writerCombineaccount<=0){
//                return back()->withErrors('写手复合模式可见 复合值<=0');
//            }
//        }


        $expiration_time = $request->get('expiration_time');
        if(is_null($expiration_time)){
            $request['expiration_time'] = 0;
        }else{
            $request['expiration_time'] = strtotime($expiration_time);
        }
        if($request->hasFile('images') && $request->file('images')->isValid()){
            $extension = $request->file('images')->extension();
            $newname = time().'images.'.$extension;
            $res = $request->file('images')->storeAs('images',$newname,'local2');
            //$request['img_path']= $newname;
            $ossKey =  md5(time().rand().rand());
            OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
            $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);

            $img_path['oss'] = $publicObjectURL;

            //压缩原图
            $local_path = public_path('app/').$res;
            $fileFrom_325 = $local_path.'_325_325.jpeg';
            $height = \Intervention\Image\Facades\Image::make($local_path)->height();
            $width = \Intervention\Image\Facades\Image::make($local_path)->width();
            //echo $height.'='.$width;exit;
            if($width>$height){
                $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(325, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }else{
                $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(null, 325, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $img285->resizeCanvas(325, 325)->save( $fileFrom_325,100 );
            OSS::publicUpload(\Config::get('alioss.BucketName'), md5($ossKey.'_325_325'), $fileFrom_325);
            OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), md5($ossKey.'_325_325'));

           //$publicObjectURL = fastdfs_storage_upload_by_filename(public_path('app/').$res);

           //$url = \Config::get('params.img_url').$publicObjectURL['group_name'].'/'.$publicObjectURL['filename'];

            $img_path['fastdfs'] = '';//$url;

            $request['img_path'] = json_encode($img_path);

            $content = $request->get('product_info');
            if($wxImgList) {

                foreach ($wxImgList as $value) {
                    $content = str_replace(html_entity_decode($value['source']),$value['url'],$content);
                }
            }
            $paramer = $request->all();
            $paramer['product_info'] = $content;
            $sKey = 0;

            $array = [];
            while (true){
                if($sKey>0){
                    $span =  $request->get('specName'.$sKey);
                }else{
                    $span =  $request->get('specName');
                }
                if(!$span){
                    break;
                }
                foreach ($span as $key =>$value){
                    if($key==0){
                        $array[$sKey]['key'] = $value;
                    }else{
                        $array[$sKey]['value'][] = $value;
                    }
                }
                $sKey++;
            }

            $paramer['specifications'] = json_encode($array);

                try{
                    $data = Curl::post('/product/add',
                        $paramer
                    );
                    if($data['status'] != 200){
                        return back()->withErrors($data['message']);
                    }else{
                        //添加产品成功，请求生成验证码
                        if(array_get($paramer, 'goods_type', 0) == 2) {

                            $product_id = array_get($data, 'data');
                            $data = Curl::post('/utils/virtual/bind_virtual',
                                [
                                    'name' => '虚拟商品' . uniqid(),
                                    'good_id' => $product_id,
                                    'number' => array_get( $paramer ,'stock', 0) == -1 ? 1000 : array_get( $paramer ,'stock', 0),
                                ]
                            );
                        }
                        $phpPath = config('params.php_path');
                        exec("cd ".public_path('../')." && ".$phpPath.' artisan command:ossGoodsStript '.$data['data'].' &');

                        return redirect(route('s_goods_lists'))->with('addsuccess', 'success');
                    }
                }catch (\Exception $e){
                    return back()->withErrors($e->getMessage());
                }
            }

    }

    /**
     * @Get("/edit", as="s_goods_edit")
     */
    public function edit(Request $request){
//        $categorylist = Curl::post('/productCategory/getProductCategoryList',['status'=>1]);
        $categorylist = Curl::post('/industry/getLists',['status'=>1,'type'=>1]);
//        $categorylist = Curl::post('/industry/getIndustryUserList');

        $categorylist = Curl::post('/industryCategory/getLists',['status'=>1,'type'=>1]);

        $brandlist = Curl::post('/product/brandList');
        $advertList = Curl::post('/advert/advertRelativeList');

        $data = Curl::post('/product/getProduct',
            ['product_id'=>$request->get('product_id', 1)]
        );
        if(isset($data['data']['contents'])){
            $data['data']['contents'] = str_replace(chr(10),'<br>',$data['data']['contents']);
            $data['data']['contents'] = str_replace(chr(13),'<br>',$data['data']['contents']);
        }else{
            $data['data']['contents'] = '';
        }


        foreach ($data['data']['product_division_methods'] as $k=>$v){
            $display_to_writer = '';

            $str = '';
            $percents = json_decode($v['percent'],true);

            if($v['type'] !=1){
                $value = 0;
                foreach ($percents as $kk=>$val){

                    if($val['type'] == 1){
                        if($val['contents']['percent']>0){
                            $value = $val['contents']['percent'];
                        }
                    }
                    if($val['type'] == 2){
                        if($val['contents']['account']>0){
                            $value = $val['contents']['account'];
                        }
                    }
//                    if($val['type'] == 1){
//                        $arr[] = '百分比'.$val['contents']['percent'].'%';
//                        $str .=  '百分比'.$val['contents']['percent'].'%     ';
//                    }
//                    if($val['type'] == 2){
//                        $arr[] = '固定金额'.$val['contents']['account'].'元';
//                        $str .=  '固定金额'.$val['contents']['account'].'元  ';
//                    }
//                    if($val['type'] == 3){
//                        $arr[] = '复合模式: 百分比'.$val['contents']['percent'].'%'.$val['contents']['account'].'元';
//                        $str .='复合模式: 百分比'.$val['contents']['percent'].'%'.$val['contents']['account'].'元  ';
//                    }
                }
                //获得 两个 模式用的 真正的值 可能都是0
                $percents[0]['value'] = $value;
            }else{
                $display_to_writer = json_decode($v['display_to_writer'],true);
//                var_dump($v['display_to_writer']);
            }

//            $data['data']['product_division_methods'][$k]['show'] = $str;
            $data['data']['product_division_methods'][$k]['display_to_writer_arr'] = $display_to_writer;
            $data['data']['product_division_methods'][$k]['product_division_methods_arr'] = $percents;
        }


        $data['data']['specifications'] = json_decode($data['data']['specifications'],true);

        return view("Goods.edit")->with('res',$data['data'])->with('categorylist',$categorylist['data']['data'])
            ->with('brandlist',$brandlist['data']['data'])
            ->with('advertList',$advertList['data']);
    }

    /**
     * @Post("/editpost", as="s_good_editpost")
     */
    public function editpost(Request $request){
        session_start();
        $wxImgList =  isset($_SESSION['wxImgList'])?json_decode($_SESSION['wxImgList'],true):'';

        $product_type = $request->get('product_type',1);
        if($product_type == 3){
            $this->validate($request, [
                'id' => 'required',
                'product_name' => 'required',
                'synopsis' => 'required',
                'product_info' => 'required',
                'stock' => 'required|Integer|min:-1',
                'selling_price' => 'required',
                'landing_page' => 'required',
                'channelpercent' => 'required|Numeric|min:0',
                'mediaAgent' => 'required|Numeric|min:0',
                'sitepercent' => 'required|Numeric|min:0',
                'writerpercent' => 'required|Numeric|min:0',
                'advertpercent' => 'required|Numeric|min:0',
            ]);
        }else{
            $this->validate($request, [
                'id' => 'required',
                'product_name' => 'required',
                'synopsis' => 'required',
                'product_info' => 'required',
                'stock' => 'required|Integer|min:-1',
                'selling_price' => 'required',
                'channelpercent' => 'required|Numeric|min:0',
                'mediaAgent' => 'required|Numeric|min:0',
                'sitepercent' => 'required|Numeric|min:0',
                'writerpercent' => 'required|Numeric|min:0',
                'advertpercent' => 'required|Numeric|min:0',
            ]);
        }



        $expiration_time = $request->get('expiration_time');
        if(is_null($expiration_time)){
            $request['expiration_time'] = 0;
        }else{
            $request['expiration_time'] = strtotime($expiration_time) ? strtotime($expiration_time):0;
        }
        if($request->hasFile('images') && $request->file('images')->isValid()) {
            $extension = $request->file('images')->extension();
            $newname = time() . 'images.' . $extension;
            $res = $request->file('images')->storeAs('images', $newname, 'local2');
            $ossKey =  md5(time().rand().rand());
            OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
            $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);
            $img_path['oss'] = $publicObjectURL;

            //压缩原图
            $local_path = public_path('app/').$res;
            $fileFrom_325 = $local_path.'_325_325.jpeg';
            $height = \Intervention\Image\Facades\Image::make($local_path)->height();
            $width = \Intervention\Image\Facades\Image::make($local_path)->width();
            //echo $height.'='.$width;exit;
            if($width>$height){
                $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(325, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }else{
                $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(null, 325, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $img285->resizeCanvas(325, 325)->save( $fileFrom_325,100 );
            OSS::publicUpload(\Config::get('alioss.BucketName'), md5($ossKey.'_325_325'), $fileFrom_325);
            OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), md5($ossKey.'_325_325'));


            //$publicObjectURL = fastdfs_storage_upload_by_filename(public_path('app/').$res);
            //$url = \Config::get('params.img_url').$publicObjectURL['group_name'].'/'.$publicObjectURL['filename'];
            $img_path['fastdfs'] = '';//$url;

            $request['img_path'] = json_encode($img_path);

        }else{
            $request['img_path'] = $request['img_path_old'];
        }

        $content = $request->get('product_info');
        if($wxImgList) {

            foreach ($wxImgList as $value) {
                $content = str_replace(html_entity_decode($value['source']),$value['url'],$content);
            }
        }
        $paramer = $request->all();
        $paramer['product_info'] = $content;
        $sKey = 0;

        $array = [];
        while (true){
            if($sKey>0){
                $span =  $request->get('specName'.$sKey);
            }else{
                $span =  $request->get('specName');
            }
            if(!$span){
                break;
            }
            foreach ($span as $key =>$value){
                if($key==0){
                    $array[$sKey]['key'] = $value;
                }else{
                    $array[$sKey]['value'][] = $value;
                }
            }
            $sKey++;
        }

        $paramer['specifications'] = json_encode($array);

//        dd($request->all());

        $data = Curl::post('/product/editProduct',
            $paramer
        );

        $phpPath = config('params.php_path');

        exec("cd ".public_path('../')." && ".$phpPath.' artisan command:ossGoodsStript '.$paramer['id'].' &');

        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            //todotodo
            //添加产品成功，请求生成验证码

            if(array_get($paramer, 'goods_type', 0) == 2) {
                $stock = array_get( $paramer ,'stock', 0);

                $data = Curl::post('/utils/virtual/update_virtual',
                    [
                        'good_id' => $paramer['id'],
                        'number' => $stock == -1 ? 10000 : $stock,
                        'code_number' => 1
                    ]
                );
            }
            return redirect(route('s_goods_lists'))->with('addsuccess', 'success');
        }
    }

    /**
     * 商品地域列表页面
     * @Get("/rgionList", as="s_goods_regionlists")
     * @Post("/rgionList", as="s_goods_regionlists")
     */
    public function rgionList(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/productRegion/productRegionList',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'product_id'=>$request->get('product_id', -1),
                    'region_name'=>$request->get('region_name', ''),
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

        return view("Goods.regionlist")->with('product_id',$request->get('product_id', 0));
    }
    /**
     * @Post("/addRegion", as="s_goods_addRegion")
     */
    public function addRegion(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'product_id' => 'required',
                'region_name' => 'required',
            ]);
            $data = Curl::post('/productRegion/addRegion',
                [
                    'product_id' => $request->get('product_id', -1),
                    'region_name'=>$request->get('region_name', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
//        if($data['status'] != 200){
//            return back()->withErrors($data['message']);
//        }else{
//            return redirect(route('s_goods_lists'))->with('addsuccess', 'success');
//            return new JsonResponse(['']);
//        }
    }
    /**
     * @Post("/delRegion", as="s_goods_delRegion")
     */
    public function delRegion(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'id' => 'required',
            ]);
            $data = Curl::post('/productRegion/delRegion',
                [
                    'id' => $request->get('id', -1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }




}
