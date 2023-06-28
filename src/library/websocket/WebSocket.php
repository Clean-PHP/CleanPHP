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

use cleanphp\App;
use cleanphp\base\Config;
use cleanphp\base\EventManager;
use cleanphp\base\Variables;
use cleanphp\cache\Cache;
use cleanphp\exception\NoticeException;
use cleanphp\exception\WarningException;
use cleanphp\file\Log;
use library\websocket\main\Server;


class WebSocket
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
        $timeout = 1; // 连接超时时间（秒）
        try {
            $socket = @fsockopen("127.0.0.1", $port, $errno, $errstr, $timeout);
            if ($socket) {
                fclose($socket);
                return true; // 端口已被占用
            } else {
                return false; // 端口未被占用
            }
        } catch (NoticeException|WarningException $exception) {
            return true; // 端口已被占用
        }

    }

    /**
     * 停止Websocket
     * @return void
     */
    static function stop(): void
    {
        Cache::init()->del("websocket");
    }

    private static ?WSEvent $WSEvent = null;

    /**
     * 设置默认的事件处理器
     * @param WSEvent $WSEvent 事件处理器，需要实现{@link WSEvent}
     * @return void
     */
    static function setDefaultEventHandler(WSEvent $WSEvent): void
    {
        self::$WSEvent = $WSEvent;
    }


}