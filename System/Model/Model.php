<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/24 0024
 * Time: 9:38
 */
namespace System\Model;
class Model extends \Illuminate\Database\Eloquent\Model
{
    public static function getInstance(){
        static $instance;
        if(null == $instance) $instance = new static();

        return $instance;
    }
}