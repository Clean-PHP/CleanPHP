<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\waf
 * Class Waf
 * Created By ankio.
 * Date : 2023/1/1
 * Time : 15:05
 * Description :
 */

namespace library\waf;

use core\App;
use core\base\Request;
use core\base\Response;
use core\base\Variables;
use core\cache\Cache;
use core\event\EventListener;
use core\file\Log;

class Waf implements EventListener
{

    public function handleEvent(string $event, &$data)
    {
        $ip = Request::getClientIP();
        $cache = Cache::init(24*3600,Variables::getCachePath("waf",DS));
        /**
         * @var $ip_info Ip
         */
        $ip_info = $cache->get($ip);
        if(!$ip_info)
            $ip_info = new Ip($ip);
        $result = $ip_info->record();//返回是否需要阻断
        if(App::$debug){
            Log::record("WAF","当前IP状态:".print_r($ip_info,true));
        }
        $cache->set($ip,$ip_info);
        if($result){
            (new Response())->render(App::getEngine()->renderMsg(true,403,$ip_info->reason,$ip_info->reason,0,"https://baidu.com"))->send();
         //   App::exit("WAF");
        }
    }
}