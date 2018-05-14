<?php

namespace App\Http\Controllers\User;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use App\Services\OSS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use think\Exception;

/**
 * Class ProductController
 * @Controller(prefix="/User")
 * @Middleware("web")
 * @Middleware("auth")
 * @Middleware("authlogin")
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    /**
     * 测试redis页面
     * @Get("/testRedisTmp", as="s_user_testRedisTmp")
     */
    public function testRedisTmp() {
        Redis::set('a','b');
        $tmp = Redis::get('a');

        $unique = Redis::setnx('f1','f1');
        var_dump($unique);

        $unique = Redis::setnx('f1','f1');
        var_dump($unique);

        var_dump($tmp);die;
    }
    /**
     * 账户总览页面
     * @Get("/accountInfo", as="s_user_accountInfo")
     */
    public function accountInfo() {

        //邀请码
        $code = $this->getRecommendCode();
        return view("User.accountInfo")->with('code',$code);
    }

    /**
     * 物料添加
     * @Get("/addUserInfo", as="s_user_addUserInfo")
     */
    public function addUserInfo() {

        return view("User.addUserInfo");
    }

    /**
     * 物料总览页面
     * @Get("/channeladdUser", as="s_user_channeladdUser")
     */
    public function channeladdUser(Request $request) {

        $uid = $this->getUserId();
        $id = $request->get("id",'');

        try {
            $post = Curl::post('/advert/getAdvertRelativeDetail', $arr = [
                'advert_id' => $uid,
                'id' => $id,
            ]);
        } catch (ApiException $e) {
            return redirect(route('s_user_channelData'));
        }

        $attachment = $post['data']['attachment'];
        $attachment = $attachment ? json_decode($attachment,  true) : null;
        $imageArr  = $attachment['image'] ? explode(',', $attachment['image']) : [];
        $textArr  = $attachment['text'] ? explode(',', $attachment['text']) : [];
        $videoArr  = $attachment['video'] ? explode(',', $attachment['video']) : [];

        $imageNameArr  = isset($attachment['image_name']) ? explode(',', $attachment['image_name']) : $imageArr;
        $textNameArr  = isset($attachment['text_name']) ? explode(',', $attachment['text_name']) : $textArr;
        $videoNameArr  = isset($attachment['video_name']) ? explode(',', $attachment['video_name']) : $videoArr;

        return view("User.channeladdUers")->with('data',$post['data'])
            ->with('attachment', $attachment ? 1 : 0)
            ->with('imageArr', $imageArr)
            ->with('textArr', $textArr)
        ->with('videoArr', $videoArr)
            ->with('imageNameArr', $imageNameArr)
            ->with('textNameArr', $textNameArr)
            ->with('videoNameArr', $videoNameArr);
    }

    /**
     * 渠道数据页面
     * @Get("/channelData", as="s_user_channelData")
     */
    public function channelData() {
        return view("User.channelData");
    }

    /**
     * 渠道数据用户详情页面2
     * @Get("/channelUserDetail", as="s_user_channelUserDetail")
     */
    public function channelUserDetail() {
        return view("User.channelUserDetail");
    }

    /**
     * 渠道数据用户文章详情页面3
     * @Get("/channelUserArticleDetail", as="s_user_channelUserArticleDetail")
     */
    public function channelUserArticleDetail() {
        return view("User.channelUserArticleDetail");
    }

    /**
     * 渠道数据用户文章详情页面4
     * @Get("/channelUserOrderList", as="s_user_channelUserOrderList")
     */
    public function channelUserOrderList() {
        return view("User.channelUserOrderList");
    }


    /**
     * 佣金提现页面1
     * @Get("/commissionInfo", as="s_user_commissionInfo")
     */
    public function commissionInfo() {
        return view("User.commissionInfo");
    }

    /**
     * 未结算佣金明细页面2
     * @Get("/unsettledCommissionFlow", as="s_user_unsettledCommissionFlow")
     */
    public function unsettledCommissionFlow() {
        return view("User.unsettledCommissionFlow");
    }

    /**
     * 余额流水页面3
     * @Get("/balanceFlow", as="s_user_balanceFlow")
     */
    public function balanceFlow() {
        return view("User.balanceFlow");
    }

    /**
     * 账户设置页面
     * @Get("/accountSetting", as="s_user_accountSetting")
     */
    public function accountSetting() {
        return view("User.accountSetting")->with('mobile',$this->getUserName());
    }


    /**
     * 添加物料Ajax
     * @Post("/addDetail", as="s_user_addDetail")
     */
    public function addDetail(Request $request) {
        try {
            $uid = $this->getUserId();

            $name = $request->get("name",'');
            $address = $request->get("address",'');
            $website = $request->get("website",'');
            $head = $request->get("head",'');
            $tel = $request->get("tel",'');
            $describe = $request->get("describe",'');
            $img = $request->get("img",'');
            $zip = json_encode($request->get("zip" , []));
            $note = $request->get("note",'');
            $post = Curl::post('/advert/addAdvertRelativeDetail', $arr = [
                'advert_id' => $uid,
                'name'      => $name,
                'address'   => $address,
                'website'   => $website,
                'head'      => $head,
                'tel'       => $tel,
                'describe'  => $describe,
                'img'       => $img,
                'zip'       => $zip,
                'note'      => $note,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }


    /**
     * 添加物料Ajax
     * @Post("/addCheckDetail", as="s_user_addCheckDetail")
     */
    public function addCheckDetail(Request $request) {
        try {
            $uid = $this->getUserId();

            $name = $request->get("name",'');


            $post = Curl::post('/advert/getChackDetail', $arr = [
                'advert_id' => $uid,
                'name'      => $name,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }


    /**
     * 上传图片
     * @Post("/uploadImg", as="s_user_uploadImg")
     */
    public function uploadImg(Request $request) {

        $extension = $request->file('images')->extension();

        $newname = time().'images.'.$extension;
        $res = $request->file('images')->storeAs('images',$newname,'local2');

        $ossKey =  md5(time().rand().rand());

        OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, public_path('app/').$res);
        $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);

        //压缩原图
       /* $local_path = public_path('app/').$res;
        $fileFrom_325 = $local_path.'_182_122.jpeg';
        $height = \Intervention\Image\Facades\Image::make($local_path)->height();
        $width = \Intervention\Image\Facades\Image::make($local_path)->width();
        //echo $height.'='.$width;exit;
        if($width>$height){
            $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(null, 182, function ($constraint) {
                $constraint->aspectRatio();
            });
        }else{
            $img285 = \Intervention\Image\Facades\Image::make($local_path)->resize(182, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $img285->resizeCanvas(182, 122)->save( $fileFrom_325,100 );
        OSS::publicUpload(\Config::get('alioss.BucketName'), md5($ossKey.'_182_122'), $fileFrom_325);
        $publicObjectURL182 = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), md5($ossKey.'_182_122'));*/

        return new JsonResponse(['status'=>200,'image' => $publicObjectURL,'image_182_122' => $publicObjectURL ]);

    }

    /**
     * 上传zip
     * @Post("/uploadZip", as="s_user_uploadZip")
     */
    public function uploadZip(Request $request) {

        $extension = $request->file('zip')->extension();
        $newname = time().'images.'.$extension;
        $res = $request->file('zip')->storeAs('images', $newname,'local2');

        $ossKey =  md5(time().rand().rand());
        OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey.'.'.$extension, public_path('app/').$res);
        $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'),  $ossKey.'.'.$extension);

        return new JsonResponse(['status'=>200,'image' => $publicObjectURL]);

    }

    /**
     * 上传zip
     * @Post("/newUploadZip", as="s_user_new_uploadZip")
     */
    public function newUploadZip(Request $request)
    {
        try {
            ini_set('memory_limit', '256M');
            $type = $request->get('type', 0);
            $Content = [];
            $upload_handler = new \App\Lib\UploadHandler($request->get('type', 0));
            if (! isset($upload_handler->response['files']) || ! $upload_handler->response['files']) {
                return new JsonResponse(['status' => 400, 'message' => '上传失败']);
            } else if (!($upload_handler->response['files'][0] instanceof \stdClass)) {
                return new JsonResponse(['status' => 400, 'message' => '上传失败']);

            }
            $file = $upload_handler->response['files'][0];
            if (property_exists($file, 'error')) {
                return new JsonResponse(['status' => 400, 'message' => $file->error]);
            }
            if($type==2){
                $Content = [
                    "ContentType"=>"application/octet-stream",
                    "ContentDisposition"=>"attachment;filename = ".$file->name,
                ];
            }

            $path = public_path('files/') . $file->name;
            $extension = pathinfo($file->name, PATHINFO_EXTENSION);
            $ossKey = md5(time() . rand() . rand());
            $funName = \Config::get('alioss.functionName');
            OSS::$funName(\Config::get('alioss.BucketName'), $ossKey . '.' . $extension, $path,$Content);
            $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey . '.' . $extension);
            return new JsonResponse(['status' => 200, 'data' => ['url' => $publicObjectURL,'filename'=>$file->name]]);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 400, 'message' => $e->getMessage()]);
        }
    }

    //apiPost


    /**
     * 获取账户总览数据
     * @Post("/getAccountInfo", as="s_user_getAccountInfo")
     */
    public function getAccountInfo(Request $request) {
        try {
            $uid = $this->getUserId();

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $type = $request->get("type",1);
            $showBy = $request->get("showBy",1);

            $post = Curl::post('/advert/getUserInfoNew', $arr = [
                'advert_id'=> $uid,
                'type' => $type,
                'showBy' => $showBy,
                'page' =>$page,
                'pagesize'=>$pagesize,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 下载渠道数据1
     * @Get("/DownLoadCommissionSettlement", as="s_user_DownLoadCommissionSettlement")
     */
    public function DownLoadCommissionSettlement(Request $request) {

        try {
            $uid = $this->getUserId();

            $download = $request->get("download",false);

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';

            $name = $request->get("name",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'add_time'){
                $order = json_encode(['times'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['account'=>$sort]);
            }
            if($type == 'commission'){
                $order = json_encode(['commission'=>$sort]);
            }
            if($type == 'nums'){
                $order = json_encode(['nums'=>$sort]);
            }


            $post = Curl::post('/smedia/getSmediaList', $arr = [
                'uid'=> $uid,
                'name' => $name,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);

            if($download && count($post['data']['data'])>0){

                $data = $post['data']['data'];
                \Excel::create('原创内容数据',function($excel) use ($data){
                    $excel->sheet('score', function($sheet) use ($data){
                        $zarray=[];
                        foreach ($data as $key=> $value){
                            $zarray[$key]['自媒体']         = $value['nickname'];
                            $zarray[$key]['接入时间']         =  date('Y-m-d H:i:s',$value['add_time']);
                            $zarray[$key]['媒体行业']         = $value['IndustryName'];
                            $zarray[$key]['成交次数']      = $value['nums'];
                            $zarray[$key]['交易总额']  = $value['account'];
                            $zarray[$key]['佣金']       = $value['commission'];

                        }
                        $sheet->fromArray($zarray);
                    });
                })->export('xls');
                exit;

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
     * 获取渠道数据1
     * @Post("/getCommissionSettlement", as="s_user_getCommissionSettlement")
     */
    public function getCommissionSettlement(Request $request) {
        try {
            $uid = $this->getUserId();

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';

            $name = $request->get("name",'');
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'number'){
                $order = json_encode(['number'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['account'=>$sort]);
            }
            if($type == 'commissions'){
                $order = json_encode(['commission'=>$sort]);
            }
            if($type == 'count'){
                $order = json_encode(['count'=>$sort]);
            }
            if(is_null($name)){
                $name = '';
            }
//            var_dump([
//                'advert_id'=> $uid,
//                'name' => $name,
//                'order' => $order,
//                'page' =>$page,
//                'pagesize'=>$pagesize,
//                'starttime'=>$starttime,
//                'endtime'=>$endtime,
//            ]);die;

            $post = Curl::post('/advert/getChannelReportListNew', $arr = [
                'advert_id'=> $uid,
                'name' => $name,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取我添加的广告主信息列表
     * @Post("/getMyAgentAdvertRelative", as="s_user_getMyAgentAdvertRelative")
     */
    public function getMyAgentAdvertRelative(Request $request) {
        try {
            $uid = $this->getUserId();
            $review_status = $request->get("review_status");
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);
            $order = '';

            $post = Curl::post('/advert/getMyAgentAdvertRelative', $arr = [
                'advert_id'=> $uid,
                'review_status' => $review_status,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
            ]);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }
    
    /**
     * 获取我添加的某个广告主信息
     * @Get("/getAdvertRelativeDetail", as="s_user_getAdvertRelativeDetail")
     * @Post("/getAdvertRelativeDetail", as="s_user_getAdvertRelativeDetail")
     */
    public function getAdvertRelativeDetail(Request $request) {
        $uid = $this->getUserId();
        $id = $request->get("id",'');

        try {
            $post = Curl::post('/advert/getAdvertRelativeDetail', $arr = [
                'advert_id' => $uid,
                'id' => $id,
            ]);
            
            $attachmentsPost = Curl::post('/advert/getAdvertRelativeAttachments', $arr = [
                'advert_id' => $uid,
                'detail_id' => $id,
            ]);
            
            $attachment = $post['data']['attachment'];
            $attachment = $attachment ? json_decode($attachment,  true) : null;
            $imageArr  = $attachment['image'] ? explode(',', $attachment['image']) : [];
            $textArr  = $attachment['text'] ? explode(',', $attachment['text']) : [];
            $videoArr  = $attachment['video'] ? explode(',', $attachment['video']) : [];

            $imageNameArr  = isset($attachment['image_name']) ? explode(',', $attachment['image_name']) : $imageArr;
            $textNameArr  = isset($attachment['text_name']) ? explode(',', $attachment['text_name']) : $textArr;
            $videoNameArr  = isset($attachment['video_name']) ? explode(',', $attachment['video_name']) : $videoArr;
            
            $firstAttachment = [
            	'image' => $imageArr,
            	'text'  => $textArr,
            	'video' => $videoArr,
            	'image_name' => $imageNameArr,
            	'text_name' => $textNameArr,
            	'video_name' => $videoNameArr,
            ];
            $post['data']['attachmentArr'] = $firstAttachment;
            
            $allAttachments = [];
            $firstAttachment['add_time'] = $post['data']['add_time'];
            $firstAttachment['describe'] = $post['data']['advert_company_describe'];
            $firstAttachment['status_show'] = $post['data']['review_status']==3 ? '已处理' : '未处理';
//            $allAttachments[] = $firstAttachment;
            if($attachmentsPost['data']) {
                foreach($attachmentsPost['data'] as $attachmentLine) {
                    $_attachment = [];
                    
                    $attachment = $attachmentLine['attachment'];
                    $attachment = $attachment ? json_decode($attachment,  true) : null;
                    $imageArr  = $attachment['image'] ? explode(',', $attachment['image']) : [];
                    $textArr  = $attachment['text'] ? explode(',', $attachment['text']) : [];
                    $videoArr  = $attachment['video'] ? explode(',', $attachment['video']) : [];

                    $imageNameArr  = isset($attachment['image_name']) ? explode(',', $attachment['image_name']) : $imageArr;
                    $textNameArr  = isset($attachment['text_name']) ? explode(',', $attachment['text_name']) : $textArr;
                    $videoNameArr  = isset($attachment['video_name']) ? explode(',', $attachment['video_name']) : $videoArr;
                    
                    $_attachment['image'] = $imageArr;
                    $_attachment['text'] = $textArr;
                    $_attachment['video'] = $videoArr;
                    $_attachment['image_name'] = $imageNameArr;
                    $_attachment['text_name'] = $textNameArr;
                    $_attachment['video_name'] = $videoNameArr;
                    $_attachment['add_time'] = $attachmentLine['add_time'];
                    $_attachment['describe'] = $attachmentLine['attachment_describe'];
                    $_attachment['status_show'] = $attachmentLine['status_show'];
                    $allAttachments[] = $_attachment;
                }
            }
            
            $post['data']['allAttachmentArr'] = $allAttachments;
            
            return new JsonResponse($post);
            
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }
    
    /**
     * 添加广告主商品物料信息
     * @Post("/appendAdvertRelativeAttachment", as="s_user_appendAdvertRelativeAttachment")
     */
    public function appendAdvertRelativeAttachment(Request $request) {
        try {
            $uid = $this->getUserId();
            $id = $request->get("detail_id");
            $describe = $request->get("describe");
            $attachment = json_encode($request->get("zip", []));

            $postArr = [
                'advert_id' => $uid,
                'detail_id' => $id,
                'describe' => $describe,
                'attachment' => $attachment
            ];
            
            $post = Curl::post('/advert/appendAdvertRelativeAttachment', $postArr);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }
    
    /**
     * 获得广告主商品物料信息 分页
     * @Get("/getAdvertRelativeAttachmentsPage", as="s_user_getAdvertRelativeAttachmentsPage")
     * @Post("/getAdvertRelativeAttachmentsPage", as="s_user_getAdvertRelativeAttachmentsPage")
     */
    public function getAdvertRelativeAttachmentsPage(Request $request) {
        try {
            $uid = $this->getUserId();
            $id = $request->get("detail_id");
            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            $postArr = [
                'advert_id' => $uid,
                'detail_id' => $id,
                'page' => $page,
                'pagesize' => $pagesize
            ];
            
            $post = Curl::post('/advert/getAdvertRelativeAttachmentsPage', $postArr);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 修改我添加的广告主信息
     * @Post("/editAdvertRelativeDetail", as="s_user_editAdvertRelativeDetail")
     */
    public function editAdvertRelativeDetail(Request $request) {
        try {
            $uid = $this->getUserId();
            $id = $request->get("id");
            $name = $request->get("name");
            $address = $request->get("address");
            $website = $request->get("website");
            $head = $request->get("head");
            $tel = $request->get("tel");
            $describe = $request->get("describe");
            $img = $request->get("img");
            $zip = $request->get("zip", []);
            $note = $request->get("note");
            

            $postArr = [
                'advert_id' => $uid,
                'id'        => $id,
            ];
            if(!is_null($name)) {
                $postArr['name'] = $name;
            }
            if(!is_null($address)) {
                $postArr['address'] = $address;
            }
            if(!is_null($website)) {
                $postArr['website'] = $website;
            }
            if(!is_null($head)) {
                $postArr['head'] = $head;
            }
            if(!is_null($tel)) {
                $postArr['tel'] = $tel;
            }
            if(!is_null($describe)) {
                $postArr['describe'] = $describe;
            }
            if(!is_null($img)) {
                $postArr['img'] = $img;
            }
            if(!is_null($zip)) {
                $postArr['zip'] = json_encode($zip);
            }
            if(!is_null($note)) {
                $postArr['note'] = $note;
            }
            $post = Curl::post('/advert/editAdvertRelativeDetail', $postArr);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取渠道数据 个人详情页面 2
     * @Post("/getUserDetail", as="s_user_getUserDetail")
     */
    public function getUserDetail(Request $request) {
        try {
            $uid = $this->getUserId();

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';


            $advert_relative_uid = $request->get("advert_relative_uid",1);

            $channel_name = $request->get('realname');
            \Session::put('advert_relative_uid',$advert_relative_uid);
            if($channel_name){
                \Session::put('channel_name',$channel_name);
            }else{
                $channel_name =  \Session::get('channel_name');
            }

            $name = $request->get("name",'');

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'number'){
                $order = json_encode(['number'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['account'=>$sort]);
            }
            if($type == 'commission'){
                $order = json_encode(['commission'=>$sort]);
            }
            if(is_null($name)){
                $name = '';
            }
//            var_dump([
//                'advert_relative_id'=> $advert_relative_uid,
//                'advert_id' => $uid,
//                'name' => $name,
//                'order' => $order,
//                'page' =>$page,
//                'pagesize'=>$pagesize,
//                'starttime'=>$starttime,
//                'endtime'=>$endtime,
//            ]);die;

            $post = Curl::post('/advert/getAdvertRelativeProListNew', $arr = [
                'advert_relative_id'=> $advert_relative_uid,
                'advert_id' => $uid,
                'name' => $name,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);

            $post['data']['advert_relative_uid']  = $advert_relative_uid;
            $post['data']['channel_name']  = urldecode($channel_name);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取渠道数据 文章订单详情页面 3
     * @Post("/getArticleDetail", as="s_user_getArticleDetail")
     */
    public function getArticleDetail(Request $request) {
        try {
            $uid = $this->getUserId();

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';

            $product_id = $request->get("product_id",1);
            $product_name = $request->get('product_name');
            \Session::put('product_id',$product_id);
            if($product_name){
                \Session::put('product_name',$product_name);
            }else{
                $product_name =  \Session::get('product_name');
            }


            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'count'){
                $order = json_encode(['count'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['account'=>$sort]);
            }
            if($type == 'commission'){
                $order = json_encode(['commission'=>$sort]);
            }

            $channel_name =  \Session::get('channel_name');
            $advert_relative_uid =  \Session::get('advert_relative_uid');

            $post = Curl::post('/advert/getAdvertArticleListNew', $arr = [
                'advert_id'=> $uid,
                'advert_relative_id' => $advert_relative_uid,
                'product_id' => $product_id,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);



            $post['data']['advert_relative_uid']  = $advert_relative_uid;
            $post['data']['channel_name']  = urldecode($channel_name);
            $post['data']['product_id']    = $product_id;
            $post['data']['product_name']  = urldecode($product_name);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }


    /**
     * 获取渠道数据 内容数据 4
     * @Post("/getOrderList", as="s_user_getOrderList")
     */
    public function getOrderList(Request $request) {
        try {
            $uid = $this->getUserId();

            //初始化数据
            $now = getCurrentTime();
            $starttime = 0;
            $endtime = 0;
            $order = '';

            $article_id = $request->get("article_id",1);


            $article_name = $request->get('article_name');
            if($article_name){
                \Session::put('article_name',$article_name);
            }else{
                $article_name =  \Session::get('article_name');
            }


            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);


            $time_fliter = $request->get("time_fliter",'all');//all  recent_30 recent_7 today
            $type = $request->get("type",'all');//add_time  account commission
            $sort = $request->get("sort",'asc');//asc,desc
            //时间过滤
            if($time_fliter == 'recent_30'){
                $starttime = strtotime('-30 day',$now);
            }
            if($time_fliter == 'recent_7'){
                $starttime = strtotime('-7 day',$now);
            }
            if($time_fliter == 'today'){
                $starttime = strtotime(date('Y-m-d',$now));
            }
            //排序
            if($type == 'count'){
                $order = json_encode(['count'=>$sort]);
            }
            if($type == 'account'){
                $order = json_encode(['account'=>$sort]);
            }
            if($type == 'commission'){
                $order = json_encode(['commission'=>$sort]);
            }

            $advert_relative_uid =  \Session::get('advert_relative_uid');

            $post = Curl::post('/advert/getAdvertOrderListNew', $arr = [
                'advert_id'=> $uid,
                'advert_relative_id' => $advert_relative_uid,
                'article_id' => $article_id,
                'order' => $order,
                'page' =>$page,
                'pagesize'=>$pagesize,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
            ]);

            $channel_name =  \Session::get('channel_name');
            $product_name =  \Session::get('product_name');
            $product_id =  \Session::get('product_id');

            $post['data']['advert_relative_uid']  = $advert_relative_uid;
            $post['data']['channel_name']  = urldecode($channel_name);
            $post['data']['product_id']    = $product_id;
            $post['data']['product_name']  = urldecode($product_name);
            $post['data']['article_name']  = urldecode($article_name);

            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取佣金提现页面数据1
     * @Post("/getUserWithdrawPage", as="s_user_getUserWithdrawPage")
     */
    public function getUserWithdrawPage(Request $request) {
        try {
            $uid = $this->getUserId();


//            $uid = 94;

            $post = Curl::post('/advert/getWithdrawInfo', $arr = [
                'advert_id'=> $uid,
            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取未结算佣金页面数据2
     * @Post("/getUserUnsettledPage", as="s_user_getUserUnsettledPage")
     */
    public function getUserUnsettledPage(Request $request) {
        try {
            $uid = $this->getUserId();


//            $uid = 94;

            //时间过滤
            $starttime = $request->get("starttime",'');
            $endtime = $request->get("endtime",'');

            $name = $request->get("name",'');

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            if(strlen($starttime) >0){
                $starttime = strtotime(date('Y-m-d',strtotime($starttime)));
            }else{
                $starttime = 0;
            }

            if(strlen($endtime) >0){
                $endtime = strtotime(date('Y-m-d 23:59:59',strtotime($endtime)));
            }else{
                $endtime = 0;
            }

            $post = Curl::post('/advert/getUnsettledComissionList', $arr = [
                'advert_id'=> $uid,
                'starttime' =>$starttime,
                'endtime' =>$endtime,
                'name'=>$name,
                'page' =>$page,
                'pagesize'=>$pagesize,

            ]);
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取余额明细页面数据3
     * @Post("/getUserAccFLowPage", as="s_user_getUserAccFLowPage")
     */
    public function getUserAccFLowPage(Request $request) {
        try {
            $uid = $this->getUserId();


//            $uid = 94;

            $page = $request->get("page",1);
            $pagesize = $request->get("pagesize",10);

            $post = Curl::post('/advert/getSmediaUserAccFLowPage', $arr = [
                'advert_id'=> $uid,
                'page' =>$page,
                'pagesize'=>$pagesize,
            ]);



            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取绑定的银行卡数据
     * @Post("/getAccountSetting", as="s_user_getAccountSetting")
     */
    public function getAccountSetting(Request $request) {
        //绑定的银行卡  传递bid hidden  是否接口
        //获取银行卡
        try {
            $bank = Curl::post('/user/getBindBankCard', ['uid' => $this->getUserId()]);
//            $bid = $bank['data']['id'];
//            $banknumber = $bank['data']['banknumber'];
//            $realname = $bank['data']['realname'];
            $bank['data']['sendMobile'] = $this->getUserName();
            return new JsonResponse($bank);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }

    }

    /**
     * 绑定银行卡
     * @Post("/bindBankCard", as="s_user_bindBankCard")
     */
    public function bindBankCard(Request $request) {
        try {
            $realname = $request->get("bind_realname",'');
            $idnumber = $request->get("bind_idnumber",'');
            $bind_banknumber = $request->get("bind_banknumber",'');
            $bind_mobile = $request->get("bind_mobile",'');
            $bank_relative = $request->get("bank_relative",0);

            if(strlen($realname)<=0 || strlen($bind_banknumber)<=0 ){
                return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
            }
            $post = Curl::post('/user/bindBankCard', [
                'realname' => $realname,
                'idnumber' => '001',
                'banknumber' => $bind_banknumber,
                'mobile' => '001',
                'bank_relative'=>$bank_relative,
                'sub_branch_name' => '支行信息',
                'sub_branch_id' => '10011111',
                'uid' => $this->getUserId()
            ]);
            $aa['ids'] = $post['data']['id'];
            $aa['banknumbs'] = $post['data']['banknumber'];
            $aa['realname'] = $post['data']['realname'];
            $post['data'] = $aa;
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取银行卡列表数据
     * @Post("/getUserBankRelative", as="s_user_getUserBankRelative")
     */
    public function getUserBankRelative(Request $request) {
        try {

            $id = $request->get("id",0);
            $post = Curl::post('/user/getUserBankRelative',
                [
                    // 'id' => $id
                ]
            );
            return new JsonResponse($post);
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 解绑银行卡
     * @Post("/unbindBankCard", as="s_user_unbindBankCard")
     */
    public function unbindBankCard(Request $request) {
        try {
            $session = \Session::get("unbind");
            $session = json_decode($session, true);
            if (!isset($session['order_number'])) {
                return new JsonResponse(['status'=>201,'message'=>'数据错误']);
            }else{

                $code = $request->get("code",0);
                $mobile = $request->get("mobile");
                $mobile = $this->getUserName();
                if(strlen($code)<=0 || !is_numeric($code)){
                    return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
                }
                $post = Curl::post('/utils/message/verificationSms', [
                    'mobile' => $mobile,
                    'code' => $code,
                    'type' => 3,
                    'order_number' => Arr::get($session, 'order_number', ''),
                ]);
                if(!is_null($post['data'])){
                    $bid = $request->get("bid",-1);

                    if(intval($bid) && $bid > 0){
                        $post = Curl::post('/user/unbindBankCard', [
                            'bid' => $bid,
                        ]);
                        return new JsonResponse($post);
                    }
                }

            }
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 切换银行卡
     * @Post("/changeBankCard", as="s_user_changeBankCard")
     */
    public function changeBankCard(Request $request) {
        try {
            $session = \Session::get("change");
            $session = json_decode($session, true);
            if (!isset($session['order_number'])) {

            }else{
                $code = $request->get("code",0);
                if(strlen($code)<=0 || !is_numeric($code)){
                    return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
                }
                $mobile = $request->get("mobile");
                $mobile = $this->getUserName();
                $post = Curl::post('/utils/message/verificationSms', [
                    'mobile' => $mobile,
                    'code' => $code,
                    'type' => 3,
                    'order_number' => Arr::get($session, 'order_number', ''),
                ]);
                if(!is_null($post['data'])){
                    $bid = $request->get("bid",-1);

                    if(intval($bid) && $bid > 0){
                        $post = Curl::post('/user/unbindBankCard', [
                            'bid' => $bid,
                        ]);
                        return new JsonResponse($post);
                    }
                }else{
//                    var_dump($post);

                }

            }
        } catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }
    /**
     * 发送银行卡验证码
     * @Post("/sendBankSms", as="s_user_sendBankSms")
     */
    public function sendBankSms(Request $request) {
        try {
            $mobile = $this->getUserName();
            $type = $request->get("type",-1);
            if(intval($type) && $type >0){
                $post = Curl::post('/utils/message/createMsg', [
                    'mobile' => $mobile,
                    'type' => 3
                ]);
                $data = $post['data'];
                unset($post['data']);

                if($type == 1){
                    //解绑
                    $name = 'unbind';
                }else{
                    //切换
                    $name = 'change';
                }
                \Session::put($name, json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
                return new JsonResponse($post);
            }else{
                return new JsonResponse(['status'=>333,'message'=>'传递参数非法或者缺少参数']);
            }
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }catch (\Exception $e){
            return new JsonResponse([
                "status"=>477,
                "message"=>'输入错误',
            ]);
        }
    }

    /**
     * 修改密码
     * @Post("/changePassword", as="s_user_changePassword")
     */
    public function changePassword(Request $request) {
        try {
            $oldpas = $request->get("oldpas",'');
            $newpas = $request->get("newpas",'');
            $newpas2 = $request->get("newpas2",'');

            if(strlen($oldpas) <=0 || strlen($newpas) <=0 || strlen($newpas2) <=0){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }

            $post = Curl::post('/user/editPassword', [
                'username' => $this->getUserName(),
                'oldpasswd' => $oldpas,
                'passwd' => $newpas,
                'passconfirm' => $newpas2,
            ]);
            return new JsonResponse($post);
        }catch (ApiException $e) {
            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
        catch (\Exception $e){
            return new JsonResponse([
                "status"=>477,
                "message"=>'输入错误',
            ]);
        }
    }

    /**
     * 发送申请提现短信
     * @Post("/sendDrawSms", as="s_sms_sendDrawSms")
     */
    public function sendDrawSms(Request $request) {

        try {
            $mobile = $this->getUserName();
            $type = $request->get("type",0);

            if($type <=0 || !is_numeric($type)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }

            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => $type
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("DrawSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 提现申请验证短信
     * @Post("/validatorDrawSms", as="s_sms_validatorDrawSms")
     */
    public function validatorDrawSms(Request $request) {
        try {
            $mobile = $this->getUserName();
            $code = $request->get("code");
            if($code <=0 || !is_numeric($code)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("DrawSms");
            $session = json_decode($session, true);
            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 4,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);
            \Session::put("validatorDrawSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 申请提现
     * @Post("/withdrawalApplication", as="s_draw_withdrawalApplication")
     */
    public function withdrawalApplication(Request $request) {
        try {
            $account = $request->get("account",0);
            $bank_id = $request->get("bank_id",0);
            if($account <=0 || !is_numeric($account) || $bank_id <=0 || !is_numeric($bank_id)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("validatorDrawSms");
            $session = json_decode($session, true);



            $post = Curl::post('/user/withdrawalApplication', [
                'uid' => $this->getUserId(),
                'bank_id' => $bank_id,
                'account' => $account,
                'account_prop'=>2,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);


            $data = $post['data'];
            //unset($post['data']);
            return new JsonResponse($data);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 获取实名信息
     * @Post("/getAuthInfo", as="s_user_getAuthInfo")
     */
    public function getAuthInfo(Request $request) {
        try {

            $post = Curl::post('/user/getAuthInfo', [
                'uid' => $this->getUserId(),
            ]);


//            $data = $post['data'];
            //unset($post['data']);
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 发送实名短信
     * @Post("/sendAuthSms", as="s_sms_sendAuthSms")
     */
    public function sendAuthSms(Request $request) {

        try {
            $mobile = $this->getUserName();
//            $type = $request->get("type",0);
            $type = 5;
            if($type <=0 || !is_numeric($type)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $post = Curl::post('/utils/message/createMsg', [
                'mobile' => $mobile,
                'type' => $type
            ]);
            $data = $post['data'];
            unset($post['data']);
            \Session::put("sendAuth", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));
            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 实名验证短信
     * @Post("/validatorAuthSms", as="s_sms_validatorAuthSms")
     */
    public function validatorAuthSms(Request $request) {
        try {
            $mobile = $this->getUserName();
            $code = $request->get("code");
            if($code <=0 || !is_numeric($code)){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("sendAuth");
            $session = json_decode($session, true);
            $post = Curl::post('/utils/message/verificationSms', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => 5,
                'order_number' => Arr::get($session, 'order_number', ''),
            ]);

            $data = $post['data'];
            unset($post['data']);
            \Session::put("validatorAuthSms", json_encode(["mobile"=>$mobile, "order_number"=>$data['order_number']]));

            return new JsonResponse($post);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }

    /**
     * 实名申请验证
     * @Post("/authVerify", as="s_auth_authVerify")
     */
    public function authVerify(Request $request) {
        try {
            $idno = $request->get("idno",'');
            $realname = $request->get("realname",'');
            if(strlen($idno)<15 || mb_strlen($realname)<2){
                return new JsonResponse([
                    "status"=>'333',
                    "message"=>'传递参数非法或者缺少参数',
                ]);
            }
            $session = \Session::get("validatorAuthSms");
            $session = json_decode($session, true);


            $post = Curl::post('/user/userAuthentication', [
                'uid' => $this->getUserId(),
                'mobile' => $this->getUserName(),
                'idno' => $idno,
                'realname' => $realname,
                'order_number' => Arr::get($session, 'order_number', ''),
                'verify_type'=>5
            ]);


            $post = Curl::post('/user/userBindAuthentication', [
                'id' => $this->getUserId(),
                'order_number' => $post['data'],
            ]);

            if($post['data']){
                return new JsonResponse([
                    "status" => '200',
                    "message" => '绑定成功',
                ]);
            }
            return new JsonResponse([
                "status" => '202',
                "message" => '绑定失败',
            ]);

//            $data = $post['data'];
            //unset($post['data']);
        } catch (ApiException $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }



    private function getUserId() {
        return \Auth::getUser()->getAuthIdentifier();
//        return 94;

    }

    private function getUserName() {
        return \Auth::getUser()->getUserMobile();
    }

    private function getRecommendCode() {
        return \Auth::getUser()->getRecommendCode();
    }

    /**
     * 广告主数据列表
     * @Get("/channelList", as="s_user_channelList")
     */
    public function channelList() {
        return view("User.channelList");
    }

    /**
     * 编辑广告主
     * @Get("/channelEditUser", as="s_user_channelEditUser")
     */
    public function channeleditUser(Request $request) {
        return view("User.channelEditUser");
    }

    /**
     * 广告主商品列表
     * @Get("/channelShops", as="s_user_channelShops")
     */
    public function channelShops() {
        return view("User.channelShops");
    }

    /**
     * 广告主添加商品
     * @Get("/channelAddShops", as="s_user_channelAddShops")
     */
    public function channelAddShops() {
        return view("User.channelAddShops");
    }

}