<?php

namespace App\Lite\Users\Services;

use App\Utils\ErrorConst;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Psr\Log\LoggerInterface;

class Wallet
{
    use EnableDIAnnotations, ThrowResponseParamerTrait;
    /**
     * @inject
     * @var DB
     */
    private $db;

    /**
     * @inject
     * @var LoggerInterface
     */
    public $logger;

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
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var \Predis\Client
     */
    private $redis;

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * info
     * @param string $wx_id
     * @return array
     */
    public function info($wx_id)
    {
        return $this->db->select('*')->from('t_weixin_wallet')->where(['wx_id' => $wx_id])->getFirst();
    }

    /**
     * info
     * @param string $wx_id
     * @param string $left
     * @param string $right
     * @return array
     */
    public function record($wx_id, $left, $right)
    {
        $map = ['wx_id' => $wx_id];
        if ($left) $map['add_time'] = ['>=' => strtotime($left)];
        if ($right) $map['add_time'] = ['<=' => strtotime($right)];
        return $this->db->select('*')->from('t_weixin_wallet_record')->where($map)->get();
    }
}