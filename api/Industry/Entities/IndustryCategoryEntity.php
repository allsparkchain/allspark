<?php
namespace App\Industry\Entities;

use App\Utils\Entity;
class IndustryCategoryEntity extends Entity
{
    /**
     * @var int
     * @v optional
     */
    public $id;

    /**
     * @var string
     */
    public $name = '';

    /**
     * icon图标
     * @var string
     */
    public $icon_img;

    /**
     * icon 高亮图标
     * @var string
     */
    public $icon_heightLight_img;

    /**
     * @var int
     */
    public $type = 1;

    /**
     * @var int
     */
    public $status = 1;

    /**
     * @var int
     */
    public $order = 0;

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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getIconImg()
    {
        return $this->icon_img;
    }

    /**
     * @return string
     */
    public function getIconHeightLightImg()
    {
        return $this->icon_heightLight_img;
    }


}