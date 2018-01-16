<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 10:16
 */

namespace System\Queue;


interface IQueue
{
    /**
     * @param $channelName
     * @param $data
     * @return mixed
     */
    public function push(/** string */$channelName,Message $data);

    /**
     * @param $channelName
     * @return Message
     */
    public function pop(/** string */$channelName);

    /**
     * @param $channelName
     * @return mixed
     */
    public function remove(/** string */$channelName);
}