<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23 0023
 * Time: 15:39
 */

namespace System;


class Env {
    /** 判断当前是否运行在 Linux 环境中 */
    static public function isLinux() { return PATH_SEPARATOR === ':'; }

    /** 判断当前是否运行在 CLI|STDIN 模式下 */
    static public function isCli() { return PHP_SAPI === 'cli' || defined('STDIN'); }

    /** 获取当前服务器的 IP 地址 */
    static public function serverIp() {
        static $value = null;
        if($value === null) {
            if(static::isLinux()) {
                @exec('ifconfig eth0 | sed -n \'s/^ *.*addr:\\([0-9.]\\{7,\\}\\) .*$/\\1/p\'', $array);
                if($tmp = array_shift($array)) {
                    $value = $tmp;
                    return $value;
                }
            }
            else {
                @exec('ipconfig', $array);
                if(preg_match_all('/IPv\\d.*?((?:\\d{1,3})\\.(?:\\d{1,3})\\.(?:\\d{1,3})\\.(?:\\d{1,3}))/', implode("\n", $array), $matches) && isset($matches[1][0])) {
                    $value = $matches[1][0];
                    return $value;
                }
            }

            if($controller = Context::controller()) {
                if($tmp = $controller->request->server('SERVER_ADDR')) {
                    $value = $tmp;
                    return $value;
                }
            }

            if($tmp = getenv('SERVER_ADDR')) {
                $value = $tmp;
                return $value;
            }

            $value = '?';
        }
        return $value;
    }

    /** 获取当前服务器的端口 */
    static public function serverPort() {
        static $value = null;
        if($value === null) {
            if(isset($_SERVER['SERVER_PORT'])) {
                $value = $_SERVER['SERVER_PORT'];
            }
            else if($tmp = getenv('SERVER_PORT')) {
                $value = $tmp;
            }
        }else $value = 0;
        return $value;
    }

    /** 获取当前服务器的 Host (ServerIp:Port) */
    static public function serverHost() { return self::serverIp().':'.self::serverPort(); }
}