<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22 0022
 * Time: 10:36
 */

namespace System\Controller;


use System\Configuration;
use System\Context;
use System\Ioc;
use System\Route;

abstract class Controller
{
    /**
     * @var null|\System\Http\Request
     */
    public $request = null;
    /**
     * @var null|\System\Http\Response
     */
    public $response = null;

    /**
     * @var \Smarty
     */
    public $smarty = null;

    public function __construct()
    {
        $this->request = Context::request();

        if ($this->response = Context::response()) {
            $this->response->header('Cache-Control', 'no-store');
            /** @var Route $route */
            $route = Ioc::getObject('route');
            if ($this->isJsonResponse($route->getAction())) $this->response->isJson(true);
        }

        $this->smarty = new \Smarty();
        $this->smarty->setTemplateDir(Configuration::getConfig('smarty')['templateDir']);
        $this->smarty->setLeftDelimiter(Configuration::getConfig('smarty')['leftDelimiter']);
        $this->smarty->setRightDelimiter(Configuration::getConfig('smarty')['rightDelimiter']);
    }

    protected function isJsonResponse($method)
    {
        return 0 === strcasecmp(substr($method, -3), 'api');
    }

    public function debug()
    {
        return $this->_debug;
    }

    private $_debug = false;

    public function display($controller = null,$action = null,$extension = 'html')
    {
        if(is_null($controller)) $controller = substr(get_class(Context::controller()),16,-10);
        $trace = debug_backtrace();
        if (is_null($action)) $action = $trace[1]['function'];
        $this->smarty->display(sprintf("%s/%s.%s",$controller,$action,$extension));
    }
}