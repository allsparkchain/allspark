<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 18:06
 */

namespace App\Utils\Services;

use App\Exceptions\RuntimeException;
use App\Utils\ErrorConst;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use App\Utils\Lib\Email\EmailInterface;


class Email
{
    use EnableDIAnnotations, ThrowResponseParamerTrait;

    /**
     * @inject
     * @var EmailInterface
     */
    protected $email;

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
     * @var Container
     */
    protected $container;

    /**
     * 发送验证码邮件
     * @param  string $to_user
     * @param string $phone
     * @return boolean
     */
    public function send($to_user = '', $phone = '')
    {
        try {
            if(!filter_var($to_user, FILTER_VALIDATE_EMAIL)){
                throw $this->exception([
                    'code' => ErrorConst::EMAIL_VALIDATE_ERROR,
                    'text' => '邮箱格式不正确',
                ]);
            }
            $code = \App\randCode(6);


           $bExist =  $this->db->select('id')
                ->from('t_mail_code')
               ->where(['email' => $to_user, 'mobile' => $phone])
                ->getFirst();
           if(!$bExist){
               $lastId = $this->db->insertInto('t_mail_code')->values([
                   'email'=>$to_user,
                   'mobile' => $phone,
                   'code'=>$code,
                   'expire_time'=> time()+ 86400,
                   'add_time' => time(),
                   'delete_time' => 0,
                   'data_status' => 1
               ])->exec()->lastInsertId();

               if($lastId<=0){
                   throw $this->exception([
                       'code'=>ErrorConst::EMAIL_SEND_CODE_ERROR,
                       'message'=>"邮箱验证码发送失败"
                   ]);
               }
           }else{
               $this->db->update('t_mail_code')
                   ->set([
                       'code' => $code,
                       'expire_time' => time() + 86400,
                       'update_time' => time(),
                       'delete_time' => 0,
                       'data_status' => 1
                   ])
               ->where(['email' => $to_user, 'mobile' => $phone])
               ->exec();
           }
            $sendCode = md5($code);
            $rs = $this->email->send($to_user, $sendCode, $phone);

            if ($rs != 0) {
                return [];
            }else{
                throw $this->exception([
                    'code' => ErrorConst::EMAIL_SEND_ERROR,
                    'message' => '邮件发送失败'
                ]);
            }
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>$e->getCode(),
                'message'=>$e->getMessage()
            ]);
        }
    }

    /**
     * 验证验证码
     * @param string $email
     * @param string $code
     * @param string $phone
     * @return boolean
     */
    public function verifyCode($email, $code, $phone)
    {
        $aRet =  $this->db->select('code', 'expire_time')
            ->from('t_mail_code')
            ->where([
                'mobile' => $phone,
                'email' => $email,
                'data_status' => 1,
            ])->getFirst();

        if($aRet) {
            if (md5($aRet['code']) == $code && time() <= $aRet['expire_time']) {
                $this->db->update('t_mail_code')
                    ->set([
                        'delete_time' => time(),
                        'data_status' => 0,
                    ])->where(['email' => $email, 'mobile' => $phone])->exec();
                return true;
            } else {
                throw $this->exception([
                    'code' => ErrorConst::CODE_ERROR,
                    'message' => '验证码不正确或已经失效',
                ]);
            }
        }else{
            throw $this->exception([
                'code' => ErrorConst::CODE_ERROR,
                'text' => '验证码不存在',
            ]);
        }
    }
}