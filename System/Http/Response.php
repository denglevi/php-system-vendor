<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19 0019
 * Time: 16:38
 */

namespace System\Http;


class Response
{

    private $_ended = false;
    private $_html = '';

    public function __construct()
    {
    }

    public function header($key, $value)
    {
        header("{$key}:{$value}");

        return $this;
    }

    public function status($statusCode)
    {
        if ($this->_ended) return $this;
        $reason = StatusCode::getStatusReason($statusCode);
        header("HTTP/1.1 $statusCode $reason", true, $statusCode);

        return $this;
    }

    public function redirect($url){
        if($this->_ended) return $this;

        $this->status(301);
        $this->header('Location',$url);

        $this->end();

        return $this;
    }

    public function endRedirect($url){
        $this->redirect($url)->end();
    }

    public function write($html=null){
        $this->_html = $html;
        echo $this->_html;
        return $this;
    }

    public function end($html=null){
        if($this->_ended) return $this;
        if(function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        $this->write($html);
        $this->_ended = true;
        return $this;
    }

    public function isJson(){

    }

    public function isJsonRedirect($url){

    }
}