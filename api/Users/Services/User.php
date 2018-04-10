<?php

namespace App\Users\Services;

use function App\getCurrentTime;
use function App\getIP;
use function App\md5string;
use App\Users\Entities\BankBindEntity;
use App\Users\Entities\UserEntity;
use App\Utils\ErrorConst;
use App\Utils\Lib\Message\Factory;
use App\Utils\Mutex;
use App\Utils\Pagination;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Psr\Log\LoggerInterface;


class User
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
     * @var Mutex
     */
    public $mutex;


    /**
     * @inject
     * @var Message
     */
    public $message;

    /**
     * @inject
     * @var Factory
     */
    public $factory;

    /**
     * 注册用户
     * @param UserEntity $userEntity
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function register(UserEntity $userEntity)
    {
        try{
            //查询mobile
            $checkExist = $this->checkMobileExist($userEntity->getMobile());
            if(count($checkExist) >0 ){
                throw $this->exception([
                    'code'=>ErrorConst::MOBILE_EXIST,
                    'text'=>"手机号".$userEntity->getMobile()."已存在"
                ]);
            }
//            //调取短信验证，返回验证单号 前端已验证
//            $orderNumber = $this->message->verificationSms($userEntity->getOrderNumber(), $userEntity->getMobile(), $userEntity->getCode());
            $now = getCurrentTime();
            $checkNumber = $data = $this->db->select('*')
                ->from('t_verify_order')
                ->where([
                        'order_number' =>$userEntity->getOrderNumber(),
                        'verify_type'=>1,
                        'status'=>1,
                        'useful_time'=> ['>='=>$now]
                        ]
                )->getFirst();
            if(count($checkNumber)<=0){
                throw $this->exception([
                    'code'=>ErrorConst::SMS_VERIFY_EXPIRED,
                    'text'=>"验证码".$userEntity->getOrderNumber()."已失效"
                ]);
            }
            $checkMobile = json_decode($checkNumber['verify_text'],true);
            if($checkMobile['mobile'] != $userEntity->getMobile()){
                throw $this->exception([
                    'code'=>ErrorConst::SMS_VERIFY_EXPIRED,
                    'text'=>"验证码".$userEntity->getOrderNumber()."已失效"
                ]);
            }

            return $this->mutex->getMutex('register'.$userEntity->getMobile())->synchronized(function() use($userEntity,$now){
                return $this->db->transaction(function(DB $db) use ($userEntity,$now){
                    $lastId = $db->insertInto('t_user')->values([
                        //'mobile'=>$userEntity->getMobile(),
                        //'password'=>md5string($userEntity->getPassword()),
                        'register_time'=>$now,
                        'register_ip'=>getIP(),
                        'status'=>1,
                        'add_time' =>$now
                    ])->exec()->lastInsertId();

                    if($lastId<=0){
                        throw $this->exception([
                            'code'=>ErrorConst::USER_CREATE_ERROR,
                            'text'=>"用户创建失败".serialize($userEntity->toArray())
                        ]);
                    }

                    $db->insertInto('t_user_login')->values([
                        'uid'=>$lastId,
                        'username'=>$userEntity->getMobile(),
                        'password'=>md5string($userEntity->getPassword()),
                        'add_time' =>$now
                    ])->exec();

                    $execResult = $db->insertInto('t_user_authentication')->values([
                        'uid' => $lastId,
                        'status' => 2,
                        'type' => 1,
                        'add_time' => $now
                    ])->exec();
                    $auid = $execResult->lastInsertId();

                    $db->insertInto('t_user_mobile_authentication')->values([
                        'auid'=>$auid,
                        'mobile'=>$userEntity->getMobile(),
                        'add_time' =>$now
                    ])->exec();

                    $db->insertInto('t_user_account')->values([
                        'uid'=>$lastId,
                        'add_time' =>$now
                    ])->exec();

                    $db->insertInto('t_user_info')->values([
                        'uid'=>$lastId,
                        'mobile'=>$userEntity->getMobile(),
                        'sms_verify_status'=>2,
                        'invite_code'=> sprintf('%x',crc32(microtime())),
                        'add_time' =>$now
                    ])->exec();

                    //邀请码判断
                    $inviteCode = $userEntity->getInviteCode();
                    if($inviteCode){
                        $userInvite = $this->db->select('*')
                            ->from('t_user_info')->where(['invite_code'=>$inviteCode])->getFirst();
                        if($userInvite['uid']){
                            $db->insertInto('t_user_invite_relate')->values([
                                'uid'=>$lastId,
                                'invite_uid'=>$userInvite['uid'],
                                'login_type'=>1,
                                'add_time' =>$now
                            ])->exec();
                        }
                    }

                    $this->db->update('t_verify_order')->set([
                        'status' => 2,
                        'use_time'=>$now
                    ])->where(['order_number' => $userEntity->getOrderNumber()])->exec();

                    return $this->checkMobileExist($userEntity->getMobile());
                });


            });






        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 检测手机号是否已经存在
     * @param string $mobile
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function checkMobile($mobile)
    {
        return $this->checkMobileExist($mobile);
    }
    /**
     * 前端用户登录
     * @param string $mobile
     * @param string $password
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function login($mobile, $password)
    {
        $this->factory->getIntance();
        $account = $this->checkMobileExist($mobile);
        if(count($account) <= 0 ){
            throw $this->exception([
                'code'=>ErrorConst::LOGIN_ERROR,
                'text'=>"手机号".$mobile."不存在"
            ]);
        }
        $now = getCurrentTime();
        $today = date("Y-m-d", $now);
        //登录失败
        if(md5string($password) != $account['password']) {
            $accountStatus = $this->getAccountStatus($account['id']);
            if (count($accountStatus) <= 0) {
                $this->db->insertInto('t_user_account_status')->values([
                    'uid' => $account['id'],
                    'error_time' => 1,
                    'operation_time' => $today,
                    'add_time' => $now
                ])->exec();
            } else {
                if ($accountStatus['operation_time'] != $today) {
                    //重置
                    $this->db->update('t_user_account_status')->set([
                        'status' => 0,
                        'lock_time' => 0,
                        'error_time' => 1,
                        'operation_time' => $today
                    ])->where(['id' => $accountStatus['id']])->exec();
                }else {
                    //状态
                    if ($accountStatus['status']) {
                        //已锁定
                        if($accountStatus['lock_time']<$now){
                            //重置
                            $this->db->update('t_user_account_status')->set([
                                'status' => 0,
                                'lock_time' => 0,
                                'error_time' => 1,
                                'operation_time' => $today
                            ])->where(['id' => $accountStatus['id']])->exec();
                        }else{
                            throw $this->exception([
                                'code'=>ErrorConst::ACCOUNT_LOCKED,
                                'text'=>"账号".json_encode($account)."锁定中"
                            ]);
                        }
                    } else {
                        //未锁定
                        if($accountStatus['error_time'] + 1 >= $this->container->get("loginerrornum")){
                            $this->db->update('t_user_account_status')->set([
                                'status' => 1,
                                'lock_time' => $now + $this->container->get("accountlocktime"),
                                'error_time' => 10,
                                'operation_time' => $today
                            ])->where(['id' => $accountStatus['id']])->exec();
                            throw $this->exception([
                                'code'=>ErrorConst::ACCOUNT_LOCKED,
                                'text'=>"账号".json_encode($account)."锁定中"
                            ]);
                        }else{
                            $this->db->update('t_user_account_status')->set([
                                'error_time' => $accountStatus['error_time'] +1,
                                'operation_time' => $today
                            ])->where(['id' => $accountStatus['id']])->exec();
                        }
                    }
                }
            }
            throw $this->exception([
                'code'=>ErrorConst::LOGIN_ERROR,
                'text'=>"用户名或密码错误"
            ]);
        }
        //登录成功，重置错误次数
        $this->db->update('t_user_account_status')->set([
            'status' => 0,
            'lock_time' => 0,
            'error_time' => 0,
            'operation_time' => ''
        ])->where(['uid' => $account['id']])->exec();
        return $account;
    }
    /**
     * 查询手机号是否已存在
     * @param string $mobile
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    private function checkMobileExist($mobile)
    {
        try {
            $rs = $this->db->select("t_user.*", DB::raw("t_user_login.username as mobile,t_user_info.invite_code"), "t_user_login.password",'t_weixin_user.nickname','t_weixin_user.headimgurl')
                ->from("t_user_login")
                ->leftJoin("t_user")->on("t_user_login.uid=t_user.id")
                ->leftJoin("t_user_info")->on("t_user_login.uid=t_user_info.uid")
                ->leftJoin('t_weixin_user_relate')->on('t_user.id=t_weixin_user_relate.uid')
                ->leftJoin('t_weixin_user')->on('t_weixin_user.id=t_weixin_user_relate.wx_id')
                ->where('username = ?',$mobile)->getFirst();
            return $rs;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 获得账户错误信息
     * @param int $uid
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    private function getAccountStatus($uid)
    {
        try {
            $rs = $this->db->select("*")
                ->from("t_user_account_status")
                ->where('uid = ?',$uid)->getFirst();
            return $rs;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 修改登录密码
     * @param string $username
     * @param string $oldpasswd
     * @param string $passwd
     * @param string $passwd2
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function editPassword($username, $oldpasswd, $passwd, $passwd2)
    {
        try {
            //调用登录接口
            $login = $this->login($username, $oldpasswd);
            if($login['password'] == md5string($passwd)){
                throw $this->exception([
                    'code'=>ErrorConst::NEWPASS_MATCH_OLDPASS,
                    'text'=>"新密码不能同原密码相同"
                ]);
            }
            if( $passwd != $passwd2){
                throw $this->exception([
                    'code'=>ErrorConst::INPUT_PASS_NOT_MATCH,
                    'text'=>"密码输入不一致"
                ]);
            }
            $execResult = $this->db->update('t_user_login')->set([
                'password' => md5string($passwd),
            ])->where(['uid' => $login['id']])->exec();

            if($execResult->rows !=1){
                throw $this->exception([
                    'code'=>ErrorConst::CHANGE_PASS_FAIL,
                    'text'=>"用户id".$login['id']."密码更新失败"
                ]);
            }
            return true;

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 重置登录密码
     * @param string $mobile
     * @param string $newpasswd
     * @param string $orderNumber
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function resetPassword($mobile, $newpasswd, $orderNumber)
    {
        try {
            $now = getCurrentTime();
            $checkNumber = $data = $this->db->select('*')
                ->from('t_verify_order')
                ->where([
                        'order_number' =>$orderNumber,
                        'verify_type'=>1,
                        'status'=>1,
                        'useful_time'=> ['>='=>$now]
                    ]
                )->getFirst();
            if(count($checkNumber)<=0){
                throw $this->exception([
                    'code'=>ErrorConst::SMS_VERIFY_EXPIRED,
                    'text'=>"验证码".$orderNumber."已失效"
                ]);
            }
            $checkMobile = json_decode($checkNumber['verify_text'],true);
            if($checkMobile['mobile'] != $mobile){
                throw $this->exception([
                    'code'=>ErrorConst::SMS_VERIFY_EXPIRED,
                    'text'=>"验证码".$orderNumber."已失效"
                ]);
            }

            $userInfo = $this->checkMobileExist($mobile);
            if($userInfo){

                 if(md5string($newpasswd) == $userInfo['password']){
                     throw $this->exception([
                         'code'=>ErrorConst::NEWPASS_MATCH_OLDPASS,
                         'text'=>"手机号".$mobile."新旧密码相同"
                     ]);
                 }
            }else{
                throw $this->exception([
                    'code'=>ErrorConst::RESETPASS_CHECK_MOBILE_NOT_EXIST,
                    'text'=>"手机号".$mobile."不存在"
                ]);
            }


            $execResult = $this->db->update('t_user_login')->set([
                'password' => md5string($newpasswd),
            ])->where(['username' => $mobile])->exec();

            if($execResult->rows !=1){
                throw $this->exception([
                    'code'=>ErrorConst::RESETPASS_ERROR,
                    'text'=>"手机号".$mobile."密码重置失败"
                ]);
            }
            $this->db->update('t_verify_order')->set([
                'status' => 2,
                'use_time'=>$now
            ])->where(['order_number' => $orderNumber])->exec();

            return true;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }


    /**
     * 查询银行卡号是否已存在
     * @param string $banknumber
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    private function checkBankNumbereExist($banknumber)
    {
        try {
            $rs = $this->db->select("*")
                ->from("t_user_bank")
                ->where('banknumber = ?',$banknumber)->getFirst();
            return $rs;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 查询银行卡信息
     * @param string $where
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    private function getBankInfo($where = [])
    {
        try {
            $rs = $this->db->select("*")
                ->from("t_user_bank");
            if($where){
                $rs->where($where);
            }
            $data = $rs->getFirst();
            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }
    /**
     * 绑定银行卡
     * @param BankBindEntity $bankBindEntity
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function bindBankCard(BankBindEntity $bankBindEntity)
    {
        try{
            //查询mobile
//            $checkExist = $this->checkBankNumbereExist($bankBindEntity->getBanknumber());
            $checkExist = $this->getBankInfo(['banknumber'=>$bankBindEntity->getBanknumber(), 'status'=>1]);

            if(count($checkExist) > 0 && $checkExist['status'] == 1 ){
                throw $this->exception([
                    'code'=>ErrorConst::BANK_NUMBER_EXIST,
                    'text'=>"银行卡号".$bankBindEntity->getBanknumber()."已存在"
                ]);
            }
            $now = getCurrentTime();


            return $this->db->transaction(function(DB $db) use ($bankBindEntity,$now){
                $lastId = $db->insertInto('t_user_bank')->values([
                    //'mobile'=>$userEntity->getMobile(),
                    //'password'=>md5string($userEntity->getPassword()),
                    'realname'=> $bankBindEntity->getRealname(),
                    'banknumber'=> $bankBindEntity->getBanknumber(),
                    'idnumber'=> $bankBindEntity->getIdnumber(),
                    'mobile'=> $bankBindEntity->getMobile(),
                    'uid'=> $bankBindEntity->getUid(),
                    'realname'=> $bankBindEntity->getRealname(),
                    'status'=> 1,
                    'add_time' => $now
                ])->exec()->lastInsertId();

                if($lastId<=0){
                    throw $this->exception([
                        'code'=>ErrorConst::BANK_USER_INSERT_ERROR,
                        'text'=>"添加银行卡失败".json_encode($bankBindEntity->toArray())
                    ]);
                }

                return $checkExist = $this->getBankInfo(['id'=>$lastId]);
            });
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 解绑银行卡
     * @param int $bid
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function unbindBankCard($bid)
    {
        try{
            return $this->mutex->getMutex('unbindBank'.$bid)->synchronized(function() use($bid){
                //查询记录是否存在
                $bankInfo = $this->getBankInfo(['id'=>$bid,'status'=>1]);
                if(is_null($bankInfo)){
                    throw $this->exception([
                        'code'=>ErrorConst::BANK_BINDED_NOTEXIST,
                        'text'=>"没有绑定的银行卡"
                    ]);
                }
                $this->db->update('t_user_bank')->set([
                    'status' => 2
                ])->where(['id'=>$bid])->exec();


                return true;

            });

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 查询用户账户account信息
     * @param string $where
     * @param string $field_string
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    private function getUserAccount($where = [], $field_string = '')
    {
        try {
            if(strlen($field_string)){
                $rs = $this->db->select(DB::raw($field_string));
            }else{
                $rs = $this->db->select("*");
            }
            $rs = $rs->from("t_user_account");
            if($where){
                $rs->where($where);
            }
            $data = $rs->getFirst();
            return $data;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }


    /**
     * 查询用户资金流水信息
     * @param string $where
     * @param $pageszie
     * @param $page
     * @param array $where
     * @param string $field_string
     * @param array $order
     * @param array $groupBy
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    private function getUserCommissionRecords($page, $pageszie, $where = [], $field_string = '', $order = [], $groupBy = [])
    {
        try {
            if(strlen($field_string)){
                $rs = $this->db->select(DB::raw($field_string));
            }else{
                $rs = $this->db->select("*");
            }
            $rs = $rs->from("t_user_commission");
            if($where){
                $rs->where($where);
            }
            if ($groupBy) {
                foreach ($groupBy as $key => $value) {
                    $rs->groupBy($value);
                }
            }
            if ($order) {
                foreach ($order as $key => $value) {
                    if ($value == DB::ORDER_BY_DESC || $value == DB::ORDER_BY_ASC) {
                        $rs->orderBy($key, $value);
                    }
                }
            } else {
                $rs->orderBy('add_time', DB::ORDER_BY_DESC);
            }
            $pagination = new Pagination($rs, $page, $pageszie,$this->db);
            return $pagination->get();
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 用户资金流水分页信息
     * @param int $page
     * @param int $pagesize
     * @param int $seetype
     * @param int $type
     * @param int $uid
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getUserCommissionRecordPage($page = 1, $pagesize = 10, $uid, $type = -1, $seetype = 1)
    {
        try {
            if($type>0){
                $where = ['type'=>$type];
            }
            $where = ['uid'=>$uid];
            $now = getCurrentTime();
            $today = date('d', $now);//18
            $settlement_day = $this->container->get("settlementday");
//            $settlement_day = 31;
//          seetype
//          1上个月未结算   上个月未结算+当天到月初 ，如果当天过了结算日期则 上个月未结算为空
//          2当月未结算 当月的下个月结算，有一天是一天
//          3历史记录   之前按月份统计   上个月 根据当天是否过了结算日而考虑

            $sql = "FROM_UNIXTIME(add_time,'%Y-%m-%d') as day,sum(account) as money";
            $Groupvalue = DB::raw("FROM_UNIXTIME(add_time,'%Y-%m-%d')");
            if($seetype == 1){
                if($today >= $settlement_day) {
                    return ['page'=>0,'count'=>0,'data'=>[]];
                }else{
                    $begin_time = strtotime(date('Y-m-01',strtotime('-1 month',$now)));
                    $where['add_time'] = ['>='=>$begin_time];
                    $where['status'] = 1;
                }
            }elseif ($seetype == 2){
                $begin_time = strtotime(date('Y-m-01 00:00:00',$now));
                $where['status'] = 1;
                $where['add_time'] = ['>='=>$begin_time];
            }else{
                //按月算
                $sql = "FROM_UNIXTIME(add_time,'%Y-%m') as day,sum(account) as money";
                $Groupvalue = DB::raw("FROM_UNIXTIME(add_time,'%Y-%m')");
//                if($today >= $settlement_day) {
                    //$end_time = strtotime(date('Y-m-01 00:00:00',$now));
                    //$where['add_time'] = ['<='=>$end_time];
//                }else{
//                    $end_time = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month',$now)));
//
//                    $where['add_time'] = ['<='=>$end_time];
//                }
                $where['status'] = 2;
            }

            $rs = $this->db->select(DB::raw($sql));

            $rs = $rs->from("t_user_commission");

            $rs->where($where);

            $rs->groupBy($Groupvalue);

            $rs->orderBy('add_time', 'DESC');
            $pagination = new Pagination($rs, $page, $pagesize, $this->db);
            $rs = $pagination->get();
            return $rs;
            
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }


    }

    /**
     * 获得用户资金流水信息
     * @param int $page
     * @param int $pagesize
     * @param int $day
     * @param int $type
     * @param int $uid
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getUserCommissionRecord($uid, $page = 1, $pagesize = 10, $type = -1, $day = -1)
    {
        try{
            $now = getCurrentTime();
            $order = [];
            $daysBefore = strtotime(date('Y-m-d',strtotime('-'.$day.' day',$now)));
            $where = [
                'uid' => $uid
            ];
            $groupby = '';
            if($day >0){
                $where['add_time'] = ['>='=>$daysBefore];
                $order['add_time'] = DB::ORDER_BY_ASC;
                $groupby = [DB::raw("FROM_UNIXTIME(add_time,'%Y-%m-%d')")];
            }
            if($type >0){
                $where['type'] = $type;
            }
            $UserCommission_record = $this->getUserCommissionRecords($page, $pagesize, $where,'',$order,$groupby);
            if(is_null($UserCommission_record)){
                throw $this->exception([
                    'code'=>ErrorConst::USER_COMMISSION_RECORD_NOT_FOUND,
                    'text'=>"用户资金流水未找到"
                ]);
            }
            return $UserCommission_record;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 用户账户信息页面
     * @param int $uid
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function userAccountInfo($uid)
    {
        try{
            //查询记录是否存在
            $user_account = $this->getUserAccount(['uid'=>$uid]);
            if(is_null($user_account)){
                throw $this->exception([
                    'code'=>ErrorConst::USER_ACCOUNT_NOT_FOUND,
                    'text'=>"用户账户未找到"
                ]);
            }

            $now = getCurrentTime();
            $today = strtotime(date('Y-m-d',$now));

            //今日订单数量
            $user_account['today_nums'] = 0;
            $where = [
                'uid' => $uid,
                'type' => 2,
                'add_time' => ['>='=>$today]
            ];
            $rs = $this->db->select("*")
                ->from("t_user_commission")
                ->where($where)->get();
            if($rs){
                $user_account['today_nums'] = count($rs);
            }

            //历史总累计产生佣金   是否算上 结算和未结算 查看status确认
            $user_account['all_commission_profit'] = 0;

            $rs = $this->db->select(DB::raw('ifnull(sum(account),0) as sums'));
            $rs = $rs->from("t_user_commission_record");
            $rs->where(['uid'=>$uid,'type'=>2]);
            $rs = $rs->groupBy('uid');
            $data =  $rs->getFirst();
            if($data){
                $user_account['all_commission_profit'] = $data['sums'];
            }

            //上月未结算佣金
            $user_account['last_month_total'] = 0;
            $settlement_day = $this->container->get("settlementday");
            $now = getCurrentTime();
            $today = date('d', $now);
            if($today < $settlement_day){

            }else{
                $rs = $this->db->select(DB::raw('ifnull(sum(account),0) as sums'));
                $rs = $rs->from("t_user_commission");
                $end = strtotime(date('Y-m-1 00:00:00',$now));
                $begin = strtotime('-1 month',$end);
                $rs->where(['status'=>2,'uid'=>$uid,'add_time'=>['BETWEEN'=>[$begin,$end]]]);
                $rs = $rs->groupBy('uid');
                $data =  $rs->getFirst();
                if($data){
                    $user_account['last_month_total'] = $data['sums'];
                }
            }

            //累计获得总佣金
            $rs = $this->db->select(DB::raw('ifnull(sum(account),0) as sums'))
                    ->from("t_user_commission")->where(['type'=>2,'uid'=>$uid])->getFirst();
            $user_account['sum_commission_account'] = $rs['sums'];
            //今日佣金
            $rd = $this->db->select(DB::raw('ifnull(sum(account),0) as sums'))
                ->from("t_user_commission")->where(['type'=>2,'uid'=>$uid,'add_time'=>['>='=>strtotime(date('Y-m-d',$now))]])->getFirst();

            $user_account['day_commission_account'] = $rd['sums'];
            return $user_account;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }
    /**
     * 获得绑定的银行卡
     * @param int $uid
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getBindBankCard($uid)
    {
        try{
            $bankInfo = $this->getBankInfo(['uid'=>$uid, 'status'=>1]);
            if(is_null($bankInfo)){
                throw $this->exception([
                    'code'=>ErrorConst::BANK_BINDED_NOTEXIST,
                    'text'=>"没有绑定的银行卡"
                ]);
            }
            return $bankInfo;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 提现申请
     * @param int $uid
     * @param int $bankId
     * @param float $account
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function withdrawalApplication($uid, $bankId, $account, $orderNumber)
    {

        try {
            return $this->mutex->getMutex('withdrawalApplication'.$uid)->synchronized(function() use($uid, $bankId, $account, $orderNumber){
                //查询uid是否存在
                $user = $this->db->select("*")
                    ->from("t_user_login")
                    ->where('uid = ?', $uid)->getFirst();
                if(!$user){
                    throw $this->exception([
                        'code'=>ErrorConst::USER_EXIST,
                        'text'=>"提现用户不存在".json_encode([$uid, $bankId, $account])
                    ]);
                }

                //验证短信
                $now = getCurrentTime();
                $checkNumber = $data = $this->db->select('*')
                    ->from('t_verify_order')
                    ->where([
                            'order_number' =>$orderNumber,
                            'verify_type'=>1,
                            'status'=>1,
                            'useful_time'=> ['>='=>$now]
                        ]
                    )->getFirst();
                if(count($checkNumber)<=0){
                    throw $this->exception([
                        'code'=>ErrorConst::SMS_VERIFY_EXPIRED,
                        'text'=>"验证码".$orderNumber."已失效"
                    ]);
                }
                $checkMobile = json_decode($checkNumber['verify_text'],true);
                if($checkMobile['mobile'] != $user['username']){
                    throw $this->exception([
                        'code'=>ErrorConst::SMS_VERIFY_EXPIRED,
                        'text'=>"验证码".$orderNumber."已失效"
                    ]);
                }
                //查询银行卡
                $bankData = $this->db->select("*")
                    ->from("t_user_bank")
                    ->where('id = ?',$bankId)->getFirst();

                //查询账户余额
                $userAccount = $this->db->select("*")
                    ->from("t_user_account")
                    ->where(['uid'=>$uid,'available_amount'=>['>='=>$account]])->getFirst();
                if(!$userAccount){
                    throw $this->exception([
                        'code'=>ErrorConst::LACKOFBALANCE_ERROR,
                        'text'=>"用户提现余额不足".json_encode([$uid, $bankId, $account])
                    ]);
                }
                return $this->db->transaction(function(DB $db) use($uid, $bankId, $account, $orderNumber, $bankData){
                    //更新账户余额 余额减 冻结加
                    $execResult = $db->update('t_user_account')->set(
                        [
                            'available_amount' => DB::raw('available_amount - ' . $account),
                            'freezing_amount' => DB::raw('freezing_amount + ' . $account),
                        ]
                    )->where(['uid' => $uid, 'available_amount' => ['>=' => $account]])->exec();
                    if($execResult->rows != 1){
                        throw $this->exception([
                            'code'=>ErrorConst::LACKOFBALANCE_ERROR,
                            'text'=>"用户提现余额不足".json_encode([$uid, $bankId, $account])
                        ]);
                    }

                    //插订单t_order
                    $orderId = $db->insertInto('t_order')->values(
                        [
                            'uid' => $uid,
                            'order_number' => $this->getOrderNumber(),
                            'status' => '1',
                            'type' => '1',
                            'account' => $account,
                            'add_time' => getCurrentTime()
                        ]
                    )->exec()->lastInsertId();

                    $userAccount = $this->db->select("*")
                        ->from("t_user_account")
                        ->where('uid = ?', $uid)->getFirst();

                    //插t_order_funds
                    $db->insertInto('t_order_funds')->values(
                        [
                            'order_id' => $orderId,
                            'contents' => json_encode($userAccount),
                            'add_time' => getCurrentTime()
                        ]
                    )->exec();

                    $db->insertInto('t_user_commission_record')->values(
                        [
                            'uid'       =>$uid,
                            'order_id' => $orderId,
                            'type' => '1',
                            'account' => $account,
                            'available_amount' => $userAccount['available_amount'],
                            'add_time' => getCurrentTime()
                        ]
                    )->exec();

                    $db->insertInto('t_user_withdrawal_application')->values(
                        [
                            'uid'       =>$uid,
                            'account'   => $account,
                            'status'    => '1',
                            'banknumber' => $bankData['banknumber'],
                            'realname'  => $bankData['realname'],
                            'mobile'    => $bankData['mobile'],
                            'idnumber'  => $bankData['idnumber'],
                            'add_time'  => getCurrentTime(),

                        ]
                    )->exec();

                    $this->db->update('t_verify_order')->set([
                        'status' => 2,
                        'use_time'=>getCurrentTime(),
                    ])->where(['order_number' => $orderNumber])->exec();

                    return true;
                });





                return true;
            });

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
                   ]);
        }
    }

    /**
     * 资金流水
     * @param int $uid
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function accountFlowingWater($page, $pagesize, $uid)
    {
        try {
            $joinRule = $this->db->select("*")
                ->from("t_user_commission_record")
                ->where([
                    'uid'=>$uid,
                ])->orderBy('t_user_commission_record.id', DB::ORDER_BY_DESC);
            $pagination = new Pagination($joinRule, $page, $pagesize);
            return $pagination->get();
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 推广数据详情
     * @param int $uid
     * @param int $articleId
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function accountSpreadDataDetail($page, $pagesize, $uid, $spread_id)
    {
        try {
            $joinRule = $this->db->select(DB::raw("FROM_UNIXTIME(t_product_order.add_time,'%Y-%m-%d') as add_time,sum(t_product_order.number) as number,sum(t_user_commission.account) as account,t_article.name,t_article_product_relate.percent"))
                ->from("t_article")
                ->leftJoin('t_product_order')->on('t_product_order.article_id=t_article.id')
                ->leftJoin('t_user_commission')->on('t_user_commission.product_order_id=t_product_order.id')
                ->leftJoin('t_article_product_relate')->on('t_article_product_relate.article_id=t_article.id')
                ->where([
                    't_product_order.counting_id'  =>  $uid,
                    't_product_order.spread_id'    =>  $spread_id,
                    't_product_order.number'=> ['>'=>0],
                    't_user_commission.type' =>2,
                ]);
                $sumRule = $joinRule;
                $sum = ($sumRule->getFirst());
                $joinRule->groupBy(DB::raw("FROM_UNIXTIME(t_product_order.add_time,'%Y-%m-%d')"))
                ->orderBy('t_product_order.add_time', DB::ORDER_BY_DESC);


            $pagination = new Pagination($joinRule, $page, $pagesize, $this->db);
            $data =  $pagination->get($this->db);
            $data['sum_count'] = $sum['number']?$sum['number']:'0';
            $data['sum_account'] = $sum['account']?$sum['account']:'0';
            $data['name'] = $sum['name'];

            if(count($data['data'])){
                $percent = json_decode($sum['percent'],true) ;
                foreach ($percent as $item) {
                    if($item['mode'] == 2){
                        //目前渠道，网站只有一个百分比分成，故不做获得
                        $data['channelpercent'] = $item['contents']['percent'];
                        break;
                    }
                }
            }else{
                $data['channelpercent'] = 0;
            }
           //$data['channelpercent'] = $percent[0]['contents']['percent'];
            return $data;

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 推广数据列表
     * @param int $uid
     * @param int $articleId
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function accountSpreadData($page, $pagesize, $uid)
    {
        try {
            $joinRule = $this->db->select(DB::raw("t_spread_list.id,t_spread_list.number,t_spread_list.commission_account,t_article.name,t_article_product_relate.article_id,t_article_product_relate.percent"))
                ->from("t_spread_list")
                ->leftJoin('t_article_product_relate')->on('t_article_product_relate.id=t_spread_list.article_product_id')
                ->leftJoin('t_article')->on('t_article_product_relate.article_id=t_article.id')
                ->where([
                    't_spread_list.channel_user_id'=>  $uid,
                ]);
            $joinRule->orderBy('t_article_product_relate.add_time', DB::ORDER_BY_DESC);
            $pagination = new Pagination($joinRule, $page, $pagesize, $this->db);
            $data =  $pagination->get($this->db);
            foreach ($data['data'] as $key => $value){
                $percent = json_decode($value['percent'],true);

                foreach ($percent as $item) {
                    if($item['mode'] == 2){
                        //目前渠道，网站只有一个百分比分成，故不做获得
                        $data['data'][$key]['channelpercent'] = $item['contents']['percent'];
                        break;
                    }
                }
            }
            return $data;

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 添加收货地址
     * @param int $uid
     * @param int mobile
     * @param string realname
     * @param string address
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function addAddress($uid, $mobile, $realname, $address)
    {
        try {

            $this->db->update('t_user_address')->set([
                'status' => 2
            ])->where(['uid' => $uid ])->exec();

            $lastInsertId = $this->db->insertInto('t_user_address')->values(
                [
                    'uid' => $uid,
                    'status'=> 1,
                    'mobile' => $mobile,
                    'realname' => $realname,
                    'address' => $address,
                    'add_time' => getCurrentTime(),

                ]
            )->exec()->lastInsertId();
            return $lastInsertId;

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 查看收货地址
     * @param int $uid
     * @param int $status
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function getUserAddress($uid,$status)
    {
        try {
            $arr = $this->db->select('*')->from('t_user_address')
                ->where(['uid' => $uid,'status'=>$status])
                ->orderBy('t_user_address.status', DB::ORDER_BY_ASC)
                ->get();
            return $arr;

        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }

    /**
     * 生成推广二维码
     * @param $spreadUid
     * @param $aprs
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function createSpreadQRcode($spreadUid, $aprs)
    {
        $data = $this->db->select('t_spread_list.*,t_article_product_relate.article_id')
            ->from('t_spread_list')
            ->leftJoin('t_article_product_relate')->on('t_article_product_relate.id=t_spread_list.article_product_id')
            ->where(['t_spread_list.article_product_id' => $aprs,'t_spread_list.channel_user_id'=>$spreadUid])
            ->getFirst();
        if($data){
            return $data;
        }else{
            $lastId = $this->db->insertInto('t_spread_list')->values([
                'article_product_id' => $aprs,
                'channel_user_id' =>$spreadUid,
                'order_no' =>$this->getOrderNumber(),
                'add_time' => getCurrentTime()
            ])->exec()->lastInsertId();

            $data = $this->db->select('t_spread_list.*,t_article_product_relate.article_id')
                ->from('t_spread_list')
                ->leftJoin('t_article_product_relate')->on('t_article_product_relate.id=t_spread_list.article_product_id')
                ->where(['t_spread_list.id'=>$lastId])->getFirst();
            if($data){
                return $data;
            }
        }
        return [];
    }

    /**
     * 根据推广id寻找信息
     * @param $spreadid
     * @param $aprs
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getInfoBySpreadId($spreadid)
    {
        $data = $this->db->select('t_spread_list.*',DB::raw('t_article_product_relate.article_id'))
            ->from('t_spread_list')
            ->innerJoin('t_article_product_relate')->on('t_article_product_relate.id=t_spread_list.article_product_id')
            ->where(['t_spread_list.id'=>$spreadid])
            ->getFirst();
        if($data){
            return $data;
        }
        return [];
    }

    /**
     * 根据用户id获得 被邀请人信息
     * @param $uid
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getInviteFriendByUId($uid, $page, $pagesize)
    {
        $joinRule = $this->db->select('t_user_invite_relate.*',DB::raw('t_user_login.username'))
            ->from('t_user_invite_relate')
            ->leftJoin('t_user_login')->on('t_user_login.uid=t_user_invite_relate.uid')
            ->where(['invite_uid'=>$uid])
            ->orderBy('add_time',DB::ORDER_BY_DESC)
            ;
        $pagination = new Pagination($joinRule, $page, $pagesize, $this->db);
        $data =  $pagination->get($this->db);

        if($data['count'] > 0){
            foreach ($data['data'] as $key=>$value){
                $data['data'][$key]['giveMoney'] = 0;
                $select = "a.uid ,ifnull(sum(a.account),0) as total,b.uid as getinvted";
                $res = $this->db->select(DB::raw($select))
                    ->from(DB::raw('t_user_commission a'))
                    ->leftJoin(DB::raw('t_user_commission b'))
                    ->on('a.product_order_id=b.product_order_id and b.type=2')
                    ->where(['a.type'=>4,'a.uid'=>$uid,'b.uid'=>$value['uid']])
                    ->groupBy('b.uid')
                   ->getFirst();

                if(!is_null($res)){
                    $data['data'][$key]['giveMoney'] = $res['total'];
                }
            }
            $data['sumTotal'] = 0;
            //累计一共获得的
            $select = "ifnull(sum(a.account),0) as total";
            $total = $this->db->select(DB::raw($select))
                ->from(DB::raw('t_user_commission a'))
                ->leftJoin(DB::raw('t_user_commission b'))
                ->on('a.product_order_id=b.product_order_id and b.type=2')
                ->where(['a.uid'=>$uid,'a.type'=>4])
                ->getFirst();

            if(!is_null($total)){
                $data['sumTotal'] = $total['total'];
            }
        }


        return $data;

    }

    /**
     * 佣金明细页面
     * @param $uid
     * @param $today
     * @param $startDay
     * @param $endDay
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function settlementDetail($uid, $today = -1, $startDay = '', $endDay = '', $page, $pagesize)
    {

        //目前就只有渠道用户所以目前user_commision.type=2  1写手，3网站

        $userCommision = "t_user_commission.id,t_user_commission.order_id,t_user_commission.product_order_id, t_user_commission.uid,
                        t_user_commission.type,t_user_commission.product_account,t_user_commission.percent as channelpercent,t_user_commission.account,
                        t_user_commission.status,FROM_UNIXTIME(t_user_commission.add_time,'%Y-%m-%d %H:%i:%s') as day ";

        $productOrder = "t_product_order.product_id,t_product_order.number,t_product_order.article_id,t_product_order.spread_id,t_product_order.counting_id ";

        $article = "t_article.name";
        $joinRule = $this->db->select(DB::raw($userCommision),DB::raw($productOrder),DB::raw($article))
            ->from('t_user_commission')
            ->innerJoin('t_product_order')->on('t_product_order.id=t_user_commission.product_order_id')
            ->innerJoin('t_article')->on('t_article.id=t_product_order.article_id');

        ;
        $where = [
            't_user_commission.type'=>2,
            't_user_commission.uid'=>$uid
        ];

        if($today > 0){
            $now = getCurrentTime();
            $todayStart = strtotime(date('Y-m-d 00:00:00',$now));


            //测试数据
//            $todayStart = 1516982400;
            $where['t_user_commission.add_time'] = ['>='=>$todayStart];
        }else{
            //如果选择当天则 时间区间不起效果
            if(strlen($startDay) > 0 && strlen($endDay) > 0){
                $start = strtotime($startDay);
                $end = strtotime($endDay);
                $where['t_user_commission.add_time'] = ['BETWEEN'=>[$start,$end]];
            }else{
                if(strlen($startDay) > 0){
                    $start = strtotime($startDay);
                    $where['t_user_commission.add_time'] = ['>='=>$start];
                }
                if(strlen($endDay) > 0){
                    $end = strtotime($endDay);
                    $where['t_user_commission.add_time'] = ['<='=>$end];
                }
            }
        }

        $joinRule->where($where)->orderBy('t_user_commission.add_time',DB::ORDER_BY_DESC);

        $pagination = new Pagination($joinRule, $page, $pagesize, $this->db);
        $data =  $pagination->get($this->db);
        return $data;

    }

    private function getOrderNumber() {
        return "ORD".time().rand(10000, 99999);
    }
}