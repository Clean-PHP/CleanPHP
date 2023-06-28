<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: extend\websocket
 * Class WSEvent
 * Created By ankio.
 * Date : 2022/8/8
 * Time : 17:46
 * Description :
 */

namespace library\websocket;

use library\websocket\main\Server;

interface WSEvent
{
    /**
     * 当Websocket链接上的时候
     * @param Server $ws
     * @param SocketInfo $socket
     * @return mixed
     */
    public function onConnect(Server $ws,  SocketInfo &$socket): mixed;

    /**
     * 当websocket关闭的时候
     * @param Server $ws
     * @param SocketInfo $socket
     * @return mixed
     */
    public function onClose(Server $ws,  SocketInfo &$socket): mixed;

    /**
     * 当收到websocket信息的时候
     * @param Server $ws
     * @param string|null $msg
     * @param SocketInfo $socket
     * @return mixed
     */
    public function onMsg(Server $ws,?string $msg,   SocketInfo &$socket): mixed;
}