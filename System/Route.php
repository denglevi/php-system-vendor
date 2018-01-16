<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/12 0012
 * Time: 16:25
 */

namespace System;


class Route
{
    public function load(){
        //兼容老版本路由
        if( isset($_GET['mod']) && $_GET['mod'] && preg_match('/^[0-9A-z_\-]{1,10}$/i', $_GET['mod']) ) {
            if($_GET['mod'] == 'product') $_GET['mod'] = 'fund';
            define('BIND_CONTROLLER',$_GET['mod']);
            define('BIND_ACTION','show');
            if(isset($_GET['fund_id'])) $_GET['id'] = $_GET['fund_id'];
            if(isset($_GET['company_id'])) $_GET['id'] = $_GET['company_id'];
            if(isset($_GET['manager_id'])) $_GET['id'] = $_GET['manager_id'];
        }
    }
}