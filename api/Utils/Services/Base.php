<?php

namespace App\Utils\Services;


use DI\Container;
use PhpBoot\DB\DB;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\Pagination;
use App\Utils\ErrorConst;
use Psr\Log\LoggerInterface;
use function App\getCurrentTime;
use PhpBoot\DB\rules\select\FromRule;
use PhpBoot\DB\rules\select\JoinRule;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class ArticleList
{
    use EnableDIAnnotations, ThrowResponseParamerTrait;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;

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
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * where
     * @var
     */
    private $where;

    /**
     * 排序
     * @var
     */
    private $sort;

    /**
     * 第几页
     * @var int
     */
    private $page = 1;

    /**
     * 每页显示多少数据
     * @var int
     */
    private $pageSize = 10;

    /**
     * @var JoinRule
     */
    private $joinRule;

    /**
     * @var string
     */
    private $groupBy;

    /**
     * @var array
     */
    private $data;

    /**
     * @param mixed $where
     */
    public function setWhere(array $where)
    {
        $this->where = $where;
    }

    /**
     * @param mixed $sort
     */
    public function setSort(array $sort)
    {
        $this->sort = $sort;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page)
    {
        $this->page = $page;
    }

    /**
     * @param int $pageSize
     */
    public function pageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * @param string $groupBy
     */
    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }

    /**
     * @param DB $db
     */
    public function setDb(DB $db)
    {
        $this->db = $db;
    }
}