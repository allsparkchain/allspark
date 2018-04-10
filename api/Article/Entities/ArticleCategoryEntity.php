<?php
namespace App\Article\Entities;

use App\Utils\Entity;
class ArticleCategoryEntity extends Entity
{
    /**
     * @var int
     * @v optional
     */
    public $id;

    /**
     * @var string
     */
    public $category_name = '';

    /**
     * @var int
     */
    public $parent = 0;

    /**
     * @var int
     */
    public $status = 1;

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
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

}