<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 2017/11/26
 * Time: 下午12:21
 */

namespace App\Utils;


class Paramers
{
    private $array = [];

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    function add($name, $value)
    {
        $this->array[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    function get($name)
    {
        return $this->array[$name];
    }


}