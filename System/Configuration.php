<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/30 0030
 * Time: 18:19
 */
namespace System;
// 定义系统公共配置文件目录
defined('COMMON_ENV_CONFIG_PATH') or define('COMMON_ENV_CONFIG_PATH',ROOT.DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.ENV.DIRECTORY_SEPARATOR);
defined('SYSTEM_MODE') or define('SYSTEM_MODE',1);

/**
 * Class Configuration
 * @package System
 */
class Configuration
{
    private static $_config = null;
    private $_domainConfig = null;
    private static $_commonConfigFiles = null;
    private static $_subProjectFiles = null;
    const MULTI_DOMAIN = 1;
    const SINGLE_MODULE = 2;
    const MULTI_MODULE = 3;

    public function __construct()
    {
        if(self::MULTI_DOMAIN == SYSTEM_MODE && file_exists($domainFilePath = COMMON_ENV_CONFIG_PATH.'domain.php')){
            $this->_domainConfig = require($domainFilePath);
        }

    }

    /**
     * @param $name
     * @param bool $refresh
     * @return null
     * @desc 用户获取系统配置参数
     */
    public static function getConfig($name=null,$refresh=false){
        if(is_null(self::$_config)) self::loadConfig();
        if(is_null($name)) return self::$_config;
//        $key = strtoupper($name);
        if(!isset(self::$_config[$name])) return null;
        return self::$_config[$name];
    }

    /**
     * @param array $config
     * @desc 用于动态设置系统参数
     */
    public static function setConfig(array $config) {
        $func = function($array1, $array2)use(&$func){
            if(!is_array($array1) || !is_array($array2)) return $array2;
            foreach($array2 as $key => $value) {
                $array1[$key] = isset($array1[$key]) ? $func($array1[$key], $value) : $value;
                //兼容TP框架
                C($key,$value);
            }
            return $array1;
        };
        self::$_config = $func(self::$_config, $config);
    }

    /**
     * 加载系统配置
     * @return array
     */
    public function loadConfig(){
        //获取公共配置项
        self::$_commonConfigFiles = self::_getCommonConfigFiles();
        //获取子项目配置项
        self::$_subProjectFiles = self::_getSubProjectConfigFiles();

        $configFiles = array_merge(self::$_commonConfigFiles,self::$_subProjectFiles);
        self::$_config = [];
        foreach($configFiles as $file){
            if(!file_exists($file)) continue;
            $config = require $file;
            if(!is_array($config)) continue;
            self::$_config = array_merge(self::$_config,$config);
        }

        return self::$_config;
    }

    /**
     * @return array
     * @throws \Exception
     * @desc 获取PPW项目的公共配置文件
     */
    private function _getCommonConfigFiles(){
        if(!is_dir(COMMON_ENV_CONFIG_PATH)) throw new \Exception('请检查【'.COMMON_ENV_CONFIG_PATH.'】路径是否存在!');
        $commonConf = [];
        self::_getFileList($commonConf,COMMON_ENV_CONFIG_PATH);

        return $commonConf;
    }

    /**
     * @return array
     * @throws \Exception
     * @desc 获取不同项目下面的配置文件
     */
    private function _getSubProjectConfigFiles(){

        //如果是命令行模式下面，通过命令行下面获取参数，不绑定域名
        if(PHP_SAPI === 'ncli') {
            getopt('m:b');
            print_r(ini_get('register_argc_argv'));exit;
        }else{
            if(self::MULTI_DOMAIN == SYSTEM_MODE){
                if(!$this->_domainConfig || !$this->_domainConfig['APP_SUB_DOMAIN_DEPLOY']) return [];
                //获取相应访问域名下面对应的目录名称
                $projectName = $this->_domainConfig['APP_SUB_DOMAIN_RULES'][$_SERVER['HTTP_HOST']];
                if(!$projectName) throw new \Exception('请检查 APP_SUB_DOMAIN_RULES 配置项是否和域名正确绑定!');
            }elseif (self::SINGLE_MODULE == SYSTEM_MODE){
                return [];
            }elseif (self::MULTI_MODULE == SYSTEM_MODE){
                return [];
            }else{
                return [];
            }

        }
        define('MODULE_NAME',$projectName);
        //根据目录名称获取具体项目的配置文件
        $subProjectConf = [];
        $subProjectConfPath = APP_PATH.MODULE_NAME.DIRECTORY_SEPARATOR.'Conf'.DIRECTORY_SEPARATOR.ENV;
        if(!file_exists($subProjectConfPath)) return $subProjectConf;
        if(!is_dir($subProjectConfPath)) throw new \Exception('请检查【'.$subProjectConfPath.'】是否存在!');

        self::_getFileList($subProjectConf,$subProjectConfPath);

        return $subProjectConf;
    }

    /**
     * @param $arr
     * @param $path
     */
    private function _getFileList(&$arr,$path){
        foreach(new \DirectoryIterator($path) as $fileInfo){
            if(is_dir($filePath = $fileInfo->getRealPath())) continue;
            if(0 !== strcasecmp('php',$fileInfo->getExtension())) continue;
            array_push($arr,$filePath);
        }
    }
}