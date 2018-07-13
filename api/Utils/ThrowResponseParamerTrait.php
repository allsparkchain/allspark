<?php
namespace App\Utils;

use App\Exceptions\RuntimeException;
use App\Exceptions\UserException;

trait ThrowResponseParamerTrait
{
    /**
     * @param array $parms
     * @return RuntimeException
     */
    protected function exception($parms) {
        foreach ($parms as $key=>$parm) {
            $this->paramer->add($key, $parm);
        }

        return $this->container->make(RuntimeException::class, [$this->paramer]);
    }
}