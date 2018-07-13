<?php

namespace App\Utils\Services;

use function App\getCurrentTime;
use App\Utils\ErrorConst;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\ThrowResponseParamerTrait;
use DI\Container;
use PhpBoot\DB\DB;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Psr\Log\LoggerInterface;

class Message
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
     * @var \App\Utils\Lib\Message\Message
     */
    protected $sms;

    /**
     * @inject
     * @var \App\Utils\Stomp
     */
    protected $stomp;

    /**
     * 发送普通短信
     * @param $mobile
     * @param $content
     * @return bool
     * @throws \App\Exceptions\RuntimeException
     */
    public function ordinary($mobile, $content) {
        try {
            $sendSMS = $this->sms->sendSMS($mobile, $content);
            if ($sendSMS->result == "SUCCESS") {
                return true;
            }
        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * 发送普通短信
     *
     * @param $mobile
     * @param $template
     * @param $signature
     * @return bool
     * @throw \App\Exceptions\RuntimeException
     */
    public function sendOrdinary($mobile, $template, $signature = null)
    {
        $signature = empty($signature) ? '蒲公英' : $signature;
        try{
            $sendSMS = $this->sms->sendSMS($mobile, $signature, $template, null);
        }catch(\Exception $e){
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getMessage()
            ]);
        }
    }

    /**
     * 发送普通短信
     *
     * @param $orderNumber
     *
     * @return bool
     * @throws \App\Exceptions\RuntimeException
     */
    public function sendMsg($orderNumber) {
        try {
            return $this->mutex->getMutex(__CLASS__."sendMsg".$orderNumber)->synchronized(function() use($orderNumber){
                $first = $this->db->select("*")->from("t_sms_order")->where([
                    "order_number" => $orderNumber,
                    "status" => 1,
                ])->getFirst();
                if (!$first) {
                    return false;
                }
                //sendSMS($mobile, $signature, $template, $code)
                $signature = empty($first['signature']) ? '蒲公英' : $first['signature'];
                $code = empty($first['code']) ? '' : json_decode($first['code'], true);
                $sendSMS = $this->sms->sendSMS($first['mobile'], $signature, $first['template'], $code);
                if ($sendSMS->result == "SUCCESS" || $sendSMS->result == 'OK') {
                    $this->db->update("t_sms_order")->set([
                        "status"=>2
                    ])->where("id = ?", $first['id'])->exec();
                    return true;
                }

                return false;
            });


        } catch (\Exception $e) {
            throw $this->exception([
                'code'=>ErrorConst::SYSTEM_ERROR,
                'text'=>$e->getMessage()
            ]);
        }
    }


    /**
     * 创建短信

     * @param $mobile
     * @param $type (1、注册验证码2、忘记密码验证码)
     * @param $contents
     *
     * @return string $orderNumber
     */
    public function createMsg($mobile, $type, $contents = '', $paramers = []) {
        /**
         * type 类型
         * 1、注册验证码
         * 2、忘记密码验证码
         */
        return $this->mutex->getMutex(__CLASS__."sendMsg".$mobile.$type)->synchronized(function() use($mobile, $type, $contents, $paramers) {
            if (isset(range(1, 101)[$type - 1])) {
                // 6 代表注册发送验证码并且发送实名认证验证码， 验证码的内容必须一致
                // 查询短信是否在60秒内添加过
                $count = $this->db->select("*")->from("t_sms_order")->where([
                    "type" => $type,
                    "mobile" => $mobile,
                    "add_time" => [
                        ">=" => getCurrentTime() - 60
                    ]
                ])->count();
                if ($count>0) {
                    throw $this->exception([
                        'code'=>ErrorConst::MESSAGE_SENDING_FREQUENTLY,
                        'text'=>"短信发送频繁".json_encode([$mobile, $type, $contents]),
                    ]);
                }
                $orderNumber = isset($paramers['orderNunber'])?$paramers['orderNunber']:$this->getOrderNumber();

                $code = isset($paramers['code'])?$paramers['code']:rand(1000, 9999);

                if($type != 10){//虚拟码不发送自定义code
                    if ($this->container->get('debug')) {
                        $code = '1234';
                    }
                }

                //短信类型\n1、注册手机验证\n2、忘记密码验证\n3、银行卡修改验证\n4、提现验证\n5、实名认证验证\n6、注册实名认证验证\n7、修改旧手机认证验证\n8、提交新手机认证验证， 10虚拟商品激活码 101cpa外教手机提交
                $template = '';
                switch($type){
                    case 1:
                        $template = 'SMS_130815095';
                        break;
                    case 2:
                        $template = 'SMS_130815094';
                        break;
                    case 3:
                        $template = 'SMS_130815093';
                        break;
                    case 4:
                        $template = 'SMS_134120262';
                        break;
                    case 5:
                        $template = 'SMS_130815098';
                        break;
                    case 6:
                        $template = 'SMS_130815098';
                        break;
                    case 7:
                        $template = 'SMS_130815093';
                        break;
                    case 8:
                        $template = 'SMS_130815093';
                        break;
                    case 10:
                        $template = 'SMS_134326559';
                        break;
                    default:
                        $template = 'SMS_130815093';
                        break;
                }
                $this->db->insertInto("t_sms_order")->values([
                    "order_number" => $orderNumber,
                    "type" => $type,
                    "mobile" => $mobile,
                    "contents" => "【蒲公英】验证码：".$code."，若非本人操作请及时修改密码。",
                    'signature' => '蒲公英',
                    'template' => $template,
                    'code' => json_encode(['code' => $code]),
                    "status" => $type==6?2:1,
                    "add_time" => getCurrentTime(),
                ])->exec();
                $this->db->insertInto("t_sms_verification")->values([
                    "order_number" => $orderNumber,
                    "mobile" => $mobile,
                    "code" => $type == 10 ? 0:$code,//type 10虚拟 不许验证，另目前code字段为int 多个code下无法正确储存
                    "type" => $type,
                    "status" => 1,
                    "error_times" => 0,
                    "add_time" => getCurrentTime(),
                ])->exec();

                $useMq = $this->container->get('useMq');
                if($useMq){
                    $this->stomp->send('sendSmsMessage', $orderNumber.'_'.$mobile);
                }else{
                    if (!$this->container->get('debug') && $type!=6  && $type!=11) {
                        $this->sendMsg($orderNumber);
                    }
                }

                // 创建验证订单
                if($type==1){
                    $this->createMsg($mobile, 6, $contents, ['orderNunber'=>$orderNumber.'_6', 'code' => $code]);
                }
                if($type==5){
                    $this->createMsg($mobile, 11, $contents, ['orderNunber'=>$orderNumber.'_11', 'code' => $code]);
                }

                return $orderNumber;
            }
        });
    }

    /**
     * @param $orderNumber
     * @param $mobile
     * @param $code
     * @return mixed
     */
    public function verificationSms($orderNumber, $mobile, $code, $type) {
        return $this->mutex->getMutex(__CLASS__."sendMsg".$orderNumber)->synchronized(function() use($orderNumber, $mobile, $code, $type) {
            // 判断验证订单是否有效
            $first = $this->db->select("*")->from("t_sms_verification")->where([
                "order_number" => $orderNumber,
                "status" => 1,
                "type" => $type
            ])->getFirst();
            if (!$first) {
                // 验证订单不存在
                throw $this->exception([
                    "code" => ErrorConst::VERIFICATION_ORDER_DOES_NOT_EXIST,
                    "text" => "验证订单不存在".json_encode([$orderNumber, $mobile, $code])
                ]);
            }
            if($first['mobile'] != $mobile){
                throw $this->exception([
                    "code" => ErrorConst::VERIFY_MOBILE_MISMATCH,
                     "text" => "传递手机号{$mobile}与发送验证码手机号{$first['mobile']}不符"
                ]);
            }

            if ($first['code'] != $code) {
                $set = [
                    "error_times" => DB::raw("error_times+1"),
                ];
                if ($first['error_times'] == 9 ) {
                    $set['status'] = 3;
                }
                $this->db->update("t_sms_verification")->set($set)->where([
                    "order_number" => $orderNumber,
                ])->exec();

                throw $this->exception([
                    "code" => ErrorConst::VERIFICATION_CODE_ERROR,
                    "text" => "验证码错误".json_encode([$orderNumber, $mobile, $code])
                ]);

                //
            }

            $this->db->update("t_sms_verification")->set([
                "status" => 2,
            ])->where([
                "order_number" => $orderNumber,
            ])->exec();

            $verifyOrderNumber = $this->getVerifyOrderNumber();
            $this->db->insertInto("t_verify_order")->values([
                "order_number" => $verifyOrderNumber,
                "verify_text" => json_encode(["mobile"=>$mobile]),
                "verify_type" => $type,
                "status" => 1,
                "useful_time" => getCurrentTime()+600,
                "add_time" => getCurrentTime(),
            ])->exec();

            return $verifyOrderNumber;
            //

        });
    }

    private function getOrderNumber() {
        return "SMS".time().rand(10000, 99999);
    }

    private function getVerifyOrderNumber() {
        return "VER".time().rand(10000, 99999);
    }
}