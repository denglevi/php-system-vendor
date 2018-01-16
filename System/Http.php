<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/8 0008
 * Time: 17:10
 */

namespace System;


use GuzzleHttp\Client;

class Http extends Client
{
    /**
     * @return null|static
     */
    public static function & getInstance(){
        static $instance = null;
        if($instance == null) $instance = new static();
        return $instance;
    }

    /**
     * @param string $url
     * @param string $apiKey
     * @return string
     */
    public function getHttpDataContents($url='',$apiKey=''){

        $contents = $this->get($url,['headers'=>['apikey' => $apiKey]])->getBody()->getContents();

        return $contents;

    }
}