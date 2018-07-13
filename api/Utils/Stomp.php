<?php
namespace App\Utils;

use PhpBoot\DI\Traits\EnableDIAnnotations;
use Stomp AS BaseStomp;
use StompException;

class Stomp
{
    use EnableDIAnnotations;

    /**
     * @var BaseStomp
     */
    protected $stomp;

    public function __construct($broker, $username = null, $password = null, $header = [], $timeout = -1)
    {
        if (!$this->stomp) {
            //$this->stomp = $this->getConnect($broker, $username, $password, $header);
            //$this->stomp->setReadTimeout($timeout);
        }
    }

    /**
     * 推送消息到队列中
     * @param string $name 队列别名
     * @param mixed $message 消息
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function send($name, $message)
    {
        return $this->stomp->send($name, $this->getSendMessage($message));
    }

    public function getMessage($name, \Closure $getMessageNext, $runtime = 0)
    {
        $time = time();
        $isRun = true;
        $this->stomp->subscribe($name);
        while (true && $isRun) {
            if ($runtime > 0) {
                $isRun = time() - $time < $runtime ? true : false;
            }
            if ($this->stomp->hasFrame()) {
                $stompFrame = $this->stomp->readFrame();
                try {
                    if ($getMessageNext($stompFrame)) {
                        $this->stomp->ack($stompFrame);
                    } else {
                        throw new \Exception('外部处理失败');
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
                usleep(100);
            } else {
                echo '0000';
            }
        }
    }

    /**
     * 获取stomp连接
     * @param string $broker 连接信息
     * @param string|null $username 用户名
     * @param string|null $password
     * @param array $header
     * @return BaseStomp
     * @throws \Exception
     */
    private function getConnect($broker, $username = null, $password = null, $header = [])
    {
        try {
            $stomp = new BaseStomp($broker, $username, $password, $header);

            return $stomp;
        } catch(StompException $e) {
            throw $e;
        }
    }

    /**
     * 返回字符串的信息
     * @param mixed $message
     * @return mixed
     */
    private function getSendMessage($message)
    {
        switch (gettype($message)) {
            case 'array':
            case 'object':
                $return = json_encode($message);
                break;
            default:
                $return = $message;
        }

        return $return;
    }
}