<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 11:01
 */

namespace System;

/**
 * 任务队列, 用于将业务逻辑中可以异步处理的操作放入队列, 在其他线程中处理后出队
 * 队列内使用了分布式锁和其他逻辑, 保证入队和出队的一致性
 * 注意： 这个队列和普通队列不同， 入队时的 data 是用来区分重复入队的， 相同的 data 在队列里面只会有一条记录， 后入的覆盖前入的，而不是追加。
 *
 * 如果需求要求重复入队当作不同的任务，请将 data 处理为非重复的。
 */
use Predis\Client as RedisClient;

final class Queue
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
        if($conn == null) $conn = new RedisClient(['host' => '192.168.99.100', 'port' => 6379]);
        $this->_redisClient = $conn;
        $this->_redisLock = new Lock();
    }

    /**
     * @param $name 队列名称
     * @param $data 要入队的数据
     * @param int $afterInterval 延迟 N 秒后入队。 默认立即执行
     * @param int $timeout 超时时间（秒）
     * @param null $now 当前时间
     * @return bool
     * @desc 入队一个 Task
     */
    public function push($name, $data, $afterInterval = 0, $timeout = 10, $now = null)
    {
        if (empty($name) || empty($data)) return false;

        // 加锁
        if (!$this->_redisLock->lock($queueName = "Queue:$name", $timeout)) return false;

        // 入队时以当前时间戳为 score, 原因见 remove
        if ($now === null) $now = microtime(true);
        $score = $afterInterval + $now;
//        foreach ((array)$data as $item) {
        $this->_redisClient->zadd($queueName,[serialize($data)=>$score]);
//        }

        // 解锁
        $this->_redisLock->unlock($queueName);

        return true;
    }

    /**
     * @param $name 队列名称
     * @param $data 任务数据
     * @param int $timeout 超时时间（秒）
     * @return bool Task 是否成功出队. 返回 false 有可能是 Redis 操作失败, 但也有可能是 $score 与队列中的值不匹配(这表示该 Task 自从获取到本地之后被其他线程入队过)
     * @desc 移除一个 Task ，需指定 $data 和 $score 。 如果 $score 与队列中的匹配则出队，否则认为该 Task 已被重新入队过, 当前操作按失败处理
     */
    public function remove($name, $data, $timeout = 10)
    {
        if (empty($name) || empty($data)) return false;

        // 加锁
        if (!$this->_redisLock->lock($queueName = "Queue:$name", $timeout)) return false;

        $result = $this->_redisClient->zrem($queueName,$data);

        // 解锁
        $this->_redisLock->unlock($queueName);

        return $result;
    }

    /**
     * @param $name 队列名称
     * @param int $count 数量
     * @param int $timeout 超时时间（秒）
     * @return QueueElement[]|bool 队列元素列表。 false 表示操作失败。
     * @desc 获取队列顶部的若干个 Task 并将其出队
     */
    public function pop($name, $count = 1, $timeout = 0)
    {
        return $this->_toppop($name, $count, true, $timeout);
    }

    /**
     * @param $name 队列名称
     * @param int $timeout 超时时间（秒）
     * @return QueueElement|null 队列元素。 null 表示未获取到数据（队列为空）， false 表示操作失败
     * @desc 获取队列顶部的一个 Task 并将其出队
     */
    public function popOne($name, $timeout = 0)
    {
        return 0 === count($result = $this->_toppop($name, 1, true, $timeout)) ? null : $result[0];
    }

    private function _toppop($name, $count, $pop, $timeout)
    {
        if (empty($name) || $count < 1) return [];

        // 加锁
        if (!$this->_redisLock->lock($lockName = "Queue:$name", $timeout)) return false;

        $result = [];
        foreach ($this->_getRangeByScore($name, false, microtime(true), true, false, [0, $count]) as $data => $score) {
            $result [] = new QueueElement($data, floatval($score));
            if ($pop) $this->_redisClient->zrem($name, $data);
        }

        // 解锁
        $this->_redisLock->unlock($lockName);

        return $result;
    }

    /**
     * @param $key zset 的 key
     * @param null $startScore 起始 score, null 表示不限制
     * @param null $endScore 终止 score, null 表示不限制
     * @param bool $withScores 是否返回 score
     * @param bool $desc 是否按 score 降序查询
     * @param array $limit 查询范围: array(startIndex, count)
     * @return array
     */
    private function _getRangeByScore($key, $startScore = null, $endScore = null, $withScores = false, $desc = false, $limit = []) {
        if($startScore === null || $startScore === false) $startScore = $desc ? INF : -INF;
        if($endScore === null || $endScore === false) $endScore = $desc ? -INF : INF;

        $options = ['withscores' => $withScores];
        if($limit) $options['limit'] = $limit;

        if($desc) {
            return $startScore < $endScore ? [] : $this->_redisClient->zRevRangeByScore($key, $startScore, $endScore, $options);
        }
        else {
            return $startScore > $endScore ? [] : $this->_redisClient->zRangeByScore($key, $startScore, $endScore, $options);
        }
    }
}

class QueueElement
{
    /** @var string */
    public $data;

    /** @var double */
    public $score;

    public function __construct($data = null, $score = null)
    {
        $this->data = $data;
        $this->score = $score;
    }
}