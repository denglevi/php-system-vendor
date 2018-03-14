<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/24 0024
 * Time: 9:38
 */
namespace System\Model;
use System\Configuration;
use System\Redis;

class Model extends \Illuminate\Database\Eloquent\Model
{

    protected $enableCached = false;
    protected $cachedTime = 0;
    protected $cachedKey = null;


    /**
     * @return static
     */
    public static function getInstance(){
        static $instance;
        if(null == $instance) $instance = new static();

        return $instance;
    }

    /**
     * @return \Illuminate\Support\Collection|mixed|string
     */
    public function get(){
        if(!$this->enableCached) return parent::get();
        if($data = Redis::getInstance()->get($this->cachedKey)) return unserialize($data);

        $data = parent::get();
        if(is_null($this->cachedKey)) $this->cachedKey = sha1($this->toSql().implode('',$this->getBindings()));
        Redis::getInstance()->set($this->cachedKey,serialize($data),null,$this->cachedTime);

        return $data;
    }

    /**
     * @param null $timeout
     * @param null $key
     * @return $this
     */
    public function enableCached($timeout=null,$key=null){
        $this->enableCached = true;
        if(is_null($timeout) && is_null($key)){
            $this->cachedTime = Configuration::getConfig('DATA_CACHE_TIME');
        } else if(is_null($key) && !is_null($timeout)) {
            $this->cachedTime = $timeout;
        } else if(is_null($timeout) && !is_null($key)){
            $this->cachedTime = Configuration::getConfig('DATA_CACHE_TIME');
            $this->cachedKey = $key;
        } else if(!is_null($timeout) && !is_null($key)){
            $this->cachedTime = $timeout;
            $this->cachedKey = $key;
        }

        return $this;
    }

    /**
     * @param $str
     * @param $bindData
     * @param string $mark
     * @return string
     */
    protected function dumpSql($str,$bindData,$mark='?'){

        if(!$position = stripos($str,$mark)) {return $str;}
        $headStr = substr($str,0,$position);
        $footStr = substr($str,$position+1);
        $val = array_shift($bindData);
        if(is_string($val)) $val = sprintf("'%s'",$val);
        $str = $headStr.$val.$footStr;

        return $this->dumpSql($str,$bindData,$mark);
    }
}