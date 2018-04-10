<?php
namespace App\Article\Entities;

use App\Utils\Entity;
class ImgTitleEntity extends Entity
{
    /**
     * @var int
     * @v optional
     */
    public $id;



    /**
     * @var string
     */
    public $img_title = '';

    /**
     * @var string
     */
    public $img_url = '';

    /**
     * @var string
     */
    public $img_path;

    /**
     * @var int
     */
    public $img_type = 1;

    /**
     * @var int
     */
    public $img_order = 1;

    /**
     * @var int
     */
    public $status = 1;

    /**
     * @return string
     */
    public function getImgTitle()
    {
        return $this->img_title;
    }

    /**
     * @return string
     */
    public function getImgUrl()
    {
        return $this->img_url;
    }

    /**
     * @return string
     */
    public function getImgpath()
    {
        return $this->img_path;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return int
     */
    public function getImgType()
    {
        return $this->img_type;
    }

    /**
     * @return int
     */
    public function getImgOrder()
    {
        return $this->img_order;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}