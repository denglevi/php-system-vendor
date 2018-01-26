<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 16:57
 */

namespace System\Queue;


final class Message implements \Serializable
{
    const IS_STRING = 'string';
    const IS_BOOL = 'bool';
    const IS_ARRAY = 'array';
    const IS_OBJECT = 'object';
    const IS_NUMBER = 'number';
    const IS_UNDEFINED = 'undefined';
    const IS_NULL = 'null';

    protected $messageType = null;
    protected $message = null;

    public function __construct($message=null)
    {
        $this->message = $message;
    }

    /**
     * @param $message
     */
    public function setMessage($message){
        $this->message = $message;
    }

    /**
     * @return null
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * @param $type
     */
    public function setMessageType($type){
        $this->messageType = $type;
    }

    /**
     * @return null|string
     */
    public function getMessageType(){
        if(!is_null($this->messageType)) return $this->messageType;

        if(is_string($this->message)) return self::IS_STRING;
        if(is_numeric($this->message)) return self::IS_NUMBER;
        if(is_object($this->message)) return self::IS_OBJECT;
        if(is_bool($this->message)) return self::IS_BOOL;
        if(is_array($this->message)) return self::IS_ARRAY;
        if(is_null($this->message)) return self::IS_NULL;

        return self::IS_UNDEFINED;
    }

    /**
     * @return string
     */
    public function serialize()
    {
       return serialize($this->message);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->message = unserialize($serialized);
    }
}