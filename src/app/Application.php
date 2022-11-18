<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: app
 * Class Application
 * Created By ankio.
 * Date : 2022/11/9
 * Time : 15:17
 * Description :
 */

namespace app;


use core\App;
use core\cache\Cache;
use core\engine\JsonEngine;
use core\file\Log;
use core\base\MainApp;
use library\redis\RedisCache;

class Application implements MainApp
{

    function onRequestArrive()
    {
        App::$debug && Log::record("App", "Application响应开始");

        App::setDefaultEngine(new JsonEngine(["code" => 0, "msg" => "OK", "data" => null, "count" => 0]));
        Cache::setDriver(new RedisCache());
        ///一般在此处注册事件监听
    }

    function onRequestEnd()
    {
        App::$debug && Log::record("App", "Application响应结束");
    }
}