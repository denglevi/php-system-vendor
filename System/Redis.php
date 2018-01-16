<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 17:09
 */

namespace System;

use Predis\Client as RedisClient;
class Redis
{
    /**
     * @return null|RedisClient
     */
    public static function & getInstance(){
        static $instance = null;
        if($instance == null) $instance = new RedisClient([
            'host'=>Configuration::getConfig('REDIS_HOST'),
            'port'=>Configuration::getConfig('REDIS_PORT')
        ]);
        return $instance;
    }
}