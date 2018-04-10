<?php

namespace App\Users\Controllers;
use App\Utils\Defines;

use App\Users\Services\Weixin;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Symfony\Component\HttpFoundation\Request;

/**
 * @path /weixin
 */
class WeixinController
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
     * @var Weixin
     */
    public $weixin;

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
     * 生成code
     *
     * @route POST /createCode
     *
     * @return array
     */
    public function createCode() {
        try {
            $rs = $this->weixin->createCode();
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 更新code
     *
     * @route POST /editCode
     * @param string $code {@v required}
     * @param int $openid {@v min:1}
     * @return array
     */
    public function editCode($code, $openid) {
        try {
            $this->weixin->editCode($code, $openid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 生成code
     *
     * @route POST /getOpenCode
     * @param string $code {@v required}
     * @param int $login_type
     *
     * @return array
     */
    public function getOpenCode($code, $login_type = 0) {
        try {
            $rs = $this->weixin->getOpenCode($code, $login_type);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }



    /**
     * 生成openId
     *
     * @route POST /createOpenId
     *
     * @param string $openid {@v required}
     * @param string $nickname
     * @param string $sex
     * @param string $headimgurl
     * @param string $content
     *
     * @return array
     */
    public function createOpenId($openid, $nickname, $sex, $headimgurl, $content) {
        try {
            $rs = $this->weixin->createOpenId($openid, $nickname, $sex, $headimgurl, $content);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 查询openId
     *
     * @route POST /getOpenId
     *
     * @param string $openid {@v required}
     *
     * @return array
     */
    public function getOpenId($openid) {
        try {
            $rs = $this->weixin->getOpenId($openid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 查询openId
     *
     * @route POST /bindOpenId
     * @param string $uid {@v required}
     * @param string $code {@v required}
     *
     * @return array
     */
    public function bindOpenId($uid, $code) {
        try {
            $rs = $this->weixin->bindOpenId($uid, $code);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 用户登录
     * @route POST /login
     * @param string $code
     * @param int $login_type
     * @return array
     */
    public function login($code, $login_type) {
        try {
            $rs = $this->weixin->login($code, $login_type);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 创建openId 用户
     * @route POST /createOpenUser
     * @param string $open_id
     * @return array
     */
    public function createOpenUser($open_id){
        try {
            $this->weixin->createOpenUser($open_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }










}
