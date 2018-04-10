<?php

namespace App\Users\Services;

use function App\getCurrentTime;
use function App\getIP;
use function App\md5string;
use App\Users\Entities\UserEntity;
use App\Utils\ErrorConst;
use App\Utils\Mutex;
use App\Utils\Pagination;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Psr\Log\LoggerInterface;


class Advert
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
     * 注册用户
     * @param string $username
     * @param string $realname
     *
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function addUser($username, $realname)
    {


        try{
            //查询mobile
            $checkExist = $this->checkMobileExist($username);
            if(count($checkExist) >0 ){
                throw $this->exception([
                    'code'=>ErrorConst::MOBILE_EXIST,
                    'text'=>"手机号".$username."已存在"
                ]);
            }


            return $this->mutex->getMutex('addUserAdvert'.$username)->synchronized(function() use($username, $realname){
                return $this->db->transaction(function(DB $db) use ($username, $realname){
                    $lastId = $db->insertInto('t_user')->values([
                        //'mobile'=>$userEntity->getMobile(),
                        //'password'=>md5string($userEntity->getPassword()),
                        'register_time'=>getCurrentTime(),
                        'register_ip'=>getIP(),
                        'status'=>1,
                        'add_time' =>getCurrentTime()
                    ])->exec()->lastInsertId();

                    if($lastId<=0){
                        throw $this->exception([
                            'code'=>ErrorConst::USER_CREATE_ERROR,
                            'text'=>"用户创建失败".json_encode([$username,$realname])
                        ]);
                    }

                    $db->insertInto('t_advert_user_login')->values([
                        'uid'=>$lastId,
                        'username'=>$username,
                        'password'=>md5string($username),
                        'add_time' =>getCurrentTime()
                    ])->exec();

                    $execResult = $db->insertInto('t_user_authentication')->values([
                        'uid' => $lastId,
                        'status' => 2,
                        'type' => 1,
                        'add_time' => getCurrentTime()
                    ])->exec();
                    $auid = $execResult->lastInsertId();

                    $db->insertInto('t_user_mobile_authentication')->values([
                        'auid'=>$auid,
                        'mobile'=>$username,
                        'add_time' =>getCurrentTime()
                    ])->exec();

                    $db->insertInto('t_user_account')->values([
                        'uid'=>$lastId,
                        'add_time' =>getCurrentTime()
                    ])->exec();

                    $db->insertInto('t_user_info')->values([
                        'uid'=>$lastId,
                        'mobile'=>$username,
                        'realname'=>$realname,
                        'sms_verify_status'=>2,
                        'invite_code'=> sprintf('%x',crc32(microtime())),
                        'add_time' =>getCurrentTime()
                    ])->exec();



                    return $this->checkMobileExist($username);
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
     * 广告主代理列表
     *
     * @return array
     */
    public function advertList()
    {
        $advertList = $this->db->select("t_advert_user_login.*", "t_user_info.realname")
            ->from("t_advert_user_login")
            ->leftJoin("t_user_info")->on("t_advert_user_login.uid=t_user_info.uid")->get();
        return $advertList;
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
            $execResult = $this->db->update('t_advert_user_login')->set([
                'password' => md5string($passwd),
            ])->where(['uid' => $login['id']])->exec();

            if($execResult->rows !=1){
                throw $this->exception([
                    'code'=>ErrorConst::CHANGE_PASS_FAIL,
                    'text'=>"广告主用户id".$login['id']."密码更新失败"
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
     * 前端用户登录
     * @param string $mobile
     * @param string $password
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function login($mobile, $password)
    {
        $account = $this->checkMobileExist($mobile);
        if(count($account) <= 0 ){
            throw $this->exception([
                'code'=>ErrorConst::LOGIN_ERROR,
                'text'=>"手机号".$mobile."不存在"
            ]);
        }
        //登录失败
        if(md5string($password) != $account['password']) {

            throw $this->exception([
                'code' => ErrorConst::LOGIN_ERROR,
                'text' => "用户名或密码错误"
            ]);
        }

        return $account;
    }

    /**
     * 本月成交
     * @param string $advert_id
     * @return array
     */
    public function getMonthTrading($advert_id, $page, $pagesize)
    {
        $tradingList = $this->db->select(DB::raw("FROM_UNIXTIME(t_product_order.add_time,'%Y-%m-%d') as add_time,sum(t_product_order.number) as number,sum(t_order.account) as account,ifnull(sum(a.account),0) as commission"))
            ->from('t_advert_product_relate')
            ->leftJoin('t_product_order')->on("t_advert_product_relate.product_id=t_product_order.product_id")
            ->leftJoin('t_order')->on("t_order.id=t_product_order.order_id")
            ->leftJoin(DB::raw('(SELECT product_order_id,sum(account) as account FROM sharing_platform.t_user_commission where add_time >='.strtotime(date('Y-m-01')).' group by product_order_id) as a'))
            ->on('a.product_order_id = t_product_order.id')
            ->where([
                't_advert_product_relate.advert_uid'=>$advert_id,
                't_order.status' =>2,
                't_product_order.add_time'=>['>='=>strtotime(date('Y-m-01'))],
            ])->groupBy(DB::raw("FROM_UNIXTIME(t_product_order.add_time,'%Y-%m-%d')"))->orderBy('t_product_order.add_time','desc');

        $pagination = new Pagination($tradingList, $page, $pagesize, $this->db);
        $data =  $pagination->get($this->db);

        return $data;
    }

    /**
     * 每月
     * @param string $advert_id
     * @return array
     */
    public function getTrading($advert_id)
    {
        $tradingList = $this->db->select(DB::raw("FROM_UNIXTIME(t_product_order.add_time,'%Y-%m') as add_time,sum(t_product_order.number) as number,sum(t_order.account) as account,ifnull(sum(a.account),0) as commission"))
            ->from('t_advert_product_relate')
            ->leftJoin('t_product_order')->on("t_advert_product_relate.product_id=t_product_order.product_id")
            ->leftJoin('t_order')->on("t_order.id=t_product_order.order_id")
            ->leftJoin(DB::raw('(SELECT product_order_id,sum(account) as account FROM sharing_platform.t_user_commission where add_time >='.strtotime(date('Y-m-01')).' group by product_order_id) as a'))
            ->on('a.product_order_id = t_product_order.id')
            ->where([
                't_advert_product_relate.advert_uid'=>$advert_id,
                't_order.status' =>2,
                't_product_order.add_time'=>['>'=>0],
            ])->groupBy(DB::raw("FROM_UNIXTIME(t_product_order.add_time,'%Y-%m')"))->orderBy('t_product_order.add_time','desc')
            ->get();

        return $tradingList;
    }

    /**
     * 按天
     * @param string $advert_id
     * @return array
     */
    public function getDayTrading($advert_id, $starttime, $endtime, $page, $pagesize)
    {


        $tradingList = $this->db->select(DB::raw("t_product_order.add_time,(t_product_order.number) as number,(t_order.account) as account,(a.account) as commission,t_product.product_name"))
            ->from('t_advert_product_relate')
            ->leftJoin('t_product_order')->on("t_advert_product_relate.product_id=t_product_order.product_id")
            ->leftJoin('t_product')->on("t_product.id=t_product_order.product_id")
            ->leftJoin('t_order')->on("t_order.id=t_product_order.order_id")
            ->leftJoin(DB::raw('(SELECT product_order_id,sum(account) as account FROM sharing_platform.t_user_commission where add_time >='.strtotime(date('Y-m-01')).'  group by product_order_id) as a'))
            ->on('a.product_order_id = t_product_order.id')
            ->where([
                't_advert_product_relate.advert_uid'=>$advert_id,
                't_order.status' =>2,
            ]);

        if($starttime && $endtime){
            $tradingList = $tradingList->where([
                't_product_order.add_time'=> ['>=' => DB::raw($starttime.' and t_product_order.add_time <= '. $endtime )]
            ]);
        }elseif($starttime){
            $tradingList = $tradingList->where([
                't_product_order.add_time'=> ['>=' => $starttime ]
            ]);
        }elseif($endtime){
            $tradingList = $tradingList->where([
                't_product_order.add_time'=> ['>=' => DB::raw('0 and t_product_order.add_time <= '. $endtime )]
            ]);
        }else{
            $tradingList = $tradingList->where([
                't_product_order.add_time'=> ['>' => 0 ]
            ]);
        }
        $tradingList = $tradingList->orderBy('t_product_order.add_time',DB::ORDER_BY_DESC);
        $pagination = new Pagination($tradingList, $page, $pagesize, $this->db);
        $data =  $pagination->get($this->db);

        return $data;
    }

    /**
     * 按天
     * @param string $advert_id
     * @return array
     */
    public function getUserInfo($advert_id)
    {
        $data = [];
        //本月
        $monthList = $this->db->select(DB::raw("ifnull(sum(t_product_order.number),0) as number,sum(t_order.account) as account,ifnull(sum(a.account),0) as commission"))
            ->from('t_advert_product_relate')
            ->leftJoin('t_product_order')->on("t_advert_product_relate.product_id=t_product_order.product_id")
            ->leftJoin('t_order')->on("t_order.id=t_product_order.order_id")
            ->leftJoin(DB::raw('(SELECT product_order_id,sum(account) as account FROM sharing_platform.t_user_commission where add_time >='.strtotime(date('Y-m-01')).' group by product_order_id) as a'))
            ->on('a.product_order_id = t_product_order.id')
            ->where([
                't_advert_product_relate.advert_uid'=>$advert_id,
                't_order.status' =>2,
                't_product_order.add_time'=>['>='=>strtotime(date('Y-m-01'))],
            ])->getFirst();
        $data['monthNumber'] = $monthList['number'];
        $data['monthAccount'] = bcsub($monthList['account'],$monthList['commission'],2);
        //今日
        $dayList = $this->db->select(DB::raw("ifnull(sum(t_product_order.number),0) as number,sum(t_order.account) as account,ifnull(sum(a.account),0) as commission"))
            ->from('t_advert_product_relate')
            ->leftJoin('t_product_order')->on("t_advert_product_relate.product_id=t_product_order.product_id")
            ->leftJoin('t_order')->on("t_order.id=t_product_order.order_id")
            ->leftJoin(DB::raw('(SELECT product_order_id,sum(account) as account FROM sharing_platform.t_user_commission where add_time >='.strtotime(date('Y-m-d')).' group by product_order_id) as a'))
            ->on('a.product_order_id = t_product_order.id')
            ->where([
                't_advert_product_relate.advert_uid'=>$advert_id,
                't_order.status' =>2,
                't_product_order.add_time'=>['>='=>strtotime(date('Y-m-d'))],
            ])->getFirst();
        $data['dayNumber'] = $dayList['number'];
        $data['dayAccount'] = bcsub($dayList['account'],$dayList['commission'],2);

        //今日
        $sumList = $this->db->select(DB::raw("sum(t_product_order.number) as number,sum(t_order.account) as account,ifnull(sum(a.account),0) as commission"))
            ->from('t_advert_product_relate')
            ->leftJoin('t_product_order')->on("t_advert_product_relate.product_id=t_product_order.product_id")
            ->leftJoin('t_order')->on("t_order.id=t_product_order.order_id")
            ->leftJoin(DB::raw('(SELECT product_order_id,sum(account) as account FROM sharing_platform.t_user_commission where add_time >=0 group by product_order_id) as a'))
            ->on('a.product_order_id = t_product_order.id')
            ->where([
                't_advert_product_relate.advert_uid'=>$advert_id,
                't_order.status' =>2,
                't_product_order.add_time'=>['>='=>0],
            ])->getFirst();
        $data['sumAccount'] = bcsub($sumList['account'],$sumList['commission'],2);

        //图表
        $infoList = $this->db->select(DB::raw("FROM_UNIXTIME(t_product_order.add_time,'%Y-%m-%d') as add_time,sum(t_product_order.number) as number,sum(t_order.account) as account,ifnull(sum(a.account),0) as commission"))
            ->from('t_advert_product_relate')
            ->leftJoin('t_product_order')->on("t_advert_product_relate.product_id=t_product_order.product_id")
            ->leftJoin('t_order')->on("t_order.id=t_product_order.order_id")
            ->leftJoin(DB::raw('(SELECT product_order_id,sum(account) as account FROM sharing_platform.t_user_commission where add_time >=0 group by product_order_id) as a'))
            ->on('a.product_order_id = t_product_order.id')
            ->where([
                't_advert_product_relate.advert_uid'=>$advert_id,
                't_order.status' =>2,
                't_product_order.add_time'=>['>='=>strtotime(date('Y-m-d',strtotime('-6 day'))) ],
            ])->groupBy(DB::raw("FROM_UNIXTIME(t_product_order.add_time,'%Y-%m-%d')"))->get();
        $info = [];
        $j = 0;
        for ($i=6;$i>=0;$i--){
            $info[$j]['time'] = date('m-d',strtotime('-'.$i.' day'));
            $info[$j]['account'] = 0;
            foreach ($infoList as $key => $value){
                if($value['add_time'] ==  $info[$j]['time']){
                    $info[$j]['account'] = bcsub($value['account'] , $value['commission'],2);
                }
            }
            $j++;
        }
        $data['info'] = $info;



        return $data;
    }

    /**
     * 重制密码
     * @param $advert_id
     * @return bool
     */
    public function  resetPassword($advert_id){

        $advert = $this->db->select('*')->from('t_advert_user_login')->where(['uid'=>$advert_id])->getFirst();
        $this->db->update('t_advert_user_login')->set(
            ['password'=>md5string($advert['username'])]
        )->where(['uid'=>$advert_id])->exec();
        return true;
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
            $rs = $this->db->select("t_user.*", DB::raw("t_advert_user_login.username as mobile,t_user_info.invite_code"), "t_advert_user_login.password,t_user_info.realname")
                ->from("t_advert_user_login")
                ->leftJoin("t_user")->on("t_advert_user_login.uid=t_user.id")
                ->leftJoin("t_user_info")->on("t_advert_user_login.uid=t_user_info.uid")
                ->where('username = ?',$mobile)->getFirst();
            return $rs;
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getTrace()
            ]);
        }
    }



}