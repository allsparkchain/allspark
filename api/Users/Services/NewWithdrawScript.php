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
use App\Utils\Paramers;
use App\Utils\ErrorConst;
use Psr\Log\LoggerInterface;
use function App\getCurrentTime;
use App\Utils\ThrowResponseParamerTrait;
use App\Utils\Lib\Allinpay\Allinpay_Life;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class NewWithdrawScript
{
    use EnableDIAnnotations, ThrowResponseParamerTrait;
    /**
     * @inject
     * @var DB
     */
    private $db;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;

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
     * 提现系统申请
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function withdrawApply($uid, $bankId, $account, $orderNumber, $feeType = 0, $account_prop)
    {
        $this->logger->info('withdrawApply');
        $user = $this->getUser($uid, $bankId);
        return $this->mutex->getMutex('withdrawApply' . $user['wx_id'])->synchronized(function () use ($user, $bankId, $account, $orderNumber, $feeType, $account_prop) {
            $this->checkAccount($account);
            $wallet = $this->checkWeixinWallet($user['wx_id'], $account);
            $this->checkSms($user['mobile'], $orderNumber);
            return $this->db->transaction(function () use ($user, $wallet, $bankId, $account, $orderNumber, $feeType, $account_prop) {
                $userAccount = $this->db->select("*")->from("t_user_account")->where('uid = ?', $user['uid'])->getFirst();
                $execResult = $this->db->update('t_weixin_wallet')->set([
                    'balance' => DB::raw('balance-' . $account),
                    'freezing_amount' => DB::raw('freezing_amount + ' . $account),
                ])->where(['wx_id' => $wallet['id']])->exec();
                if ($execResult->rows) {
                    throw $this->exception([
                        'code' => ErrorConst::SYSTEM_ERROR,
                        'text' => "提现失败",
                    ]);
                }
                $orderId = $this->insertOrder($wallet['id'], $user['uid'], 1, $account);
                $this->db->insertInto('t_order_funds')->values([
                    'order_id' => $orderId,
                    'contents' => json_encode([
                        'user' => $user,
                        'wallet' => $wallet,
                        'userAccount' => $userAccount,
                    ]),
                    'add_time' => getCurrentTime()
                ])->exec();
                $rid = $this->db->insertInto('t_weixin_wallet_record')->values([
                    'is_plus' => 2,
                    'type' => 3,
                    'wallet_id' => $wallet['id'],
                    'uid' => $user['uid'],
                    'order_number' => \App\flowNumber(),
                    'account' => $account,
                    'current_balance' => bcsub($wallet['current_balance'], $account, 4),
                    'explain' => '提现',
                    'add_time' => getCurrentTime()
                ])->exec()->lastInsertId();
                $poundage = 0;
                if ($feeType > 0) $poundage = 3;
                $withdrawId = $this->db->insertInto('t_user_withdrawal_application')->values([
                    'uid' => $user['uid'],
                    'wx_id' => $user['wx_id'],
                    'account' => $account,
                    'poundage' => $poundage,
                    'status' => '1',
                    'weixin_wallet_record_id' => $rid,
                    'banknumber' => $user['banknumber'],
                    'realname' => $user['realname'],
                    'mobile' => $user['mobile'],
                    'idnumber' => $user['idnumber'],
                    'account_prop' => $account_prop,
                    'add_time' => getCurrentTime(),
                ])->exec()->lastInsertId();
                $wlid = $this->db->insertInto('t_user_withdraw_log')->values([
                    'withdraw_id' => $withdrawId,
                    'add_time' => getCurrentTime(),
                    'request_content' => json_encode([
                        'uid' => $user['uid'],
                        'wx_id' => $user['wx_id'],
                        'bankId' => $bankId,
                        'account' => $account,
                        'orderNumber' => $orderNumber,
                        'feeType' => $feeType,
                        'account_prop' => $account_prop,
                        'user' => $user,
                        'userAccount' => $userAccount,
                        'orderId' => $orderId,
                        'weixin_wallet_record_id' => $rid,
                    ])
                ])->exec()->lastInsertId();
                $this->db->insertInto('t_weixin_wallet_record_extend')->values([
                    'weixin_wallet_record_id' => $rid,
                    'uid' => $wallet['uid'],
                    'content' => json_encode([
                        'user' => $user,
                        'wallet' => $wallet,
                        'userAccount' => $userAccount,
                        'withdraw_id' => $withdrawId,
                        'user_withdraw_log_id' => $wlid
                    ]),
                    'add_time' => getCurrentTime()
                ])->exec();
                $this->db->update('t_verify_order')->set([
                    'status' => 2,
                    'use_time' => getCurrentTime(),
                ])->where(['order_number' => $orderNumber])->exec();
                return ['message' => '申请成功', 'status' => 200];
            });
        });
    }

    /**
     * 提现系统审核
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    public function withdrawalAudit()
    {
        $this->logger->info('withdrawalAudit');
        return $this->mutex->getMutex('withdrawalAudit')->synchronized(function () {
            $list = $this->db->select('*')->from('t_user_withdrawal_application')
                ->where(['status' => 1, 'weixin_wallet_record_id' => ['>' => 0]])
                ->orderBy('id', DB::ORDER_BY_ASC)->get();
            $withdrawFunction = $this->container->get('withdrawFunction');
            foreach ($list as $k => $v) {
                $log = $this->db->select('id', 'withdraw_id', 'error')->from('t_user_withdraw_log')
                    ->where(['withdraw_id' => $v['id']])->getFirst();
                if ($this->withdrawalCheck($v, $log) !== true) continue;
                if ($this->fundMonitor($v, $log) !== true) continue;
                $this->withdrawalAuditDetail($v, $log, $withdrawFunction['apply']);
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
            $where .= ' and select_count<=10 and response_content is not null';
            $where .= ' and UNIX_TIMESTAMP(t_user_withdraw_log.update_time)<' . (getCurrentTime() - 60 * 10);
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
                ])->exec();
                $code = isset($res['AIPG']['INFO']['RET_CODE']) ? $res['AIPG']['INFO']['RET_CODE'] : null;
                if ($res && $code == '0000') {//返回成功
                    $execResult = $this->db->update('t_user_withdrawal_application')->set(['status' => 3])
                        ->where(['id' => $v['id'], 'status' => 2])->exec();
                    if (!$execResult->rows) return false;
                    //更新余额
                    $execResult = $this->db->update('t_weixin_wallet')->set([
                        'freezing_amount' => DB::raw('freezing_amount - ' . $v['account']),
                    ])->where(['wx_id' => $v['wx_id'], 'freezing_amount' => ['>=' => $v['account']]])->exec();
                    if ($execResult->rows != 1) {
                        $this->db->getConnection()->rollBack();
                        return false;
                    }
                    $orderId = $this->insertOrder($v['wx_id'], $v['uid'], 6, $v['account']);
                    if (!$orderId) {
                        $this->db->getConnection()->rollBack();
                        return false;
                    }
                    $wallet = $this->db->select("*")->from("t_weixin_wallet")->where('wx_id = ?', $v['wx_id'])->getFirst();
                    $execResult = $this->db->insertInto('t_order_funds')->values([
                        'order_id' => $orderId,
                        'contents' => json_encode($wallet),
                        'add_time' => getCurrentTime()
                    ])->exec();
                    if (!$execResult->lastInsertId()) {
                        $this->db->getConnection()->rollBack();
                        return false;
                    }
                } else if ($v['select_count'] == 10) {
                    $log = $this->db->select('id', 'withdraw_id', 'error')->from('t_user_withdraw_log')
                        ->where(['withdraw_id' => $v['id']])->getFirst();
                    $this->db->update('t_user_withdrawal_application')->set(['status' => 4])
                        ->where(['id' => $v['id']])->exec();
                    $this->editWithdrawLog($log, 12);
                }
                $this->db->getConnection()->commit();
            }
            return true;
        });
    }

    //提现失败
    private function cashFail($v, $log)
    {
        $this->db->getConnection()->beginTransaction();
        $this->logger->info('cashFail', ['withdrawal' => $v, 'log' => $log]);
        $execResult = $this->db->update('t_user_withdrawal_application')->set(['status' => 5])
            ->where(['id' => $v['id'], 'status' => 4])->exec();
        if (! $execResult->rows) return false;
        $execResult = $this->db->update('t_weixin_wallet')->set([
            'balance' => DB::raw('balance + ' . $v['account']),
            'freezing_amount' => DB::raw('freezing_amount - ' . $v['account']),
        ])->where(['wx_id' => $v['wx_id'], 'freezing_amount' => ['>=' => $v['account']]])->exec();
        if ($execResult->rows != 1) {
            $this->editWithdrawLog($log, 11);
            $this->db->getConnection()->rollBack();
            return false;
        }
        $orderId = $this->insertOrder($v['wx_id'], $v['uid'], 7, $v['account']);
        $wallet = $this->db->select("*")->from("t_weixin_wallet")->where(['wx_id' => $v['wx_id']])->getFirst();
        $this->db->insertInto('t_order_funds')->values([
            'order_id' => $orderId,
            'contents' => json_encode($wallet),
            'add_time' => getCurrentTime()
        ])->exec();
        $rid = $this->db->insertInto('t_weixin_wallet_record')->values([
            'type' => 4,
            'wallet_id' => $wallet['id'],
            'wx_id' => $wallet['wx_id'],
            'uid' => $v['uid'],
            'order_number' => \App\flowNumber(),
            'account' => $v['account'],
            'current_balance' => $wallet['balance'],
            'explain' => '提现失败',
            'add_time' => getCurrentTime()
        ])->exec()->lastInsertId();
        $this->db->insertInto('t_weixin_wallet_record_extend')->values([
            'weixin_wallet_record_id' => $rid,
            'uid' => $wallet['uid'],
            'content' => json_encode([
                'wallet' => $wallet,
                'userAccount' => $this->db->select("*")->from("t_user_account")->where('uid = ?', $v['uid'])->getFirst(),
                'withdraw_id' => $v['id'],
            ]),
            'add_time' => getCurrentTime()
        ])->exec();
        $this->db->getConnection()->commit();
        return true;
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
            $record = $this->db->select('*')->from('t_weixin_wallet_record')
                ->where(['id' => $v['weixin_wallet_record_id']])->getFirst();
            if (!$record || $record['type'] != 1) return $this->editWithdrawLog($log, 3);
            $recordUp = $this->db->select('*')->from('t_weixin_wallet_record')->where([
                'wx_id' => $record['wx_id'],
                'account' => ['>' => 0],
                'add_time' => ['<' => $record['add_time']],
            ])->orderBy('add_time', DB::ORDER_BY_DESC)->limit(0, 1)->getFirst();
            if (!$recordUp) return $this->editWithdrawLog($log, 4);
            if ($record['is_plus'] == 1) {
                if (bcadd($recordUp['current_balance'], $record['account'], 4) != bcadd($record['current_balance'], 0, 4)) {
                    return $this->editWithdrawLog($log, 5);
                }
            } else {
                if (bcadd($record['current_balance'], $record['account'], 4) != bcadd($recordUp['current_balance'], 0, 4)) {
                    return $this->editWithdrawLog($log, 5);
                }
            }
            $recordDown = $this->db->select('*')->from('t_weixin_wallet_record')->where([
                'wx_id' => $record['wx_id'],
                'account' => ['>' => 0],
                'add_time' => ['>' => $record['add_time']],
            ])->orderBy('add_time', DB::ORDER_BY_ASC)->limit(0, 1)->getFirst();
            if (! $recordDown) return true;
            if ($recordDown['is_plus'] == 1) {
                if (bcadd($record['current_balance'], $recordDown['account'], 4) != bcadd($recordDown['current_balance'], 0, 4)) {
                    return $this->editWithdrawLog($log, 5);
                }
            } else {
                if (bcadd($recordDown['current_balance'], $recordDown['account'], 4) != bcadd($record['current_balance'], 0, 4)) {
                    return $this->editWithdrawLog($log, 5);
                }
            }
            return true;
        });
    }


    //提现校验
    private function withdrawalCheck($v, $log)
    {
        $this->logger->info('提现校验', ['withdrawal' => $v, 'log' => $log]);
        if ($v['account'] <= 0) return $this->editWithdrawLog($log, 1);
        $user = $this->db->select('uid')->from("t_user_info")->where('uid = ?', $v['uid'])->getFirst();
        if (!$user) $this->editWithdrawLog($log, 2);
        return true;
    }

    /**
     * 获取weixin_user
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    private function getUser($uid, $bankId = 0)
    {
        $field = 'i.uid,wur.wx_id,i.mobile,b.realname,id_card,banknumber,sub_branch_name,sub_branch_id,idnumber';
        $user = $this->db->select($field, DB::raw('b.id as bank_id'))->from('t_weixin_user_relate', 'wur')
            ->leftJoin(DB::raw('t_user_info i'))->on('i.uid = wur.uid')
            ->leftJoin(DB::raw('t_user_bank b'))->on('b.uid = wur.uid')
            ->where(['wur.uid' => $uid])->getFirst();
        if (!$user) {
            throw $this->exception([
                'code' => ErrorConst::USER_EXIST,
                'text' => '提现用户不存在uid' . $uid,
            ]);
        } else if ($bankId && $user['bank_id'] != $bankId) {
            throw $this->exception([
                'code' => ErrorConst::BANK_BINDED_NOTEXIST,
                'text' => '没有绑定的银行卡bankId:' . $bankId,
            ]);
        }
        return $user;
    }

    /**
     * 验证短信
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    private function checkSms($mobile, $orderNumber)
    {
        $checkNumber = $this->db->select('*')->from('t_verify_order')->where([
            'order_number' => $orderNumber,
            'verify_type' => 4,
            'status' => 1,
            'useful_time' => ['>=' => getCurrentTime()],
        ])->getFirst();
        if (count($checkNumber) <= 0) {
            throw $this->exception([
                'code' => ErrorConst::SMS_VERIFY_EXPIRED,
                'text' => '验证码' . $orderNumber . '已失效...',
            ]);
        }
        $checkMobile = json_decode($checkNumber['verify_text'], true);
        if ($checkMobile['mobile'] != $mobile) {
            throw $this->exception([
                'code' => ErrorConst::SMS_VERIFY_EXPIRED,
                'text' => '验证码' . $orderNumber . '已失效',
            ]);
        }
    }

    /**
     * 检验金额
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    private function checkAccount($account)
    {
        if ($account > 0) return;
        throw $this->exception([
            'code' => ErrorConst::LACKOFBALANCE_ERROR,
            'text' => '提现金额不正确',
        ]);
    }

    /**
     * 检验钱包用户
     * @return mixed
     * @throws \App\Exceptions\RuntimeException
     */
    private function checkWeixinWallet($wx_id, $account)
    {
        $wallet = $this->db->select("*")->from("t_weixin_wallet")->where(['wx_id' => $wx_id])->getFirst();
        if (!$wallet) {
            throw $this->exception([
                'code' => ErrorConst::USER_EXIST,
                'text' => '提现用户不存在 ' . $wx_id,
            ]);
        }
        if ($wallet['balance'] < $account) {
            throw $this->exception([
                'code' => ErrorConst::LACKOFBALANCE_ERROR,
                'text' => "用户提现余额不足account:$account wallet" . json_encode($wallet)
            ]);
        }
        return $wallet;
    }

    //生成订单
    private function insertOrder($wx_id, $uid, $type, $account)
    {
        return $this->db->insertInto('t_order')->values([
            'wx_id' => $wx_id,
            'uid' => $uid,
            'order_number' => 'ORD' . time() . rand(10000, 99999),
            'status' => 3,
            'type' => $type,
            'account' => $account,
            'add_time' => getCurrentTime(),
        ])->exec()->lastInsertId();
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