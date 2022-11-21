<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\websocket
 * Class WebSocketServer
 * Created By ankio.
 * Date : 2022/11/19
 * Time : 22:32
 * Description :
 */

namespace library\websocket;

use core\App;
use core\cache\Cache;
use core\config\Config;
use core\event\EventListener;
use core\file\Log;


class WebSocket implements EventListener
{
    /**
     * 启动Websocket
     * @return void
     * @throws WebsocketException
     */
    static function start(){

        if(empty(Cache::init(20)->get("websocket"))){//没有锁定，请求保持锁定
            App::$debug && Log::record("Websocket","WebSocket进程未锁定，下发任务");
            go(function () {
                $websocket = new WS(Config::getConfig("websocket")["ip"], Config::getConfig("websocket")["port"], App::$debug, self::$WSEvent);
                $websocket->run();
                Cache::init()->del("websocket");
            },0);
        }else{
            App::$debug && Log::record("Websocket","WebSocket进程锁定，不处理定时任务");
        }

    }

    /**
     * 停止Websocket
     * @return void
     */
    static function stop(){
        Cache::init()->del("websocket");
    }

    private static ?WSEvent $WSEvent = null;
    /**
     * 设置默认的事件处理器
     * @param WSEvent $WSEvent 事件处理器，需要实现{@link WSEvent}
     * @return void
     */
    static function setDefaultEventHandler(WSEvent $WSEvent){
        self::$WSEvent = $WSEvent;
    }


    /**
     * 事件响应
     * @param string $event
     * @param mixed $data
     * @return void
     */
    public function handleEvent(string $event, &$data)
    {
        try {
            WebSocket::start();
        } catch (WebsocketException $e) {
            Log::recordFile("Websocket",$e->getMessage());
        }
    }
}