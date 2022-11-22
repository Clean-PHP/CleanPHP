<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core\base
 * Class Error
 * Created By ankio.
 * Date : 2022/11/9
 * Time : 23:07
 * Description :
 */

namespace core\base;

use core\App;
use core\engine\JsonEngine;
use core\engine\ResponseEngine;
use core\engine\ViewEngine;
use core\event\EventManager;
use core\exception\ExitApp;
use core\file\Log;
use Throwable;

class Error
{

    public static function register()
    {
        $old_error_handler = set_error_handler([__CLASS__, 'appError'], E_ALL);
        set_exception_handler([__CLASS__, 'appException']);
    }


    /**
     *
     * App异常退出
     * @param $e Throwable
     */
    public static function appException(Throwable $e)
    {
        //捕获异常后清除数据
        error_clear_last();
        if ($e instanceof ExitApp) {
            App::$debug && Log::record("Frame", sprintf("框架执行退出: %s", $e->getMessage()));
            return;//Exit异常不进行处理
        }
        try{
            self::err($e->getMessage(),$e->getTrace(),get_class($e));
        }catch (ExitApp $e){
            App::$debug && Log::record("Frame", sprintf("框架执行退出[Exception]: %s", $e->getMessage()));
        }
    }

    /**
     * 报错退出
     * @param string $msg 错误信息
     * @param array $errInfo 堆栈
     * @param string $log_tag 记录日志的tag
     * @throws ExitApp
     */
    public static function err(string $msg, array $errInfo = [],string $log_tag = "ErrorInfo")
    {
            Log::record($log_tag, $msg,Log::TYPE_ERROR);
            $traces = sizeof($errInfo) === 0 ? debug_backtrace() : $errInfo;

            if ($dump = ob_get_contents()) {
                ob_end_clean();
            }
            //判断是否有应用进行了异常处理
            if (EventManager::trigger("__on_system_error__", $msg, true)) return;

            $engine = self::getEngine($result);
            if($result!==null){
                (new Response())->render($result, 200, $engine->getContentType())->send();
            }else if (App::$debug) {
                (new Response())->render($engine->renderError($msg, $traces, $dump,$log_tag), 200, $engine->getContentType())->send();
            } else {
                (new Response())->render($engine->renderMsg(true, 404, lang("404 Not Found"), lang("您访问的资源不存在。"), 5, "/", lang("立即跳转")), 404, $engine->getContentType())->send();
            }
    }

    /**
     * 获取渲染器
     * @return JsonEngine|ResponseEngine|ViewEngine|null
     */
    private static function getEngine(&$result){
        $__module = Variables::get("__request_module__",'');
        $__controller = Variables::get("__request_controller__",'');
        $__action = Variables::get("__request_action__",'');
        if($__module==='') return App::getEngine();
        $controller = 'app\\controller\\' . $__module . '\\' . ucfirst($__controller);
        $base = 'app\\controller\\' . $__module . '\\BaseController';
        $_controller_exist = class_exists($controller);
        if(!$_controller_exist && class_exists($base)){
            $controller = $base;
            $_controller_exist = true;
        }
        if($_controller_exist){
            /**
             * @var $obj Controller
             */
            $obj = new $controller($__module, $__controller, $__action);
            $result = $obj->eng()->onControllerError();

            return $obj->eng();
        }
        return App::getEngine();
    }

    /**
     * 报错退出
     * @param int $errno
     * @param string $err_str
     * @param string $err_file
     * @param int $err_line
     * @return bool
     */
    public static function appError(int $errno, string $err_str, string $err_file = '', int $err_line = 0): bool
    {
        $msg = "ERROR";
        if ($errno == E_WARNING) {
            $msg = "WARNING";
        }
        if ($errno == E_NOTICE) {
            $msg = "NOTICE";
        }
        if ($errno == E_STRICT) {
            $msg = "STRICT";
        }
        if ($errno == 8192) {
            $msg = "DEPRECATED";
        }
        try{

            self::err("$msg: $err_str in $err_file on line $err_line");
       }catch (ExitApp $e){
            App::$debug && Log::record("Frame", sprintf("框架执行退出[appError]: %s", $e->getMessage()));
            }
        return false;
    }

    /**
     * app正常退出前检查异常
     */
    public static function appShutdown()
    {
        if ($err = error_get_last())
            try{
                self::err("Fatal error: {$err['message']} in {$err['file']} on line {$err['line']}");
            }catch (ExitApp $e){
                App::$debug && Log::record("Frame", sprintf("框架执行退出[shutDown]: %s", $e->getMessage()));
            }

    }

}
