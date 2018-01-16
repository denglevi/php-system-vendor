<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 11:01
 */

namespace System\Task;

use function Hprose\Future\value;
use System\Configuration;
use System\Helper\StringHelper;
use System\Queue;
use System\Redis;
use Task\Task;
use Think\Exception;

final class Scheduler
{

    /**
     * 同时运行的任务调度器的最大数量
     *
     * @var int
     */
    public $maxThreadCount = 3;

    /**
     * 任务调度器的生命周期（秒）
     *
     * @var int
     */
    public $threadLifetime = 300;

    /**
     * 操作 Redis 或 Queue 的超时时间（秒）
     *
     */
    public $connectionTimeout = 3;

    public static function getInstance(){
        static $instance = null;
        if(null === $instance) $instance = new static();
        return $instance;
    }

    private function _getTaskSchedulerList(){
        return Configuration::getConfig('schedulerTask');
    }

    /**
     * @return int
     * @desc 获取正在运行的任务调度器的数量
     */
    public function getRunningThreadCount() { return count(Redis::getInstance()->keys('TaskScheduler:Thread:*')); }

    public function runTaskScheduler(){

        if($this->getRunningThreadCount() >= $this->maxThreadCount) throw new \Exception('任务处理进程已满!');

        $threadId = StringHelper::rand(6);

        Redis::getInstance()->hmset('TaskScheduler:Thread:'.$threadId,[
            'startTime'=>time(),
            'endTime'=>time()+$this->threadLifetime
        ]);

        $taskSchedulerList = $this->_getTaskSchedulerList();

        try{
            foreach($taskSchedulerList as $key => $value){

                $res = $this->initialize($value);
                if(empty($res)) continue;
                $res[0]->$res[1]();
                $queueName = $value['handler'][0];
                if(-1 == $value['interval']) continue;
                $value['startTime'] = date('Y-m-d H:i:s',time()+$value['interval']);
                Queue::instance()->push($queueName,$value,$value['interval']);
            }
            while (true){
                $this->_runTaskFromQueue();
            }

        }catch (\Exception $e){
            throw new Exception($e);
        }
    }

    private function _runTaskFromQueue() {

        $queue = Redis::getInstance()->keys('Queue:*');

        foreach($queue as $queueName){
            if(!($queueItem = Queue::instance()->popOne($queueName, $this->connectionTimeout))) return false;
            $data = @unserialize($queueItem->data);
            $task = $this->initialize($data);
            if(empty($task)) continue;
            $task[0]->$task[1]();
            $queueNameArr = explode(':',$queueName);
            Queue::instance()->push($queueNameArr[1],$data,$data['interval']);
        }

        return true;
    }

    public function initialize($value){
        $time = strtotime($value['startTime']);
        if($time > time()) return [];
        if(!isset($value['handler'][0])) return [];
        $className = '\Task\\'.$value['handler'][0];
        if(!$class = new $className()) return [];
        if(!$class instanceof Task) return [];
        if(isset($value['handler'][1]) && (new \ReflectionMethod($className,$value['handler'][1]))->isPublic()){
            return [$class,$value['handler'][1]];
        }else{
            return [$class,'exec'];
        }
    }
}