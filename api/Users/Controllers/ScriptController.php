<?php

namespace App\Users\Controllers;
use App\Users\Services\Script;
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
 * @path /script
 */
class ScriptController
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
     * @var Script
     */
    public $script;

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
     * 用户资金1分钱回滚
     * @route POST /userAccountRollback
     * @param string $uid
     * @return array
     */
    public function userAccountRollback($uid='') {
        try {
            $rs = $this->script->userAccountRollback($uid);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS, $rs);

        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());

        }
    }



}
