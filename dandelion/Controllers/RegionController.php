<?php
/**
 * Created by PhpStorm
 * author: viki
 * Date: 2018/04/11
 * Time: 12:00
 */

namespace App\Http\Controllers\Other;

use App\Lib\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * Class RegionController
 * @Controller(prefix="/region")
 * @Middleware("web")
 * @package App\Http\Controllers
 */
class RegionController extends Controller
{

    /**
     * @Get("", as="s_region")
     */
    public function index(Request $request)
    {
        $referer = $request->server->get('HTTP_REFERER');
        if (is_null($referer) || strstr($referer, route('s_region'))) $referer = route('s_index_index');
        return view("region")->with('referer', $referer);
    }

    /**
     * @Post("/hotCity", as="s_region_hotCity")
     */
    public function hotCity()
    {
        try {
            $cache = cache('region:hotCity');
            if (!$cache) {
                $cache = Curl::post('/region/hotCity', ['default' => 1]);
                cache(['region:hotCity' => $cache], 30);
            }
            return new JsonResponse($cache);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 400]);
        }
    }

    /**
     * @Post("/more", as="s_region_more")
     */
    public function more()
    {
        try {
            $cache = cache('region:more');
            $post = ['status' => 200, 'data' => $cache];
            if (!$cache) {
                $data = [];
                $post = Curl::post('/region/more');
                if ($post['status'] != 200) return new JsonResponse(['status' => 400]);
                foreach ($post['data'] as $k => $v) $data[$v['initial']][] = ['id' => $v['id'], 'name' => $v['name'], 'prefix' => $v['prefix']];
                $post['data'] = $data;
                cache(['region:more' => $data], 30);
            }
            return new JsonResponse($post);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 400]);
        }
    }

    /**
     * @Post("/choose", as="s_region_choose")
     */
    public function choose(Request $request)
    {
        try {
            $post = Curl::post('/region/choose', [
                'province_id' => $request->get('province_id', 0),
                'city_id' => $request->get('city_id', 0),
                'basic' => $request->get('basic', 0),
            ]);
            return new JsonResponse($post);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 400]);
        }
    }

    /**
     * @Post("/search", as="s_region_search")
     */
    public function search(Request $request)
    {
        try {
            $keywords = $this->lib_replace_end_tag($request->get('keywords'));
            if (! $keywords) return new JsonResponse(['status' => 200, 'data' => []]);
            return new JsonResponse(Curl::post('/region/search', ['keywords' => $keywords]));
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 400]);
        }
    }

    /**
     * @Post("/selected", as="s_region_selected")
     */
    public function selected(Request $request)
    {
        try {
            $user = \Auth::getUser();
            $selected = $request->get('selected', 0);
            if ($user) Curl::post('/region/selected', ['uid' => $user->getAuthIdentifier(), 'selected' => $selected]);
            $foreverCookie = cookie()->forever('region_id', $selected);
            return response()->make()->withCookie($foreverCookie)->header('Content-Type', 'application/json')
                ->setContent('{"status":200}');
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 400]);
        }
    }

    /**
     * @Post("/current", as="s_region_current")
     */
    public function current(Request $request)
    {
        try {
            $uid = 0;
            $user = \Auth::getUser();
            if ($user) $uid = $user->getAuthIdentifier();
            $region_id = $request->cookie('region_id', 0);
            $post = Curl::post('/region/current', ['uid' => $uid, 'region_id' => $region_id]);
            return new JsonResponse($post);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 400]);
        }
    }

    private function lib_replace_end_tag($keywords)
    {
        $str = trim($keywords);
        if (! strlen($str)) return '';
        $str = htmlspecialchars($str);
        $str = str_replace('/', '', $str);
        $str = str_replace('\\', '', $str);
        $str = str_replace('>', '', $str);
        $str = str_replace('<', '', $str);
        $str = str_replace('<SCRIPT>', '', $str);
        $str = str_replace('</SCRIPT>', '', $str);
        $str = str_replace('<script>', '', $str);
        $str = str_replace('</script>', '', $str);
        $str = str_replace('select', 'select', $str);
        $str = str_replace('join', 'join', $str);
        $str = str_replace('union', 'union', $str);
        $str = str_replace('where', 'where', $str);
        $str = str_replace('insert', 'insert', $str);
        $str = str_replace('delete', 'delete', $str);
        $str = str_replace('update', 'update', $str);
        $str = str_replace('like', 'like', $str);
        $str = str_replace('drop', 'drop', $str);
        $str = str_replace('create', 'create', $str);
        $str = str_replace('modify', 'modify', $str);
        $str = str_replace('rename', 'rename', $str);
        $str = str_replace('alter', 'alter', $str);
        $str = str_replace('cas', 'cast', $str);
        $str = str_replace('&', '&', $str);
        $str = str_replace('>', '>', $str);
        $str = str_replace('<', '<', $str);
        $str = str_replace(' ', chr(32), $str);
        $str = str_replace(' ', chr(9), $str);
        $str = str_replace(' ', chr(9), $str);
        $str = str_replace('&', chr(34), $str);
        $str = str_replace("'", chr(39), $str);
        $str = str_replace("<br />", chr(13), $str);
        $str = str_replace("''", "'", $str);
        $str = str_replace("css", "'", $str);
        $str = str_replace("CSS", "'", $str);
        return $str;
    }
}