<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/29 0029
 * Time: 16:33
 */
namespace System\Middleware;
use Apps\Model\UserModel;
use System\Context;
use System\Helper\MessageHelper;
use System\Ioc;
class AuthMiddleware implements IMiddleware
{

    public $excludeMethods = ['login'];

    public function handle()
    {
        $action = Ioc::getObject('route')->getAction();
        //@todo 主动登录
        if(!UserModel::getInstance()->isLogin()) UserModel::getInstance()->login();
//        Context::request()->session(null);
        if(0 === strncasecmp(substr($action,-3,3),'Api',3)){
            if(empty(Context::request()->session('userInfo'))) Context::response()->endJson(MessageHelper::UN_LOGIN_CODE,MessageHelper::UN_LOGIN_MSG);
        }
//        if(!empty(Context::request()->session('userInfo')) && !in_array($action,$this->excludeMethods)){
//            Context::response()->endRedirect('/User/login');
//        }
    }
}