<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/31 0031
 * Time: 15:17
 * Desc: 系统启动文件，初始化基础数据
 */
namespace System;
define('ENVIRONMENT_DEV', 'dev');
define('ENVIRONMENT_TEST', 'test');
define('ENVIRONMENT_PRE', 'pre');
define('ENVIRONMENT_PROD', 'prod');
defined('ROOT') or define('ROOT',dirname(__DIR__));
class Bootstrap
{
    public static function run(){

        self::init();
        /** 加载错误异常处理类 */
        if(class_exists(ErrorHandler::class)) ErrorHandler::register();
        /** 配置处理组件 */
        Ioc::singleton('configuration',Configuration::class);
        /** 路由处理组件 */
        Ioc::singleton('route',Route::class);
        /** 消息队列组件 */
        Ioc::singleton('queue',Queue\RabbitQueue::class);
        /** 数据库组件 */
        Ioc::singleton('database',Db\Db::class);

        Ioc::singleton('request',Http\Request::class);
        Ioc::singleton('response',Http\Response::class);

        Ioc::getObject('configuration')->loadConfig();
        Ioc::getObject('database')->load();

        /** @var Route $app */
        $app = Ioc::getObject('route')->load();

        if(!Env::isCli()) $app->run();
    }

    private static function init(){
        if(!file_exists($localConfigFile = ROOT.DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.'local.php')) {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            exit('没有找到 '.ROOT.'/Config/local.php 。 该文件会由发布脚本自动创建。 如果当前是开发环境，请参考 '.ROOT.'/config/local.sample.php 手动创建。');
        }
        if(!file_exists($autoloadFile = ROOT.'/vendor/autoload.php')){
            header("HTTP/1.1 503 Service Unavailable",true,503);
            exit('缺少自动加载文件，请执行composer install安装相应的库文件!');
        }
        require $autoloadFile;
        require $localConfigFile;
        switch (ENV) {
            case ENVIRONMENT_DEV:
            case ENVIRONMENT_TEST:
                ini_set('display_errors', 1);
                error_reporting(-1);
                break;
            case ENVIRONMENT_PRE:
            case ENVIRONMENT_PROD:
                ini_set('display_errors', 0);
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
                break;
            default:
                header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
                exit('无法识别的 ENV('.ENV.')');
                break;
        }
    }
}