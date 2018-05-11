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
use function App\FC;
use App\Utils\Paramers;
use App\Utils\ErrorConst;
use Psr\Log\LoggerInterface;
use function App\getCurrentTime;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class SettlementScript
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
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * 支付回调 结算佣金
     * @param string $pay_order_number
     * @return bool
     * @throws \App\Exceptions\RuntimeException
     */
    public function paymentCallback($pay_order_number)
    {
        try {
            return $this->mutex->getMutex('paymentCallback' . $pay_order_number)->synchronized(function () use ($pay_order_number) {
                return $this->db->transaction(function (DB $db) use ($pay_order_number) {
                    $scriptOrder = $this->db->select('*')->from('t_script_order')
                        ->where(['pay_order_number' => $pay_order_number])->getFirst();
                    if (!$scriptOrder || $scriptOrder['status'] == 2) {
                        throw $this->exception([
                            'code' => ErrorConst::SYSTEM_ERROR,
                            'text' => '订单不存在或已经执行过了 pay_order_number:' . $pay_order_number,
                        ]);
                    }
                    $productOrder = $this->db->select('*')->from('t_product_order')
                        ->where(['id' => $scriptOrder['product_order_id']])->getFirst();
                    if (!$productOrder) {
                        throw $this->exception([
                            'code' => ErrorConst::SYSTEM_ERROR,
                            'text' => '找不到订单 pay_order_number:' . $pay_order_number,
                        ]);
                    }
                    $execResult = $db->update('t_script_order')->set(['status' => 2])
                        ->where(['id' => $scriptOrder['id']])->exec();
                    if (!$execResult->rows) {
                        throw $this->exception([
                            'code' => ErrorConst::SYSTEM_ERROR,
                            'text' => '修改操作状态失败 pay_order_number:' . $pay_order_number,
                        ]);
                    }
                    $this->userNewCommission($scriptOrder['product_order_id']);
                    return true;
                });
            });
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    //佣金分成
    private function userNewCommission($product_order_id)
    {
        $productOrder = $this->getproductOrder($product_order_id);
        $userCollect = $this->userCollect($productOrder);
        $fc = FC($productOrder['percent'], $productOrder['account'], $productOrder['number']);
        $this->platformOrder($productOrder);
        $this->compensationPrice($productOrder, $fc, $userCollect);
        $this->advertFC($productOrder, $fc, $userCollect);
        $this->userFC($productOrder, $fc, $userCollect);
    }

    //购买商品成功后 给平台加钱 增加资金流水 增加order订单
    private function platformOrder($productOrder)
    {
        $userAccount = $this->db->select('available_amount')->from('t_user_account')->where(['uid' => 1])->getFirst();
        $this->db->update('t_user_account')->set([
            'available_amount' => DB::raw('available_amount + ' . $productOrder['account'])
        ])->where(['uid' => 1])->exec();
        $pid = $this->insertOrder(1, 8, $productOrder['account']);
        $this->db->insertInto('t_order_funds')->values([
            'order_id' => $pid,
            'contents' => json_encode([
                'uid' => 1,
                'productOrder' => $productOrder,
                'available_amount' => bcadd($userAccount['available_amount'], $productOrder['account'], 4),
            ]),
            'add_time' => getCurrentTime(),
        ])->exec();
        $this->db->insertInto('t_user_commission_record')->values([
            'uid' => 1,
            'order_id' => $productOrder['order_id'],
            'type' => 5,
            'account' => $productOrder['account'],
            'available_amount' => $userAccount['available_amount'] + $productOrder['account'],
            'add_time' => getCurrentTime(),
        ])->exec();
        $this->logger->info('购买商品成功后 给平台加钱 增加资金流水 增加order订单', [
            'order_id' => $pid,
            'product_order_id' => $productOrder['id']
        ]);
    }

    private function getproductOrder($product_order_id)
    {
        $field = 't_product_order.*,t_product.settled_day,t_order.account,t_article.author';
        $field .= ',t_spread_list.article_product_id,t_article_product_relate.percent';
        return $this->db->select($field)->from('t_product_order')
            ->leftJoin('t_product')->on('t_product_order.product_id=t_product.id')
            ->leftJoin('t_order')->on('t_product_order.order_id=t_order.id')
            ->leftJoin('t_article')->on('t_product_order.article_id=t_article.id')
            ->leftJoin('t_spread_list')->on('t_spread_list.id=t_product_order.spread_id')
            ->leftJoin('t_article_product_relate')->on('t_spread_list.article_product_id=t_article_product_relate.id')
            ->where(['t_product_order.id' => $product_order_id])->getFirst();
    }

    //广告主收入结束
    private function advertFC($productOrder, $fc, $userCollect)
    {
        if ($fc[7] * 1 <= 0) return;
        $tara = $this->db->select(DB::raw('sum(account) as account'))
            ->from('t_advert_relative_account_flow')->where(['advert_relative_uid' => $userCollect[7]])->getFirst();
        $before_account = isset($tara['account']) ? $tara['account'] : 0;
        $account_min = bcsub($productOrder['account'], $fc[7], 4);
        $new_total = bcadd($before_account, $fc[7], 4);
        //获得广告主 产品 结算时间
        $cpa_settlementday = $this->container->get('cpa_settlementday');
        $cpa_settlementday = $cpa_settlementday + $productOrder['settled_day'];
        //购买后30天 + t_product settled_day
        $unlock_time = strtotime("+" . $cpa_settlementday . " day", strtotime(date('Y-m-d', getCurrentTime())));
        if (!$cpa_settlementday) $unlock_time = getCurrentTime();
        $this->db->insertInto('t_advert_relative_account_flow')->values([
            'advert_uid' => $userCollect[5],
            'advert_relative_uid' => $userCollect[7],
            'product_order_id' => $productOrder['id'],
            'order_account' => $productOrder['account'],
            'commission' => $account_min,
            'before_account' => $before_account,
            'account' => $fc[7],
            'status' => 1,
            'contents' => json_encode(['fc' => $fc, 'userCollect' => $userCollect]),
            'after_account' => $new_total,
            'unlock_time' => $unlock_time,
            'add_time' => getCurrentTime(),
        ])->exec();
        //添加广告主，广告主代理 产品订单记录
        $article_info = $this->db->select(DB::raw('name,shortname,summary,author'))->from('t_article')
            ->where(['id' => $productOrder['article_id']])->getFirst();
        $product_info = $this->db->select('*')->from('t_product')->where(['id' => $productOrder['product_id']])
            ->getFirst();
        $this->db->insertInto('t_advert_order_relate')->values([
            'advert_uid' => $userCollect[5],
            'advert_relative_uid' => $userCollect[7],
            'product_order_id' => $productOrder['id'],
            'article_info' => json_encode($article_info),
            'product_info' => json_encode($product_info),
            'add_time' => getCurrentTime()
        ])->exec();
    }

    //获得收入
    private function userFC($productOrder, $fc, $userCollect)
    {
        $percent = $this->percent($productOrder);
        $settlementday = $this->container->get('settlementday');
        foreach ($fc as $k => $v) {
            if ($v * 1 == 0 || in_array($k, [0, 7]) || !$userCollect[$k]) continue;
            $orderId = $this->db->insertInto('t_order')->values([
                'uid' => $userCollect[$k],
                'order_number' => $this->getOrderNumber(),
                'status' => 2,
                'type' => 4,
                'account' => $v,
                'add_time' => getCurrentTime(),
            ])->exec()->lastInsertId();
            $this->db->insertInto('t_order_funds')->values([
                'order_id' => $orderId,
                'contents' => json_encode($productOrder),
                'add_time' => getCurrentTime(),
            ])->exec();
            $sum_account = $this->db->select(DB::raw('sum(account) as account'))->from('t_user_commission')
                ->where(['uid' => $userCollect[$k]])->getFirst();
            $sum_account = isset($sum_account['account']) ? $sum_account['account'] : 0;
            //此时 未结算的 总佣金
            $sum_unsettled_account = $this->db->select(DB::raw('sum(account) as account'))->from('t_user_commission')
                ->where(['uid' => $userCollect[$k], 'status' => 1])->getFirst();
            $sum_unsettled_account = isset($sum_unsettled_account['account']) ? $sum_unsettled_account['account'] : 0;

            //佣金结算日期type5广告主代理
            //结算时间
            //购买后30天
            $unlock_time = strtotime("+" . $settlementday . " day");
            if (!$settlementday) $unlock_time = getCurrentTime();
            $this->db->insertInto('t_user_commission')->values([
                'uid' => $userCollect[$k],
                'order_id' => $orderId,
                'product_order_id' => $productOrder['id'],
                'type' => $k,
                'product_account' => $productOrder['account'],
                'percent' => $percent[$k],
                'account' => $v,
                'after_account' => bcadd($sum_account, $v, 4),
                'after_unsettled_account' => bcadd($sum_unsettled_account, $v, 4),
                'status' => 1,
                'unlock_time' => $unlock_time,
                'add_time' => getCurrentTime(),
            ])->exec();
            if ($k == 1) {//写手
                $this->db->update('t_article_product_relate')->set([
                    'number' => DB::raw('number + ' . $productOrder['number']),
                    'commission_account' => DB::raw('commission_account + ' . $v)
                ])->where(['id' => $productOrder['article_product_id']])->exec();
            } elseif ($k == 2) {//渠道
                $this->db->update('t_spread_list')->set([
                    'number' => DB::raw('number + ' . $productOrder['number']),
                    'commission_account' => DB::raw('commission_account + ' . $v)
                ])->where(['id' => $productOrder['spread_id']])->exec();
            }
            $this->db->update('t_user_account')->set([
                'unsettled_amount' => DB::raw('unsettled_amount + ' . $v),
                'unsettled_amount_month' => DB::raw('unsettled_amount_month + ' . $v)
            ])->where(['uid' => $userCollect[$k]])->exec();
        }
        $this->db->update('t_advert_relative_account_flow')->set([
            'contents' => json_encode(['fc' => $fc, 'userCollect' => $userCollect])
        ])->where(['product_order_id' => $productOrder['id']])->exec();
    }

    private function percent($productOrder)
    {
        $method = json_decode($productOrder['percent'], true);
        $percent = [0, 0, 0, 0, 0, 0, 0, 0];
        foreach ($method as $k => $v) {
            if (!isset($v['mode'])) continue;
            $percent[$v['mode']] = isset($v['contents']['percent']) ? $v['contents']['percent'] : 0;
        }
        return $percent;
    }

    private function userCollect($productOrder)
    {
        $arr = [0, 0, 0, 0, 0, 0, 0, 0];
        $arr[1] = $productOrder['author'];
        $arr[2] = $productOrder['counting_id'];
        $arr[3] = 1;
        $inviteUid = $this->db->select('invite_uid')->from('t_user_invite_relate')
            ->where(['t_user_invite_relate.uid' => $productOrder['counting_id']])->getFirst();
        $arr[4] = $inviteUid ? $inviteUid['invite_uid'] : 0;
        $advert = $this->db->select('advert_relative_uid')->from('t_advert_product_relate')
            ->where(['product_id' => $productOrder['product_id']])->getFirst();
        $arr[7] = $advert ? $advert['advert_relative_uid'] : 0;
        if ($advert['advert_relative_uid'] > 0) {
            $advertInfo = $this->db->select('advert_uid')->from('t_advert_relative_user_login')
                ->where(['uid' => $advert['advert_relative_uid']])->getFirst();
            $arr[5] = $advertInfo ? $advertInfo['advert_uid'] : 0;
        }
        $inviteUid = $this->db->select('invite_uid')->from('t_user_invite_relate')
            ->where(['t_user_invite_relate.uid' => $productOrder['counting_id']])->getFirst();
        $arr[6] = $inviteUid ? $inviteUid['invite_uid'] : 0;
        return $arr;
    }

    //第一次分成处理差价
    private function compensationPrice($productOrder, $fc, $userCollect)
    {
        $sum = bcadd($fc[3], $fc[7], 4);
        $money = bcadd($productOrder['account'], 0, 4);
        if ($money >= $sum) return;
        $account = bcsub($sum, $money, 4);
        $zAccount = $this->db->select('*')->from('t_user_account')->where(['uid' => 4])->getFirst();
        $pid = $this->insertOrder(4, 9, $account);
        $this->db->insertInto('t_order_funds')->values([
            'order_id' => $pid,
            'contents' => json_encode([
                'fc' => $fc,
                'user_account' => $zAccount,
                'userCollect' => $userCollect,
                'productOrder' => $productOrder,
                'innfo' => '第一次分成处理差价,生成转账订单',
            ]),
            'add_time' => getCurrentTime(),
        ])->exec();
        $zid = $this->insertOrder(4, 10, $account, $pid);
        $this->db->insertInto('t_order_funds')->values([
            'order_id' => $zid,
            'contents' => json_encode([
                'fc' => $fc,
                'user_account' => $zAccount,
                'userCollect' => $userCollect,
                'productOrder' => $productOrder,
                'innfo' => '第一次分成处理差价,生成支出订单',
            ]),
            'add_time' => getCurrentTime(),
        ])->exec();
        $this->db->insertInto('t_user_commission_record')->values([
            'uid' => 4,
            'type' => 6,//分成第一次差价
            'account' => $account,
            'available_amount' => bcsub($zAccount['available_amount'], $account, 4),
            'add_time' => getCurrentTime(),
        ])->exec();

        $adminAccount = $this->db->select('available_amount')->from('t_user_account')->where(['uid' => 1])->getFirst();
        $this->db->update('t_user_account')->set(['available_amount' => DB::raw('available_amount + ' . $account)])
            ->where(['uid' => 1])->exec();
        $zid = $this->insertOrder(1, 11, $account, $pid);
        $this->db->insertInto('t_order_funds')->values([
            'order_id' => $zid,
            'contents' => json_encode([
                'fc' => $fc,
                'user_account' => $zAccount,
                'userCollect' => $userCollect,
                'productOrder' => $productOrder,
                'innfo' => '第一次分成处理差价,生成收入订单',
            ]),
            'add_time' => getCurrentTime(),
        ])->exec();
        $this->db->insertInto('t_user_commission_record')->values([
            'uid' => 1,
            'type' => 6,//分成第一次差价平台收入
            'account' => $account,
            'available_amount' => bcadd($adminAccount['available_amount'], $account, 4),
            'add_time' => getCurrentTime(),
        ])->exec();
        $did = $this->db->insertInto('t_product_order_division_diff')->values([
            'product_order_id' => $productOrder['id'],
            'content' => json_encode([
                'fc' => $fc,
                'user_account' => $zAccount,
                'userCollect' => $userCollect,
                'productOrder' => $productOrder,
                'innfo' => '第一次分成处理差价',
            ]),
            'add_time' => getCurrentTime(),
        ])->exec()->lastInsertId();
        $this->logger->info('第一次分成处理差价', ['t_product_order_division_diff_id' => $did]);
    }

    //生成订单
    private function insertOrder($uid, $type, $account, $pid = 0)
    {
        return $this->db->insertInto('t_order')->values([
            'uid' => $uid,
            'pid' => $pid,
            'order_number' => $this->getOrderNumber(),
            'status' => 3,
            'type' => $type,
            'account' => $account,
            'add_time' => getCurrentTime(),
        ])->exec()->lastInsertId();
    }

    //生成订单编号
    private function getOrderNumber($type = 'ORD')
    {
        return $type . time() . rand(10000, 99999);
    }
}