<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 16:57
 */

namespace System\Queue;


final class Message
{
    protected $messageType = null;
    protected $message = null;

    public function __construct($message=null)
    {
        $this->message = $message;
    }

    public function setMessage($message){
        $this->message = $message;
    }

    public function getMessage(){
        return $this->message;
    }

    public function setMessageType($type){
        $this->messageType = $type;
    }

    public function getMessageType(){
        if(!is_null($this->messageType)) return $this->messageType;

        if(is_string($this->message)) return 'string';
        if(is_numeric($this->message)) return 'number';
        if(is_object($this->message)) return 'object';
        if(is_bool($this->message)) return 'bool';
        if(is_array($this->message)) return 'array';

        return null;
    }
}