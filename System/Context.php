<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22 0022
 * Time: 10:49
 */

namespace System;


use System\Controller\Controller;
use System\Http\Request;
use System\Http\Response;

class Context
{
    /**
     * @return Request
     */
    public static function request(){
       return self::_getSet(__FUNCTION__,func_get_args());
    }

    /**
     * @return Response
     */
    public static function response(){
        return self::_getSet(__FUNCTION__,func_get_args());
    }

    /**
     * @return Controller
     */
    public static function controller(){
        return self::_getSet(__FUNCTION__,func_get_args());
    }

    public static function reset(){
        return self::_getSet(__FUNCTION__);
    }

    private static function _getSet($name,$args=null){
        static $instanceMap = [];

        // get
        if($name !== 'reset' && 0 === count($args)) {
            return isset($instanceMap[$name]) ? $instanceMap[$name] : null;
        }

        // 检查调用堆栈， 只允许在 Route->load() 中被调用。
//        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2];
//        if(!isset($backtrace['class']) || !isset($backtrace['function']) || $backtrace['function'] !== 'run' || ($backtrace['class'] !== 'UrlRunner' && $backtrace['class'] !== Route::class)) {
//            throw new \LogicException('禁止手动调用 '.__CLASS__."::$name 方法");
//        }

        // reset
        if($name === 'reset') {
            return $instanceMap = [];
        }

        // 检查类型
        static $instanceClassMap = [
            'controller' => Controller::class,
            'request' => Request::class,
            'response' => Response::class,
        ];

        if(!is_a($obj = $args[0], $className = $instanceClassMap[$name])) throw new \LogicException(__CLASS__."::$name 不是一个 '$className' 对象");

        // 设置值
        $instanceMap[$name] = $obj;
    }
}