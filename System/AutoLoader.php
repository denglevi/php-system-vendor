<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23 0023
 * Time: 16:04
 */

namespace System;


class AutoLoader
{
    /**
     * Stores namespaces as key, and path as values.
     *
     * @var array
     */
    protected $psr4 = [];

    /**
     * Stores class name as key, and path as values.
     *
     * @var array
     */
    protected $classmap = [];

    public function __construct(array $config = null) {
        if($config !== null && 0 !==count($config)) $this->initialize($config);
    }

    /**
     * Reads in the configuration array (described above) and stores
     * the valid parts that we'll need.
     *
     * @param $config
     */
    public function initialize(array $config) {
        if(isset($config['psr4'])) $this->psr4 = $config['psr4'];
        if(isset($config['classmap'])) $this->classmap = $config['classmap'];
    }

    /**
     * Register the loader with the SPL autoloader stack.
     *
     */
    public function register() {
        spl_autoload_extensions('.php,.inc');
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    /**
     * 加载指定的类
     *
     * @param string $class
     */
    public function loadClass($class) {
        $class = trim($class, '\\');
        $includePath = set_include_path(ROOT.PATH_SEPARATOR.get_include_path());
        try {
            if(isset($this->classmap[$class])) {
                if(file_exists($file = $this->classmap[$class])) {
                    require_once $file;
                    return true;
                }
                else if(!is_absolute_path($file) && file_exists($tmp = ROOT.'/'.ltrim($file, '\\/'))) {
                    require_once $tmp;
                    return true;
                }
            }

            return $this->loadInNamespace($class);
        }
        finally {
            set_include_path($includePath);
        }
        return false;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name
     *
     * @return mixed The mapped file name on success, or boolean false on fail
     */
    protected function loadInNamespace($class) {
        if(strpos($class, '\\') === false) return false;
        foreach($this->psr4 as $namespace => $directories) {
            $namespace = trim($namespace, '\\/');
            if(0 === strncmp($class, $namespace.'\\', $len = strlen($namespace) + 1)) {
                $path = substr($class, $len);
                if(DIRECTORY_SEPARATOR !== '\\') $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
                foreach((array)$directories as $directory) {
                    if(file_exists($file = rtrim($directory, '\\')."/$path.php")) {
                        require_once $file;
                        return true;
                    }
                    else if(!is_absolute_path($file) && file_exists($tmp = ROOT.'/'.ltrim($file, '\\/'))) {
                        require_once $tmp;
                        return true;
                    }
                }
            }
        }
        return false;
    }

    // --------------------------------------------------------------------
    /**
     * Registers a namespace with the autoloader.
     *
     * @param $namespace
     * @param $path
     *
     * @return $this
     */
    public function addNamespace($namespace, $path) {
        if(isset($this->psr4[$namespace])) {
            if (is_string($this->psr4[$namespace])) {
                $this->psr4[$namespace] = [$this->psr4[$namespace], $path];
            }
            else {
                $this->psr4[$namespace] []= $path;
            }
        }
        else {
            $this->psr4[$namespace] = [$path];
        }
        return $this;
    }

    /**
     * Removes a single namespace from the psr4 settings.
     *
     * @param $namespace
     *
     * @return $this
     */
    public function removeNamespace($namespace) { unset($this->psr4[$namespace]); return $this; }

    /**
     * Registers a class with the autoloader.
     *
     * @param $class
     * @param $path
     *
     * @return $this
     */
    public function addClass($class, $path) {
        $this->classmap[$class] = $path;
        return $this;
    }

    /**
     * Registers multi classes with the autoloader.
     *
     * @param $array
     *
     * @return $this
     */
    public function addClassBatch($array) {
        $this->classmap = 0 === count($this->classmap) ? $array : array_map($this->classmap, $array);
        return $this;
    }
}