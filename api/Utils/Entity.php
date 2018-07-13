<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 2017/11/24
 * Time: 下午2:01
 */

namespace App\Utils;


abstract class Entity
{
    public function __set($name, $value)
    {
       $filed = "";
       foreach (explode('_', $name)  as $v) {
           $filed .= ucfirst($v);
       }
       $filed = lcfirst($filed);
       $this->$filed = $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $return = [];
        foreach (get_class_vars(get_class($this)) as $key=>$value) {
             $return[$key] = $this->$key;
        }
        unset($return['id']);

        return $return;
    }
}