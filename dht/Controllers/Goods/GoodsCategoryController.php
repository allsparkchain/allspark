<?php
namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Lib\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


/**
 * Class GoodsCategoryController
 * @Controller(prefix="/GoodsCategory")
 * @Middleware("web")
 * @Middleware("auth")
 * @package App\Http\Controllers
 */
class GoodsCategoryController extends Controller
{
    /**
     * @Get("/lists", as="s_goods_category_lists")
     * @Post("/lists", as="s_goods_category_lists")
     */
    public function lists(Request $request) {

        if ($request->ajax()) {
            $data = Curl::post('/productCategory/getProductCategoryList',
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

        return view("Goods.Category.list");
    }

    /**
     *
     * @Post("/changeStatus", as="s_goods_category_change_status")
     */
    public function changeStatus(Request $request) {
        if ($request->ajax()) {
            $data = Curl::post('/productCategory/categoryChangeStatus',
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
     * @Get("/add", as="s_goods_category_add")
     *
     */
    public function add(Request $request){
        return view("Goods.Category.add");
    }

    /**
     * @Post("/save", as="s_goods_category_save")
     */
    public function saveCategory(Request $request) {
        $this->validate($request, [
            'category_name' => 'required|unique:t_category',
        ]);//unique
        $data = Curl::post('/productCategory/categoryAdd',
            $request->all()
        );
        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            return redirect(route('s_goods_category_lists'))->with('addsuccess', 'success');
        }
    }

    /**
     * @Get("/edit", as="s_goods_category_edit")
     */
    public function edit(Request $request){
        $id = $request->get('cid', -1);
        if($id<=0){
            return redirect(route('s_goods_category_lists'));
        }
        $data = Curl::post('/productCategory/getCategory',
            ['id'=>$id]
        );
        return view("Goods.Category.edit")
            ->with('res',$data['data']);
    }

    /**
     * @Post("/editpost", as="s_goods_category_editpost")
     */
    public function editpost(Request $request){
        $this->validate($request, [
            'category_name' => 'required|unique:t_category',
            'id' => 'required',
        ]);
        $data = Curl::post('/productCategory/editCategory',
            $request->all()
        );
        if($data['status'] != 200){
            return back()->withErrors($data['message']);
        }else{
            return redirect(route('s_goods_category_lists'))->with('addsuccess', 'success');
        }
    }

}