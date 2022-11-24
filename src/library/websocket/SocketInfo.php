<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\websocket
 * Class SocketInfo
 * Created By ankio.
 * Date : 2022/11/23
 * Time : 23:28
 * Description :
 */

namespace library\websocket;

use core\objects\ArgObject;

class SocketInfo extends ArgObject
{
    public  $resource = null;
    public bool $handshake = false;
    public string $ip = '';
    public int $port = 0;
    public $data = null;
}