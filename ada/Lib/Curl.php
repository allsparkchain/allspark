<?php

namespace App\Lib;


use App\Exceptions\ApiException;
use GuzzleHttp\Client;

class Curl
{
    /**
     * @var Client
     */
    private static $curl;

    public static function getInstance()
    {
        if (!self::$curl) {

            self::$curl = new Client();
        }

        return self::$curl;
    }

    /**
     * @param $uri
     * @param array $paramer
     * @return mixed
     * @throws ApiException
     */
    public static function post($uri, $paramer = []) {

        $paramer['appid'] = config('params.app_id');
        $paramer['sign_time'] = time();
        $key = config('params.sign_key');
        //数组排序
        $paramer = argSort($paramer);
        //排序后序列化拼密钥后md5\

        $paramer['sign'] = md5( http_build_query($paramer) . $key);

        $uri = rtrim(config('params.api_host'), '/') . "/". ltrim($uri, '/');
        $contents = self::getInstance()->request('post', $uri, ['form_params'=>$paramer])->getBody()->getContents();

        $json = json_decode($contents, true);
        if ($json['status'] != 200) {
            throw new ApiException($json['message'], $json['status']);
        }

        return $json;
    }

    /**
     * @param $uri
     * @param array $paramer
     * @return string
     */
    public static function get($uri, $paramer = [], $header = []) {
        if (count($paramer)>0) {
            $uri .= "?";
            foreach ($paramer as $key => $value) {
                $uri .= $key . "=" . $value . "&";
            }
            $uri = rtrim($uri, '&');
        }
        $contents = self::getInstance()->request('get', $uri, [ "headers"=>$header])->getBody()->getContents();

        return $contents;
    }

}