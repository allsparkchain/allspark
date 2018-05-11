<?php

namespace App\Users\Controllers;

use App\Users\Entities\SmediaEntity;
use App\Users\Services\Smedia;
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
 * @path /smedia
 */
class SmediaController {
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait;

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
     * @inject
     * @var Smedia
     */
    public $smedia;

    /**
     * 注册
     *
     * @route POST /register
     * @param SmediaEntity $entity {@bind request.request}
     *
     * @return array
     */
    public function register(SmediaEntity $entity) {
        try {

            $rs = $this->smedia->register($entity);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }

    /**
     * 手动注册
     * @route POST /autoRegister
     * @param SmediaEntity $entity {@bind request.request}
     *
     * @return array
     */
    public function autoRegister(SmediaEntity $entity) {
        try {
            $rs = $this->smedia->autoRegister($entity);
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
            $rs = $this->smedia->login($mobile, $passwd);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 自媒体代理 账户总览
     * @route POST /getSmediaAccountInfo
     * @param string $uid
     * @param int $type
     * @param int $showBy
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getSmediaAccountInfo($uid, $type = 1, $showBy = 1, $page=1, $pagesize=10) {
        try {
            $rs = $this->smedia->getSmediaAccountInfo($uid, $type,$showBy, $page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 自媒体代理 下属 自媒体 列表新信息  渠道数据1
     * @route POST /getSmediaList
     * @param string $uid
     * @param string $name
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $starttime
     * @param string $endtime
     * @return array
     */
    public function getSmediaList($uid, $name = '', $page=1, $pagesize=10, $order = '', $starttime = 0, $endtime = 0) {
        try {
            $rs = $this->smedia->getSmediaList($uid,$name,$page, $pagesize,$order,$starttime,$endtime);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 自媒体代理 下属 自媒体 列表新信息  渠道数据2
     * @route POST /getSmediaUserArticleList
     * @param string $invited_uid
     * @param string $invite_uid
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $starttime
     * @param string $endtime
     * @return array
     */
    public function getSmediaUserArticleList($invited_uid,$invite_uid, $name = '', $page=1, $pagesize=10, $order = '', $starttime = 0, $endtime = 0) {
        try {
            $rs = $this->smedia->getSmediaUserArticleList($invited_uid,$invite_uid,$name,$page, $pagesize,$order,$starttime,$endtime);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 自媒体代理 下属 自媒体 文章的具体数据  渠道数据3
     * @route POST /getSmediaUserArticleDetail
     * @param string $invited_uid
     * @param string $invite_uid
     * @param string $spread_id
     * @param int $page
     * @param int $pagesize
     * @param string $order
     * @param string $starttime
     * @param string $endtime
     * @return array
     */
    public function getSmediaUserArticleDetail($invited_uid, $invite_uid, $spread_id, $page=1, $pagesize=10, $order = '', $starttime = 0, $endtime = 0) {
        try {
            $rs = $this->smedia->getSmediaUserArticleDetail($invited_uid,$invite_uid, $spread_id,$page, $pagesize,$order,$starttime,$endtime);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 自媒体代理 佣金提现页面
     * @route POST /getSmediaUserWithdrawPage
     * @param string $advert_id
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getSmediaUserWithdrawPage($advert_id, $page = 1, $pagesize = 10) {
        try {
            $rs = $this->smedia->getSmediaUserWithdrawPage($advert_id,$page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 自媒体代理 未结算佣金明细
     * @route POST /getUnsettledComissionList
     * @param string $advert_id
     * @param int $starttime
     * @param int $endtime
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getUnsettledComissionList($advert_id, $starttime=0, $endtime=0, $page=1, $pagesize=10) {
        try {
            $rs = $this->smedia->getUnsettledComissionList($advert_id, $starttime, $endtime, $page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 自媒体代理 余额流水
     * @route POST /getSmediaUserAccFLowPage
     * @param string $advert_id
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getSmediaUserAccFLowPage($advert_id, $page=1, $pagesize=10) {
        try {
            $rs = $this->smedia->getSmediaUserAccFLowPage($advert_id,$page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * 根据自媒体代理用户id获得 被邀请人（自媒体）列表信息
     * @route POST /getSmediaUserList
     * @param string $uid
     * @param int $page
     * @param int $pagesize
     * @return array
     */
    public function getSmediaUserList($uid, $page=1, $pagesize=10) {
        try {
            $rs = $this->smedia->getSmediaUserList($uid, $page, $pagesize);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 检测手机号是否已经存在
     * @route POST /checkMobile
     * @param string $mobile
     * @return array
     */
    public function checkMobile($mobile) {
        try {
            $rs = $this->smedia->checkMobile($mobile);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
}