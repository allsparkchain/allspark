<?php

namespace App\Utils;


class PaymentPlan
{
    private $_account;      //投资金额
    private $_apr;          //年利率
    private $_period;       //投资周期
    private $_periodType;   //期限类型，1=月，2=周，3=天
    private $_time;         //投资时间
    private $_type;         //还款方式，1=一次性还本付息，2=等额本息，3=等本等息，4=按期付息到期还本
    private $_monthApr;     //月利率（百分比）
    private $_dayApr;       //天利率
    private $_return;       //返回结果

    /**
     * 获取还款计划
     * @param float $account 投资金额
     * @param float $apr 利率（*100）
     * @param int $time 投资时间（时间戳）
     * @param int $period 投资周期
     * @param int $periodType 期限类型，1=天，2=周，3=月
     * @param int $type 还款方式，1=一次性还本付息，2=等额本息，3=等本等息，4=按期付息到期还本
     * @return array 还款方式
     */
    public function getPaymentPlan($account, $apr, $time, $period, $periodType = 1, $type = 1)
    {
        $this->_account = $account;
        $this->_apr = bcdiv($apr, 100, 10);
        $this->_period = $period;
        $this->_periodType = $periodType;
        $this->_time = $time;
        $this->_type = $type;
        $this->_monthApr = $this->_apr / 12;
        $this->_dayApr = $this->_apr / 360;

        $this->_checkParam();
        switch ($type) {
            case 1:
                $this->_return = $this->equalEnd();
                break;
            default:
                $this->_return = [];
        }

        return $this->_return;
    }

    /**
     * 一次性还本付息
     * @return array
     */
    protected function equalEnd()
    {
        if ($this->_periodType == 3) {          //月
            $interest = $this->_monthApr * $this->_period * $this->_account;
            $type = 'month';
        } elseif ($this->_periodType == 2) {        //周
            $interest = $this->_dayApr * $this->_period * 7 * $this->_account;
            $type = 'week';
        } else {                                    //天
            $interest = $this->_dayApr * $this->_period * $this->_account;
            $type = 'days';
        }
        return [
            'account_total' => $this->_formatNumber($this->_account, $interest),    //本息
            'interest_total' => $this->_formatNumber($interest),     //总利息
            'capital_total' => $this->_formatNumber($this->_account), //本金
            'repay' => [        //还款计划
                [
                    'period' => 1,
                    'time' => strtotime("+{$this->_period} {$type}", $this->_time),   //还款时间
                    'total' => $this->_formatNumber($this->_account, $interest),               //还款本息
                    'interest' => $this->_formatNumber($interest),             //还款利息
                    'capital' => $this->_formatNumber($this->_account),        //还款本金
                ],
            ]
        ];
    }

    /**
     * 等本等息
     * @return array
     */
    protected function EqualInstallmentsOfPrincipalAndInterest()
    {
        if ($this->_periodType == 3) {          //月
            $interest = $this->_monthApr * $this->_period * $this->_account;
            $type = 'month';
        } elseif ($this->_periodType == 2) {        //周
            $interest = $this->_dayApr * $this->_period * 7 * $this->_account;
            $type = 'week';
        } else {                                    //天
            $interest = $this->_dayApr * $this->_period * $this->_account;
            $type = 'days';
        }

        $repayData = [
            'account_total' => $this->_formatNumber($this->_account, $interest),    //本息
            'interest_total' => $this->_formatNumber($interest),     //总利息
            'capital_total' => $this->_formatNumber($this->_account), //本金
        ];

        for ($i = 1;$i<= $this->_period;$i++){
            $repayList = [];
            if($i<$this->_period){
                $repayList = [
                    'period' => $this->_period,
                    'time' => strtotime("+{$i} {$type}", $this->_time),   //还款时间
                    'total' => bcdiv($repayData['account_total'],$this->_period) ,               //还款本息
                    'interest' => bcdiv($repayData['interest_total'],$this->_period),             //还款利息
                    'capital' => bcdiv($repayData['capital_total'],$this->_period),        //还款本金
                ];
            }else{
                $repayList = [
                    'period' => $this->_period,
                    'time' => strtotime("+{$i} {$type}", $this->_time),   //还款时间
                    'total' => $this->_getLastPeriodValue($repayData['account_total'],$this->_period) ,               //还款本息
                    'interest' => $this->_getLastPeriodValue($repayData['interest_total'],$this->_period),             //还款利息
                    'capital' => $this->_getLastPeriodValue($repayData['capital_total'],$this->_period),        //还款本金
                ];
            }

            $repayData['repay'][]=$repayList;
        }

        return $repayData;
    }

    protected function equalMonth()
    {
        if ($this->_periodType == 3) {
            $return = [
                'account_total' => 0,       //本息
                'interest_total' => 0,      //总利息
                'capital_total' => 0,       //总本金
                'repay' => [],              //收款计划
            ];

            $_apr = pow((1 + $this->_monthApr), $this->_period);
            $investTotal = $this->_account * $this->_monthApr * $_apr / ($_apr - 1);
            for ($i = 0; $i < $this->_period; $i++) {
                if ($i == 0) {
                    $interest = bcmul($this->_account, $this->_monthApr, 2);
                } else {
                    $_lu = pow((1 + $this->_monthApr), $i);
//                    $interest = bcadd('', $)
                }
            }
        }
    }

    private function _checkParam()
    {
        $this->_validatorParam('_period', '投资期数');
        $this->_validatorParam('_account', '投资金额');
        $this->_validatorParam('_periodType', '期数类型');
//        $this->_validatorParam('_apr', '年利率');
    }

    private function _validatorParam($fieldName, $title)
    {
        if (!($this->$fieldName && (int)$this->$fieldName)) {
            throw new \Exception($title . '错误');
        }
    }

    private function _formatNumber($number, $plus = 0, $decimal = 2)
    {
        return bcadd($number, $plus, $decimal);
    }

    /**
     * 计算最后一期金额
     * @param $total
     * @param $period
     * @return string
     */
    private function _getLastPeriodValue($total , $period){
        $eachPeriodValue = bcdiv($total, $period); //计算后每期期数
        $allPeriodExcludeLastPeriod = bcsub ($period, 1);  //除了最后一期总期数
        $allPeriodValueExcludeLastPeriod = bcmul($eachPeriodValue, $allPeriodExcludeLastPeriod); //最后一期剩余等额本息金额
        $lastPeriodValue = bcsub ($total, $allPeriodValueExcludeLastPeriod);  //总金额减去 除了最后一期的金额
        return $lastPeriodValue;
    }
}