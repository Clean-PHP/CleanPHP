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

interface WSEvent
{
    /**
     * 当Websocket链接上的时候
     * @param WS $ws
     * @param SocketInfo $socket
     * @return mixed
     */
    public function onConnect(WS $ws,  SocketInfo &$socket);

    /**
     * 当websocket关闭的时候
     * @return mixed
     */
    public function onClose(WS $ws,  SocketInfo &$socket);

    /**
     * 当收到websocket信息的时候
     * @param WS $ws
     * @param string|null $msg
     * @param SocketInfo $socket
     * @return mixed
     */
    public function onMsg(WS $ws,?string $msg,   SocketInfo &$socket);
}