<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/14 0014
 * Time: 11:58
 *  依赖注入
 * 代码 Ioc::register('Abc', 'MyAbc'); $obj = Ioc::getObject('Abc') 会返回 MyAbc 的实例， 等同于 $obj = new MyAbc();
 * 代码 Ioc::register('Abc', function(array $constructorParams, mixed $caller){ return new MyAbc2(); }); $obj = Ioc::getObject('Abc') 会返回 MyAbc2 的实例， 等同于 $obj = new MyAbc2();
 * 通过这样的方式可以实现依赖注入， 底层框架中部分模块使用了 Ioc::getObject 代替了 new 操作符， 以便上层可以灵活改变类的依赖关系。
 */

namespace System;


final class Ioc
{

    /**
     * @var array
     * @desc 存储绑定对象
     */
    private static $instances = [];

    /**
     * Ioc constructor.
     * @desc 私有化构造函数
     */
    private function __construct()
    {
    }

    /**
     * @param $abstract
     * @param $value
     * @desc 对象注入
     */
    public static function bind($abstract, $value)
    {
        if ($value) self::$instances[$abstract] = [$value, false];
    }

    /**
     * @param $abstract
     * @param $value
     * @desc 单例对象注入
     */
    public static function singleton($abstract, $value)
    {
        if ($value) self::$instances[$abstract] = [$value, true];
    }

    /**
     * @param $abstract
     * @param array $constructParams
     * @return mixed|object
     * @throws \Exception
     * @desc 获取注入对象
     */
    public static function getObject($abstract, $constructParams = [])
    {
        if (isset(self::$instances[$abstract])) {
            list($value, $shared) = self::$instances[$abstract];
            if (!is_bool($shared)) return $shared;

            if ($value instanceof \Closure) {
                $object = $value($constructParams);
            } else if (is_string($value)) {
                if (!class_exists($value)) throw new \Exception('要绑定的类不存在');
                $class = new \ReflectionClass($value);
                if ($class->isAbstract()) throw new \Exception('要反射的类不能为纯虚类');
                if ($constructFunc = $class->getConstructor()) {
                    if ($constructFunc->getNumberOfRequiredParameters() != count($constructParams))
                        throw new \Exception($value . '构造函数的参数个数不正确!');
                    $object = $class->newInstanceArgs($constructParams);
                } else {
                    $object = $class->newInstanceWithoutConstructor();
                }
            }

            if ($shared) self::$instances[$abstract][1] = $object;
            return $object;
        }

        if (!class_exists($abstract)) throw new \Exception('要绑定的类不存在');
        $class = new \ReflectionClass($abstract);
        if ($class->isAbstract()) throw new \Exception('要反射的类不能为纯虚类');
        if ($constructFunc = $class->getConstructor()) {
            if ($constructFunc->getNumberOfRequiredParameters() != count($constructParams))
                throw new \Exception($abstract . '构造函数的参数个数不正确!');
            $object = $class->newInstanceArgs($constructParams);
        } else {
            $object = $class->newInstanceWithoutConstructor();
        }

        return $object;
    }

}