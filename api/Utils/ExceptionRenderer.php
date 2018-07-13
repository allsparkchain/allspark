<?php
namespace App\Utils;

use DI\Container;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionRenderer
{
    use EnableDIAnnotations; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Exception $e
     * @return Response
     * @throws \Exception
     */
    public function render(\Exception $e)
    {
        if ($this->container->get('debug')) {
            \Symfony\Component\Debug\Debug::enable(E_ALL);
            $this->logger->error($e->getCode(), $e->getTrace());
            throw $e;
        }
        $message = json_encode(
            ['status' => ErrorConst::COMMON_PARAMETER_MISSING, 'message' => $this->getMessage($e), 'data' => []],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $this->logger->error($message, $e->getTrace());

        if($e instanceof HttpException){
            return new Response(
                $message,
                $e->getStatusCode(),
                ['Content-Type'=>'application/json']
            );
        } if($e instanceof \InvalidArgumentException){
            //return new JsonResponse()
            return new Response($message, Response::HTTP_OK, ['Content-Type'=>'application/json']);
        }else{
            return new Response($message,  Response::HTTP_OK, ['Content-Type'=>'application/json']);
        }
    }

    private function getMessage(\Exception $e)
    {
        if ($message = json_decode($e->getMessage(), true)) {
            $return = is_array($message) ? array_pop($message) : $message;

            return is_array($return) && isset($return[0]) ? $return[0] : $return;
        }

        return $e->getMessage();
    }
}