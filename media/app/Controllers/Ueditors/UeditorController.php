<?php

namespace App\Http\Controllers\Ueditors;

use App\Http\Controllers\Controller;

use App\Services\Uploader;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;


/**
 * Class GoodsController
 * @Controller(prefix="/Ueditors")
 * @package App\Http\Controllers
 */
class UeditorController extends Controller
{
    private $config;
    /**
     * UeditorController constructor.
     */
    public function __construct()
    {
        $this->config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(public_path('config.json'))), true);
    }


    /**
     * @Get("/imgageGet", as="s_ueditor_imgageGet")
     * @Post("/imgageGet", as="s_ueditor_imgageGet")
     */
    public function imgageGet(Request $request) {

        $action = $request->get('action');
        $callback = $request->get('callback');
        switch ($action) {
            case 'config':
                $result =  ($this->config);
                break;
            /* 上传图片 */
            case 'uploadimage':
                $result = $this->actionUpload($action);
                break;
            case 'uploadbase64image':
                $result = $this->actionUpload('uploadimage', 'base64');
                break;
            /* 抓取远程文件 */
            case 'catchimage':
                $result = $this->actionCrawler($request);
                break;
            default:
                $result = (array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        /* if (isset($callback)) {
             if (preg_match("/^[\w_]+$/", $callback)) {
                 $html =  htmlspecialchars($callback) . '(' . $result . ')';
                 return new Response($html);
             } else {
                 $result =  array(
                     'state'=> 'callback参数不合法'
                 );
             }
         }*/
        return new JsonResponse($result);
    }
    
    /**
     * word文件转换html
     * @Post("word2html", as="s_ueditor_word2html")
     */
    public function word2html(Request $request) {
        try {
            $saveDir = '/ueditor/php/upload/word/'.date('Ymd').'/';
            $saveName = md5(mt_rand().time());
            $this->_uploadWordFile('upload', $saveDir, $saveName);
            $htmlPath = $this->_convertWordToHtml($saveName, $saveDir);
            $wordHtmlContent = file_get_contents($htmlPath);
            $matchImgs = [];
            preg_match_all('/<img src="([^"]+)"/', $wordHtmlContent, $matchImgs);
            $replaceImgs = [];
            if($matchImgs && $matchImgs[1]) {
                foreach($matchImgs[1] as $_imgPath) {
//                    $replaceName = $this->_saveBase64Img($base64Img, $saveDir);
//                    $replaceImgs[] = $replaceName ? $replaceName : '';
                    $replaceImgs[] = $saveDir.str_replace('../','',$_imgPath);
                }
                $wordHtmlContent = str_replace($matchImgs[1], $replaceImgs, $wordHtmlContent);
            }
            
            return new JsonResponse([
                'status' => 200,
                'data' => html_entity_decode($wordHtmlContent)
            ]);
            
        } catch (Exception $e) {

            return new JsonResponse([
                "status"=>$e->getCode(),
                "message"=>$e->getMessage(),
            ]);
        }
    }
    
    private function _uploadWordFile($uploadFileField, $saveDir, $saveName) {
        $saveDirPrefix = public_path();
        
        $uploadFile = $_FILES[$uploadFileField];
        if (!$uploadFile) {
            throw new Exception('上传失败', 401); //ERROR_FILE_NOT_FOUND
        }
        if ($uploadFile['error']) {
            throw new Exception('上传失败', 401); //$uploadFile['error']
        } else if (!file_exists($uploadFile['tmp_name'])) {
            throw new Exception('上传失败', 401); //ERROR_TMP_FILE_NOT_FOUND
        } else if (!is_uploaded_file($uploadFile['tmp_name'])) {
            throw new Exception('上传失败', 401); //ERROR_TMPFILE
        }else if ($uploadFile['size'] > 10*1024*1024) { //10MB
            throw new Exception('上传失败:文件过大', 401); //ERROR_SIZE_EXCEED
        }else if(!preg_match('/(\.doc|\.docx)$/i',$uploadFile['name'])) {
            throw new Exception('上传失败:文件类型错误', 401); //ERROR_TYPE_NOT_ALLOWED
        }
        if(!file_exists($saveDirPrefix.$saveDir) && !mkdir($saveDirPrefix.$saveDir, 0777, true)) {
            throw new Exception('上传失败', 401); //ERROR_CREATE_DIR
        }
        if (!(move_uploaded_file($uploadFile["tmp_name"], $saveDirPrefix.$saveDir.$saveName) && file_exists($saveDirPrefix.$saveDir.$saveName))) {
            throw new Exception('上传失败', 401); //ERROR_FILE_MOVE
        }
        return $saveDir.$saveName;
    }
    
    private function _convertWordToHtml($fileName, $saveDir) {
        $saveDirPrefix = public_path();
        
        $htmlPath = $saveDirPrefix . $saveDir . $fileName . '.html';
        $cmd = config('params.libreoffice_path')."libreoffice6.0 --headless --invisible --convert-to html --outdir {$saveDirPrefix}{$saveDir} {$saveDirPrefix}{$saveDir}{$fileName}";
        shell_exec($cmd);
//        copy('/Users/casparzhu/test.html', $htmlPath);
        
        if(!file_exists($htmlPath)) {
            throw new Exception('上传失败:转换错误', 401); //CONVERT ERROR
        }
        return $htmlPath;
    }
    
    private function _saveBase64Img($base64Img, $saveDir) {
        $saveDirPrefix = public_path();
        $match = [];
        $fileType = '';
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Img, $match)) {
            $fileType = '.'.$match[2];
            $base64Img = str_replace($match[1], '', $base64Img);
            $img = base64_decode($base64Img);
            $saveName = md5(mt_rand().time()) . $fileType;
            $savePath = $saveDirPrefix . $saveDir . $saveName;
            file_put_contents($savePath, $img);
            return $saveDir . $saveName;
        }
        return false;
    }

    private function actionUpload($action, $type='upload'){
        $base64 = $type;
        switch (htmlspecialchars($action)) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" =>$this->config['imagePathFormat'],
                    "maxSize" =>$this->config['imageMaxSize'],
                    "allowFiles" =>$this->config['imageAllowFiles'],
                    "oriName" => ''
                );
                $fieldName =$this->config['imageFieldName'];
                break;

            default:
                $config = array(
                    "pathFormat" =>$this->config['filePathFormat'],
                    "maxSize" =>$this->config['fileMaxSize'],
                    "allowFiles" =>$this->config['fileAllowFiles']
                );
                $fieldName =$this->config['fileFieldName'];
                break;
        }

        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($fieldName, $config, $base64);

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */

        /* 返回数据 */
        return $up->getFileInfo();
    }

    private function actionCrawler($request){

        /* 上传配置 */
        $config = array(
            "pathFormat" =>$this->config['catcherPathFormat'],
            "maxSize" =>$this->config['catcherMaxSize'],
            "allowFiles" =>$this->config['catcherAllowFiles'],
            "oriName" => "remote.png"
        );
        $fieldName =$this->config['catcherFieldName'];

        /* 抓取远程图片 */
        $list = array();
        if (!empty($request->query->get($fieldName))) {
            $source = $request->query->get($fieldName);
        } else {
            $source = $request->get($fieldName);
        }
        foreach ($source as $imgUrl) {
            $item = new Uploader($imgUrl, $config, "remote");
            $info = $item->getFileInfo();
            array_push($list, array(
                "state" => $info["state"],
                "url" => $info["url"],
                "size" => $info["size"],
                "title" => htmlspecialchars($info["title"]),
                "original" => htmlspecialchars($info["original"]),
                //"source" => htmlspecialchars($imgUrl)
                "source" => $imgUrl
            ));
        }

        /* 返回抓取数据 */
        return [
            'state'=> count($list) ? 'SUCCESS':'ERROR',
            'list'=> $list
            ];
    }
}