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



    static function isLock($port): bool {
       try{
           $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
           if ($socket === false) {
               return false; // 无法创建socket
           }
           // 设置连接超时时间为0，即立即返回
           socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 0, 'usec' => 0]);
           socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 0, 'usec' => 0]);
           // 尝试绑定端口
           $result = socket_bind($socket, '127.0.0.1', $port);
           // 关闭socket
           socket_close($socket);
           return $result === false && socket_last_error() === SOCKET_EADDRINUSE;

       }catch (WarningException $exception){
          return false;
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