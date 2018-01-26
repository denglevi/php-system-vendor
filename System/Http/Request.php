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
    public $get;
    public $post;
    public $header;
    public $server;
    public $cookie;
    public $session;
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

    public function get($key = null,$default=null)
    {
        if (is_null($key)) return $this->get;
        if (isset($this->get[$key])) return $this->get[$key];

        return $default;
    }

    public function post($key = null,$default=null)
    {
        if (is_null($key)) return $this->post;
        if (isset($this->post[$key])) return $this->post[$key];

        return $default;
    }

    public function getAndPost($key = null)
    {
        if (is_null($key)) return array_merge($this->post, $this->get);
        if (!isset($this->get[$key])) return $this->get[$key];
        return $this->post($key);
    }

    public function header($key = null,$default=null)
    {
        if (is_null($key)) return $this->header;
        if (isset($this->header[$key])) return $this->header[$key];

        return $default;
    }

    public function cookie($key = null,$default=null)
    {
        if (is_null($key)) return $this->cookie;
        if (isset($this->cookie[$key])) return $this->cookie[$key];

        return $default;
    }

    public function session($key = null,$default=null)
    {
        if (is_null($key)) return $this->session;
        if (isset($this->session[$key])) return $this->session[$key];

        return $default;
    }

    public function server($key = null,$default=null)
    {
        if (is_null($key)) return $this->server;
        if (isset($this->server[$key])) return $this->server[$key];

        return $default;
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
}