<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: library\cache
 * Class RedisException
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 21:55
 * Description :
 */

namespace library\redis;

use Exception;

class RedisCacheException extends Exception
{
    public function __construct($message = "")
    {
        //App::$debug && Log::record("Redis", sprintf("Redis异常：%s", $message),Log::TYPE_ERROR);

        parent::__construct($message);
    }


}