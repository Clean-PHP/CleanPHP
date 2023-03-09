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
use core\base\Variables;
use core\cache\Cache;
use core\config\Config;
use core\event\EventListener;
use core\event\EventManager;
use core\file\Log;
use library\websocket\main\Server;


class WebSocket implements EventListener
{
    /**
     * 启动Websocket
     * @return void
     * @throws WebsocketException
     */
    static function start(){

        //加锁
        if(!self::isLock(Config::getConfig("websocket")["port"]))
        {

            App::$debug && Log::record("Websocket","WebSocket进程未锁定，下发任务",Log::TYPE_WARNING);
            go(function (){
                EventManager::trigger('__on_start_websocket__');
                Variables::set("__frame_log_tag__", "ws_");
                $websocket = new Server(Config::getConfig("websocket")["ip"], Config::getConfig("websocket")["port"], App::$debug, self::$WSEvent);
                $websocket->run();
            },0);
        }
        else
        {
            App::$debug && Log::record("Websocket","WebSocket进程锁定，不处理定时任务",Log::TYPE_WARNING);
        }

    }

    static function isLock($port): bool
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($sock);
        socket_connect($sock,'127.0.0.1', $port);
        socket_set_block($sock);
        $r = array($sock);
        $w = array($sock);
        $f = array($sock);
        $return = @socket_select($r , $w,$f , 3);
        socket_close($sock);
        return $return !== 2;

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
            Log::record("Websocket", $e->getMessage(), Log::TYPE_ERROR);
        }
    }
}