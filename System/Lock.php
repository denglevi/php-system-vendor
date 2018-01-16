<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 11:02
 */

namespace System;

use System\Helper\StringHelper;
use Predis\Client as RedisClient;

/**
 * 在 Redis 上实现的分布式锁
 * 为避免特殊原因导致锁无法释放, 在加锁成功后, 锁会被赋予一个生存时间(通过 lock 方法的参数设置或者使用默认值), 超出生存时间锁将被自动释放.
 * 锁的生存时间默认比较短(秒级, 具体见 lock 方法), 因此若需要长时间加锁, 可以通过 expire 方法延长锁的生存时间为适当的时间. 比如在循环内调用 expire
 *
 * 系统级的锁当进程无论因为任何原因出现crash，操作系统会自己回收锁，所以不会出现资源丢失。
 * 但分布式锁不同。若一次性设置很长的时间，一旦由于各种原因进程 crash 或其他异常导致 unlock 未被调用，则该锁在剩下的时间就变成了垃圾锁，导致其他进程或进程重启后无法进入加锁区域。
 */
final class Lock
{
    private $_redisClient;
    private $_lockId = [];

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
    }

    /**
     * @param string 锁的标识名
     * @param int 获取锁失败时的等待超时时间(秒), 在此时间之内会一直尝试获取锁直到超时. 为 0 表示失败后直接返回不等待
     * @param int 当前锁的最大生存时间(秒), 必须大于 0 . 如果超过生存时间后锁仍未被释放, 则系统会自动将其强制释放
     * @param int 获取锁失败后挂起再试的时间间隔(微秒)
     * @return bool
     * @desc 加锁
     */
    public function lock($name, $timeout = 0, $expire = 15, $waitIntervalUs = 1000)
    {
        if (empty($name)) return false;

        $expire = max((int)$expire, 5);
        $lockId = StringHelper::rand(6);

        if ($result = $this->_redisClient->setnx($lockName = 'Lock:' . $name, $lockId)) {
            $this->_redisClient->expire($lockName, $expire);
            $this->_lockId[$name] = $lockId;
            return true;
        }

        if (($timeout = intval($timeout)) <= 0) return false;

        $timeoutAt = number_format(microtime(true) + $timeout, 4, '.', '');
        $keyPrefixLen = strlen($keyPrefix = 'LockWaiting:' . $name . ':');
        $this->_redisClient->setnx($waitingKey = $keyPrefix . $timeoutAt . ',' . $lockId);
        $this->_redisClient->expire($waitingKey,$expire);
        while (true) {
            if (($now = microtime(true)) >= $timeoutAt) {
                $this->_redisClient->del($waitingKey);
                return false;
            }

            $waitingKeyList = $this->_redisClient->keys($keyPrefix . '*');
            sort($waitingKeyList);
            foreach ($waitingKeyList as $key) {
                list($tmpTimeout, $tmpLockId) = explode(',', substr($key, $keyPrefixLen));
                if ($tmpTimeout >= $now) {
                    if ($tmpLockId === $lockId && false !== $this->_redisClient->setnx($lockName, $lockId)) {
                        $this->_redisClient->expire($lockName, $expire);
                        $this->_lockId[$name] = $lockId;
                        $this->_redisClient->del($waitingKey);
                        return true;
                    }
                    break;
                }
            }

            usleep($waitIntervalUs);
        }

        return false;
    }

    /**
     * @param $name 锁的标识名
     * @param $expire 生存时间(秒), 必须大于 0
     * @return bool
     * @desc 给当前锁增加指定的生存时间(秒), 必须大于 0
     */
    public function expire($name, $expire)
    {
        if (!$this->isLocking($name)) return false;
        if ($this->_redisClient->expire("Lock:$name", max($expire, 1))) {
            return true;
        }
    }

    /**
     * @param $name
     * @return bool
     * @desc 判断当前是否拥有指定名称的锁
     */
    public function isLocking($name)
    {
        if (!isset($this->_lockId[$name])) return false;
        if ($this->_lockId[$name] == $this->_redisClient->get("Lock:$name")) {
            return true;
        } else {
            unset($this->_lockId[$name]);
            return false;
        }
    }

    /**
     * @param $name 锁的标识名
     * @return bool
     * @desc 释放锁
     */
    public function unlock($name)
    {
        if (!$this->isLocking($name)) return false;
        if ($this->_redisClient->del("Lock:$name")) {
            unset($this->_lockId[$name]);
            return true;
        }
    }

    /**
     * @return bool
     * @desc 释放当前已经获取到的所有锁
     */
    public function unlockAll()
    {
        $allSuccess = true;
        foreach ($this->_lockId as $name => $item) {
            if (false === $this->unlock($name)) {
                $allSuccess = false;
            }
        }
        return $allSuccess;
    }
}