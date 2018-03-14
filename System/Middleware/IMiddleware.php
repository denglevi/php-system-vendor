<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/29 0029
 * Time: 16:33
 */
namespace System\Middleware;
interface IMiddleware
{
    public function handle();
}