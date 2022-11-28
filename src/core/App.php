<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: core
 * Class App
 * Created By ankio.
 * Date : 2022/11/9
 * Time : 12:40
 * Description :
 */

namespace core;

use core\base\Controller;
use core\base\Error;
use core\base\Lang;
use core\base\Loader;
use core\base\MainApp;
use core\base\Response;
use core\base\Route;
use core\base\Variables;
use core\config\Config;
use core\engine\JsonEngine;
use core\engine\ResponseEngine;
use core\engine\ViewEngine;
use core\event\EventManager;
use core\exception\ControllerError;
use core\exception\ExitApp;
use core\file\Log;
use core\process\Async;


class App
{
    public static bool $debug = false;//是否调试模式
    public static bool $cli = false;//是否命令行模式
    protected static $engine = null;//输出引擎
    public static bool $exit = false;//标记是否退出运行
    /**
     * @var $app ?MainApp
     */
    private static ?MainApp $app = null;


    /**
     * @param bool $debug
     * @throws ExitApp
     */
    static function run(bool $debug)
    {

        error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
        ini_set("display_errors", "Off");
        if(version_compare(PHP_VERSION,'7.4.0','<')){
            self::exit("请使用PHP 7.4以上版本运行该应用", true);
        }
        //禁用错误提醒
        App::$debug = $debug;
        self::$cli = !isset($_SERVER["SERVER_NAME"]);

        define("DS", DIRECTORY_SEPARATOR);//定义斜杠符号
        define("APP_CORE", APP_DIR . DS . 'core' . DS);//定义程序的核心目录
        include_once APP_CORE . DS . "helper.php";//载入内置助手函数
        include_once APP_CORE . "base" . DS . "Variables.php";// 加载变量
        include_once APP_CORE . "base" . DS . "Loader.php";// 加载自动加载器

        App::$debug && Variables::set('__frame_start__', microtime(true));


        try {
            Loader::register();// 注册自动加载

            App::$debug && Log::record("Frame", "框架启动...");


            if(!is_dir(Variables::getCachePath()))mkdir(Variables::getCachePath(),0777,true);

            Config::register();// 加载配置文件

            if (self::$debug) {
                if (self::$cli)
                    Log::record("Request", "命令行启动框架", Log::TYPE_WARNING);
                else
                    Log::record("Request", $_SERVER["REQUEST_METHOD"] . " " . $_SERVER["REQUEST_URI"]);
            }

            Lang::register();//加载语言文件

            Error::register();// 注册错误和异常处理机制

            Async::register();//异步任务注册

            $app = "\app\Application"; //入口初始化

            if (class_exists($app) && ($imp = class_implements($app)) && in_array(MainApp::class, $imp)) {
                self::$app = new $app();
                self::$app->onRequestArrive();
            }

            EventManager::trigger("__frame_init__");//框架初始化

            //清除缓存
            App::$debug && self::cleanCache();
            //路由
            [$__module, $__controller, $__action] = Route::rewrite();

            EventManager::trigger("__before_create_controller__");//框架初始化


            //模块检查
            Variables::set("__request_module__", $__module);
            Variables::set("__request_controller__", $__controller);

            Variables::set("__request_action__", $__action);

            if (!self::isAvailableClassname($__module))
                Error::err("模块 '$__module' 命名不符合规范!", [], "Module");


            if (!is_dir(Variables::getControllerPath($__module))) {
                Error::err("模块 '$__module' 不存在!", [], "Module");
            }



            // 控制器检查
            if (strtolower($__controller) === 'basecontroller')
                Error::err("基类 'BaseController' 不允许被访问！", [], "Controller");

            if (!self::isAvailableClassname($__controller))
                Error::err("控制器 '".htmlspecialchars($__controller)."' 命名不符合规范!", [], "Controller");

            $controller_name = ucfirst($__controller);

            $controller_class = 'app\\controller\\' . $__module . '\\' . $controller_name;



            if (!class_exists($controller_class, true)) {
                Error::err("模块 ( $__module )  控制器 ( $controller_name ) 不存在!", [],"Controller");
            }



            $method = method_exists($controller_class, $__action);
            if (!$method) {

                Error::err("模块 ( $__module )  控制器 ( $controller_name ) 中的方法 ( $__action ) 不存在!", [],"Action");
            }


            if (!in_array($__action, get_class_methods($controller_class))) {
                Error::err("模块 ( $__module ) 控制器 ( $controller_name ) 中的方法 ( $__action ) 为私有方法，禁止访问!", [],"Action");
            }

            /**
             * @var $controller_obj Controller
             */

            $controller_obj = new $controller_class($__module, $__controller, $__action);

            $result = $controller_obj->$__action();
            if ($result !== null)
                (new Response())->render($result, $controller_obj->getCode(), $controller_obj->getContentType())->send();
        }  catch (ExitApp $exit_app) {//执行退出
            App::$debug && Log::record("Frame", sprintf("框架执行退出: %s", $exit_app->getMessage()));
        } finally {
            Error::appShutdown();
            self::$app && self::$app->onRequestEnd();
            if (App::$debug) {
                Log::record("Frame", "框架响应结束...");
                $t = round((microtime(true) - Variables::get("__frame_start__", 0)) * 1000, 2);
                Log::record("Frame", sprintf("会话运行时间：%s 毫秒", $t),Log::TYPE_WARNING);
                if ($t > 100) {
                    Log::record("Frame", sprintf("优化提醒：您的当前应用会话处理用时（%s毫秒）超过 100 毫秒，建议对代码进行优化以获得更好的使用体验。", $t),Log::TYPE_WARNING);
                }
            }

        }
    }


    /**
     * 清除缓存文件
     * @return void
     */
    static function cleanCache()
    {
        //清除opcache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * 检查命名规范
     * @param $__module
     * @return bool
     */
    private
    static function isAvailableClassname($__module): bool
    {
        return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $__module);
    }

    /**
     * 获取渲染引擎
     * @return JsonEngine|ResponseEngine|ViewEngine|null
     */
    public static function getEngine()
    {
        //如果之前没有设置输出引擎，则启用文档引擎
        !self::$engine && self::setDefaultEngine(new ViewEngine());
        return self::$engine;
    }

    /**
     * 设置默认引擎
     * @param $engine ResponseEngine
     * @return void
     */
    static function setDefaultEngine(ResponseEngine $engine)
    {
        self::$engine = $engine;
        self::$engine->setByDefault();
    }

    /**
     * 退出会话
     * @param $msg
     * @param bool $output 直接输出
     * @return void
     * @throws ExitApp
     */
    static function exit($msg, bool $output = false)
    {
        if (self::$exit) return; //防止一个会话中重复抛出exit异常
        self::$exit = true;
        if($output)echo $msg;
        throw new ExitApp($msg);
    }



}