<?php
namespace App\Industry\Entities;

use App\Utils\Entity;

class IndustryEntity extends Entity {

    /**
     * @var int
     * @v optional
     */
    public $id;

    /**
     * @var int
     */
    public $pid = 0;

    /**
     * @var int
     */
    public $type = 0;



    /**
     * @var string
     */
    public $fname = '';

    /**
     * @var string
     */
    public $name = '';


    /**
     * @var string
     */
    public $desc = '';


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
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return string
     */
    public function getFname()
    {
        return $this->fname;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}