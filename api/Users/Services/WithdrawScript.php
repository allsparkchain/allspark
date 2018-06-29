<?php
/**
 * Created by PhpStorm.
 * User: viki
 * Date: 2018/5/8/015
 * Time: 15:00
 */

namespace App\Users\Services;

use DI\Container;
use PhpBoot\DB\DB;
use App\Utils\Mutex;
use Psr\Log\LoggerInterface;
use function App\getCurrentTime;
use App\Utils\Lib\Allinpay\Allinpay_Life;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class WithdrawScript
{
    use EnableDIAnnotations;
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
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * 提现系统审核
     * @return mixed
     */
    public function withdrawalAudit()
    {
        $this->logger->info('withdrawalAudit');
        return $this->mutex->getMutex('withdrawalAudit')->synchronized(function () {
            $list = $this->db->select('*')->from('t_user_withdrawal_application')
                ->where(['status' => 1, 'user_commission_record_id' => ['>' => 0]])
                ->orderBy('id', DB::ORDER_BY_ASC)->get();
            $withdrawFunction = $this->container->get('withdrawFunction');
            foreach ($list as $k => $v) {
                $log = $this->db->select('id', 'withdraw_id', 'error')->from('t_user_withdraw_log')
                    ->where(['withdraw_id' => $v['id']])->getFirst();
                $check = $this->check($v, $log);
                if (!$check) continue;
                $check = $this->fundMonitor($v, $log);
                if ($check !== true) continue;
                $this->withdrawalAuditDetail($v, $log, $withdrawFunction['apply']);
            }
            return true;
        });
    }

    /**
     * 提现 资金监控
     *
     * @return array
     */
    public function fundMonitor($v, $log)
    {
        $this->logger->info('fundMonitor', ['withdrawal' => $v, 'log' => $log]);
        return $this->mutex->getMutex('fundMonitor' . $v['id'])->synchronized(function () use ($v, $log) {
            $record = $this->db->select('*')->from('t_user_commission_record')
                ->where(['id' => $v['user_commission_record_id']])->getFirst();
            if (!$record || $record['type'] != 1) return $this->editWithdrawLog($log, 3);
            $recordUp = $this->db->select('*')->from('t_user_commission_record')->where([
                'uid' => $record['uid'],
                'account' => ['>' => 0],
                'add_time' => ['<' => $record['add_time']],
            ])->orderBy('add_time', DB::ORDER_BY_DESC)->limit(0, 1)->getFirst();
            if (!$recordUp) return $this->editWithdrawLog($log, 4);
            else if (bcadd($recordUp['available_amount'], 0, 4) != bcadd($record['account'], $record['available_amount'], 4)) {
                return $this->editWithdrawLog($log, 5);
            }
            $recordDown = $this->db->select('*')->from('t_user_commission_record')->where([
                'uid' => $record['uid'],
                'account' => ['>' => 0],
                'add_time' => ['>' => $record['add_time']],
            ])->orderBy('add_time', DB::ORDER_BY_ASC)->limit(0, 1)->getFirst();
            if ($recordDown) {
                $type = in_array($recordDown['type'], [1, 3, 4]) ? 1 : $recordDown['type'];
                if ($type == 1 && bcadd($record['available_amount'], 0, 4) != bcadd($recordDown['account'], $recordDown['available_amount'], 4)) {
                    return $this->editWithdrawLog($log, 6);
                } else if ($type == 2 && bcadd($record['available_amount'], $recordDown['account'], 4) != bcadd($recordDown['available_amount'], 0, 4)) {
                    return $this->editWithdrawLog($log, 7);
                }
            }
            $list = $this->db->select('type', DB::raw('sum(account) as money'))->from('t_user_commission_record')->where([
                'uid' => $record['uid'],
                'account' => ['>' => 0],
                'type' => ['in' => [1, 2, 3]],
                'add_time' => ['<=' => $record['add_time']],
            ])->groupBy('type')->orderBy('type', DB::ORDER_BY_ASC)->get();
            if (!$list) return $this->editWithdrawLog($log, 8);
            $money1 = $money2 = $money3 = 0;
            foreach ($list as $k => $v) {
                if ($v['type'] == 1) $money1 = $v['money'];
                else if ($v['type'] == 2) $money2 = $v['money'];
                else $money3 = $v['money'];
            }
            $money4 = bcadd($money1, $money3, 4);
            $money4 = bcadd($money4, $record['available_amount'], 4);
            if (bcadd($money2, 0, 4) != $money4) return $this->editWithdrawLog($log, 9);
            return true;
        });
    }

    /**
     * 根据流水号查询提现是否成功
     *
     * @return mixed
     */
    public function withdrawalSelect()
    {
        $this->logger->info('withdrawalSelect');
        return $this->mutex->getMutex('withdrawalSelect')->synchronized(function () {
            $where = 'withdraw_id is not null and withdraw_no is not null and request_content is not null and status=2';
            $where .= ' and select_count<=10 and response_content is not null ';
            $where .= 'and UNIX_TIMESTAMP(t_user_withdraw_log.update_time)<' . (getCurrentTime() - 60 * 10);
            $list = $this->db->select('t_user_withdrawal_application.*', 'withdraw_no', 'select_count')
                ->from('t_user_withdrawal_application')->leftJoin('t_user_withdraw_log')
                ->on('t_user_withdrawal_application.id=t_user_withdraw_log.withdraw_id')->where($where)
                ->orderBy('t_user_withdrawal_application.id', DB::ORDER_BY_ASC)->get();
            if (!count($list)) {
                $this->logger->info('withdrawalSelect', ['no withdrawal select']);
                return true;
            }
            $allinpay = new Allinpay_Life($this->db, $this->container);
            $withdrawFunction = $this->container->get('withdrawFunction');
            foreach ($list as $k => $v) {
                $res = $allinpay->{$withdrawFunction['select']}($v['withdraw_no']);
                $this->logger->info('selectOrderMock', ['withdraw' => $v, 'res' => $res]);
                $this->db->getConnection()->beginTransaction();
                $this->db->update('t_user_withdraw_log')
                    ->set(['select_count' => DB::raw('select_count + 1')])->where(['withdraw_id' => $v['id']])->exec();
                $this->db->insertInto('t_user_withdraw_select_log')->values([
                    'withdraw_id' => $v['id'],
                    'content' => json_encode($res),
                    'add_time' => getCurrentTime()
                ])->exec()->lastInsertId();
                $code = isset($res['AIPG']['INFO']['RET_CODE']) ? $res['AIPG']['INFO']['RET_CODE'] : null;
                if ($res && $code == '0000') {//返回成功
                    $execResult = $this->db->update('t_user_withdrawal_application')->set(['status' => 3])
                        ->where(['id' => $v['id'], 'status' => 2])->exec();
                    if (!$execResult->rows) return false;
                    //更新余额
                    $execResult = $this->db->update('t_user_account')->set([
                        'freezing_amount' => DB::raw('freezing_amount - ' . $v['account']),
                    ])->where(['uid' => $v['uid'], 'freezing_amount' => ['>=' => $v['account']]])->exec();
                    if ($execResult->rows != 1) {
                        $this->db->getConnection()->rollBack();
                        return false;
                    }
                    //插订单t_order
                    $orderId = $this->db->insertInto('t_order')->values([
                        'uid' => $v['uid'],
                        'order_number' => $this->getOrderNumber(),
                        'status' => '3',
                        'type' => '6',
                        'account' => $v['account'],
                        'add_time' => getCurrentTime()
                    ])->exec()->lastInsertId();
                    if (!$orderId) {
                        $this->db->getConnection()->rollBack();
                        return false;
                    }
                    $userAccount = $this->db->select("*")->from("t_user_account")->where('uid = ?', $v['uid'])->getFirst();
                    $execResult = $this->db->insertInto('t_order_funds')->values([
                        'order_id' => $orderId,
                        'contents' => json_encode($userAccount),
                        'add_time' => getCurrentTime()
                    ])->exec();
                    if (!$execResult->lastInsertId()) {
                        $this->db->getConnection()->rollBack();
                        return false;
                    }
                    $execResult = $this->db->update('t_user_commission_record')->set(['type' => 3])
                        ->where(['id' => $v['user_commission_record_id']])->exec();
                    if (!$execResult->rows) {
                        $this->db->getConnection()->rollBack();
                        return false;
                    }
                } else if ($v['select_count'] == 10) {
                    $log = $this->db->select('id', 'withdraw_id', 'error')->from('t_user_withdraw_log')
                        ->where(['withdraw_id' => $v['id']])->getFirst();
                    $this->editWithdrawLog($log, 12);
                }
                $this->db->getConnection()->commit();
            }
            return true;
        });
    }

    /**
     * 提现系统审核
     *
     * @return bool
     */
    private function withdrawalAuditDetail($v, $log, $function)
    {
        $this->logger->info('withdrawalAuditDetail', ['withdrawal' => $v, 'log' => $log]);
        return $this->mutex->getMutex('withdrawalAuditDetails' . $v['id'])->synchronized(function () use ($v, $log, $function) {
            $execResult = $this->db->update('t_user_withdrawal_application')
                ->set(['sub_count' => DB::raw('sub_count + 1')])
                ->where('id = ? and UNIX_TIMESTAMP(update_time) < ? ', [$v['id']], [getCurrentTime() - 60])->exec();//设置超时
            if ($execResult->rows != 1) return $this->editWithdrawLog($log, 10);
            return $this->db->transaction(function () use ($v, $log, $function) {
                $this->db->update('t_user_withdrawal_application')->set([
                    'status' => 2,
                    'remark' => '系统提现',
                    'verfy_user_id' => 1,
                    'verfy_time' => getCurrentTime(),
                ])->where(['id' => $v['id'], 'status' => 1])->exec();
                $allinpay = new Allinpay_Life($this->db, $this->container);
                $res = $allinpay->$function($v);
                $res = $res ? $res : [];
                $this->db->update('t_user_withdraw_log')->set([
                    'withdraw_no' => $allinpay->withdrawNo,
                    'response_content' => json_encode($res)
                ])->where(['withdraw_id' => $v['id']])->exec();
                $this->logger->info('审核中', ['withdrawal' => $v, 'res' => $res]);
                return true;
            });
        });
    }

    //提现校验
    private function check($v, $log)
    {
        $this->logger->info('提现校验', ['withdrawal' => $v, 'log' => $log]);
        if ($v['account'] <= 0) return $this->editWithdrawLog($log, 1);
        $user = $this->db->select("*")->from("t_user_info")->where('uid = ?', $v['uid'])->getFirst();
        if (!$user) $this->editWithdrawLog($log, 2);
        return true;
    }

    //提现失败
    private function cashFail($v, $log)
    {
        $this->logger->info('cashFail', ['withdrawal' => $v, 'log' => $log]);
        $execResult = $this->db->update('t_user_account')->set([
            'freezing_amount' => DB::raw('freezing_amount - ' . $v['account']),
            'available_amount' => DB::raw('available_amount + ' . $v['account']),
        ])->where(['uid' => $v['uid'], 'freezing_amount' => ['>=' => $v['account']]])->exec();
        if ($execResult->rows != 1) return $this->editWithdrawLog($log, 11);
        $orderId = $this->db->insertInto('t_order')->values([
            'uid' => $v['uid'],
            'order_number' => $this->getOrderNumber(),
            'status' => '3',
            'type' => '7',
            'account' => $v['account'],
            'add_time' => getCurrentTime()
        ])->exec()->lastInsertId();
        $userAccount = $this->db->select("*")->from("t_user_account")->where('uid = ?', $v['uid'])->getFirst();
        $this->db->insertInto('t_order_funds')->values([
            'order_id' => $orderId,
            'contents' => json_encode($userAccount),
            'add_time' => getCurrentTime()
        ])->exec();
        $this->db->update('t_user_commission_record')->set(['type' => 4])
            ->where(['id' => $v['user_commission_record_id']])->exec();
        return true;
    }

    //获取订单 编号
    private function getOrderNumber()
    {
        return "ORD" . time() . rand(10000, 99999);
    }

    //提现异常
    private function editWithdrawLog($log, $type)
    {
        $errorArr = [
            '',
            '金额不正确',
            '提现用户不存在',
            '资金流水记录不存在',
            '资金流水记录异常',
            '资金流水记录异常',
            '资金流水记录异常',
            '资金流水记录异常',
            '资金流水记录异常',
            '资金流水记录异常',
            '提现操作频繁',
            '用户提现余额不足',
            '银行提现失败',
        ];
        $error = [
            'type' => $type,
            'text' => $errorArr[$type],
            'date' => date('Y-m-d H:i:s'),
        ];
        $this->db->update('t_user_withdraw_log')->set(['error' => json_encode($error)])->where(['id' => $log['id']])->exec();
        $this->db->update('t_user_withdrawal_application')->set(['abnormal' => $type])
            ->where(['id' => $log['withdraw_id']])->exec();
        $this->logger->info('editWithdrawLog', $error);
        return false;
    }
}