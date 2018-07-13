<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 2017/11/28
 * Time: 下午2:29
 */

namespace App\Utils;


use PhpBoot\DB\DB;
use PhpBoot\DB\rules\select\LimitRule;

class Pagination
{
    /**
     * @var LimitRule
     */
    protected $limitRule;
    protected $page;
    protected $pagesize;
    /**
     * @var DB
     */
    protected $db;

    public function __construct(LimitRule $limitRule, $page, $pagesize, DB $db = null)
    {
        $this->limitRule = $limitRule;
        $this->page = $page;
        $this->pagesize = $pagesize;
        $this->db = $db;
    }

    public function get() {
        $return = [];
        if ($this->db) {
            $count = $this->db->select()->from($this->limitRule, "count")->count();
        } else {
            $count = $this->limitRule->count();
        }
        $return['page'] = $this->page;
        $return['count'] = $count;
        $return['page_count'] = ceil($count / $this->pagesize);
        $this->limitRule->limit(($this->page - 1)*$this->pagesize, $this->pagesize);
        $return['data'] = $this->limitRule->get();

        return $return;

    }
}