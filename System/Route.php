<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/12 0012
 * Time: 16:25
 */

namespace System;

use System\Http\Request;
use System\Http\Response;

defined('DEFAULT_MODULE') or define('DEFAULT_MODULE','Default');
defined('DEFAULT_CONTROLLER') or define('DEFAULT_CONTROLLER','Default');
defined('DEFAULT_ACTION') or define('DEFAULT_ACTION','index');
class Route
{

    private $module = DEFAULT_MODULE;
    private $controller = DEFAULT_CONTROLLER;
    private $action = DEFAULT_ACTION;

    public function load(){

        Context::request(Ioc::getObject('request'));
        Context::response(Ioc::getObject('response'));
        $this->parseUrl();

        if(SYSTEM_MODE == MULTI_MODULE or SYSTEM_MODE == MULTI_DOMAIN) {
            $controllerClassName = 'Apps\\'.$this->getModule() . '\Controller\\' . $this->getController() . 'Controller';
        }else{
            $controllerClassName = 'Apps\\' . 'Controller\\' . $this->getController() . 'Controller';
        }

        if(!class_exists($controllerClassName)) throw new \Exception('所要访问的模块['.$controllerClassName.']不存在');
        $class = new \ReflectionClass($controllerClassName);
        if(!$class->hasMethod($this->action) && !$class->getMethod($this->action)->isPublic()) throw new \Exception('所要访问的方法['.$this->action.']不存在');
        $controller = new $controllerClassName();
        Context::controller($controller);

        return $this;
    }

    public function run(){
        $actionName = $this->action;
        Context::controller()->$actionName();
    }

    protected function parseUrl(){
        if(is_null($pathInfo = Request::getInstance()->server('PATH_INFO'))) return false;

        $pathInfoArr = explode('/',$pathInfo);
        if(SYSTEM_MODE == MULTI_MODULE or SYSTEM_MODE == MULTI_DOMAIN){
            if(isset($pathInfoArr[1]) && !empty($pathInfoArr[1])) $this->setModule(ucfirst($pathInfoArr[1]));
            if(isset($pathInfoArr[2]) && !empty($pathInfoArr[2])) $this->setController(ucfirst($pathInfoArr[2]));
            if(isset($pathInfoArr[3]) && !empty($pathInfoArr[3])) $this->setAction(lcfirst($pathInfoArr[3]));
        }else{
            if(isset($pathInfoArr[1]) && !empty($pathInfoArr[1])) $this->setController(ucfirst($pathInfoArr[1]));
            if(isset($pathInfoArr[2]) && !empty($pathInfoArr[2])) $this->setAction(lcfirst($pathInfoArr[2]));
        }

        return true;

    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

}