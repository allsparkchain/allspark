<?php
namespace App\Utils;

//use App\Exceptions\RuntimeException;

trait CacheResultTrait
{
    /**
     * @param $key
     * @return bool
     * @throws \Exception
     */
    protected function getFromCache($key) {

        try {
            $cache = $this->container->make(\Predis\Client::class);
            if (is_null($key) || '' === $key) {
                throw new \Exception('cache key is empty', 500);
            }
            if ($cache->exists($key)) {
                return json_decode($cache->get($key), true);
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $key
     * @param $data
     * @param int $ttl
     * @return \Exception
     */
    protected function saveToCache($key, $data, $ttl = 120) {
        try {
            $cache = $this->container->make(\Predis\Client::class);
            $cache->setex($key, $ttl, json_encode($data));
            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }

}