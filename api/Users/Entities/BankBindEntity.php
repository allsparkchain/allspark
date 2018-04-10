<?php
namespace App\Users\Entities;

use App\Utils\Entity;

class BankBindEntity extends Entity
{
    /**
     * @var int
     * @v optional
     */
    public $id;

    /**
     * @var int
     */
    public $uid = 1;

    /**
     * @var string
     */
    public $mobile;

    /**
     * @var string
     */
    public $realname;

    /**
     * @var string
     */
    public $banknumber;

    /**
     * @var string
     */
    public $idnumber;

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
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @return string
     */
    public function getRealname()
    {
        return $this->realname;
    }

    /**
     * @return string
     */
    public function getBanknumber()
    {
        return $this->banknumber;
    }

    /**
     * @return string
     */
    public function getIdnumber()
    {
        return $this->idnumber;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

}