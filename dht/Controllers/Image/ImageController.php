<?php

namespace App\Http\Controllers\Image;
use App\Http\Controllers\Controller;
use App\Lib\Curl;
use App\Services\OSS;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ProductController
 * @Controller(prefix="/Image")
 * @package App\Http\Controllers
 */
class ImageController extends Controller
{
    /**
     *
     * @Get("/imageGet", as="s_image_get")
     * @Post("/imageGet", as="s_image_get")
     */
    public function imgageGet(Request $request) {
        header('Access-Control-Allow-Origin: http://127.0.0.1'); //设置http://www.baidu.com允许跨域访问
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
        $file_path = public_path("lib/ueditor/1.4.3/php/");
        //?action=config  &callback=bd__editor__hh5qqd
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($file_path."config.json")), true);
        switch ($request->get('action')) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = include($file_path."action_upload.php");
                break;

            /* 列出图片 */
            case 'listimage':
                $result = include($file_path."action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include($file_path."action_list.php");
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = include($file_path."action_crawler.php");
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        if ($request->get("callback", "")) {
            if (preg_match("/^[\w_]+$/", $request->get("callback"))) {
                echo htmlspecialchars($request->get("callback")) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;exit;
        }
    }

    /**
     * @Post("/upload", as="s_index_upload")
     */
    public function upload(Request $request) {

        $img_path = $request->get('img_path');

     /*   if(strstr($img_path,"mmbiz.qpic.cn")){
           // $url = 'https://mmbiz.qpic.cn/mmbiz_jpg/NrC5Dj1o18goGa11jkGKjc8JWYcveaCayyEia4RNCWDumoMj9GEjPAIAaywWHBvibXdfByjiaFbwKwHsTGTBibgliaA/640?wx_fmt=jpeg&wxfrom=5&wx_lazy=1';
            $arr = parse_url($img_path);
            $arr_query = $this->convertUrlQuery($arr['query']);
            $imgName = '/public/uploads/'.date('Ymdhis').rand(10000,99999).'.'.$arr_query['wx_fmt'];
            file_put_contents(base_path().$imgName, file_get_contents($img_path));

            $img_path = $imgName;
            return response($img_path,'200');
        }*/

        $ossKey =  md5(time().rand().rand());
        OSS::publicUpload(\Config::get('alioss.BucketName'), $ossKey, $img_path);
        $publicObjectURL = OSS::getPublicObjectURL(\Config::get('alioss.BucketName'), $ossKey);



        return response($publicObjectURL,'200');
    }

    function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

}