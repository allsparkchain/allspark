<?php
namespace App\Goods\Entities;

use App\Utils\Entity;

class ProductEntity extends Entity
{
    /**
     * @var int
     * @v optional
     */
    public $id;

    /**
     * @var string
     */
    public $product_name;

    /**
     * @var string
     */
    public $product_info;

    /**
     * @var int
     */
    public $stock = -1;

    /**
     * @var int
     */
    public $brand_id = 0;

    /**
     * @var int
     */
    public $category_id = 0;



    /**
     * @var float
     * @v optional
     */
    public $writerpercent = 0;

    /**
     * @var float
     * @v optional
     */
    public $writerMoney = 0;

    /**
     * @var float
     * @v optional
     */
    public $writerCombinepercent = 0;

    /**
     * @var float
     * @v optional
     */
    public $writerCombineaccount = 0;

    /**
     *渠道，媒体
     * @var float
     * @v optional
     */
    public $channelpercent = 0;

    /**
     * @var float
     * @v optional
     */
    public $sitepercent = 0;

    /**
     * 媒体代理分成
     * @var float
     */
    public $mediaAgent = 0;


    /**
     * @var int
     * @v optional
     */
    public $settlement_day;

    /**
     * @var float
     */
    public $selling_price;


    /**
     * @var int
     */
    public $regionArea = -1;


    /**
     * @var int
     */
    public $range_id = -1;

    /**
     * @var string
     */
    public $img_path = '';

    /**
     * @var int
     */
    public $advert_id = '';



    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * @return string
     */
    public function getProductInfo()
    {
        return $this->product_info;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return int
     */
    public function getBrandId()
    {
        return $this->brand_id;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * @return float
     */
    public function getWriterpercent()
    {
        return $this->writerpercent;
    }

    /**
     * @return float
     */
    public function getChannelpercent()
    {
        return $this->channelpercent;
    }

    /**
     * @return float
     */
    public function getSitepercent()
    {
        return $this->sitepercent;
    }

    /**
     * @return int
     */
    public function getSettlementDay()
    {
        return $this->settlement_day;
    }

    /**
     * @return float
     */
    public function getSellingPrice()
    {
        return $this->selling_price;
    }

    /**
     * @return int
     */
    public function getRegionArea()
    {
        return $this->regionArea;
    }

    /**
     * @return int
     */
    public function getRangeId()
    {
        return $this->range_id;
    }

    /**
     * @return string
     */
    public function getImgPath()
    {
        return $this->img_path;
    }

    /**
     * @return float
     */
    public function getWriterMoney()
    {
        return $this->writerMoney;
    }

    /**
     * @return float
     */
    public function getWriterCombinepercent()
    {
        return $this->writerCombinepercent;
    }

    /**
     * @return float
     */
    public function getWriterCombineaccount()
    {
        return $this->writerCombineaccount;
    }

    /**
     * @return int
     */
    public function getAdvertId()
    {
        return $this->advert_id;
    }

    /**
     * @return float
     */
    public function getMeiaAgent()
    {
        return $this->mediaAgent;
    }

}