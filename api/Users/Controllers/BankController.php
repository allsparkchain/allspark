<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 13:27
 */

namespace App\Users\Controllers;
use App\Utils\Defines;
use App\Utils\HttpResponseTrait;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use App\Users\Services\Bank;

/**
 * @path /bank
 */
class BankController
{
    
    use EnableDIAnnotations, HttpResponseTrait, ThrowResponseParamerTrait; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Bank
     */
    public $bank;
    /**
     * 银行支行选择
     * @route POST /bank_select
     * @param int $province_id
     * @param int $city_id
     * @param int $bank_id
     * @return array
     */
    public function bankSelect($province_id = 0, $city_id = 0, $bank_id = 0) {
        try {
            $rs = $this->bank->select($province_id, $city_id, $bank_id);
            return $this->respone(Defines::HTTP_OK, Defines::SUCCESS,$rs);
        } catch (\Exception $e) {
            return $this->respone($e->getCode(), $e->getMessage());
        }
    }
}