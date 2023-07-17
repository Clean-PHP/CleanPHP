<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * File autoload.php
 * Created By ankio.
 * Date : 2022/11/26
 * Time : 15:46
 * Description :
 */

use cleanphp\App;
use cleanphp\base\Config;
use cleanphp\base\EventManager;
use cleanphp\base\Request;
use cleanphp\base\Response;
use cleanphp\base\Variables;
use cleanphp\cache\Cache;
use cleanphp\engine\EngineManager;
use cleanphp\file\Log;
use library\waf\Ip;

if (Config::getConfig("waf")) {
    EventManager::addListener("__frame_init__", function ($event, &$data) {
        $ip = Request::getClientIP();
        $cache = Cache::init(24 * 3600, Variables::getCachePath("waf", DS));
        /**
         * @var $ip_info Ip
         */
        $ip_info = $cache->get($ip);
        if (!$ip_info)
            $ip_info = new Ip($ip);
        $result = $ip_info->record();//返回是否需要阻断
        if (App::$debug) {
            Log::record("WAF", "当前IP状态:" . print_r($ip_info, true));
        }
        $cache->set($ip, $ip_info);
        if ($result) {
            EventManager::trigger("__waf_on_deny__", $ip_info);
            (new Response())
                ->render(EngineManager::getEngine()->renderMsg(403, "Oops!", $ip_info->reason, 0, "https://baidu.com"))
                ->code(403)
                ->send();
        }
    });
    //监听视图输出，补充js脚本调用
}
