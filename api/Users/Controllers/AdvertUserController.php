<?php

namespace App\Users\Controllers;
use App\Users\Entities\UserEntity;
use App\Users\Services\Advert;
use App\Users\Services\User;
use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Symfony\Component\HttpFoundation\Request;

/**
 * @path /advert
 */
class AdvertUserController
{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Message
     */

    /**
     * @inject
     * @var Advert
     */
    public $advert;

    /**
     * @inject
     * @var ErrorConst
     */
    public $errorConst;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;

    /**
     * @inject
     * @var Request
     */
    public $request;


    /**
     * 后台添加用户
     *
     * @route POST /addUser
     * @param string $username
     * @param string $realname
     *
     * @return array
     */
    public function addUser($username, $realname) {
        try {
            $rs = $this->advert->addUser($username, $realname);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 用户登录
     * @route POST /login
     * @param string $mobile
     * @param string $passwd
     * @return array
     */
    public function login($mobile, $passwd) {
        try {
            $rs = $this->advert->login($mobile, $passwd);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 广告主列表
     *
     * @route POST /advertList
     *
     * @return array
     */
    public function advertList() {
        try {
            $rs = $this->advert->advertList();
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 修改密码
     * @route POST /editPassword
     * @param string $username {@v lengthMin:6}
     * @param string $oldpasswd {@v lengthMin:6|lengthMax:16|alphaNum}
     * @param string $passwd {@v lengthMin:6|lengthMax:16|alphaNum}
     * @param string $passconfirm {@v lengthMin:6|lengthMax:16|alphaNum}
     * @return array
     */
    public function editPassword($username, $oldpasswd, $passwd, $passconfirm) {
        try {
            $rs = $this->advert->editPassword($username, $oldpasswd, $passwd, $passconfirm);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 本月成交
     * @route POST /getMonthTrading
     * @param string $advert_id
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getMonthTrading($advert_id, $page=1, $pagesize=10) {
        try {
            $rs = $this->advert->getMonthTrading($advert_id, $page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 本月成交
     * @route POST /getTrading
     * @param string $advert_id
     * @return array
     */
    public function getTrading($advert_id) {
        try {
            $rs = $this->advert->getTrading($advert_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 今日成交
     * @route POST /getDayTrading
     * @param string $advert_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getDayTrading($advert_id, $starttime=0, $endtime=0, $page=1, $pagesize=10) {
        try {
            $rs = $this->advert->getDayTrading($advert_id, $starttime, $endtime, $page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 账户总览
     * @route POST /getUserInfo
     * @param string $advert_id
     * @return array
     */
    public function getUserInfo($advert_id) {
        try {
            $rs = $this->advert->getUserInfo($advert_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 重制密码
     * @route POST /resetPassword
     * @param string $advert_id
     * @return array
     */
    public function resetPassword($advert_id) {
        try {
            $rs = $this->advert->resetPassword($advert_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }



}
