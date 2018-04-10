<?php

namespace App\Users\Services;

use function App\getCurrentTime;
use function App\getIP;
use function App\md5string;
use App\Utils\ErrorConst;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\Services\Message;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Psr\Log\LoggerInterface;


class Weixin
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
     * 生成code
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function createCode()
    {
        try{
            $jzstate = md5(time().rand(1000000, 9999999));
            $this->db->insertInto('t_weixin_user_login_code')->values([
                'code'=>$jzstate,
                'status'=> 1,
                'add_time' => getCurrentTime()
            ])->exec();
            return $jzstate;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 生成code
     * @return bool
     * @throws \App\Exceptions\RuntimeException
     */
    public function editCode($code, $openid)
    {
        try{
            $execResult = $this->db->update('t_weixin_user_login_code')->set([
                'wx_open_id' => $openid,
                'status' => 2
            ])->where(['code' => $code])->exec()->rows;

            if($execResult != 1){
                throw $this->exception([
                    'code'=>  ErrorConst::UPDATE_ERROR,
                    'text'=> 't_weixin_user_login_code更新失败'.json_encode([$code,$openid])
                ]);
            }

            return true;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }



    /**
     * 生成openId
     * @return int
     * @throws \App\Exceptions\RuntimeException
     */
    public function createOpenId($openid, $nickname, $sex, $headimgurl, $content)
    {
        try{

            $this->db->insertInto('t_weixin_user')->values([
                'openid' => $openid,
                'nickname' => $nickname,
                'sex' => $sex,
                'headimgurl' => $headimgurl,
                'content' => $content,
                'add_time' => getCurrentTime(),
            ])->exec()->lastInsertId();

            //t_user  t_user_info
            $lastId = $this->db->insertInto('t_user')->values([
                //'mobile'=>$userEntity->getMobile(),
                //'password'=>md5string($userEntity->getPassword()),
                'register_time'=>getCurrentTime(),
                'register_ip'=>getIP(),
                'status'=>1,
                'add_time' =>getCurrentTime()
            ])->exec()->lastInsertId();

            $this->db->insertInto('t_user_account')->values([
                'uid'=>$lastId,
                'add_time' =>getCurrentTime()
            ])->exec();

            $this->db->insertInto('t_user_info')->values([
                'uid'=>$lastId,
                'mobile'=>'',
                'sms_verify_status'=>2,
                'invite_code'=> sprintf('%x',crc32(microtime())),
                'add_time' =>getCurrentTime()
            ])->exec();


            return $lastId;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 生成openId
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getOpenId($openid)
    {
        try{
            $openData = $this->db->select('*')->from('t_weixin_user')->where(['openid' => $openid])->getFirst();
            return $openData;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
 * 生成openId
 * @return array
 * @throws \App\Exceptions\RuntimeException
 */
    public function getOpenCode($code, $loginType = 0)
    {
        try{

            $openData = $this->db->select('t_weixin_user_login_code.*,t_weixin_user.nickname,t_weixin_user.headimgurl')
                ->from('t_weixin_user_login_code')
                ->leftJoin('t_weixin_user')->on('t_weixin_user.id=t_weixin_user_login_code.wx_open_id')
                ->where([
                    't_weixin_user_login_code.code' => $code,
                    't_weixin_user_login_code.status' => 2,
                ])->getFirst();

            $openData['user'] = $this->db->select('*')
                ->from('t_weixin_user')
                ->leftJoin('t_weixin_user_relate')->on('t_weixin_user_relate.wx_id=t_weixin_user.id')
                ->where([
                    't_weixin_user.id' => $openData['wx_open_id'],
                    't_weixin_user_relate.login_type' => $loginType
                ])->getFirst();

            return $openData;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 绑定openId
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function bindOpenId($uid, $code)
    {
        try{
            $openData = $this->db->select('t_weixin_user.id')
                ->from('t_weixin_user_login_code')
                ->leftJoin('t_weixin_user')->on('t_weixin_user.id=t_weixin_user_login_code.wx_open_id')
                ->where([
                    't_weixin_user_login_code.code' => $code,
                    't_weixin_user_login_code.status' => 2,
                ])->getFirst();
            if($openData){

                $this->db->insertInto('t_weixin_user_relate')->values([
                    'uid'=> $uid,
                    'wx_id'=> $openData['id'],
                    'login_type'=> 1,
                    'add_time' => getCurrentTime()
                ])->exec();

                $this->db->update('t_weixin_user_login_code')->set([
                    'status' => 3
                ])->where(['code' => $code])->exec();

            }
            return true;
        }catch(\PDOException $e){
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }


    /**
     * 前端用户登录
     * @param string $code
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function login($code, $loginType)
    {

        $openData = $this->db->select('*')
            ->from('t_weixin_user_login_code')
            ->leftJoin('t_weixin_user')->on('t_weixin_user.id=t_weixin_user_login_code.wx_open_id')
            ->leftJoin('t_weixin_user_relate')->on('t_weixin_user.id=t_weixin_user_relate.wx_id')
            ->where([
                't_weixin_user_login_code.code' => $code,
                't_weixin_user_login_code.status' => 2,
                't_weixin_user_relate.login_type' => $loginType
            ])->getFirst();

        $account = [];
        if($openData['uid'] && $openData['status']==2) {

            if ($loginType == 2) {
                $account = $this->db->select("t_user.*", DB::raw("t_weixin_user.nickname as mobile,t_weixin_user.headimgurl,t_user_info.invite_code,'' as password,t_weixin_user.openid"))
                    ->from("t_user")
                    ->leftJoin("t_user_info")->on("t_user.id=t_user_info.uid")
                    ->leftJoin("t_weixin_user_relate")->on("t_user.id=t_weixin_user_relate.uid and login_type=2")
                    ->leftJoin("t_weixin_user")->on("t_weixin_user.id=t_weixin_user_relate.wx_id")
                    ->where('t_user.id = ?', $openData['uid'])->getFirst();
            } else {
                $account = $this->db->select("t_user.*", DB::raw("t_user_login.username as mobile,t_user_info.invite_code,t_weixin_user.nickname,t_weixin_user.headimgurl"), "t_user_login.password")
                    ->from("t_user_login")
                    ->leftJoin("t_user")->on("t_user_login.uid=t_user.id")
                    ->leftJoin("t_user_info")->on("t_user_login.uid=t_user_info.uid")
                    ->leftJoin("t_weixin_user_relate")->on("t_user.id=t_weixin_user_relate.uid and login_type=1")
                    ->leftJoin("t_weixin_user")->on("t_weixin_user.id=t_weixin_user_relate.wx_id")
                    ->where('t_user_login.uid = ?', $openData['uid'])->getFirst();
            }
        }

        $this->db->update('t_weixin_user_login_code')->set([
            'status' => 3
        ])->where(['code' => $code])->exec();


        if(count($account) <= 0 ){
            throw $this->exception([
                'code'=>ErrorConst::LOGIN_ERROR,
                'text'=>"用户".$openData['uid']."不存在"
            ]);
        }

        return $account;
    }


    /**
     * 创建openId 用户
     * @param string $code
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function createOpenUser($openId)
    {

        return $this->mutex->getMutex('createOpenUser' . $openId)->synchronized(function () use ($openId) {

            $openData = $this->db->select('*')
                ->from('t_weixin_user')
                ->leftJoin('t_weixin_user_relate')->on('t_weixin_user.id=t_weixin_user_relate.wx_id')
                ->where([
                    't_weixin_user.id' => $openId,
                    't_weixin_user_relate.login_type' => 2,
                ])->getFirst();

            if (!$openData) {
                 $this->db->transaction(function(DB $db) use ($openData, $openId) {
                    $lastId = $db->insertInto('t_user')->values([
                        //'mobile'=>$userEntity->getMobile(),
                        //'password'=>md5string($userEntity->getPassword()),
                        'register_time' => getCurrentTime(),
                        'register_ip' => getIP(),
                        'status' => 1,
                        'add_time' => getCurrentTime(),
                    ])->exec()->lastInsertId();
                    if($lastId<=0){
                        throw $this->exception([
                            'code'=>ErrorConst::USER_CREATE_ERROR,
                            'text'=>"用户创建失败"
                        ]);
                    }

                     $db->insertInto('t_user_info')->values([
                        'uid' => $lastId,
                        'mobile' => '',
                        'sms_verify_status' => 2,
                        'invite_code' => sprintf('%x', crc32(microtime())),
                        'add_time' => getCurrentTime(),
                    ])->exec();

                     $db->insertInto('t_user_account')->values([
                        'uid' => $lastId,
                        'add_time' => getCurrentTime(),
                    ])->exec();

                    //绑定openId
                     $db->insertInto('t_weixin_user_relate')->values([
                        'uid' => $lastId,
                        'wx_id' => $openId,
                        'login_type' => 2,
                        'add_time' => getCurrentTime()
                    ])->exec();
                });
            }

            return true;
        });

    }



}