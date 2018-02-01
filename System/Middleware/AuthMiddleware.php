<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/29 0029
 * Time: 16:33
 */

use System\Context;
use System\Ioc;
class AuthMiddleware implements IMiddleware
{

    public $excludeMethods = ['login'];

    public function handle()
    {
        $action = Ioc::getObject('route')->getAction();
        if(!empty(Context::request()->session('userInfo')) && !in_array($action,$this->excludeMethods)){
            Context::response()->endRedirect('/User/login');
        }
    }
}