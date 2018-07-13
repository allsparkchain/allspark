<?php

namespace App\Utils;


use malkusch\lock\mutex\PredisMutex;

class Mutex
{
    private $redis;

    public function setRedis(...$redis) {
        $this->redis = $redis;
    }

    /**
     * @param $name
     * @return \malkusch\lock\mutex\PredisMutex
     */
    public function getMutex($name, $timout = 6) {
        return new PredisMutex($this->redis, $name, $timout);
    }
}