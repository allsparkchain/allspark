<?php
/**
 * Created by PhpStorm.
 * User: viki
 * Date: 2018/5/14/014
 * Time: 14:56
 */

namespace App\Lite\Users\Services;

use App\Utils\ErrorConst;

class LiteToken
{
    use \PhpBoot\DI\Traits\EnableDIAnnotations, \App\Utils\ThrowResponseParamerTrait;

    /**
     * @inject
     * @var \PhpBoot\DB\DB
     */
    private $db;

    /**
     * @inject
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * @inject
     * @var \App\Utils\Paramers
     */
    protected $paramer;

    /**
     * @inject
     * @var \DI\Container
     */
    protected $container;

    /**
     * @inject
     * @var \Predis\Client
     */
    private $redis;

    /**
     * @inject
     * @var \App\Utils\Mutex
     */
    public $mutex;

    /**
     * @var string
     */
    public $expire = 3600;

    /**
     * token
     * @param string $code
     * @param string $iv
     * @param string $encryptedData
     * @return array
     * @throws \DI\NotFoundException
     * @throws \DI\DependencyException
     * @throws \App\Exceptions\RuntimeException
     */
    public function token($code, $iv, $encryptedData)
    {
        $config = $this->container->get('lite_config');
        $XBizDataCrypt = new \App\Utils\Lib\WX\Token($this->db, $config);
        $data = $XBizDataCrypt->decryptData($code, $iv, $encryptedData);
        if (!is_array($data)) {
            throw $this->exception([
                'code' => ErrorConst::TOKEN_ERROR,
                'text' => '获取token失败',
            ]);
        }
        return $this->mutex->getMutex('requestToken')->synchronized(function () use ($data) {
            $token = md5(md5(microtime() . rand(10000000, 99999999)) . $data['unionId']);
            $this->redis->set('token:' . $token, $data['unionId']);
            $this->redis->expire('token:' . $token, $this->expire);
            $this->getWid($data);
            return ['token' => $token];
        });
    }

    /**
     * token
     * @param string $token
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function checkToken($token)
    {
        $unionId = $this->redis->get('token:' . $token);
        if (!$unionId) {
            throw $this->exception([
                'code' => ErrorConst::TOKEN_ERROR,
                'text' => 'token失效',
            ]);
        }
        $wx = $this->redis->get('weixin:' . $unionId);
        if ($wx) return json_decode($wx, true);
        $wx = $this->db->select('id')->from('t_weixin_user')->where(['unionid' => $unionId])->getFirst();
        if ($wx) {
            $this->redis->set('token:' . $token, $unionId);
            $this->redis->expire('token:' . $token, $this->expire);
            $relate = $this->db->select('uid')->from('t_weixin_user_relate')->where(['wx_id' => $wx['id'], 'login_type' => 7])->getFirst();
            if ($relate) {
                $arr = ['id' => $wx['id'], 'uid' => $relate['uid']];
                $this->redis->set('weixin:' . $unionId, json_encode($arr));
                return $arr;
            } else {
                return $this->mutex->getMutex('fundMonitor' . $unionId)->synchronized(function () use ($wx, $unionId) {
                    return $this->createUser($wx['id'], $unionId);
                });
            }
        }
        throw $this->exception([
            'code' => ErrorConst::TOKEN_ERROR,
            'text' => 'token失效',
        ]);
    }

    /**
     * getWid
     * @return array
     */
    private function getWid($data)
    {
        $wx = $this->redis->get('weixin:' . $data['unionId']);
        if ($wx) {
            $wx = json_decode($wx, true);
            $this->updateWeixin($wx['id'], $data);
            return $wx;
        }
        $wx = $this->db->select('id')->from('t_weixin_user')->where(['unionid' => $data['unionId']])->getFirst();
        if ($wx) {
            $this->updateWeixin($wx['id'], $data);
            $relate = $this->db->select('uid')->from('t_weixin_user_relate')->where(['wx_id' => $wx['id'], 'login_type' => 7])->getFirst();
            if ($relate) {
                $arr = ['id' => $wx['id'], 'uid' => $relate['uid']];
                $this->redis->set('weixin:' . $data['unionId'], json_encode($arr));
                return $arr;
            } else {
                return $this->createUser($wx['id'], $data['unionId']);
            }
        }
        $id = $this->db->insertInto('t_weixin_user')->values([
            'lite_openid' => $data['openId'],
            'unionid' => $data['unionId'],
            'nickname' => $data['nickName'],
            'sex' => $data['gender'],
            'headimgurl' => $data['avatarUrl'],
            'content' => json_encode($data),
            'add_time' => \App\getCurrentTime(),
        ])->exec()->lastInsertId();
        return $this->createUser($id, $data['unionId']);
    }

    //创建钱包用户
    private function createUser($id, $unionId)
    {
        $uid = $this->db->insertInto('t_user')->values([
            'register_time' => \app\getCurrentTime(),
            'register_ip' => \App\getIP(),
            'status' => 1,
            'add_time' => \App\getCurrentTime(),
        ])->exec()->lastInsertId();
        $this->db->insertInto('t_user_account')->values(['uid' => $uid, 'add_time' => \App\getCurrentTime()])->exec();
        $this->db->insertInto('t_user_info')->values([
            'uid' => $uid,
            'sms_verify_status' => 2,
            'invite_code' => sprintf('%x', crc32(microtime())),
            'add_time' => \App\getCurrentTime()
        ])->exec();
        $this->db->insertInto('t_weixin_user_relate')->values([
            'uid' => $uid,
            'wx_id' => $id,
            'login_type' => 7,
            'add_time' => \App\getCurrentTime(),
        ])->exec();
        $this->db->insertInto('t_weixin_wallet')->values([
            'uid' => $uid,
            'wx_id' => $id,
            'add_time' => \App\getCurrentTime(),
        ])->exec();
        $arr = ['id' => $id, 'uid' => $uid];
        $this->redis->set('weixin:' . $unionId, json_encode($arr));
        return $arr;
    }

    //跟新用户信息
    private function updateWeixin($id, $data)
    {
        return $this->db->update('t_weixin_user')->set([
            'lite_openid' => $data['openId'],
            'nickname' => $data['nickName'],
            'sex' => $data['gender'],
            'headimgurl' => $data['avatarUrl'],
            'content' => json_encode($data),
        ])->where(['id' => $id])->exec();
    }
}