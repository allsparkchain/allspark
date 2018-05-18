<?php
namespace App\Http\Controllers\Goods;


use App\Http\Controllers\Controller;
use App\Lib\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class GoodsRegionController
 * @Controller(prefix="/GoodsRegion")
 * @Middleware("web")
 * @Middleware("auth")
 * @package App\Http\Controllers
 */
class GoodsRegionController extends Controller{
    /**
     * 地域分类列表
     * @Get("/lists", as="s_goods_region_category_lists")
     * @Post("/lists", as="s_goods_region_category_lists")
     */
    public function lists(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/productRegion/getProductCategoryList',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'category_name'=>$request->get('category_name', ''),
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

        return view("Goods.Region.list");
    }

    /**
     *
     * @Post("/changeStatus", as="s_goods_region_category_change_status")
     */
    public function changeStatus(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/productRegion/categoryChangeStatus',
                [
                    'cid' => $request->get('cid', 0),
                    'status'=>$request->get('status', 1),
                ]
            );
            return new JsonResponse($data);
        }
        return false;
    }

    /**
     * @Get("/add", as="s_goods_region_category_add")
     *
     */
    public function add(Request $request){
        return view("Goods.Region.add");
    }

    /**
     * @Post("/save", as="s_goods_region_category_save")
     */
    public function saveCategory(Request $request) {
        $this->validate($request, [
            'category_name' => 'required|unique:t_region_category',
        ]);//unique
        $data = Curl::post('/productRegion/categoryAdd',
            $request->all()
        );
        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            return redirect(route('s_goods_region_category_lists'))->with('addsuccess', 'success');
        }
    }

    /**
     * @Get("/edit", as="s_goods_region_category_edit")
     */
    public function edit(Request $request){
        $id = $request->get('cid', -1);
        if($id<=0){
            return redirect(route('s_goods_region_category_lists'));
        }
        $data = Curl::post('/productRegion/getCategory',
            ['id'=>$id]
        );
        return view("Goods.Region.edit")
            ->with('res',$data['data']);
    }

    /**
     * @Post("/editpost", as="s_goods_region_category_editpost")
     */
    public function editpost(Request $request){
        $this->validate($request, [
            'category_name' => 'required|unique:t_region_category',
            'id' => 'required',
        ]);
        $data = Curl::post('/productRegion/editCategory',
            $request->all()
        );
        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            return redirect(route('s_goods_region_category_lists'))->with('addsuccess', 'success');
        }
    }

    /**
     * 某个具体 地域分类 下的 所有 地域的列表
     * @Get("/category_relate_regionlists", as="s_goods_region_category_relate_regionlists")
     * @Post("/category_relate_regionlists", as="s_goods_region_category_relate_regionlists")
     */
    public function category_relate_regionlists(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/productRegion/categoryRelateRegionlists',
                [
                    'page' => $request->get('page', 1),
                    'pagesize'=>$request->get('pagesize', 10),
                    'category_id'=>$request->get('category_id', 0),
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

        return view("Goods.Region.regionlist")->with('category_id',$request->get('category_id', 0));
    }

    /**
     * @Post("/addRegionForRegionCategory", as="s_goods_region_addRegionForRegionCategory")
     */
    public function addRegion(Request $request) {
        if ($request->ajax()) {
            $this->validate($request, [
                'category_id' => 'required',
                'region_name' => 'required',
            ]);
            $data = Curl::post('/productRegion/addRegionForRegionCategory',
                [
                    'product_region_category_id' => $request->get('category_id', -1),
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


}