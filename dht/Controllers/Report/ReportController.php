<?php

namespace App\Http\Controllers\Report;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;

/**
 * Class ReportController
 * @Controller(prefix="/Report")
 * @Middleware("web")
 * @Middleware("auth")
 * @package App\Http\Controllers
 */
class ReportController extends Controller
{
    /**
     * 本月未结算佣金
     * @Get("/settleCommission", as="s_report_settleCommission")
     * @Post("/settleCommission", as="s_report_settleCommission")
     */
    public function settleCommission(Request $request) {
        //echo date('Y-m-d H:i:s',strtotime('-1 month '.date('Y-m-01 00:00:00')));
        if ($request->ajax()) {
            $page = $request->get('page',1);
            $pageszie = $request->get('pageszie',10);
            $mobile = $request->get('mobile','');
            $type = $request->get('type','');


            if($type == 3){
                $joinRule = \DB::table("t_user_commission_record")->select('t_user_commission_record.add_time','t_user_commission_record.account','t_user_commission_record.available_amount as after_account',\DB::raw('"2" as type'))
                    ->leftJoin('t_user_login', 't_user_commission_record.uid', '=', 't_user_login.uid');
                if(strlen($mobile)>0){
                    $joinRule = $joinRule->where('t_user_login.username',$mobile);
                }else{
                    $joinRule = $joinRule->where('t_user_login.username','-1');
                }
                $joinRule = $joinRule->where('t_user_commission_record.type','1');//查询未结算佣金
                $joinRule = $joinRule->orderBy('t_user_commission_record.add_time','desc');
                $joinRule = $joinRule->orderBy('t_user_commission_record.id','desc');

                $joinRule = $joinRule->paginate($pageszie,['*'],'page',$page);
            }else{
                $joinRule = \DB::table("t_user_commission")
                            ->leftJoin('t_user_login', 't_user_commission.uid', '=', 't_user_login.uid');

                if(strlen($mobile)>0){
                    $joinRule = $joinRule->where('t_user_login.username',$mobile);
                }else{
                    $joinRule = $joinRule->where('t_user_login.username','-1');
                }
                if( $type == 1 ){//上月
                    $start_time = strtotime('-1 month '.date('Y-m-01 00:00:00'));
                    $end_time = strtotime(date('Y-m-01 00:00:00'));
                    $joinRule = $joinRule->where('t_user_commission.add_time','>=',$start_time);
                    $joinRule = $joinRule->where('t_user_commission.add_time','<',$end_time);
                }elseif ($type == 2){//本月
                    $start_time = strtotime(date('Y-m-01 00:00:00'));
                    $joinRule = $joinRule->where('t_user_commission.add_time','>=',$start_time);
                }

                $joinRule = $joinRule->where('t_user_commission.status','1');//查询未结算佣金
                $joinRule = $joinRule->orderBy('t_user_commission.add_time','desc');
                $joinRule = $joinRule->orderBy('t_user_commission.id','desc');
                $joinRule = $joinRule->paginate($pageszie,['*'],'page',$page);
            }

            if(strlen($mobile)>0){
                //总未结算
                $account = \DB::table("t_user_commission")
                    ->leftJoin('t_user_login', 't_user_commission.uid', '=', 't_user_login.uid')
                    ->where('t_user_login.username',$mobile)
                    ->where('t_user_commission.status','1')
                    ->sum('t_user_commission.account');
                //本月未结算
                $start_time = strtotime(date('Y-m-01 00:00:00'));
                $current_account = \DB::table("t_user_commission")
                    ->leftJoin('t_user_login', 't_user_commission.uid', '=', 't_user_login.uid')
                    ->where('t_user_login.username',$mobile)
                    ->where('t_user_commission.add_time','>=',$start_time)
                    ->where('t_user_commission.status','1')
                    ->sum('t_user_commission.account');
                //上月未结算
                $start_time = strtotime('-1 month '.date('Y-m-01 00:00:00'));
                $end_time = strtotime(date('Y-m-01 00:00:00'));
                $before_account = \DB::table("t_user_commission")
                    ->leftJoin('t_user_login', 't_user_commission.uid', '=', 't_user_login.uid')
                    ->where('t_user_login.username',$mobile)
                    ->where('t_user_commission.status','1')
                    ->where('t_user_commission.add_time','>=',$start_time)
                    ->where('t_user_commission.add_time','<',$end_time)
                    ->sum('t_user_commission.account');

                //可提现佣金
                $available_amount = \DB::table("t_user_account")->leftJoin('t_user_login', 't_user_account.uid', '=', 't_user_login.uid')->where('t_user_login.username',$mobile)->sum('t_user_account.available_amount');

            }else{
                $account = 0;
                $current_account = 0;
                $before_account = 0;
                $available_amount = 0;

            }

            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $joinRule->total(),
                'iTotalDisplayRecords' => $joinRule->total(),
                'aaData'            => $joinRule->items(),
                'account'           => number_format($account,2),
                'current_account'   => number_format($current_account,2),
                'before_account'    => number_format($before_account,2),
                'available_amount'  => number_format($available_amount,2),
            ];


        //$joinRule = $joinRule->paginate($pageszie,['*'],'page',$page);
            return new JsonResponse($return);
        }
        return view("Report.settleCommission");
    }

    /**
     * 资讯
     * @Get("/information", as="s_report_information")
     * @Post("/information", as="s_report_information")
     */
    public function information(Request $request) {
        //echo date('Y-m-d H:i:s',strtotime('-1 month '.date('Y-m-01 00:00:00')));
        if ($request->ajax()) {
            $page = $request->get('page',1);
            $pageszie = $request->get('pageszie',10);
            $mobile = $request->get('mobile','');



            $joinRule = \DB::table("t_article_product_relate")
                ->select('t_article.name','t_article_product_relate.number','t_article_product_relate.commission_account','t_product_division.channelpercent')
                ->leftJoin('t_user_login', 't_article_product_relate.uid', '=', 't_user_login.uid')
                ->leftJoin('t_article', 't_article_product_relate.article_id', '=', 't_article.id')
                ->leftJoin('t_product_division', 't_article_product_relate.product_id', '=', 't_product_division.product_id');

            if(strlen($mobile)>0){
                $joinRule = $joinRule->where('t_user_login.username',$mobile);
            }else{
                $joinRule = $joinRule->where('t_user_login.username','-1');
            }

            $joinRule = $joinRule->orderBy('t_article_product_relate.add_time','desc');
            $joinRule = $joinRule->orderBy('t_article_product_relate.id','desc');
            $joinRule = $joinRule->paginate($pageszie,['*'],'page',$page);


            $return = [
                'initEcho' => 1,
                'iTotalRecords' => $joinRule->total(),
                'iTotalDisplayRecords' => $joinRule->total(),
                'aaData'            => $joinRule->items(),
            ];


            //$joinRule = $joinRule->paginate($pageszie,['*'],'page',$page);
            return new JsonResponse($return);
        }
        return view("Report.information");
    }


}