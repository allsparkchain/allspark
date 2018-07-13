<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 18:01
 */

namespace App\Utils\Controllers;

use App\Utils\Defines;
use App\Utils\Lib\Elasticsearch\Elasticsearch;
use App\Utils\HttpResponseTrait;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;



/**
 * Class ElasticsearchController
 * @path /utils/elsearch
 */
class ElasticsearchController
{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait;

    /**
     * @inject
     * @var Elasticsearch
     */
    private $elsearch;

    /**
     * 生成首页搜索数据 全量／或指定分钟内
     *
     * @route POST /saveAllSearchIndexData
     * @param int $minutes
     * @return array
     */
    public function saveAllSearchIndexData($minutes = 0) {
        try {
            $rs = $this->elsearch->saveAllSearchIndexData($minutes);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 生成首页搜索数据产品 全量／或指定分钟内
     * @route POST /saveAllProductSearchIndexData
     * @param int $minutes
     * @return array
     */
    public function saveAllProductSearchIndexData($minutes = 0) {
        try {
            $rs = $this->elsearch->saveAllProductSearchIndexData($minutes);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 生成首页搜索数据文章 全量／或指定分钟内
     * @route POST /saveAllArticleSearchIndexData
     * @param int $minutes
     * @return array
     */
    public function saveAllArticleSearchIndexData($minutes = 0) {
        try {
            $rs = $this->elsearch->saveAllArticleSearchIndexData($minutes);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 删除首页搜索数据
     * @route POST /deleteAllSearchIndex
     * @return array
     */
    public function deleteAllSearchIndex() {
        try {
            $rs = $this->elsearch->deleteSearchIndex();
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 搜索建议提示
     * @route POST /suggestSearch
     * @param string $content
     * @param int $size {@v min:1|max:100}
     * @return array
     */
    public function suggestSearch($content = '', $size = 5) {
        try {
            $rs = $this->elsearch->suggestSearch($content, $size);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

//    /**
//     * 空白搜索页
//     * @route POST /blankSearch
//     * @return array
//     */
//    public function blankSearch() {
//        try {
//            $rs = $this->elsearch->blankSearch();
//            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
//        } catch (\Exception $e) {
//            return $this->respone($e->getCode(), $e->getMessage());
//        }
//    }

    /**
     * 简单搜索
     * @route POST /developeSearch
     * @param string $content
     * @param int $page {@v min:1}
     * @param int $pagesize {@v min:1|max:100}
     * @param string $scrollId
     * @return array
     */
    public function developeSearch($content = '', $page =1, $pagesize = 20,$scrollId = '') {
        try {
            $rs = $this->elsearch->developeSearch($content,$page,$pagesize,$scrollId);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 简单搜索分页
     * @route POST /developeGetNextPage
     * @param string $scroll
     * @param string $scrollId
     * @return array
     */
    public function developeGetNextPage($scroll, $scrollId =1) {
        try {
            $rs = $this->elsearch->developeGetNextPage($scroll,$scrollId);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 查询产品下的所有文章
     * @route POST /developeGetAllArticle
     * @param string $product_id {@v required}
     * @param string $content
     * @param int $page {@v min:1}
     * @param string $scrollId
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function developeGetAllArticle($product_id = '', $content = '', $page = '1', $scrollId = '', $pagesize=10) {
        try {
            $rs = $this->elsearch->developeGetAllArticle($product_id,$content,$page,$pagesize,$scrollId);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 查询产品下的所有文章
     * @route POST /developeGetAllArticleByProductID
     * @param string $product_id {@v required}
     * @param string $content
     * @param int $current
     * @param int $page {@v min:1}
     * @param string $scrollId
     * @param int $pagesize {@v min:1|max:100}
     * @return array
     */
    public function developeGetAllArticleByProductID($product_id = '', $content = '', $current = 0, $page = '1', $scrollId = '', $pagesize=10) {
        try {
            $rs = $this->elsearch->developeGetAllArticleByProductID($product_id,$content,$current,$page,$pagesize,$scrollId);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

}