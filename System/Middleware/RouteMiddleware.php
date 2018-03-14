<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/5 0005
 * Time: 17:34
 */

namespace System\Middleware;

use System\Context;

class RouteMiddleware implements IMiddleware
{

    /**
     * @return bool
     */
    public function handle()
    {
        $pathInfo = Context::request()->server('PATH_INFO');
        if (!$pathInfo) return false;

        if (
            preg_match('/^\/product\/(HF[0-9A-z]{8})$/i', $pathInfo, $matches) ||
            preg_match('/^\/fund\/(HF[0-9A-z]{8})$/i', $pathInfo, $matches)
        ) {
            $fundId = array_pop($matches);
            Context::request()->get('fundId', $fundId);
            Context::request()->server('PATH_INFO', '/fund/show');
        }

        if (
            preg_match('/^\/company\/(CO[0-9A-z]{8})$/i', $pathInfo, $matches)
        ) {
            $companyId = array_pop($matches);
            Context::request()->get('companyId', $companyId);
            Context::request()->server('PATH_INFO', '/company/show');
        }

        return true;
    }
}