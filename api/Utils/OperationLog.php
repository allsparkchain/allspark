<?php

namespace App\Utils;

use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class OperationLog
{
    use EnableDIAnnotations;
    /**
     * @inject
     * @var DB
     */
    private $db;

    /**
     * @param string $operator 操作人信息
     * @param string $business_type 业务线类型
     * @param string $business_id 业务线id
     * @param string $business_info 业务信息快照
     * @param string $business_return 业务信息操作结果
     * @param string $business_return_info 操作结果信息
     */
    public function createLog($operator, $business_type, $business_id, $business_info, $business_return, $business_return_info){
        $this->db->insertInto("f_operation_log")->values([
            'operator'=>$operator,
            'business_type'=>$business_type,
            'business_id'=>$business_id,
            'business_info'=>$business_info,
            'business_return'=>$business_return,
            'business_return_info'=>$business_return_info,
            'add_time'=>time()
        ])->exec();
    }
}