<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 10:16
 */

namespace System\Queue;

use System\Lock;
use System\Redis;

final class RedisQueue implements IQueue
{
    private $_redisClient;
    private $_redisLock;

    /** @return static */
    public static function instance()
    {
        static $instance = null;
        if ($instance === null) $instance = new static();
        return $instance;
    }

    public function __construct($conn = null)
    {
        if($conn == null) $conn = Redis::getInstance();
        $this->_redisClient = $conn;
        $this->_redisLock = new Lock();
    }

    /**
     * @param $name
     * @param $data
     * @param int $timeout
     * @return bool
     */
    public function push($name, Message $data, $timeout = 10)
    {
        if (empty($name) || empty($data)) return false;
        $data = serialize($data);
        // 加锁
        if (!$this->_redisLock->lock($queueName = "Queue-$name", $timeout)) return false;
        $this->_redisClient->lpush($queueName,$data);
        // 解锁
        $this->_redisLock->unlock($queueName);

        return true;
    }

    /**
     * @param $name
     * @param int $timeout
     * @return bool
     */
    public function remove($name,$timeout = 10)
    {
        if (empty($name) || empty($data)) return false;

        // 加锁
        if (!$this->_redisLock->lock($queueName = "Queue-$name", $timeout)) return false;

        $result = $this->_redisClient->lrem($queueName,0);

        // 解锁
        $this->_redisLock->unlock($queueName);

        return true;
    }

    /**
     * @param $name
     * @return Message
     */
    public function pop($name)
    {
        $res = $this->_redisClient->lpop($queueName = "Queue-$name");
        if(is_null($res)) return new Message();
        /** @var Message $message */
        $message = unserialize($res);
        return $message;
    }
}