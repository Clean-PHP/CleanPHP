<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\websocket
 * Class WebsocketException
 * Created By ankio.
 * Date : 2022/11/20
 * Time : 11:46
 * Description :
 */

namespace library\websocket;

use core\file\Log;
use Throwable;

class WebsocketException extends \Exception
{
    public function __construct($message = "")
    {
        Log::record("Websocket",$message,Log::TYPE_ERROR);
        parent::__construct($message, 0, null);
    }
}