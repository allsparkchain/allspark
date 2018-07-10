<?php
/**
 * Created by PhpStorm
 * author: viki
 * Date: 2018/04/11
 * Time: 12:00
 */

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;

/**
 * Class ToutiaoController
 * @Controller(prefix="/toutiao")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class ToutiaoController extends Controller
{
    /**
     * @Get("", as="s_toutiao")
     */
    public function index()
    {
        $code = request('code');
        $errorCode = request('error_code');
        if (!$code) return $this->jump();
        if ($errorCode) return $this->jump();
        $decrypt = decrypt(request('encrypt'));
        $id = $decrypt['id'];
        $url = urlencode($decrypt['url']);

        $shellStr = config('params.php_path') . ' ' . public_path('../') . 'artisan command:ToutiaoCommand'
            . " $id $code $url" . ' > /dev/null  & ';
        \Log::info(date('Y-m-d H:i:s:'). $shellStr);

        shell_exec($shellStr);
        
        return redirect(route('s_aricle_detail', ['id' => $id]));
    }

    public function jump()
    {
        $url = "https://open.snssdk.com/auth/authorize?response_type=code&client_key=" . env('TOUTIAO_KEY');
        $url .= "&auth_only=1&redirect_uri=" . env('TOUTIAO_CALLBACK') . '?encrypt=' . request('encrypt');
        return redirect($url);
    }
}