<?php
namespace App\Users\Entities;

use App\Utils\Entity;

class UserEntity extends Entity
{
    /**
     * @var int
     * @v optional
     */
    public $id;

    /**
     * @var string
     */
    public $mobile;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $code = '' ;

    /**
     * @var string
     */
    public $order_number;

    /**
     * @var string
     */
    public $invite_code = '';

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
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return sring
     */
    public function getOrderNumber()
    {
        return $this->order_number;
    }

    /**
     * @return string
     */
    public function getInviteCode()
    {
        return $this->invite_code;
    }


}