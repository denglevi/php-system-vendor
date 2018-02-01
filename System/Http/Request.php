<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19 0019
 * Time: 16:38
 */

namespace System\Http;


class Request
{
    protected $get;
    protected $post;
    protected $header;
    protected $server;
    protected $cookie;
    protected $session;
    public $segments = [];

    public function __construct()
    {
        $this->get = &$_GET;
        $this->post = &$_POST;
        $this->server = &$_SERVER;
        $this->cookie = &$_COOKIE;
        $this->session = &$_SESSION;

        foreach ($this->server as $key => $value) {
            if (0 === strncmp($key, 'HTTP_', 5)) {
                $this->header[strtolower(str_replace(['_', ' '], '-', substr($key, 5)))] = $value;
            }
        }
    }

    public static function getInstance()
    {
        static $instance = null;
        if (is_null($instance)) $instance = new static();

        return $instance;
    }

    public function getAndPost($key = null,$value=null)
    {
        if (is_null($key) && is_null($value)) return array_merge($this->post, $this->get);
        if (!is_null($value) && !is_null($key)) {
            $this->post[$key] = $value;
            $this->get[$key] = $value;
            return true;

        }
        if (!is_null($key) && is_null($value) && isset($this->post[$key])){
            if (!isset($this->get[$key])) return $this->get[$key];
            return $this->post($key);
        }

        if (is_null($key) && !is_null($value)) {
            $this->post = $value;
            $this->get = $value;
            return true;
        }

        return false;

    }

    public function __call($name, $arguments)
    {

        if(!in_array($name,['post','get','header','cookie','session','server'])) return false;

        $key = isset($arguments[0])?$arguments[0]:null;
        $value = isset($arguments[1])?$arguments[1]:null;

        $valuesRef = &$this->$name;

        if (is_null($key) && is_null($value)) return $valuesRef;
        if (!is_null($value) && !is_null($key)) {
            $valuesRef[$key] = $value;
            return true;
        }

        if (!is_null($key) && is_null($value) && isset($valuesRef[$key])) {
            return $valuesRef[$key];
        }

        if (is_null($key) && !is_null($value)) {
            $valuesRef = $value;
            return true;
        }

        return false;
    }

    /** 获取客户端 IP 地址 */
    public function clientIp()
    {
        if (isset($this->server['HTTP_X_FORWARDED_FOR'])) {
            return $this->server['HTTP_X_FORWARDED_FOR'];
        } else if (isset($this->server['HTTP_CLIENT_IP'])) {
            return $this->server['HTTP_CLIENT_IP'];
        } else {
            return $this->server['REMOTE_ADDR'];
        }
    }

    public function scheme()
    {
        return $this->server('REQUEST_SCHEME');
    }

    public function baseUrl()
    {
        return sprintf("%s://%s", $this->scheme(), $this->server('HTTP_HOST'));
    }

    public function pathInfo()
    {
        var_dump($this->server);exit;
        return $this->server('PATH_INFO');
    }

    public function queryString()
    {
        return $this->server('QUERY_STRING');
    }

    public function uri()
    {
        return $this->server('REQUEST_URI');
    }

    /**
     * @param \IMiddleware $middleware
     * @return $this
     */
    public function with(\IMiddleware $middleware){
        $middleware->handle();

        return $this;
    }
}