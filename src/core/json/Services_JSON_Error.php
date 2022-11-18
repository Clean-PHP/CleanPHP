<?php

namespace core\json;

use core\file\Log;
use Exception;

class Services_JSON_Error extends Exception
{
    function __construct($message = 'unknown error')
    {
        Log::record("JSON", lang("JSON数据解析错误：%s", $message));
        parent::__construct($message, 0, null);
    }
}