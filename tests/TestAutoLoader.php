<?php

/**
use core\App;
use core\base\Loader;
use core\config\Config;

const DS = DIRECTORY_SEPARATOR;//定义斜杠符号
define('APP_DIR', dirname(__FILE__, 2).DS."src");//定义运行根目录
const APP_CORE = APP_DIR . DS . 'core' . DS;//定义程序的核心目录
include_once APP_CORE.DS."helper.php";//载入内置助手函数
include_once APP_CORE."base".DS."Variables.php";// 加载变量
include_once APP_CORE."base".DS."Loader.php";// 加载自动加载器
Loader::register();
Config::register();// 加载配置文件
\core\base\Error::register();
spl_autoload_register(function (string $raw){
    $real_class = str_replace("\\", DS, $raw) . ".php";
    //拼接类名文件
    $file = __DIR__ . DS . $real_class;
    //存在就加载
    if (file_exists($file)) {
        include_once $file;
    }
}, true, true);
App::$debug = true;
 **/

require_once __DIR__.DIRECTORY_SEPARATOR."../src/public/index.php";