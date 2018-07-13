<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 18:01
 */

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
use App\Utils\Services\Email;



/**
 * Class EmailController
 * @path /utils/email
 */
class EmailController
{
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait;

    /**
     * @inject
     * @var Email
     */
    public $email;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;

    /**
     * @inject
     * @var Container
     */
    protected $container;

    /**
     * 发送邮件
     *
     * @route POST /send
     *
     * @param string $to_user
     * @param string $phone
     *
     * @return mixed
     */
    public function send($to_user, $phone)
    {
        try {
            $rs = $this->email->send($to_user, $phone);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }

}