<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22 0022
 * Time: 11:37
 */

namespace System\Db;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DbManager;
use Illuminate\Events\Dispatcher;

class Db
{

    public function load(){
        $db = new DbManager();
        $db->addConnection([
            'driver'    => 'mysql',
            'host'      => '211.154.153.13',
            'database'  => 'rz_new_data_channel',
            'username'  => 'webdbsu',
            'password'  => '4z&i44sL$M6)<DAlJ^1z',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ]);

        $db->setEventDispatcher(new Dispatcher(new Container));
        $db->setAsGlobal();
        $db->bootEloquent();
    }
}