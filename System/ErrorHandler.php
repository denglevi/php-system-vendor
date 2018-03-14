<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23 0023
 * Time: 15:33
 */

namespace System;


class ErrorHandler {
    static public function register() {
        set_error_handler([__CLASS__, 'onError']);
        set_exception_handler([__CLASS__, 'onException']);
        register_shutdown_function([__CLASS__, 'onShutdown']);
    }

    static public function onError($severity, $message, $file, $line) {
        echo sprintf("文件【%s】的第【%d】行出现错误:【%s】",$file,$line,$message);
    }

    static public function onException(\Exception $exception) {
        echo $exception->getMessage();
    }

    static public function onShutdown() {

    }

    static public function showError(\Exception $error) {

    }
}