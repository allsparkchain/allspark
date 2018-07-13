<?php

namespace App\Utils\Controllers;

use App\Utils\Defines;
use App\Utils\ErrorConst;
use App\Utils\HttpResponseTrait;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DI\Traits\EnableDIAnnotations;

/**
 * Class MessageController
 * @path /utils/message
 */
class MessageController
{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Mutex
     */
    protected $mutex;

    /**
     * @inject
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var ErrorConst
     */
    protected $errorConst;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;

    /**
     * @inject
     * @var Message
     */
    protected $sms;

    /**
     * 发送普通短信
     *
     * @route POST /ordinary
     *
     * @param string $mobile
     * @param string $content
     *
     * @return array
     */
    public function ordinary($mobile, $content) {

        try {
            $this->sms->ordinary($mobile, $content);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }


    /**
     * 发送功能短信
     *
     * @route POST /sendMsg
     * @param $order_number
     *
     * @return array
     */
    public function sendMsg($order_number) {
        try {
            if ($this->sms->sendMsg($order_number)) {
                return $this->respone(Defines::HTTP_OK, Defines::SUCCESS);
            }
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 创建短信
     *
     * @route POST /createMsg
     * @param $mobile
     * @param $type 1-9
     * @param $contents
     * @param $param
     *
     * @return array
     */
    public function createMsg($mobile, $type, $contents = "", $param = []) {
        try {
            $msg = $this->sms->createMsg($mobile, $type, $contents, $param);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, ["order_number"=>$msg]);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 创建短信
     *
     * @route POST /verificationSms
     * @param $order_number
     * @param $mobile
     * @param $code
     * @param $type
     *
     * @return array
     */
    public function verificationSms($order_number, $mobile, $code, $type) {
        try {
            $msg = $this->sms->verificationSms($order_number, $mobile, $code, $type);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, ["order_number"=>$msg]);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
}