<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 10:17
 */

namespace System\Queue;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class RabbitQueue implements IQueue
{

    const HOST = '192.168.1.76';
    const PORT = 5672;
    const USERNAME = 'admin';
    const PASSWORD = 'admin';

    public $connection = null;
    public $channel = null;

    /**
     * @return null|static
     */
    public static function getInstance(){
        static $instance = null;
        if(null == $instance) $instance = new static();

        return $instance;
    }

    public function __construct()
    {
        $this->connection  = new AMQPStreamConnection(self::HOST,self::PORT,self::USERNAME,self::PASSWORD);
    }

    /**
     * @param $channelName
     * @param Message $data
     * @return bool
     */
    public function push($channelName,Message $data)
    {
        if(false == $data instanceof Message) return false;
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($channelName,false,false,false,false);
        $data = serialize($data);
        $message = new AMQPMessage($data);
        $this->channel->basic_publish($message,'',$channelName);

        return true;
    }

    /**
     * @param $channelName
     * @return Message
     */
    public function pop($channelName)
    {
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($channelName,false,false,false,false);
        $res = $this->channel->basic_get($channelName);
        if(is_null($res)) return new Message();
        $this->channel->basic_ack($res->delivery_info['delivery_tag']);
        /** @var Message $message */
        $message = unserialize($res->body);
        return $message;
    }

    /**
     * @param $channelName
     * @return bool
     */
    public function remove($channelName)
    {
        $this->connection->channel()->queue_delete($channelName);
        return true;
    }


    public function __destruct()
    {
        if(null != $this->channel) $this->channel->close();
        if(null != $this->connection) $this->connection->close();
    }
}