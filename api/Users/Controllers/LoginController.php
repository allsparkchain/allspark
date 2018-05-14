<?php

namespace App\Users\Controllers;
use App\Users\Services\Login;
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
 * @path /login
 */
class LoginController
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
     * @var Login
     */
    public $login;

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
     * 邮箱登录
     * @route POST /userEmailLogin
     * @param string $email
     * @param string $passwd
     * @param string $login_type
     * @return array
     */
    public function userEmailLogin($email, $passwd, $login_type) {
        try {
            $rs = $this->login->userEmailLogin($email, $passwd, $login_type);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }



}
